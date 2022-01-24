package main

import (
	"bufio"
	"bytes"
	"context"
	"crypto/hmac"
	"crypto/sha256"
	"crypto/tls"
	"encoding/hex"
	"flag"
	"io"
	"log"
	"math/rand"
	"net"
	"strconv"
	"strings"
	"time"

	"github.com/valyala/fasthttp"
)

var secret = []byte("vinhjaxt | grep done")
var connectAddr = flag.String("connect-addr", "192.168.1.7:9998", "Connect address")

func main() {
	flag.Parse()

	go servePeer2()
	ln, err := net.Listen("tcp", ":9999")
	if err != nil {
		log.Panicln(err)
	}
	log.Println("Running..")

	host, _, err := splitHostnameDefaultPort(*connectAddr, "443")
	if err != nil {
		log.Panicln(err)
	}
	tlsClientConfig := &tls.Config{
		InsecureSkipVerify: true,
		ServerName:         host,
	}

	// Main challenge server
	for {
		conn, err := ln.Accept()
		if err != nil {
			log.Println(err)
			continue
		}
		// Client server act as http proxy server
		go proxyConnect(conn, tlsClientConfig)
	}
}

// Verify server
func servePeer2() {
	ln, err := net.Listen("tcp", ":9998")
	if err != nil {
		log.Panicln(err)
	}
	// openssl req -x509 -newkey rsa:4096 -sha256 -days 3650 -nodes -keyout server.key -out server.cert -subj "/CN=localhost" -addext "subjectAltName=DNS:localhost,DNS:local.host,IP:10.10.10.1"
	tlsServerConfig := &tls.Config{
		MinVersion:               tls.VersionTLS12,
		CurvePreferences:         []tls.CurveID{tls.X25519, tls.CurveP521, tls.CurveP384, tls.CurveP256},
		PreferServerCipherSuites: true,
		CipherSuites: []uint16{
			tls.TLS_RSA_WITH_AES_128_GCM_SHA256,
			tls.TLS_RSA_WITH_AES_256_GCM_SHA384,
			tls.TLS_ECDHE_RSA_WITH_AES_128_GCM_SHA256,
			tls.TLS_ECDHE_ECDSA_WITH_AES_128_GCM_SHA256,
			tls.TLS_ECDHE_RSA_WITH_AES_256_GCM_SHA384,
			tls.TLS_ECDHE_ECDSA_WITH_AES_256_GCM_SHA384,
			tls.TLS_ECDHE_RSA_WITH_CHACHA20_POLY1305_SHA256,
			tls.TLS_ECDHE_ECDSA_WITH_CHACHA20_POLY1305_SHA256,
			tls.TLS_ECDHE_ECDSA_WITH_CHACHA20_POLY1305,
			tls.TLS_ECDHE_RSA_WITH_CHACHA20_POLY1305,
		},
	}

	cert, err := tls.LoadX509KeyPair("server.cert", "server.key")
	if err != nil {
		log.Panicln(err)
	}

	tlsServerConfig.Certificates = append(tlsServerConfig.Certificates, cert)

	tlsServerConfig.BuildNameToCertificate()

	for {
		conn, err := ln.Accept()
		if err != nil {
			log.Println(err)
			continue
		}
		go handlePeer2(conn, tlsServerConfig)
	}
}

// Handle verify request
func handlePeer2(conn net.Conn, tlsServerConfig *tls.Config) {
	defer func() {
		time.Sleep(time.Second)
		conn.Close()
	}()
	tlsConn := tls.Server(conn, tlsServerConfig)
	ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	conn.SetDeadline(time.Time{})
	err := tlsConn.HandshakeContext(ctx)
	cancel()
	if err != nil {
		conn.SetWriteDeadline(time.Now().Add(3 * time.Second))
		conn.Write([]byte("\n\nHandshake error: " + err.Error() + "\n\n"))
		return
	}
	// log.Println("Handshake OK")

	scanner := bufio.NewScanner(tlsConn)
	for {

		tlsConn.SetReadDeadline(time.Now().Add(3 * time.Second))
		if !scanner.Scan() {
			return
		}
		text := strings.Trim(scanner.Text(), "\n")

		h := hmac.New(sha256.New, secret)
		h.Write([]byte(text))

		tlsConn.SetWriteDeadline(time.Now().Add(3 * time.Second))
		tlsConn.Write([]byte(hex.EncodeToString(h.Sum(nil))))
		tlsConn.Write([]byte{'\n'})
	}

}

// Read http 1.0, http 1.1 header
func readConnectHeader(conn net.Conn) (header []byte, err error) {
	buf := make([]byte, 1024)
	for {
		var n int
		conn.SetReadDeadline(time.Now().Add(3 * time.Second))
		n, err = conn.Read(buf)
		if n > 0 {
			header = append(header, buf[:n]...)
			if idx := bytes.Index(header, []byte("\r\n\r\n")); idx != -1 {
				header = header[:idx+4]
				return
			}
		}
		if err != nil {
			return
		}
	}
}

// Handle client as http proxy server
func proxyConnect(conn net.Conn, tlsClientConfig *tls.Config) {
	defer func() {
		time.Sleep(time.Second)
		conn.Close()
	}()
	var err error

	connBuf := bufio.NewReader(conn)

	// Test HTTP request
	conn.SetWriteDeadline(time.Now().Add(3 * time.Second))
	_, err = conn.Write([]byte("GET http://xn--lun-lna.vn/cdn-cgi/trace HTTP/1.1\r\nHost: xn--lun-lna.vn\r\nUser-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:97.0) Gecko/20100101 Firefox/97.0\r\nAccept: */*\r\nProxy-Connection: Keep-Alive\r\n\r\n"))
	if err != nil {
		return
	}

	resp := fasthttp.AcquireResponse()
	defer fasthttp.ReleaseResponse(resp)

	conn.SetReadDeadline(time.Now().Add(5 * time.Second))
	err = resp.Read(connBuf)
	if err != nil {
		if err != io.EOF {
			log.Println(err)
			conn.SetWriteDeadline(time.Now().Add(3 * time.Second))
			conn.Write([]byte("\n\nRead response error\n\n"))
		}
		return
	}

	// Verify HTTP response
	body := string(resp.Body())
	if !strings.Contains(body, "h=xn--lun-lna.vn") {
		conn.SetWriteDeadline(time.Now().Add(3 * time.Second))
		conn.Write([]byte("\n\nResponse verify error\n\n"))
		return
	}

	// Connect verify server using TLS
	conn.SetWriteDeadline(time.Now().Add(3 * time.Second))
	_, err = conn.Write([]byte("CONNECT " + *connectAddr + " HTTP/1.1\r\nUser-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:97.0) Gecko/20100101 Firefox/97.0\r\nProxy-Connection: keep-alive\r\nConnection: keep-alive\r\nHost: " + *connectAddr + "\r\n\r\n"))
	if err != nil {
		return
	}

	// Connect response
	header, err := readConnectHeader(conn)
	if err != nil {
		conn.SetWriteDeadline(time.Now().Add(3 * time.Second))
		conn.Write([]byte("\n\nConnect response error\n\n"))
		return
	}

	// Verify response
	if !(bytes.HasPrefix(header, []byte("HTTP/1.0 200 ")) || bytes.HasPrefix(header, []byte("HTTP/1.1 200 "))) {
		conn.SetWriteDeadline(time.Now().Add(3 * time.Second))
		conn.Write([]byte("\n\nConnect verify error\n\n"))
		return
	}

	// Test tls connection
	handlePeer(conn, tlsClientConfig)

}

// Test tls connection
func handlePeer(conn net.Conn, tlsClientConfig *tls.Config) {
	// Handshake
	tlsConn := tls.Client(conn, tlsClientConfig)
	ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	conn.SetDeadline(time.Time{})
	err := tlsConn.HandshakeContext(ctx)
	cancel()
	if err != nil {
		conn.SetWriteDeadline(time.Now().Add(3 * time.Second))
		conn.Write([]byte("\n\nHandshake error: " + err.Error() + "\n\n"))
		return
	}

	scanner := bufio.NewScanner(tlsConn)
	// Challenges
	for i := 0; i < 1024; i++ {
		// Random string
		challenges := strconv.FormatInt(time.Now().UnixNano(), 10) + strconv.FormatUint(rand.Uint64(), 10)

		tlsConn.SetWriteDeadline(time.Now().Add(3 * time.Second))
		_, err = tlsConn.Write([]byte(challenges + "\n"))
		if err != nil {
			return
		}

		tlsConn.SetReadDeadline(time.Now().Add(3 * time.Second))
		if !scanner.Scan() {
			return
		}

		ans := strings.Trim(scanner.Text(), "\n")

		// Calc hashmac
		h := hmac.New(sha256.New, secret)
		h.Write([]byte(challenges))

		// Verify response
		if hex.EncodeToString(h.Sum(nil)) != ans {
			conn.SetWriteDeadline(time.Now().Add(3 * time.Second))
			conn.Write([]byte("\n\nPeer verify error\n\n"))
			return
		}
	}

	// Write flag over tls
	tlsConn.SetWriteDeadline(time.Now().Add(3 * time.Second))
	_, err = tlsConn.Write([]byte("\r\n\r\nFlag 2: KMACTF{Y0u_g0t_MitM_hehe}\r\n\r\n"))
	if err != nil {
		return
	}

	// Write flag over tcp
	conn.SetWriteDeadline(time.Now().Add(3 * time.Second))
	_, err = conn.Write([]byte("\r\n\r\nFlag: KMACTF{Pr0xy_1s_C0ol!!!x!!}\r\n\r\n"))
	if err != nil {
		return
	}

}

func splitHostnameDefaultPort(addr, defaultPort string) (string, string, error) {
	// no suitable address found => ipv6 can not dial to ipv4,..
	hostname, port, err := net.SplitHostPort(addr)
	if err != nil {
		if err1, ok := err.(*net.AddrError); ok && strings.Index(err1.Err, "missing port") != -1 {
			hostname, port, err = net.SplitHostPort(strings.TrimRight(addr, ":") + ":" + defaultPort)
		}
		if err != nil {
			return "", "", err
		}
	}
	if len(port) == 0 {
		port = defaultPort
	}
	return hostname, port, nil
}
