# RGB-Multiplexer

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Bietet ein Farbrad, welches im Hintergrund drei einzelne R, G, B Kanäle ansteuert
* Sollten die R, G, B Kanäle den Wert ändern wird der neue Zustand auch ins Farbrad übertragen

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/symcon/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'RGB Multiplexer'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name                | Beschreibung
------------------- | ---------------------------------
Variable (R)        | Variable für den Rot Kanal
Variable (G)        | Variable für den Grün Kanal
Variable (B)        | Variable für den Blau Kanal
Button "RGB setzen" | Sendet den RGB-Wert an die Kanäle 


### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name  | Typ     | Beschreibung
----- | ------- | ----------------
Color | Integer | Beinhaltet Wert passend zum Farbrad

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront werden die Variablen angezeigt. Über das Farbrad kann die Farbe eingestellt werden.

### 7. PHP-Befehlsreferenz

`boolean RGBM_SetRGB(integer $InstanzID, integer $Rot, integer $Gruen, integer, $Blau);`  
Sendet die R, G, B Werte an die einzelnen Kanäle und aktualisiert die Color Variable  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`RGBM_SetRGB(12345, 255, 255, 255);`

`boolean RGBM_RequestStatus(integer $InstanzID);`  
Ermittelt den Wert der einzelnen R, G, B Kanäle und setzt die Color Variable. Diese Funktion muss nicht aufgerufen werden. Dies passiert bei Änderung einer der Variable der R, G, B Kanäle automatisch.   
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`RGBM_RequestStatus(12345);`
