to_format = open('toFormat.csv', 'r')
lines = to_format.read().split('\n')
lines.pop(0)
to_format.close()

addresses = []
clean_file = open('addresses.data', 'w')
for line in lines:
    if len(line) == 0:
        continue
    line_contents = line.split(',')
    address = ' '.join([word.capitalize() for word in line_contents[2].split()])
    address = address + ', Philadelphia, PA'
    addresses.append(address)

addresses = list(set(addresses))
clean_file.write('\n'.join(addresses))
clean_file.close()
