# ctf-deploy
How to deploy challenges for a CTF event?

Làm thế nào để triển khai các challenges exploit cho một giải CTF?

# exploit challenges
## Vấn đề
- Vấn đề chính của việc này là tạo một proxy để map từ stdin/stdout <=> network, ngoài ra, buffering của stdout cũng khá quan trọng (mình đã mất khá nhiều thời gian cho khó khăn này, cuối cùng mình tìm thấy `stdbuf`). Cũng như crypto, việc scan challenges cũng không quan trọng lắm.
- Do đó, mình để public ports các challenges này và khi nào mở bài thi thì mới bật container

## Giải quyết
- Để giải quyết vấn đề về stdin/stdout <=> network, mình đã viết một proxy có sẵn, sử dụng stdbuf để proxy các challenge: [main.go](../proxy-cmd/main.go)
- Vậy thôi, rất đơn giản

# Chạy
Đây là 2 challenge demo 32-bit và 64-bit sử dụng ubuntu
- `./chall01/run.sh`
- `./chall02/run.sh`

# Truy cập
- `nc -v 127.0.0.1 4441`
- `nc -v 127.0.0.1 4442`
