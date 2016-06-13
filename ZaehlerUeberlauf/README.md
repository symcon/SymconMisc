# Zählerüberlauf
Das Modul stellt überlaufende Zähler als kontinuierliche Zähler dar.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Berechnet den Gesamtwert einer Variable und zählt diese hoch, obwohl das Gerät einen Überlauf hat.
* Erstellt ein Ereignis, welches auf Variablenänderung der Quellvariable reagiert.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/paresy/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Zählerüberlauf'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name        | Beschreibung
----------- | ---------------------------------
Quelle      | Quellvariable, welche für die Berechnung genutzt werden soll.
Maximalwert | Ab welchem Wert ein Überlauf stattfindet. Der maximale Wert, welcher das Gerät zählt.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name    | Typ   | Beschreibung
------- | ----- | ----------------
Counter | Float | Fortlaufend hochzählender Wert.

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront wird die Variable angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz

`boolean ZUL_Update(integer $InstanzID, float $OldValue, float $Value);`  
Berechnet über $OldValue und $Value den Überlauf und setzt den Wert Counter.  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`ZUL_Update(12345, $_IPS['OLDVALUE'], $_IPS['VALUE']);`