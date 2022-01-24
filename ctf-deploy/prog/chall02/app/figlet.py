# python -m pip install pyfiglet
import pyfiglet, sys, time

import random, string
def id_generator(size=6, chars=string.ascii_uppercase):
  return ''.join(random.SystemRandom().choice(chars) for _ in range(size))

def main():
  level = 1
  try:
    challenge = id_generator().replace('0', 'O')
    print("Level", level)
    print(pyfiglet.figlet_format(challenge))
    start_time = time.time()
    for line in sys.stdin:
      if time.time() - start_time > 2:
        print("Timeout! Try harder!")
        return
      line = line.rstrip('\n').replace('0', 'O')
      if line != challenge:
        print("You lose! Try harder!")
        return
      if level > 20:
        print("You win! KMACTF{Tay_To_DE'y!}")
        return
      level += 1
      print("Level", level)
      challenge = id_generator().replace('0', 'O')
      print(pyfiglet.figlet_format(challenge))
      start_time = time.time()
  except:
    pass

main()