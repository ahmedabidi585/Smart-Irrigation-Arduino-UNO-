#include <LiquidCrystal.h>
#include <DHT.h>

// =====================================================
//                 CONFIGURATION DES PINS
// =====================================================

// ---------- Capteurs ----------
#define SOIL_SENSOR_PIN A0
#define WATER_LEVEL_PIN A1

#define DHT_PIN 2
#define DHT_TYPE DHT22

// ---------- Actionneurs ----------
#define GREEN_LED_PIN 3
#define RED_LED_PIN   4

#define TANK_PUMP_PIN 5
#define WATER_PUMP_PIN 6

// ---------- LCD 16x2 ----------
// RS, E, D4, D5, D6, D7
LiquidCrystal lcd(12, 11, 10, 9, 8, 7);

// ---------- DHT ----------
DHT dht(DHT_PIN, DHT_TYPE);


// =====================================================
//                 PARAMETRES SYSTEME
// =====================================================

// Humidité sol minimale avant arrosage
const int SOIL_MIN = 35;

// Humidité sol suffisante
const int SOIL_OK = 60;

// Niveau minimum du réservoir
const int WATER_MIN = 20;

// Niveau maximum du réservoir
const int WATER_MAX = 90;

// Durée maximale d'arrosage
const unsigned long WATERING_TIME = 5000;

// Intervalle entre deux mesures
const unsigned long SENSOR_INTERVAL = 2000;


// =====================================================
//                 VARIABLES
// =====================================================

float temperature = 0.0;
float humidityAir = 0.0;

int soilHumidity = 0;
int waterLevel = 0;

bool wateringPump = false;
bool tankPump = false;

bool automaticMode = true;

unsigned long wateringStart = 0;
unsigned long lastSensorRead = 0;


// =====================================================
//              FONCTIONS UTILES
// =====================================================

int convertSoilHumidity(int raw)
{
    /*
       Selon le modèle du capteur dans Proteus,
       une valeur faible peut correspondre à un sol humide
       et une valeur élevée à un sol sec.

       On convertit donc :
       1023 -> 0 %
       0    -> 100 %
    */

    int humidity = map(raw, 1023, 0, 0, 100);

    humidity = constrain(humidity, 0, 100);

    return humidity;
}


int convertWaterLevel(int raw)
{
    /*
       Potentiomètre utilisé comme simulation
       du niveau d'eau.
    */

    int level = map(raw, 0, 1023, 0, 100);

    level = constrain(level, 0, 100);

    return level;
}


// =====================================================
//                POMPE ARROSAGE
// =====================================================

void startWaterPump()
{
    if (!wateringPump)
    {
        digitalWrite(WATER_PUMP_PIN, HIGH);

        wateringPump = true;

        wateringStart = millis();

        Serial.println("PUMP_WATER:ON");
    }
}


void stopWaterPump()
{
    if (wateringPump)
    {
        digitalWrite(WATER_PUMP_PIN, LOW);

        wateringPump = false;

        Serial.println("PUMP_WATER:OFF");
    }
}


// =====================================================
//                POMPE RESERVOIR
// =====================================================

void startTankPump()
{
    if (!tankPump)
    {
        digitalWrite(TANK_PUMP_PIN, HIGH);

        tankPump = true;

        Serial.println("PUMP_TANK:ON");
    }
}


void stopTankPump()
{
    if (tankPump)
    {
        digitalWrite(TANK_PUMP_PIN, LOW);

        tankPump = false;

        Serial.println("PUMP_TANK:OFF");
    }
}


// =====================================================
//             GESTION DES LEDS
// =====================================================

void updateLEDs()
{
    /*
       Vert :
       système normal

       Rouge :
       niveau d'eau trop faible
       ou problème
    */

    if (waterLevel < WATER_MIN)
    {
        digitalWrite(GREEN_LED_PIN, LOW);
        digitalWrite(RED_LED_PIN, HIGH);
    }
    else
    {
        digitalWrite(GREEN_LED_PIN, HIGH);
        digitalWrite(RED_LED_PIN, LOW);
    }
}


// =====================================================
//             CONTROLE AUTOMATIQUE
// =====================================================

void automaticControl()
{
    if (!automaticMode)
        return;


    // -------------------------------------------------
    // CAS 1 : RESERVOIR VIDE
    // -------------------------------------------------

    if (waterLevel <= WATER_MIN)
    {
        // Impossible d'arroser
        stopWaterPump();

        // On active la pompe de remplissage
        startTankPump();

        return;
    }


    // -------------------------------------------------
    // CAS 2 : RESERVOIR SUFFISAMMENT REMPLI
    // -------------------------------------------------

    if (waterLevel >= WATER_MAX)
    {
        stopTankPump();
    }


    // -------------------------------------------------
    // CAS 3 : SOL SEC
    // -------------------------------------------------

    if (soilHumidity < SOIL_MIN)
    {
        if (!wateringPump)
        {
            startWaterPump();
        }
    }


    // -------------------------------------------------
    // CAS 4 : SOL HUMIDE
    // -------------------------------------------------

    if (soilHumidity >= SOIL_OK)
    {
        stopWaterPump();
    }


    // -------------------------------------------------
    // SECURITE : TEMPS MAXIMUM
    // -------------------------------------------------

    if (wateringPump)
    {
        if (millis() - wateringStart >= WATERING_TIME)
        {
            stopWaterPump();
        }
    }
}


// =====================================================
//                 LCD
// =====================================================

void displayLCD()
{
    lcd.clear();

    // Ligne 1
    lcd.setCursor(0, 0);

    lcd.print("T:");
    lcd.print(temperature, 1);

    lcd.print("C H:");
    lcd.print(humidityAir, 0);

    lcd.print("%");


    // Ligne 2
    lcd.setCursor(0, 1);

    lcd.print("Sol:");
    lcd.print(soilHumidity);

    lcd.print("% ");

    lcd.print("Eau:");
    lcd.print(waterLevel);

    lcd.print("%");
}


// =====================================================
//              ENVOI DES DONNEES
// =====================================================

void sendData()
{
    /*
       Format :

       T=27.5;H=60;SOL=45;EAU=70;ARROSAGE=0;RESERVOIR=0;MODE=AUTO
    */

    Serial.print("T=");
    Serial.print(temperature, 1);

    Serial.print(";H=");
    Serial.print(humidityAir, 0);

    Serial.print(";SOL=");
    Serial.print(soilHumidity);

    Serial.print(";EAU=");
    Serial.print(waterLevel);

    Serial.print(";ARROSAGE=");
    Serial.print(wateringPump ? 1 : 0);

    Serial.print(";RESERVOIR=");
    Serial.print(tankPump ? 1 : 0);

    Serial.print(";MODE=");

    if (automaticMode)
        Serial.println("AUTO");
    else
        Serial.println("MANUEL");
}


// =====================================================
//              LECTURE DES CAPTEURS
// =====================================================

void readSensors()
{
    // -----------------------------------------------
    // DHT22
    // -----------------------------------------------

    float newHumidity = dht.readHumidity();
    float newTemperature = dht.readTemperature();


    // Vérification
    if (!isnan(newHumidity))
    {
        humidityAir = newHumidity;
    }

    if (!isnan(newTemperature))
    {
        temperature = newTemperature;
    }


    // -----------------------------------------------
    // SOIL SENSOR
    // -----------------------------------------------

    int soilRaw = analogRead(SOIL_SENSOR_PIN);

    soilHumidity = convertSoilHumidity(soilRaw);


    // -----------------------------------------------
    // WATER LEVEL
    // -----------------------------------------------

    int waterRaw = analogRead(WATER_LEVEL_PIN);

    waterLevel = convertWaterLevel(waterRaw);


    // -----------------------------------------------
    // LCD
    // -----------------------------------------------

    displayLCD();


    // -----------------------------------------------
    // SERIAL
    // -----------------------------------------------

    sendData();


    // -----------------------------------------------
    // LED
    // -----------------------------------------------

    updateLEDs();
}


// =====================================================
//              COMMANDES SERIE
// =====================================================

void processSerialCommand()
{
    if (!Serial.available())
        return;


    String command = Serial.readStringUntil('\n');

    command.trim();

    command.toUpperCase();


    // -------------------------------------------------
    // MODE AUTO
    // -------------------------------------------------

    if (command == "AUTO")
    {
        automaticMode = true;

        Serial.println("MODE:AUTO");

        return;
    }


    // -------------------------------------------------
    // MODE MANUEL
    // -------------------------------------------------

    if (command == "MANUAL")
    {
        automaticMode = false;

        stopWaterPump();
        stopTankPump();

        Serial.println("MODE:MANUAL");

        return;
    }


    // -------------------------------------------------
    // POMPE ARROSAGE ON
    // -------------------------------------------------

    if (command == "WATER_ON")
    {
        automaticMode = false;

        startWaterPump();

        return;
    }


    // -------------------------------------------------
    // POMPE ARROSAGE OFF
    // -------------------------------------------------

    if (command == "WATER_OFF")
    {
        stopWaterPump();

        return;
    }


    // -------------------------------------------------
    // POMPE RESERVOIR ON
    // -------------------------------------------------

    if (command == "TANK_ON")
    {
        automaticMode = false;

        startTankPump();

        return;
    }


    // -------------------------------------------------
    // POMPE RESERVOIR OFF
    // -------------------------------------------------

    if (command == "TANK_OFF")
    {
        stopTankPump();

        return;
    }


    // -------------------------------------------------
    // STATUS
    // -------------------------------------------------

    if (command == "STATUS")
    {
        sendData();

        return;
    }
}


// =====================================================
//                    SETUP
// =====================================================

void setup()
{
    // -----------------------------------------------
    // Serial
    // -----------------------------------------------

    Serial.begin(9600);


    // -----------------------------------------------
    // Pins
    // -----------------------------------------------

    pinMode(GREEN_LED_PIN, OUTPUT);
    pinMode(RED_LED_PIN, OUTPUT);

    pinMode(WATER_PUMP_PIN, OUTPUT);
    pinMode(TANK_PUMP_PIN, OUTPUT);


    // -----------------------------------------------
    // Sécurité : pompes OFF
    // -----------------------------------------------

    digitalWrite(WATER_PUMP_PIN, LOW);
    digitalWrite(TANK_PUMP_PIN, LOW);

    digitalWrite(GREEN_LED_PIN, LOW);
    digitalWrite(RED_LED_PIN, LOW);


    // -----------------------------------------------
    // LCD
    // -----------------------------------------------

    lcd.begin(16, 2);

    lcd.clear();

    lcd.setCursor(0, 0);
    lcd.print("Smart Irrigation");

    lcd.setCursor(0, 1);
    lcd.print("Starting...");


    // -----------------------------------------------
    // DHT
    // -----------------------------------------------

    dht.begin();


    // -----------------------------------------------
    // Startup delay
    // -----------------------------------------------

    delay(2000);


    lcd.clear();

    Serial.println();
    Serial.println("================================");
    Serial.println(" SMART IRRIGATION");
    Serial.println(" Arduino UNO");
    Serial.println("================================");

    Serial.println("SYSTEM:READY");
}


// =====================================================
//                     LOOP
// =====================================================

void loop()
{
    // -----------------------------------------------
    // Lire les commandes venant du PC
    // -----------------------------------------------

    processSerialCommand();


    // -----------------------------------------------
    // Lire les capteurs périodiquement
    // -----------------------------------------------

    if (millis() - lastSensorRead >= SENSOR_INTERVAL)
    {
        lastSensorRead = millis();


        // Lire les capteurs
        readSensors();


        // Contrôle automatique
        automaticControl();


        // Mettre à jour les LEDs
        updateLEDs();
    }
}