import time
import serial
import os
import sys

if os.name == 'nt':
	ser = serial.Serial(
		port='COM27',
		baudrate=9600
	)
else:
	ser = serial.Serial(
		port='/dev/ttyUSB0',
		baudrate=9600,
	)
	ser.open()

ser.isOpen()

if len(sys.argv) != 2 :
	print "This tool need a command to send"
else:
	cmd=sys.argv[1]
	out = ''
	# wait for RFbee init
	time.sleep(3)
	while ser.inWaiting() > 0:
		out += ser.read(1)
		
	if out != '':
		print ">>" + out
	
	time.sleep(1)
	ser.write("%s\n"%(cmd))
	print (">>%s"%(cmd))
	
	out = ''
	# let's wait one second before reading output (let's give device time to answer)
	time.sleep(1)
	while ser.inWaiting() > 0:
		out += ser.read(1)
		
	if out != '':
		print ">>" + out
