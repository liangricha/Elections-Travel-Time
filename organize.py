import sys, getopt
from operator import itemgetter

info_file_name = ''
data_file_name = ''

if len(sys.argv) < 5 or sys.argv[1] != '-i' or sys.argv[3] != '-d':
    print 'Usage: organize.py -i <travel_info_file> -d <original_data_file>'
    sys.exit(2)

try:
    opts, args = getopt.getopt(sys.argv[1:], 'i:d:', ['ifile=', 'dfile='])
except getopt.GetoptError:
    print 'Usage: organize.py -i <travel_info_file> -d <original_data_file>'
    sys.exit(2)

for opt, arg in opts:
    if opt in ('-i', '--ifile'):
        info_file_name = arg
    elif opt in ('-d', '--dfile'):
        data_file_name = arg

data_file_contents = open(data_file_name, 'r').read().split('\n')
info_file_contents = open(info_file_name, 'r').read().split('\n')
consolidated_data = zip(data_file_contents, info_file_contents)

formatted_info = []
for original_address, api_info in consolidated_data:
    address_from_api, travel_time = api_info.split(' : ')[0], api_info.split(' : ')[1]
    if address_from_api == 'Philadelphia, PA, USA':
        print 'API call failed for:', original_address
        continue
    
    formatted_info.append((address_from_api, int(travel_time.split()[0])))

formatted_info.sort(key=itemgetter(1))
output_file_name = data_file_name + '.info'
output_file = open(output_file_name, 'w')
output_file.write('\n'.join([address.rjust(100) + ' : ' + str(travel_time) + ' mins' for address, travel_time in formatted_info]))
