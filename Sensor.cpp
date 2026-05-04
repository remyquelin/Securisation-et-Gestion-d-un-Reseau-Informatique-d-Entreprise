///////////////////////////////////////////////////////////
//  Sensor.cpp
//  Implementation of the Class Sensor
//  Created on:      30-mars-2026 15:29:37
//  Original author: ASGA
///////////////////////////////////////////////////////////

#include "Sensor.h"
#include <iostream>
#include <cmath>
#include <cstdint>
#include <wiringPi.h>


Sensor::Sensor(){
	pin = -1;
	type = "unknown";
	value = 0.0f;

}

Sensor::Sensor(int p, const std::string &t) {
	pin = p;
	type = t;
	value = 0.0f;
}

// DHT22 and Door sensor constructors and begin methods
DHT22Sensor::DHT22Sensor(int p) : Sensor(p, "DHT22"), humidity(NAN) {}
void DHT22Sensor::begin() { Sensor::begin(); }

DoorSensor::DoorSensor(int p) : Sensor(p, "Door") {}
void DoorSensor::begin() { Sensor::begin(); pinMode(pin, INPUT); pullUpDnControl(pin, PUD_UP); }


Sensor::~Sensor(){

}


void Sensor::begin(){
	// Initialize wiringPi once. If running on non-RPi this will fail -> user must compile on RPi.
	static bool inited = false;
	if (!inited) {
		if (wiringPiSetup() == -1) {
			std::cerr << "wiringPi setup failed. Are you on Raspberry Pi and is wiringPi installed?\n";
		}
		inited = true;
	}
}


float Sensor::readValue(){
	return value;
}

// DHT22 implementation (bit-banged). Uses wiringPi digital I/O.
// This is a basic implementation and may need timing tuning on some systems.
float DHT22Sensor::readValue() {
	if (pin < 0) return NAN;

	uint8_t data[5] = {0,0,0,0,0};

	// Send start signal
	pinMode(pin, OUTPUT);
	digitalWrite(pin, LOW);
	delay(2); // >1ms
	digitalWrite(pin, HIGH);
	delayMicroseconds(30);
	pinMode(pin, INPUT);

	// Wait for sensor response
	unsigned int count = 0;
	// pull low ~80us
	count = 0;
	while (digitalRead(pin) == HIGH) {
		if (++count > 1000) return NAN;
		delayMicroseconds(1);
	}
	// pull high ~80us
	count = 0;
	while (digitalRead(pin) == LOW) {
		if (++count > 1000) return NAN;
		delayMicroseconds(1);
	}
	count = 0;
	while (digitalRead(pin) == HIGH) {
		if (++count > 1000) return NAN;
		delayMicroseconds(1);
	}

	// Read 40 bits
	for (int i = 0; i < 40; ++i) {
		// wait for low
		count = 0;
		while (digitalRead(pin) == LOW) {
			if (++count > 1000) return NAN;
			delayMicroseconds(1);
		}
		// measure length of high
		unsigned int len = 0;
		while (digitalRead(pin) == HIGH) {
			++len;
			delayMicroseconds(1);
			if (len > 200) break;
		}
		data[i/8] <<= 1;
		if (len > 40) { // threshold: longer HIGH means '1'
			data[i/8] |= 1;
		}
	}

	// Verify checksum
	uint8_t checksum = data[0] + data[1] + data[2] + data[3];
	if (checksum != data[4]) {
		return NAN;
	}

	float humid = ((data[0] << 8) | data[1]) * 0.1f;
	int tempRaw = ((data[2] & 0x7F) << 8) | data[3];
	float temp = tempRaw * 0.1f;
	if (data[2] & 0x80) temp = -temp;

	setValue(temp);
	this->humidity = humid;
	return temp;
}

float DHT22Sensor::getHumidity() const {
	return humidity;
}

// Door sensor: simple digital input (reed switch). Returns 1.0 for open, 0.0 for closed.
float DoorSensor::readValue() {
	if (pin < 0) return NAN;
	pinMode(pin, INPUT);
	// Enable pull-up by default (adjust if using pull-down)
	pullUpDnControl(pin, PUD_UP);
	int v = digitalRead(pin);
	// Assuming switch connects to GND when closed: LOW = closed (0.0), HIGH = open (1.0)
	float result = (v == HIGH) ? 1.0f : 0.0f;
	setValue(result);
	return result;
}