GOARCH=amd64 go build -o proxy-cmd-64
GOARCH=386 go build -o proxy-cmd-32
go-alpine go build -o proxy-cmd-alpine
