# FertigMelder

_Die aktuelle Version dieses Moduls ist nicht mehr in SymconMisc verfügbar._
_Die aktuelle Version finden Sie in dem seit Version 5.1 verfügbaren Module Store._
_Alternativ können Sie es über das Module Control installieren: https://github.com/symcon/FertigMelder_

Der FertigMelder meldet ob ein Gerät fertig ist.
Dazu wird die Variable der Leistungsaufnahme des Geräts ausgewählt und ein Grenzwert festgelegt.
Wird dieser Grenzwert unterschritten und in einer einstellbaren Zeitspanne nicht wieder überschritten, wird die Statusvariable auf "Fertig" gesetzt. 

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Ein/Ausschaltbarkeit des gesamten Moduls
* Auswahl einer Quellvariable
* Setzen des Grenzwerts
* Zeitspanne bis Fertigmeldung nach Grenzwertunterschreitung gesetzt wird

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/symcon/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Fertigmelder'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name      | Beschreibung
--------- | ---------------------------------
Quelle    | Quellvariable, welche zum Vergleich mit dem Grenzwert genutzt wird.
Grenzwert | Wert bei desssen Unterschreitung eine Fertigmeldung gesetzt wird.
Intervall | Zeitraum bis eine Fertigmeldung gesetzt wird. Wird erst aktiv nachdem der Grenzwert unterschritten wird.


### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name   | Typ     | Beschreibung
------ | ------- | ----------------
Active | boolean | Schaltet das Modul Ein/Aus
Status | integer | Spiegelt die Status Aus/Läuft/Fertig wieder

##### Profile:

Name      | Typ
--------- | ------- 
FM.Status | Integer

### 6. WebFront

Über das WebFront oder in den mobilen Apps werden Werte angezeigt.
Das gesammte Modul kann über das WebFront oder die mobilen App de-/aktiviert werden.

### 7. PHP-Befehlsreferenz

Es sind keine besonderen Funktionen vorhanden.