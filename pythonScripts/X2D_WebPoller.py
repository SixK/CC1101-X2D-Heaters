import urllib, json
import time
import os

scriptPath=os.path.dirname(__file__)+'/'
if scriptPath=='/' :
        scriptPath='./'

urlPath="http://yourserver/X2D/"
if os.name == 'nt':
	pythonPath='c:\Python27\python.exe'
else :
	pythonPath='python'

url = urlPath+"modeStatus.php"
response = urllib.urlopen(url)
mode = json.loads(response.read())
print mode


if mode['mode'] == "Manual" :
	url = urlPath+"readZones.php"

else :
	url = urlPath+"zonesStatusFromPlanning.php"

response = urllib.urlopen(url)
zones = json.loads(response.read())
print zones

for zone,value in zones.items():
	if zone == "zone1" :
		param = value+"1"
	
	if zone == "zone2" :
		param = value+"2"
	
	if zone == "zone3" :
		param = value+"3"

	os.system(pythonPath+" "+scriptPath+"pyX2DCmd.py "+param)
	
	time.sleep(15)
