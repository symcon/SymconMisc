# Umrechnen

_Die aktuelle Version dieses Moduls ist nicht mehr in SymconMisc verfügbar._
_Die aktuelle Version finden Sie in dem seit Version 5.1 verfügbaren Module Store._
_Alternativ können Sie es über das Module Control installieren: https://github.com/symcon/Rechenmodule_


### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Berechnet über eine eingerichtete Formel einen Wert aus einer ausgewählten Quellvariable.
* Bei Variablenänderung der Quellvariable wird der Wert automatisch neuberechnet.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/symcon/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Umrechnen'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name               | Beschreibung
------------------ | ---------------------------------
Quelle             | Quellvariable, die Berechnung genutzt werden soll.
Formel             | Formel, bei der Rechnung genutzt werden soll.
Wert               | Testwert um die Formel zu Testen
Button "Calculate" | Berechnet den Wert anhand des Test-"Wert"


### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name  | Typ     | Beschreibung
----- | ------- | ----------------
Value | Float   | Beinhaltet den anhand der eingerichteten Formel berechneten Wert.

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz

`boolean UMR_Calculate(integer $InstanzID);`  
Berechnet und setzt den Wert "Value" des Umrechnenmoduls mit der InstanzID $InstanzID anhand der Formel neu.  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`UMR_Calculate(12345);`
