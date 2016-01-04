#include "cc1101.h"

#include "X2D_Heater_Messages.h"

CC1101 cc1101;

// Handle Serial commands
String inputString = "";         // a string to hold incoming data
boolean stringComplete = false;  // whether the string is complete

#define LEDOUTPUT 7

byte counter;
byte b;

//byte syncWord = 1;
// byte syncWord[] = {0x55, 0x55};


void blinker(){
digitalWrite(LEDOUTPUT, HIGH);
delay(100);
digitalWrite(LEDOUTPUT, LOW);
delay(100);
}

void setup()
{
  Serial.begin(9600);
  Serial.println("Starting CC1101...");


  pinMode(LEDOUTPUT, OUTPUT);
  digitalWrite(LEDOUTPUT, LOW);

  // blink once to signal the setup
  blinker();

  // reset the counter
  counter=0;
  Serial.println("Initialize and set registers.");


  cc1101.init();

  Serial.println("Setting PA_TABLE.");
  config2();

  // cc1101.setSyncWord(syncWord, false);
  cc1101.setCarrierFreq(CFREQ_868);
  cc1101.disableAddressCheck();
  //cc1101.setTxPowerAmp(PA_LowPower);

  delay(1000);

  Serial.print("CC1101_PARTNUM ");
  Serial.println(cc1101.readReg(CC1101_PARTNUM, CC1101_STATUS_REGISTER));
  Serial.print("CC1101_VERSION ");
  Serial.println(cc1101.readReg(CC1101_VERSION, CC1101_STATUS_REGISTER));
  Serial.print("CC1101_MARCSTATE ");
  Serial.println(cc1101.readReg(CC1101_MARCSTATE, CC1101_STATUS_REGISTER) & 0x1f);

  Serial.println("device initialized");

  inputString.reserve(50);
}


void send_data(byte *array) {
  CCPACKET data;
  byte blinkCount=counter++;

  // Going to Sun

  data.length = 14;
  for(int i=0;i<data.length;i++) data.data[i] = array[i];

  if(cc1101.sendData(data)){
    Serial.print(blinkCount,HEX);
    Serial.println(" sent ok :)");
    //blinker();
  }else{
    Serial.println("sent failed :(");
  }

delay(10000);
}

void loop()
{
  // print the string when a newline arrives:
  if (stringComplete) {
      if(inputString == "Sun1")  send_data(SunArea1);
      if(inputString == "Moon1")  send_data(MoonArea1);

      if(inputString == "Sun2")  send_data(SunArea2);
      if(inputString == "Moon2")  send_data(MoonArea2);
    
      if(inputString == "Sun3")  send_data(SunArea3);
      if(inputString == "Moon3")  send_data(MoonArea3);
    
    Serial.println(inputString);
    // clear the string:
    inputString = "";
    stringComplete = false;
  }
  mySerialEvent();


// send_data();

  delay(4000);
}

/*
  SerialEvent doesn't work with rfBee, so we create our function we will call manually
 */
void mySerialEvent() {
  while (Serial.available()) {
    // get the new byte:
    char inChar = (char)Serial.read();
    // add it to the inputString:

    // if the incoming character is a newline, set a flag
    // so the main loop can do something about it:
    if (inChar == '\n') {
      stringComplete = true;
    }else
    {
          inputString += inChar;
    }
  }
}


void config2()
{
byte PA_TABLE[]= {0x00,0xC0,0x00,0x00,0x00,0x00,0x00,0x00};
cc1101.writeBurstReg(CC1101_PATABLE,PA_TABLE,8);
}
