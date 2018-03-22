# USB Mapper
Das Modul setzt automatisch den korrekten USB-Port zu den eingetragenen Serial-Ports.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Das Modul mappt automatisch eingetragene SerialPorts auf den jeweiligen korrekten USB-Port.
* Es können mehere Serial-Ports in der Liste eingetragen werden.
* Es wird bei Start von IP-Symcon und jede Minute kontrolliert ob die SerialPorts korrekt konfiguriert sind.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.2
- Linux oder Raspberry Pi

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/symcon/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'USBMapper'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name    | Beschreibung
------- | ---------------------------------
Geräte  | Liste, welche die zu mappenden SerialPorts beinhaltet

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es sind keine Statusvariablen vorhanden.

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt.

### 6. WebFront

Das Modul wird nich im WebFront visualisiert.

### 7. PHP-Befehlsreferenz

`boolean USBM_FixPorts(integer $InstanzID);`  
Kontrolliert alle, in der Liste des USBMappers mit der InstanzID $InstanzID, eingetragenen SerialPorts, ob diese noch die richten USB-Ports konfiguriert haben.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`USBM_FixPorts(12345);`