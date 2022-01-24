const { WebSocket } = require('ws')
const fs = require('fs')
const mammoth = require("mammoth")

const ws = new WebSocket('ws://172.30.15.42:1115/')
ws.on('open', () => console.log('ws opened'))
ws.on('close', () => console.log('ws closed'))
ws.on('error', () => console.error(e))

ws.on('message', d => {
  fs.writeFileSync('m.docx', d)
  mammoth.extractRawText({ path: "m.docx" }).then(r => {
    const text = r.value
    const m = /captcha sau:\s+([A-Z0-9]+)\s/.exec(text)
    console.log(m[1])
    ws.send(m[1])
  }).catch(e => {
    console.log(d.toString())
  }).done()
})
