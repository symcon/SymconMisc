# EnergiezählerImpuls
Das Modul berechnet via eines Stromzählers (z.B. S0-Anschluss) den momentanen und kumlativen Stromverbrauch.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Berechnet den momentanen Stromverbrauch in Watt und den gesamten Stromverbrauch in kWh.
* Einstellbarkeit der Impulse des zählenden Geräts.
* Einstellbarkeit der Intervalhäufigkeit in Sekunden zur Neuberechnung des Verbrauchs

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/symcon/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'EnergiezählerImpuls'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name      | Beschreibung
--------- | ---------------------------------
Quelle    | Quellvariable (Impulszähler), welche für die Berechnung genutzt werden soll.
Impulse   | Wieviele Impulse das Gerät pro Kilowatt sendet. Dies muss der jeweiligen Betriebsanleitung des Geräts entnommen werden.
Intervall | In welchem Sekunden-Intervall automatisch neuberechnet werden soll. (Hinweis: Zu kurz eingestellte Intervalle (schneller als einkommende Impulse), können zu starken Schwankungen bei der Angabe des aktuellen Verbrauch führen. Es hat keinen Einfluss auf den berechneten Gesamtverbrauch. => Empfehlung: mind. 300 Sekunden)


### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name                   | Typ     | Beschreibung
---------------------- | ------- | ----------------
Current                | Float   | Angabe in W
Counter                | Float   | Angabe in kWh
Last Value (Temporary) | Float   | Hilfsvariable für letzten Wert. Wird zur Differenzberechnung benötigt.

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz

`boolean EZS_Update(integer $InstanzID);`  
Aktualisiert die berechneten Werte des EnergieZählerImpuls-Moduls mit der InstanzID $InstanzID.  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`EZI_Update(12345);`