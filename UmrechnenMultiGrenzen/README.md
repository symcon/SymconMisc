# UmrechnenMultiGrenzen

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Berechnet über eingerichtete Formeln einen Wert aus einer ausgewählten Quellvariable.
* Welche Formel genutzt werden soll, wird über einrichtbare Grenzwerte entschieden.
* Bei Variablenänderung der Quellvariable wird der Wert automatisch neuberechnet.
* Wenn keine Berechnung/Grenzen zutrifft/zutreffen, wird der Originalwert eingetragen.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.1

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/symcon/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'UmrechnenMultiGrenzen'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name               | Beschreibung
------------------ | ---------------------------------
Quelle             | Quellvariable, die zur Berechnung genutzt werden soll.
Formel 1-10        | Formel, die auf die Quellvariable angewendetet werden soll.
Grenze 0-10        | Grenzwerte zwischen denen die eingerichtete Formel genutzt wird.
Wert               | Testwert um die Formel zu Testen
Button "Calculate" | Berechnet den Wert anhand des Test-"Wert"

Der Wert der Quellvariable kann innerhalb der Formel mit "$Value" implementiert werden.
Beispiel:
    10*$Value+20

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

`boolean UMG_Calculate(integer $InstanzID);`  
Berechnet und setzt den Wert "Value" des UmrechnenMultiGrenzenmoduls mit der InstanzID $InstanzID anhand der Formel neu.  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`UMG_Calculate(12345);`
