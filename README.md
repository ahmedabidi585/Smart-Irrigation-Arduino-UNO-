# 🌱 Smart Irrigation (Arduino UNO)

A smart irrigation project using Arduino UNO.

## 📋 Description

- 💧 An automatic irrigation system that reads soil moisture sensors and activates a water pump/relay when needed.
- 📂 Main folders and files:
  - `code.cpp`: Arduino code (C++ version of the sketch).
  - `Smart_Irrigation_Proteus.pdsprj`: Proteus project file for circuit simulation.
  - `irrigation_web/`: Web interface and API.
    - `irrigation_web/api.php`
    - `irrigation_web/index.php`

## 🔧 System Components

- 🧠 Controller: Arduino UNO
- 🌱 Sensors: Soil moisture sensor (e.g., YL-69 or similar)
- 🚿 Motor/Pump: Water pump connected through a relay
- 📡 Optional connection: WiFi/ESP module for data transmission or a local server using XAMPP

## 🛠️ Installation & Usage

### 🔌 1. Arduino

- Open `code.cpp` using Arduino IDE.
- Select the appropriate COM port and set the board to `Arduino UNO`.
- Compile and upload the code to the board.

### 🌐 2. Web Interface

- Place the `irrigation_web` folder inside your local server directory, for example `htdocs` in XAMPP.
- Open your browser and go to:

```text
http://localhost/irrigation_web/
```

- `api.php` provides a simple API for reading and updating the system status.
- Make sure the connection settings are correctly configured if required.

### 🧪 3. Proteus

- Open `Smart_Irrigation_Proteus.pdsprj` in Proteus to simulate the circuit before testing the system on real hardware.

## 📊 Web Monitoring & Control

The project includes a web interface connected to the Arduino UNO through **COM0COM**.

The web page allows the user to monitor the irrigation system in real time and view the current sensor and pump states.

The interface can display:

- 🌱 Soil moisture
- 💧 Water tank level
- 🌡️ Temperature
- 💨 Air humidity
- 🚿 Irrigation pump status
- 🛢️ Tank filling pump status
- ⚙️ Operating mode
- 🎚️ Current irrigation thresholds

### 🔗 COM0COM Communication

COM0COM is used to create a virtual serial connection between the Arduino communication interface and the web application.

Example:

```text
Arduino UNO
     │
     │ Serial Communication
     ▼
   COM0COM
     │
     ▼
 Web Application
     │
     ▼
 Web Dashboard
```

The exact COM port numbers depend on the computer configuration.

### 📡 Real-Time Monitoring

The Arduino sends sensor information through the serial communication interface.

Example:

```text
T=27.5;H=60;SOL=42;EAU=75;ARROSAGE=0;RESERVOIR=0;MODE=AUTO
```

The web page processes this information and displays the current state of the system.

### 🎚️ Changing Irrigation Thresholds

The web interface also allows the user to modify the irrigation thresholds directly from the page.

For example:

```text
Start Irrigation  : 35 %
Stop Irrigation   : 60 %
Minimum Water     : 20 %
Tank Full         : 90 %
Maximum Pump Time : 5 seconds
```

The user can change these values from the web dashboard without modifying or recompiling the Arduino code.

Example:

```text
Start irrigation : [ 30 ] %
Stop irrigation  : [ 65 ] %
Minimum water    : [ 20 ] %
Tank full        : [ 90 ] %

          [ SAVE SETTINGS ]
```

This makes the system easier to configure for different plants, soil conditions, and irrigation requirements.

## 🚀 Uploading the Project to GitHub

### 📂 1. Navigate to the project directory

Open Command Prompt or Git Bash:

```bash
cd "c:/Users/Ahmed/Smart Irrigation"
```

### ⬆️ 2. Initialize Git and upload the project

If Git has not been configured yet:

```bash
git init
git add .
git commit -m "Initial commit: Smart Irrigation project"
git remote add origin https://github.com/ahmedabidi585/Smart-Irrigation-Arduino-UNO-.git
git branch -M main
git push -u origin main
```

> Note: If the repository already exists and contains different files, use `git_pull` or change the branch/repository configuration as needed. If you have SSH access, replace the HTTPS URL with your SSH repository URL.

## 💡 Additional Recommendations

- Create a `.gitignore` file to exclude development environment files or large temporary simulation files.
- Add a `LICENSE` file if you want to specify a license for the project.

## 📁 Project Structure

```text
Smart-Irrigation/
│
├── code.cpp
├── Smart_Irrigation_Proteus.pdsprj
│
└── irrigation_web/
    ├── api.php
    └── index.php
```

## 🔮 Future Improvements

Possible improvements include:

- 📱 Mobile application
- 🌐 IoT connectivity
- ☁️ Cloud monitoring
- 📊 Web dashboard
- 📈 Historical sensor data
- 🔔 Low-water notifications
- 🌦️ Weather API integration
- 🌿 Multiple irrigation zones
- 📡 ESP32 Wi-Fi connectivity
- ☀️ Solar-powered operation
- 🤖 AI-based irrigation prediction

## 👨‍💻 Author

**Ahmed Abidi**

Embedded Systems & IoT Engineering Student

### 🧰 Technologies

```text
Arduino
C/C++
Proteus
PHP
HTML/CSS
JavaScript
XAMPP
COM0COM
Embedded Systems
Sensors
IoT
Serial Communication
Automation
```

## 📜 License

This project is intended for educational and research purposes.

You are free to modify and improve the project for your own applications.
