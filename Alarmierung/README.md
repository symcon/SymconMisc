# Alarmierung
Das Modul löst einen Alarm aus, wenn eine der Sensorenvariablen aktiv wird.
Dabei werden Zielvariablen bei einem Alarm auf den maximalen Wert bzw. An (True) gesetzt.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Schalten von verlinkten Aktoren/Variablen.
* Auswahl von verlinkten Sensoren, welche einen Alarm auslösen können.
* Ein-/Ausschaltbarkeit via WebFront-Button oder Skript-Funktion.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/paresy/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Alarmierung'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name                    | Beschreibung
----------------------- | ---------------------------------
Button "Aktualisierung" | Neueinlesen aller Sensoren und erstellen der benötigten Ereignisse.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name         | Typ       | Beschreibung
------------ | --------- | ----------------
Alert Target | Kategorie | Verlinkte Targets werden auf den maximalen Wert bzw. An (True) gesetzt, wenn ein Alarm ausgelöst wird.
Sensors      | Kategorie | Verlinkte Variablen, welche den Alarm auslösen können.
Active       | Boolean   | De-/Aktiviert die Alarmierung. Wird die Alarmierung deaktiviert, so wird auch der ggf. vorhandene Alarm deaktiviert.
Alert        | Boolean   | De-/Aktiviert den Alarm.

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront kann die Alarmierung de-/aktiviert werden.  
Es wird zusätzlich angezeigt, ob ein Alarm vorliegt oder nicht.
Der Alarm kann auch manuell de-/aktiviert werden.

### 7. PHP-Befehlsreferenz

`boolean ARM_UpdateEvents(integer $InstanzID);`  
Aktualisiert die Sensorenereignisse des Alarmierungsmodul mit der InstanzID $InstanzID.  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`ARM_UpdateEvents(12345);`

`boolean ARM_SetActive(integer $InstanzID, boolean $Value);`
Schaltet das Alarmierungsmodul mit der InstanzID $InstanzID  auf den Wert $Value (true = An; false = Aus).  
Die Funktion liefert keinerlei Rückgabewert.  
`ARM_SetActive(12345, true);`

`boolean ARM_SetAlert(integer $InstanzID, boolean $Value);`
Schaltet den Alarm mit der InstanzID $InstanzID auf den Wert $Value (true = An; false = Aus).  
Die Funktion liefert keinerlei Rückgabewert.  
`ARM_SetAlert(12345, false);`