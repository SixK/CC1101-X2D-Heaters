# cc1101-X2D-Heaters

* Handle your compatible to X2D protocol heaters (Delta dore) with an arduino

* Requierements :
- RFBee (or any other CC1101 based device)  
- UartSBee Usb to serial converter (or any thing that can upload firmware to Rfbee)  
- USB DVB Key (compatible with SDR) 
- RTL_433 tool (https://github.com/merbanan/rtl_433  probably only work on Linux, you will probably have to compile it manually)  
- (optional) DFRobot Leonardo&Xbee arduino clone

* First capture X2D messages from your Deltia or any X2D emitter device with your DVB Key and RTL_433 with the following command :
rtl_433 -D -A -f 868340000

2 messages of 74 bytes length should be captured when pressing a manual heater command on Deltia emitter device.
Only 1rst message is necessary.
In this message only 14 first bytes are mandatory

Capture messages when switching from Moon to Sun and from Sun to Moon for all areas you want to control.

Edit "X2D_Heater_Messages.h" file and replace values by those you have captured. 
Default Values are Binary but Hexa values are OK too. (It's not mandatory to convert Hexa to Binary)
Don't forget to prefix values with 0x for Hexa or 0b for binary. 

Compile and upload Arduino Sketch to RFbee with your RFBee on the UartSBee.

Control your RFbee with Arduino Ide Serial monitor or any serial tool Sending commands like "Sun1" to switch from Moon (Eco mode) to Sun (Comfort Mode) on Area 1. (or "Moon1" to switch from Sun to Moon on Area 1) 

* Compile Arduino Sketch for RFBee :
Don't forget to set UARTSbee on 3.3v switch before connecting (RFBee and CC1101 don't support 5.0v)
Set board parameters to "Lilypad Arduino" and ATMega328 for RFBee V1.2 (ATMega168 for V1.1 and previous versions)
Double click on cc1101-X2D-Heaters.ino file
Modify "X2D_Heater_Messages.h" (Default values are mine and won't work with your X2D heaters)

* Configure Arduino IDE Serial monitor :
Set end of line drop down button to "New Line" and Baudrate to 9600 baud in Serial monitor.

* Commands to Use cc1101-X2D-Heaters :
Sun1 -> Switch from Moon to Sun on Area 1
Moon1 -> Switch from Sun to Moon on Area 1
Sun2 -> Switch from Moon to Sun on Area 2
Moon2 -> Switch from Sun to Moon on Area 2
Sun3 -> Switch from Moon to Sun on Area 3
Moon3 -> Switch from Sun to Moon on Area 3

SixK