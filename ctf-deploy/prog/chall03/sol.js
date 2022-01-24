const net = require('net')
const chall = net.connect({
  host: '127.0.0.1',
  port: 9999
}, () => console.log('Chall Connected!'))
chall.on('close', () => {
  console.log('Chall Closed')
  process.exit(0)
})
chall.on('error', () => { })

/*
const server = net.Server({}, sock => {
  sock.on('error', () => {})
  handle(sock)
})
server.listen(8081)
//*/

let firstConnect = true
let proxy
function handle(chall) {
  chall.on('data', d => {
    console.log('>>', d.toString())
    if (firstConnect) {
      if (d.toString().startsWith('CONNECT ')) firstConnect = false
      proxy = net.connect({
        host: '127.0.0.1',
        port: 8080
      }, () => {
        console.log('Proxy Connected!')
      })
      proxy.on('close', () => console.log('Proxy Closed'))
      proxy.on('data', d => {
        console.log('<<', d.toString())
        chall.write(d)
      })
      proxy.on('error', () => { })
    }

    proxy.write(d)
  })
}

handle(chall)