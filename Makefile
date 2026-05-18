CXX     = g++
CXXFLAGS = -Wall -std=c++11
LIBS    = -lwiringPi -lcurl

TARGET  = alarme
SRCS    = main.cpp Sensor.cpp IoT_Client.cpp

$(TARGET): $(SRCS)
	$(CXX) $(CXXFLAGS) -o $(TARGET) $(SRCS) $(LIBS)

clean:
	rm -f $(TARGET)
