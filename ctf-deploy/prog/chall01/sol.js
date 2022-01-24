const net = require('net')
const sock = net.connect({
  host: '103.28.172.12',
  port: 1111
}, () => {
  console.log('Connected')
})

let quest = '', cont = false
function resolveQuest(quest) {
  let ans = quest.split(' ')
  ans.sort((a, b) => BigInt(a) > BigInt(b) ? 1 : -1)
  ans = ans.join(' ')
  // console.log(ans)
  sock.write('[' + ans + ']\n')
}
sock.on('data', d => {
  quest += d.toString()
  console.error(d.toString())
  let idx = quest.lastIndexOf('Please sort these values:\n[')
  if (~idx) {
    cont = false
    quest = quest.substr(idx + 27)
    idx = quest.indexOf(']')
    if (~idx) {
      quest = quest.substr(0, idx)
      // console.log('`', quest, '`')
      resolveQuest(quest)
    } else {
      cont = true
    }
  } else if (cont) {
    idx = quest.indexOf(']')
    if (~idx) {
      cont = false
      quest = quest.substr(0, idx)
      // console.log('`', quest, '`')
      resolveQuest(quest)
    } else {
      cont = true
    }
  }
})

sock.on('close', () => {
  console.log('Closed')
})
