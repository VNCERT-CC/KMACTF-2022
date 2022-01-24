package main

// cc: https://github.com/smallnest/1m-go-tcp-server/blob/master/1_simple_tcp_server/server.go

import (
	"flag"
	"log"
	"net"
	"os"
	"os/exec"
	"sync/atomic"
	"time"
)

const cmd = "stdbuf"

var cmdArgs = []string{"-i0", "-o0", "-e0"}
var flagset = flag.NewFlagSet("OPTIONS", flag.ExitOnError)
var isStdinAsArg = flagset.Bool("stdin-as-argument", false, "Use stdin as an argument pass to the CMD")
var maxArgSize = flagset.Int("stdin-maxsize", 50*1024, "Maximum number of bytes stdin as argument")
var isPrintStderr = flagset.Bool("stderr-print", false, "Print stderr to connection")
var readTimeout = flagset.Duration("read-timeout", 5*time.Second, "Connection read timeout")
var runTimeout = flagset.Duration("run-timeout", 2*time.Minute, "Run timeout")
var workingDir = flagset.String("w", "/opt", "Working directory")
var bindAddr = flagset.String("b", ":9999", "Bind address")

func main() {
	for i, arg := range os.Args[1:] {
		if arg == "--" {
			flagset.Parse(os.Args[1 : i+1])
			cmdArgs = append(cmdArgs, os.Args[i+2:]...)
		}
	}
	if !flagset.Parsed() || len(cmdArgs) == 3 {
		os.Stderr.Write([]byte(`Usage: ` + os.Args[0] + " [OPTIONS] -- CMD [ARGUMENTS...]\n"))
		flagset.PrintDefaults()
		os.Stderr.Write([]byte(`Example: ` + os.Args[0] + " -w /opt -b :9999 -- cat /etc/passwd\n"))
		os.Exit(1)
	}

	err := os.Chdir(*workingDir)
	if err != nil {
		log.Panicln(err)
	}

	ln, err := net.Listen("tcp", *bindAddr)
	if err != nil {
		log.Panicln(err)
	}
	log.Println("Listening", *bindAddr)

	for {
		conn, e := ln.Accept()
		if e != nil {
			if ne, ok := e.(net.Error); ok && ne.Temporary() {
				log.Println("Accept temp err:", ne)
				continue
			}
			log.Println("Accept err:", e)
			return
		}
		if *isStdinAsArg {
			go proxyStdinArg(conn)
		} else {
			go proxyStdin(conn)
		}
	}
}

func proxyStdinArg(conn net.Conn) {
	defer func() {
		time.Sleep(time.Second)
		conn.Close()
	}()
	conn.Write([]byte("We take your input to pass to CMD as an argument. We accept binary formats like \xff, except null-character (\\x00). We will wait " + readTimeout.String() + " for you to finish typing.\r\n"))
	lenData := 0
	data := make([]byte, *maxArgSize)
	data = data[:0]
	buf := make([]byte, 1024)
	conn.SetReadDeadline(time.Now().Add(*readTimeout + time.Second))
	for {
		n, err := conn.Read(buf)
		if n != 0 {
			if n >= *maxArgSize-lenData {
				buf = buf[:*maxArgSize-lenData]
				data = append(data, buf...)
				break
			}
			buf = buf[:n]
			lenData += n
			data = append(data, buf...)
		}
		if err != nil {
			// log.Println("Read remote:", err)
			break
		}
	}
	newArgs := append(cmdArgs, string(data))
	cmd := exec.Command(cmd, newArgs...)
	cmd.Stdout = conn
	if *isPrintStderr {
		cmd.Stderr = conn
	} else {
		cmd.Stderr = os.Stderr
	}
	err := cmd.Start()
	if err != nil {
		log.Println("Run cmd:", err)
	}
	defer cmd.Process.Kill()
	doneChan := make(chan struct{})
	go func() {
		cmd.Wait()
		close(doneChan)
	}()
	select {
	case <-time.After(*runTimeout):
		cmd.Process.Kill()
		conn.SetWriteDeadline(time.Now().Add(*readTimeout))
		conn.Write([]byte("Timeout\n"))
	case <-doneChan:
	}
}

func proxyStdin(conn net.Conn) {
	defer func() {
		time.Sleep(time.Second)
		conn.Close()
	}()

	var doneFlag int32 = 1
	doneChan := make(chan struct{})

	cmd := exec.Command(cmd, cmdArgs...)
log.Println(cmdArgs)
	stdout, err := cmd.StdoutPipe()
	if err != nil {
		log.Println("Stdout:", err)
		return
	}
	stdin, err := cmd.StdinPipe()
	if err != nil {
		log.Println("Stdin:", err)
		return
	}
	if *isPrintStderr {
		cmd.Stderr = conn
	} else {
		cmd.Stderr = os.Stderr
	}

	go func() {
		defer func() {
			if atomic.CompareAndSwapInt32(&doneFlag, 1, 0) {
				close(doneChan)
			}
		}()
		buf := make([]byte, 1024)
		for {
			n1, err := stdout.Read(buf)
log.Println(string(buf[:n1]))
			if n1 > 0 {
				conn.SetWriteDeadline(time.Now().Add(*readTimeout))
				n2, err := conn.Write(buf[:n1])
				if n2 != n1 || err != nil {
					return
				}
			}
			if err != nil {
				return
			}
		}
	}()

	go func() {
		defer func() {
			if atomic.CompareAndSwapInt32(&doneFlag, 1, 0) {
				close(doneChan)
			}
		}()
		buf := make([]byte, 1024)
		for {
			conn.SetReadDeadline(time.Now().Add(*readTimeout))
			n1, err := conn.Read(buf)
			if n1 > 0 {
				_, err := stdin.Write(buf[:n1])
				if err != nil {
					return
				}
			}
			if err != nil {
				return
			}
		}
	}()

	err = cmd.Start()
	if err != nil {
		log.Println("Run cmd:", err)
		return
	}
	defer cmd.Process.Kill()

	select {
	case <-time.After(*runTimeout):
		conn.SetWriteDeadline(time.Now().Add(*readTimeout))
		conn.Write([]byte("Timeout\n"))
	case <-doneChan:
	}
}
