package main

import (
	"bytes"
	"crypto/rand"
	"fmt"
	"log"
	"math/big"
	"os"
	"sort"
)

func getChallenge(level int) (string, error) {
	// Max random value, a 130-bits integer, i.e 2^130 - 1
	max := new(big.Int)
	switch true {
	case level > 90:
		{
			max.Exp(big.NewInt(2), big.NewInt(16384), nil).Sub(max, big.NewInt(1))
			break
		}
	case level > 80:
		{
			max.Exp(big.NewInt(2), big.NewInt(8192), nil).Sub(max, big.NewInt(1))
			break
		}
	case level > 70:
		{
			max.Exp(big.NewInt(2), big.NewInt(4096), nil).Sub(max, big.NewInt(1))
			break
		}
	case level > 60:
		{
			max.Exp(big.NewInt(2), big.NewInt(2048), nil).Sub(max, big.NewInt(1))
			break
		}
	case level > 50:
		{
			max.Exp(big.NewInt(2), big.NewInt(1024), nil).Sub(max, big.NewInt(1))
			break
		}
	case level > 40:
		{
			max.Exp(big.NewInt(2), big.NewInt(512), nil).Sub(max, big.NewInt(1))
			break
		}
	case level > 30:
		{
			max.Exp(big.NewInt(2), big.NewInt(256), nil).Sub(max, big.NewInt(1))
			break
		}
	case level > 20:
		{
			max.Exp(big.NewInt(2), big.NewInt(128), nil).Sub(max, big.NewInt(1))
			break
		}
	case level > 10:
		{
			max.Exp(big.NewInt(2), big.NewInt(32), nil).Sub(max, big.NewInt(1))
			break
		}
	default:
		{
			max.Exp(big.NewInt(2), big.NewInt(8), nil).Sub(max, big.NewInt(1))
			break
		}
	}

	arr := make([]*big.Int, level+10)
	for i := 0; i < level+10; i++ {
		// Generate cryptographically strong pseudo-random between 0 - max
		n, err := rand.Int(rand.Reader, max)
		if err != nil {
			return "", err
		}
		arr[i] = n
	}

	fmt.Println(arr)

	sort.SliceStable(arr, func(i, j int) bool {
		return arr[i].Cmp(arr[j]) < 0
	})

	return fmt.Sprint(arr), nil
}

func readLine(buf []byte, lastBuf []byte) ([]byte, error) {
	for {
		n, err := os.Stdin.Read(buf)
		if err != nil {
			return nil, err
		}
		idx := bytes.IndexRune(buf[:n], '\n')
		if idx == -1 {
			lastBuf = append(lastBuf, buf[:n]...)
			continue
		}
		lastBuf = append(lastBuf, buf[:idx]...)

		lineBuf := make([]byte, len(lastBuf))
		lineBuf = lineBuf[:0]
		lineBuf = append(lineBuf, lastBuf...)
		lineBuf = bytes.Trim(lineBuf, "\r")

		lastBuf = lastBuf[:0]
		lastBuf = append(lastBuf, buf[idx+1:n]...)

		return lineBuf, nil
	}
}

func main() {
	buf := make([]byte, 1024)
	lastBuf := make([]byte, 1024*1024) // 1 MB
	lastBuf = lastBuf[:0]
	level := 0
	for {
		level += 1
		if level > 101 {
			fmt.Println("Done, here is your flag: KMACTF{F4st_n_Fur10us_BigInt}")
			return
		}
		fmt.Println("\n[ Level", level, "] Please sort these values:")
		ans, err := getChallenge(level)
		if err != nil {
			log.Println(err)
			fmt.Println("Error, please try again.")
			return
		}
		if level == 1 {
			fmt.Println("For example, here is the answer to go to the next level:")
			fmt.Println(ans)
			continue
		}
		answerBuf, err := readLine(buf, lastBuf)
		if err != nil {
			// log.Println(err)
			return
		}
		if len(answerBuf) == 0 {
			break
		}
		if !bytes.Equal(answerBuf, []byte(ans)) {
			break
		}
	}
	fmt.Println("You lose! Try harder!")
}
