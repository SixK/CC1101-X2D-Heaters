#include "Arduino.h"
#include "ccpacket.h"

byte SunArea1[14] = {0b01010101,0b01111111,0b01011101,0b10100100,0b11001010,0b10010101,0b00110010,0b10101101,0b11010101,0b00010101,0b01111110,0b00101000,0b01111111,0b11000000};
byte MoonArea1[14] = {0b01010101,0b01111111,0b01011101,0b10100100,0b11001010,0b10010101,0b00110010,0b10101101,0b11010101,0b01010101,0b01111110,0b00001000,0b01111111,0b11000000};
byte SunArea2[14] ={0b01010101,0b01111111,0b01011101,0b10100100,0b11001010,0b11010101,0b00110010,0b10101101,0b11010101,0b00010101,0b01111110,0b00010111,0b10000000,0b00111111};
byte MoonArea2[14]={0b01010101,0b01111111,0b01011101,0b10100100,0b11001010,0b11010101,0b00110010,0b10101101,0b11010101,0b01010101,0b01111110,0b00100111,0b10000000,0b00111111};
byte SunArea3[14]= {0b01010101,0b01111111,0b01011101,0b10100100,0b11001010,0b10010101,0b00110010,0b10101101,0b11010101,0b00010101,0b01111110,0b00101000,0b01111111,0b11000000};
byte MoonArea3[14]={0b01010101,0b01111111,0b01011101,0b10100100,0b11001010,0b10010101,0b00110010,0b10101101,0b11010101,0b01010101,0b01111110,0b00001000,0b01111111,0b11000000};