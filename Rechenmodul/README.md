# Energie-Ampel
Das Modul analysiert den Verbrauch und die Produktion von Energie. Die Rohdaten werden vom Modul aufbereitet und bieten dem Benutzer verschiedene Übersichten.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Diverse Übersichten bezogen auf 
  * die aktuelle Woche
  * den aktuellen Monat
  * das aktuelle Jahr
* Ausgabe von Kosten und Ertrag für Energie
* Bestimmung einer Tendenz des Energieverbrauchs im Vergleich zum Vorjahr oder Wunschwert
  * Geplanter Verbrauch kann individuell auf die Monate eines Jahres verteilt werden

### 2. Voraussetzungen

- IP-Symcon ab Version 4.3

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/paresy/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Energie-Ampel'-Modul unter dem Hersteller '(Sonstiges)' aufgeführt.  

__Konfigurationsseite__:
___Erwarteter Energieverbrauch___:

Name                  | Beschreibung
--------------------- | ---------------------------------
Vergleich mit Vorjahr | Ist die Checkbox aktiviert so wird die Tendenz auf Basis des Verbrauches im Vorjahr bestimmt, ansonsten auf Basis der angegebenen geplanten kWh / Jahr
Geplante kWh / Jahr   | Geplanter Jahresverbrauch für die Bestimmung der Tendenz falls 'Vergleich mit Vorjahr' nicht aktiviert ist

___Startmonat für die Berechnung___:

Name       | Beschreibung
---------- | ---------------------------------
Startmonat | Der Startmonat für die jährliche Auswertung. Die Berechnung aller Ausgaben der jährlichen Auswertung erfolgen auf Basis der Daten, welche seit dem letzten Startmonat gesammelt wurden

___Verbrauch___:

Name              | Beschreibung
----------------- | ---------------------------------
Variable          | Variable in welcher der Verbrauch in kWh geloggt wird
Cent / kWh        | Energiepreis für den Energiebezug zur Berechnung der Kosten
Verbrauch / Monat | Verteilung des geplanten Energieverbrauchs im Laufe des Jahres. Für jeden Monat wird der prozentuale Anteil des Energieverbrauchs angegeben

___Erzeugung___:

Name       | Beschreibung
---------- | ---------------------------------
Variable   | Variable in welcher die erzeugte Energie in kWh geloggt wird
Cent / kWh | Energiepreis für den Energieverkauf zur Berechnung des Gewinns

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Für die Zeitintervalle Jahr, Monat und Woche werden jeweils eine eigene Menge an Variablen erstellt, welche durch einen Prefix ihre Zugehörigkeit zeigen. Die Variablen für Woche und Monat beschreiben den Verlauf seit Beginn des letzten Beginns eines entsprechenden Zeitintervalls, also seit dem letzten Montag oder seit dem ersten Tag des aktuellen Monats. Die Variablen für das jährliche Intervall beschreiben den Verlauf seit dem ersten Tages des letzten angegebenen Startmonats. Die Variablen werden automatisch stündlich aktualisiert.

Name               | Typ      | Beschreibung
------------------ | -------- | ----------------
Tendenz            | Variable | Die Tendenz des aktuellen Verbrauchs. Bei 100% wird innerhalb des laufenden Zeitintervalls genau die anvisierte Menge an Energie verbraucht
Verbauch           | Ereignis | Der Energieverbrauch innerhalb des laufenden Zeitintervalls
Verbrauch (Ertrag) | Variable | Die Kosten für den Energieverbrauch des laufenden Zeitintervalls
Erzeugung          | Variable | Die erzeugte Energie innerhalb des laufenden Zeitintervalls
Erzeugung (Ertrag) | Variable | Der Gewinn für die erzeugte Energie Energie innerhalb des laufenden Zeitintervalls
Gesamt             | Variable | Die Differenz zwischen den Erträgen für Verbrauch und Erzeugung

##### Profile:

Name        | Typ
----------- | -------
Euro.EA     | Float
Tendency.EA | Integer

### 6. WebFront

Über das WebFront werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz

`boolean EA_UpdateAll(integer $InstanzID);`  
Aktualisiert alle Statusvariablen
Beispiel:  
`EA_UpdateAll(12345);`

`boolean EA_UpdateWeek(integer $InstanzID);`  
Aktualisiert die Statusvariablen des wöchentlichen Zeitintervalls
Beispiel:  
`EA_UpdateWeek(12345);`

`boolean EA_UpdateMonth(integer $InstanzID);`  
Aktualisiert die Statusvariablen des monatlichen Zeitintervalls
Beispiel:  
`EA_UpdateMonth(12345);`

`boolean EA_UpdateYear(integer $InstanzID);`  
Aktualisiert die Statusvariablen des jährlichen Zeitintervalls
Beispiel:  
`EA_UpdateYear(12345);`