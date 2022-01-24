# ctf-deploy
How to deploy challenges for a CTF event?

Làm thế nào để triển khai các challenges web cho một giải CTF?

# web challenges
## Vấn đề
- Khi deploy một hay nhiều web challenges, chúng ta thường sẽ có một lo lắng, đó là sợ người chơi scan hay đoán được các challenges mình chưa mở trên dashboard, gây mất công bằng cho các người chơi khác. Vì bình thường, các challenges sẽ được bật trước và được test kỹ càng xem có khai thác được không, chứ không nên đến đúng giờ mới bật challenge. Nhỉ? (Theo quan điểm cá nhân mình là vậy).
- Vậy khi mình deploy như nào, thì người chơi sẽ có thể scan được?
 + Mở public ports trên cùng 1 server, ví dụ: web01 0.0.0.0:8081, web02 0.0.0.0:8082,..
 + Đặt tên miền quá dễ đoán, ví dụ: web01.ctf-event.com, web02.ctf-event.com,..
 + Mở internal ports trên cùng 1 server, ví dụ: web01 127.0.0.1:8081, web02 127.0.0.1:8082,.. và dùng nginx làm reverse proxy, lúc này, user có thể scan mạng nội bộ của chúng ta

## Giải pháp
- Chúng ta chỉ cần 1 server để host tất cả các challenges của các mảng (web, exploit, crypto)
- Ở quan điểm cá nhân, mình đề xuất kiến trúc build các challenges web như này:
```
---- internet ----> tcp/80 nginx container (reverse proxy) ------ unix socket file ----> Web01 container
                            |
                            |------------------- unix socket file ----> Web02 container
                            .
                            .
```
- Mình nhận thấy là hầu hết, tất cả mọi web frameworks/languages đều cho phép listen bằng unix socket file
- Để khắc phục nhược điểm scan public ports: mình cho nginx làm reverse proxy và phân biệt các web challenges thông qua hostname
- Để khắc phục nhược điểm scan internal ports: mình sử dụng unix socket file thay thế, như vậy ta không cần phải mở port nào cả, và đường dẫn unix socket file thì mình chỉ mount cho nginx containter thôi. Do vậy web01 và web02 không scan nhau được. Có nhiều người đề xuất là: web01 và web02, mỗi con tạo 1 network riêng để không scan internal ports được, đúng, nhưng như vậy sử dụng nginx làm reverse proxy sẽ khó khăn trong việc config network. Với kiến trúc trên, tạo các network riêng cho từng challenges cũng rất dễ dàng, và nó cũng chẳng ảnh hưởng tới việc config nginx, vì ta đã config sử dụng unix socket file rồi.
- Đơn giản như vậy thôi, trừ khi nginx, docker hay kernel của chúng ta có lỗ hổng, không thì việc local attack khó có thể xảy ra được. Kiến trúc này mình thấy gọn nhẹ mà vẫn an toàn nhất, bạn đọc có kiến trúc nào đơn giản và an toàn hơn thì có thể tạo issue nhé <3

## Source code
Để dễ hiểu hơn, các bạn đọc source code nhé
Ở đây, mình demo 1 web chạy php và 1 web chạy nodejs nhớ <3

# Chạy
- `./nginx/run.sh`
- `./php-challenge/run.sh`
- `./node-challenge/run.sh`

# Truy cập (localhost)
- http://php-challenge.ctf-event.com/
- http://node-challenge.ctf-event.com/
