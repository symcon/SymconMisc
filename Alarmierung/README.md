# Alarmierung
Das Modul löst einen Alarm aus, wenn eine der Sensorenvariablen aktiv wird.
Dabei werden Zielvariablen bei einem Alarm auf den maximalen Wert bzw. An (True) gesetzt.
Ein einmal geschalteter Alarm wird nicht automatisch deaktiviert, dieser muss manuell zurückgesetzt werden.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Konfiguration von Sensor- und Zielvariablen via ListenAuswahl, welche den Alarm auslösen oder bei einem Alarm geschaltet werden.
* Ein-/Ausschaltbarkeit via WebFront-Button oder Skript-Funktion.
* Konvertierungsfunktion für alte Versionen des Alarmierungsmoduls

### 2. Voraussetzungen

- IP-Symcon ab Version 4.2

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/symcon/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Alarmierung'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name                   | Beschreibung
---------------------- | ---------------------------------
Button "Konvertierung" | (Wird nur angezeigt, wenn die Listen leer und alte Links vorhanden sind) Wenn eine alte Version des Moduls erkannt wurde, können die alten Links in die neuen Listen via Knopfdruck eingepflegt werden. Ist dies Erfolgreich erscheint ein Meldungsfenster.
Sensorvariablen        | Diese Liste beinhaltet die Variablen, welche bei Aktualisierung einen Alarm auslösen.
Zielvariablen          | Diese Liste beinhaltet die Variablen, welche bei Alarm geschaltet werden. Diese müssen eine Standardaktion oder Aktionsskript beinhalten.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name         | Typ       | Beschreibung
------------ | --------- | ----------------
Active       | Boolean   | De-/Aktiviert die Alarmierung. Wird die Alarmierung deaktiviert, so wird auch der ggf. vorhandene Alarm deaktiviert.
Alert        | Boolean   | De-/Aktiviert den Alarm.

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront kann die Alarmierung de-/aktiviert werden.  
Es wird zusätzlich angezeigt, ob ein Alarm vorliegt oder nicht.
Der Alarm kann auch manuell de-/aktiviert werden.

### 7. PHP-Befehlsreferenz

`boolean ARM_SetActive(integer $InstanzID, boolean $Value);`
Schaltet das Alarmierungsmodul mit der InstanzID $InstanzID  auf den Wert $Value (true = An; false = Aus).  
Die Funktion liefert keinerlei Rückgabewert.  
`ARM_SetActive(12345, true);`

`boolean ARM_SetAlert(integer $InstanzID, boolean $Value);`
Schaltet den Alarm mit der InstanzID $InstanzID auf den Wert $Value (true = An; false = Aus).  
Die Funktion liefert keinerlei Rückgabewert.  
`ARM_SetAlert(12345, false);`