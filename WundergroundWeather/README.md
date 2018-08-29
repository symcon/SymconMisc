# WundergroundWeather
Das Modul fragt über die Wunderground API Wetterdaten ab.  
Dafür ist eine Registrierung auf www.wunderground.com nötig, um einen API-Key zu erhalten.  
Es können aktuelle Daten, Unwetterwarnungen, sowie stündliche als auch täglichen Vorhersagen abgefragt werden.  

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* De-/Aktivierbare Abfrage von gewünschten Wetterdaten.
* Einstellbarkeit der Menge der Unwetter, stündlichen und täglichen Daten.
* Timer für automatische Aktualisierung der Daten.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/symcon/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'WundergroundWeather'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name                              | Beschreibung
--------------------------------- | ---------------------------------
Standort                          | Standort, von dem die Daten entnommen werden sollen. Ob ein Standort verfügbar ist, kann auf der www.wunderground.com Seite ausprobiert werden. Sollte ein Standort nicht vorhanden sein, sollte ein nächstgelegener größerer Ort gewählt werden.
Land                              | Hier muss das Land eingetragen werden.
PWS-ID                            | ID der 'Personal Weatherstation' Hiermit kann man eine spezifische Wetterstation abfragen
API Key                           | Wunderground API-Key. Kann auf der Wunderground Homepage nach Registrierung angefordert werden. "More"->"Weather API for Developers".
Aktuelle Daten abfragen           | Aktiviert die Abfrage der aktuellen Wetterdaten.
Stündliche Vorhersage             | Aktiviert die Abfrage der stündlichen Vorhersage.
12stündliche Vorhersage           | Aktiviert die Abfrage der 12-stündlichen Vorhersage.
tägliche Vorhersage               | Aktiviert die Abfrage der täglichen Vorhersage.
Unwetterwarnung abfragen          | Aktiviert die Abfrage der Unwetter Vorhersage.
Anzahl Vorhersagen (12-stündlich) | Die Anzahl der 12-stündlichen Vorhersagen. Maximalwert: 8
Anzahl Vorhersagen (täglich)      | Die Anzahl der täglichen Vorhersagen. Maximalwert: 4
Anzahl Vorhersagen (stdündlich)   | Die Anzahl der stündlichen Vorhersagen. Maximalwert: 24
Anzahl Unwetterwarnung            | Die Anzahl der Unwetter Vorhersagen. Maximalwert: 6
Update Wetterdaten                | Setzt den Timer in Minuten, wie oft die Wetterdaten aktualisiert werden sollen. (aktuell/stündlich/12-stündlich)
Update Unwetterwarnungen          | Setzt den Timer in Minuten, wie oft die Unwetterwarnungen aktualisiert werden sollen.
Button Update Wetter              | Aktualisiert die Wetterdaten (aktuell/stündlich/12-stündlich/täglich). Sofern alle drei Abfragen deaktiviert sind oder der Timer auf 0 gesetzt ist => Timer deaktiviert
Button Update Unwetterwarnungen   | Aktualisiert die Unwetterwarnungen. Sofern die Unwetterwarnungsabfrage deaktiviert oder der Timer auf 0 gesetzt ist => Timer deaktiviert


### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Aktuelle Wetterdaten

Name                | Typ     | Beschreibung
------------------- | ------- | ----------------
Luftdruck           | Float   | Angabe in hPa
Luftfeuchtigkeit    | Float   | Angabe in %
Niederschlag/h      | Float   | Angabe in Liter/m²
Niederschlag Tag    | Float   | Angabe in Liter/m²
Sichtweite          | Float   | Angabe in km
Sonnenstrahlung     | Float   | Angabe in W/m²
Temperatur          | Float   | Angabe in °C
Temperatur gefühlt  | Float   | Angabe in °C
Temperatur Taupunkt | Float   | Angabe in °C
UV Strahlung        | Integer | Informationen: [UVIndex Erklärung](https://www.wunderground.com/resources/health/uvindex.asp)
Windböe             | Float   | Angabe in km/h
Windgeschwindigkeit | Float   | Angabe in km/h
Windrichtung        | Float   | Angabe in Himmelsrichtungen

##### Stündliche Vorhersage
Die Variablen werden mit 1..24h gekennzeichnet. (1 = Vorhersage nächste volle Stunde; 24 = Vorhersage der 24ten vollen Stunde)

Name                | Typ     | Beschreibung
------------------- | ------- | ----------------
Gegebenheit         | String  | Beschreibt das Wetter z.B. "Bedeckt", "Regen möglich"
Luftfeuchtigkeit    | Float   | Angabe in %
Luftdruck           | Float   | Angabe in hPa
Regenmenge          | Float   | Angabe in Liter/m²
Regenwahrscheinlichkeit | Integer   | Angabe in %
Temperatur          | Float   | Angabe in °C
Wolkendecke         | Integer | Angabe in %
Windgeschwindigkeit | Float   | Angabe in km/h
Windrichtung        | Float   | Angabe in Himmelsrichtungen

##### 12-stündliche Vorhersage
Die Variablen werden mit 12, 24..96h gekennzeichnet (12 = Vorhersage in 12 Stunden; 96 = Vorhersage in 96 Stunden)

**Hinweis: die 12h-stündliche Vorhersage wird von WUnderground nicht mehr geliefert. Die Variablen bleiben aus Kompatibilitätsgründen erhalten, enthalten aber die Werte der täglichen Vorhersage.**


Name             | Typ   | Beschreibung
---------------- | ----- | ----------------
Höchsttemperatur | Float | Angabe in °C
Tiefsttemperatur | Float | Angabe in °C

##### tägliche Vorhersage
Die Variablen werden mit 1..4d gekennzeichnet (1 = morgen; 2 = übermorgen ... )

Name                    | Typ     | Beschreibung
----------------------- | ------- | ----------------
Gegebenheit             | String  | Beschreibt das Wetter z.B. "Bedeckt", "Regen möglich"
Höchsttemperatur        | Float   | Angabe in °C
Tiefsttemperatur        | Float   | Angabe in °C
Luftfeuchtigkeit        | Float   | Angabe in %
Regenmenge              | Float   | Angabe in Liter/m²
Regenwahrscheinlichkeit | Integer | Angabe in %
Windgeschwindigkeit     | Float   | Angabe in km/h
Windrichtung            | Float   | Angabe in Himmelsrichtungen

##### Unwetterwarnung

Name         | Typ     | Beschreibung
------------ | ------- | ----------------
Beschreibung | String  | Beschreibt die Warnung mit möglichen weiteren Informationen wie z. B. Windgeschwindigkeiten oder Regenmengen.
Datum        | Integer | Angabe in UnixTimeStamp. Datum wann die Warnung ausgesprochen wurde.
Name         | String  | Ausgeschriebener Typ. z.B. Gewitter
Typ          | String  | 3-Buchstabenkürzel für die Warnung ([Übersicht](https://www.wunderground.com/weather/api/d/docs?d=data/alerts))

##### Profile:

Name             | Typ
---------------- | -------
WGW.Rainfall     | Float
WGW.Sunray       | Float
WGW.Visibility   | Float
WGW.WindSpeedkmh | Float
WGW.UVIndex      | Integer
WGW.ProbabilityOfRain | Integer

### 6. WebFront

Über das WebFront werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz

`boolean WGW_UpdateWeatherData(integer $InstanzID);`  
Aktualisiert die Wetterdaten (aktuell/stündlich/täglich) des Weathergroundmoduls mit der InstanzID $InstanzID.  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`WGW_UpdateWeatherData(12345);`

`boolean WGW_UpdateStormWarningData(integer $InstanzID);`  
Aktualisiert die Unwetterwarnungen des Weathergroundmoduls mit der InstanzID $InstanzID.  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`WGW_UpdateStormWarningData(12345);`
