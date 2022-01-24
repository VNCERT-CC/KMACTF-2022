import time
import mysql.connector
import random
mydb = mysql.connector.connect(
  host="localhost",
  user="user",
  password="user_password",
  database="default_db",
  unix_socket="/var/run/mysqld/mysqld.sock"
)
mycursor = mydb.cursor()
def job():
    print("I'm working...")
    ticket=random.randint(1,9999)
    #ticket=30000
    print("WIN ticket: "+str(ticket))
    sql = "INSERT INTO win (ticket) VALUES ("+str(ticket)+")"
    mycursor.execute(sql)
    mydb.commit()
    id=mycursor.lastrowid
    if id>3:
        mycursor.execute("DELETE FROM ticket WHERE round = "+ str(id-2))
        mydb.commit()
        
    time.sleep(180)
    print("Tra thuong ki" + str(id))
    mycursor.execute("SELECT * FROM ticket where number = "+str(ticket)+" AND id_user IS NOT NULL AND round="+ str(id))
    myresult = mycursor.fetchall()
    mydb.commit()
    for i in range(len(myresult)):
        mycursor.execute("SELECT * FROM users where id = "+str(myresult[i][3]))
        myresult2 = mycursor.fetchall()
        
        amount=myresult2[0][3]+int(1000000000/len(myresult))
        #val = (amount, myresult[i][3])
        print("Winner: "+ myresult2[0][1]+ " = "+ str(amount))
        mycursor.execute("UPDATE users SET amount = "+str(amount)+" WHERE id = "+str(myresult[i][3]))
        mydb.commit()

        

while True:
    time.sleep(120)
    job()
