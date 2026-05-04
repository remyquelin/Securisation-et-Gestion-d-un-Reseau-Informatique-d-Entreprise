///////////////////////////////////////////////////////////
//  Sensor.h
//  Implementation of the Class Sensor
//  Created on:      30-mars-2026 15:29:37
//  Original author: ASGA
///////////////////////////////////////////////////////////

#if !defined(EA_FC9BBF63_86D2_43cf_9EA4_7D568D834937__INCLUDED_)
#define EA_FC9BBF63_86D2_43cf_9EA4_7D568D834937__INCLUDED_

#include <string>


class Sensor
{

public:
    Sensor();
	Sensor(int pin, const std::string &type);
	virtual ~Sensor();

	virtual void begin();
    virtual float readValue();

	void setPin(int p) { pin = p; }
	int getPin() const { return pin; }

	void setType(const std::string &t) { type = t; }
	const std::string &getType() const { return type; }

	void setValue(float v) { value = v; }
	float getValue() const { return value; }

protected:
	int pin;

private:
	std::string type;
	float value;

};

// Concrete sensor types (simulated). Implementations are in Sensor.cpp
class DHT22Sensor : public Sensor {
public:
	DHT22Sensor(int p);
	void begin() override;
	float readValue() override;
    float getHumidity() const;
private:
	float humidity;
};

class DoorSensor : public Sensor {
public:
	DoorSensor(int p);
	void begin() override;
	float readValue() override;
};
#endif // !defined(EA_FC9BBF63_86D2_43cf_9EA4_7D568D834937__INCLUDED_)
