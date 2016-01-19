import csv
import HandlePlanning
from datetime import  datetime
import os
import time

'''
This script can be used to replace a Deltia Emitter 
and command heaters following a planning during a week

Planning file must be saved as csv file with values quoted and ";" separator 

Call it in crontab with this parameters :
# m h  dom mon dow   command
*/30 * * * * python /home/pi/pyDeltia.py

This script will be called each day every 30 minutes
'''

path=os.path.dirname(__file__)+'/'
if path=='/' :
        path='./'

data=list()
dayNames=['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche']
stateNames=['Moon','Sun']

with open('chauffage.csv', 'rb') as f:
	reader = csv.reader(f, delimiter=';', quotechar='"', quoting=csv.QUOTE_ALL)
	for row in reader:
		# print row
		data.append(row)
	
zone1=HandlePlanning.HandlePlanning()
zone2=HandlePlanning.HandlePlanning()
	
zone1.setData(data)
zone2.setData(data)

horaires=zone1.getHoraires()
zone2.getHoraires()
	
zone1.getZone("Zone 1")
zone2.getZone("Zone 2")

d=datetime.now()
dayName=dayNames[d.weekday()]
print(dayName)	

timeStr=d.strftime("%H:%M:%S")
print timeStr

'''
# Some tests
# timeStr="07:35:00"
# timeStr="07:00:00"
# timeStr="06:35:00"
'''
stat1=stateNames[zone1.getStatus(dayName, timeStr)]
print stat1
stat2=stateNames[zone2.getStatus(dayName, timeStr)]
print stat2

os.system("python %spyX2DCmd.py %s1"%(path,stat1))
time.sleep(15)
os.system("python %spyX2DCmd.py %s2"%(path,stat2))


'''  
# Some Tests
zone1.getStatus("mercredi", "10:30:05")
zone1.getStatus("mercredi", "11:00:05")
zone1.getStatus("mercredi", "11:30:05")
zone1.getStatus("mercredi", "12:00:05")
'''