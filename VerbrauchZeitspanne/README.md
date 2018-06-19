# VerbrauchZeitspanne

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Berechnet für eine Zeitspanne den Verbrauch anhand der Aggregation der ausgewählten Quellvariable.
* Bei Änderung der Zeitspanne wird der Verbrauch neu ermittelt

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/symcon/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Verbrauch in Zeitspanne'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name               | Beschreibung
------------------ | ---------------------------------
Quelle             | Quellvariable, die für Verbrauch genutzt werden soll.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name        | Typ     | Beschreibung
----------- | ------- | ----------------
Start-Datum | Integer | Start-Datum für den Verbrauch
End-Datum   | Integer | End-Datum für den Verbrauch
Verbrauch   | Float   | Verbrauch zwischen Start- und End-Datum

##### Profile

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz

`boolean VIZ_Calculate(integer $InstanzID);`  
Berechnet den Verbrauch zwischen Start- und End-Datum neu.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`VIZ_Calculate(12345);`
