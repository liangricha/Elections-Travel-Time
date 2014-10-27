to_format = open('toFormat.csv', 'r')
lines = to_format.read().split('\n')
lines.pop(0)
to_format.close()

clean_file = open('addresses.csv', 'w')
for line in lines:
    line_contents = line.split(',')
    address = line_contents[2].lower().title()
    address = address + ', Philadelphia, PA'
    clean_file.write(address + '\n')

clean_file.close()
