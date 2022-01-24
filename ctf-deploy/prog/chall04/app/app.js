const http = require('http')
const fs = require('fs')

const { WebSocketServer } = require('ws')
const createReportDocx = require('docx-templates').default

const templateFile = 'template.docx'

const server = http.createServer((req, res) => {
  res.writeHead(200, { 'content-type': 'text/html;charset=utf-8' })
  res.end('Hello, please connect to websocket ws://<script>document.write(location.host)</script>/')
})
server.listen(9999, () => console.log('Server running..'))
const wss = new WebSocketServer({
  server,
  perMessageDeflate: {
    zlibDeflateOptions: {
      // See zlib defaults.
      chunkSize: 1024,
      memLevel: 7,
      level: 3
    },
    zlibInflateOptions: {
      chunkSize: 10 * 1024
    },
    // Other options settable:
    clientNoContextTakeover: true, // Defaults to negotiated value.
    serverNoContextTakeover: true, // Defaults to negotiated value.
    serverMaxWindowBits: 10, // Defaults to negotiated value.
    // Below options specified as default values.
    concurrencyLimit: 10, // Limits zlib concurrency for perf.
    threshold: 1024 // Size (in bytes) below which messages
    // should not be compressed if context takeover is disabled.
  }
})

wss.on('connection', ws => {
  let lastChallenge = getChallenge()
  let lastTime
  let level = 0
  let done = false
  ws.on('error', e => console.error(e))
  ws.on('message', d => {
    if (done) return
    if (d.toString() == lastChallenge && Date.now() - lastTime < 3000) {
      level++
      if (level > 300) {
        done = true
        ws.send('Flag: KMACTF{Sur3_y0u_c4n_re4d_d0cx}', e => ws.close())
        return
      }
      lastChallenge = getChallenge()
      getDocx(lastChallenge).then(buf => ws.send(buf, e => {
        if (e) return ws.close()
        lastTime = Date.now()
      })).catch(e => {
        console.error(e)
        done = true
        ws.send('Error', e => ws.close())
      })
      return
    }
    level = 0
    done = true
    ws.send('Wrong or timeout!', e => {
      ws.close()
    })
  })
  getDocx(lastChallenge).then(buf => ws.send(buf, e => {
    if (e) return ws.close()
    lastTime = Date.now()
  })).catch(e => {
    console.error(e)
    done = true
    ws.send('Error', e => ws.close())
  })
})

wss.on('error', e => console.error(e))

function getChallenge() {
  const length = 7;
  const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  let result = '';
  const charactersLength = characters.length;
  for (let i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() *
      charactersLength));
  }
  return result;
}

async function getDocx(challenge) {
  const buf = await createReportDocx({
    noSandbox: false,
    template: fs.readFileSync(templateFile),
    data: {
      challenge,
    },
    additionalJsContext: {
      getChallenge: () => Promise.resolve(challenge),
    }
  })
  return buf
}
