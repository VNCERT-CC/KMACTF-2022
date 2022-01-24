# python -m pip install pyfiglet pwntools
import pyfiglet, string, copy, json

print(pyfiglet.figlet_format('vinhjaxt'))

import random, string
def id_generator(size=6, chars=string.ascii_uppercase):
  return ''.join(random.SystemRandom().choice(chars) for _ in range(size))

chars = string.ascii_uppercase + string.digits

first_map = {}
last_map = {}
mid_map = {}

'''
 ----- d ------- x ⟶
|     _    _   _
|    / \  | | | |
h   / _ \ | |_| |
|  / ___ \|  _  |
| /_/   \_\_| |_|
|
y
🠗

Matrix[h][d] =
Matrix[y][x] = c
Matrix[0] = row1 Data
Matrix[1] = row2 Data
'''

def print_matrix(m):
  for row in m:
    for c in row:
      if c is None:
        print('x', end='')
      else:
        print(c, end='')
    print()

def lines2matrix(lines):
  lines = lines.strip('\n').split('\n')
  y = 0
  h = len(lines)
  matrix = [None] * h
  for line in lines:
    x = 0
    d = len(line)
    matrix[y] = [None] * d
    can_be_none = True
    for c in line:
      if c == ' ' and can_be_none:
        c = None
      else:
        can_be_none = False
      matrix[y][x] = c
      x +=1

    can_be_none = True
    i = d
    for c in line[::-1]:
      if c == ' ' and can_be_none:
        i-=1
        matrix[y][i] = None
      else:
        break

    y += 1
  return matrix
  
def compare_matrix(sub, full, idx=0):
  h = len(sub)
  d = len(sub[0])
  for y in range(h):
    for x in range(d):
      if sub[y][x] is None:
        continue
      if (not sub[y][x] == full[y][x + idx]):
        return False
  return True

def collect_2chars_matrix():
  for i in chars:
    d0 = len(lines2matrix(pyfiglet.figlet_format(i))[0])
    for j in chars:
      d1 = len(lines2matrix(pyfiglet.figlet_format(j))[0])
      key = i+j
      sub = lines2matrix(pyfiglet.figlet_format(key))

      first_matrix = copy.deepcopy(sub)
      last_matrix = copy.deepcopy(sub)

      for row in first_matrix:
        if j == '1':
          row.pop()
        else:
          for _ in range(d0+2, len(row)):
            row.pop()
      first_map[key] = first_matrix

      for row in last_matrix:
        if i == '1':
          row.pop(0)
        else:
          for _ in range(0, len(row)-d1-2):
            row.pop(0)
      last_map[key] = last_matrix

      for k in chars:
        mid_matrix = lines2matrix(pyfiglet.figlet_format(k+key))
        for row in mid_matrix:
          if j == '1':
            row.pop()
          else:
            for _ in range(0, 2):
              row.pop()
          if k == '1':
            row.pop(0)
          else:
            for _ in range(0, 2):
              row.pop(0)
        mid_map[k+key] = mid_matrix
  # Save json file
  with open('first_map.json', 'w') as f:
    json.dump(first_map, f)
  with open('mid_map.json', 'w') as f:
    json.dump(mid_map, f)
  with open('last_map.json', 'w') as f:
    json.dump(last_map, f)

def resolve_captcha(lines):
  full = lines2matrix(lines)
  ans = ''
  try:
    idx = -1
    maxIdx = len(full[0])
    while idx < maxIdx:
      found = False
      idx += 1
      if len(ans) == 0:
        for c in first_map:
          if compare_matrix(first_map[c], full, idx):
            ans += c[0]
            found = True
            break
        if found:
          continue
      if len(ans) == 5:
        for c in last_map:
          if compare_matrix(last_map[c], full, len(full[0]) - len(last_map[c][0])):
            ans += c[1]
            found = True
            break
        if found:
          continue
      if len(ans) != 0:
        for c in mid_map:
          if c[0] == ans[-1]:
            if compare_matrix(mid_map[c], full, idx):
              ans += c[1]
              found = True
              break
        if found:
          continue
  except:
    pass
  return ans

try:
  with open('first_map.json') as json_file:
      first_map = json.load(json_file)
  with open('mid_map.json') as json_file:
      mid_map = json.load(json_file)
  with open('last_map.json') as json_file:
      last_map = json.load(json_file)
except:
  collect_2chars_matrix()
  pass

def test():
  for i in range(100):
      challenge = id_generator().replace('0', 'O')
      # challenge = 'vinhjaxt'
      art = pyfiglet.figlet_format(challenge)
      print(art)
      ans = resolve_captcha(art)
      print(ans)
      if not ans == challenge:
        print("Wrong, challenge:", challenge)
        break

# test()

from pwn import *
def main():
  io = remote("172.30.15.42", 1112)
  while True:
    print(io.recvline().decode("utf-8") )
    challenge = io.recvuntil('\n\n').decode("utf-8") 
    print(challenge)
    ans = resolve_captcha(challenge)
    print(">>",ans)
    io.sendline(ans)

main()