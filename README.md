# cc1101-X2D-Heaters

Handle your compatible to X2D protocol heaters (Delta dore) with an arduino

### Requierements :  
- RFBee (or any other CC1101 based device)  
- UartSBee Usb to serial converter (or any thing that can upload firmware to Rfbee)  
- USB DVB Key (compatible with SDR) 
- RTL_433 tool (https://github.com/merbanan/rtl_433  probably only work on Linux, you will probably have to compile it manually)  
- (optional) DFRobot Leonardo&Xbee arduino clone  

First capture X2D messages from your Deltia or any X2D emitter device with your DVB Key and RTL_433 with the following command :  
rtl_433 -D -A -f 868340000  

2 messages of 74 bytes length should be captured when pressing a manual heater command on Deltia emitter device.  
1rst message is for Comfort/Eco mode.  
2nd message is for Power On/Off and HG (Frost prevent) mode
In this messages only 14 first bytes are mandatory  (13 first bytes for Association message)

Capture messages when switching from Moon to Sun and from Sun to Moon for all areas you want to control.  

Edit "X2D_Heater_Messages.h" file and replace values by those you have captured. 
Default Values are Binary but Hexa values are OK too. (It's not mandatory to convert Hexa to Binary)  
Don't forget to prefix values with 0x for Hexa or 0b for binary.  

Compile and upload Arduino Sketch to RFbee with your RFBee on the UartSBee.  

Control your RFbee with Arduino Ide Serial monitor or any serial tool Sending commands like "Sun1" to switch from Moon (Eco mode) to Sun (Comfort Mode) on Area 1. (or "Moon1" to switch from Sun to Moon on Area 1) 

### Compile Arduino Sketch for RFBee :  
Don't forget to set UARTSbee on 3.3v switch before connecting (RFBee and CC1101 don't support 5.0v)  
Set board parameters to "Lilypad Arduino" and ATMega328 for RFBee V1.2 (ATMega168 for V1.1 and previous versions)  
Double click on cc1101-X2D-Heaters.ino file  
Modify "X2D_Heater_Messages.h" (Default values are mine and won't work with your X2D heaters)  

### Configure Arduino IDE Serial monitor :  
Set end of line drop down button to "New Line" and Baudrate to 9600 baud in Serial monitor.  

### Commands to Use cc1101-X2D-Heaters :  
Sun1 -> Switch from Moon to Sun on Area 1  
Moon1 -> Switch from Sun to Moon on Area 1 
Hg1 -> Go to "Frost Prevent" mode on Area2 (Hors Gel). (Use On or Sun command to exit this mode)  
Off1 -> Go to Power Off mode on Area2. (Use On command to exit this mode)  
On1 -> Power On mode on Area2 (can be used to exit Power Off or "Frost Prevent" mode)  

Sun2 -> Switch from Moon to Sun on Area 2  
Moon2 -> Switch from Sun to Moon on Area 2  
Hg2 -> Go to "Frost Prevent" mode on Area2 (Hors Gel). (Use On or Sun command to exit this mode)  
Off2 -> Go to Power Off mode on Area2. (Use On command to exit this mode)  
On2 -> Power On mode on Area2 (can be used to exit Power Off or "Frost Prevent" mode)  

Sun3 -> Switch from Moon to Sun on Area 3  
Moon3 -> Switch from Sun to Moon on Area 3  
Hg3 -> Go to "Frost Prevent" mode on Area3 (Hors Gel). (Use On or Sun command to exit this mode)  
Off3 -> Go to Power Off mode on Area3. (Use On command to exit this mode)  
On3 -> Power On mode on Area3 (can be used to exit Power Off or "Frost Prevent" mode)  


### Python Scripts :  
pyX2DCmd.py is a simple python script to send commands to RFBee.
It has been tested working under Windows and Raspbian (Raspberry Pi)
This script can be run with such a command line :  
python pyX2DCmd.py Sun3  

Before running the script don't forget to edit Serial parameters at beginning of file to suite your Serial Port.  
It can be added in crontab or scheduled tasks to rule your Heaters a bit more friendly...  

pyDeltia.py a script that parse  chauffage.csv file to command Heaters on a week.
This script import HandlePlanning a class dedicated to chauffage.csv file parsing.
This script can be called in crontab each 30 minutes and is actually looking for Area 1 ("Zone 1") and Area 2 ("Zone 2") defined state for current time.
It then call pyX2DCmd.py to send commands to RFbee device

X2D_WebPoller.py a script that call a Web page to get heaters mode to set.
This script is to use with X2D web site provided in "www" directory.


### chauffage.ods openOffice file
A file to define Heaters planning by Area for a Week.
Actually 30 minutes periods are defined just as a Deltia Emitter can be set, but any periods could be defined. (every 15 minutes, every 5 minutes, ...)
Then you will have to edit crontab to poll state according to period you have defined.
File must be saved as csv ("chauffage.csv") with ";" separator and values quoted (").

### www directory  
A minimal 2 zones Heaters IHM Web manager. 
In Auto mode, Heaters status are following a planning file (chauffage.ods).
In Manual mode, user choose Heaters status for Zone 1 and Zone 2.

Functionnalities are :  
Auto/Manual mode   
Turn Zone1 / Zone2 manually to Comfort or Eco mode.  
Upload heaters Planning (openoffice .ods files)  

This Web interface is to use with X2D_WebPoller.py script
Requierements : 
A Web server with PHP >5.6  


SixK
