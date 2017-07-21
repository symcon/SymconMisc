# WasserAlarm
Das Modul dient dazu einen unnatürlich hohen Wasserverbrauch festzustellen. Es reagiert auf eine Zählervariable und schaltet unter bestimmten Bedingungen einen Alarm.
Es gibt zwei Alarmvariablen.  
Einen Durchfluss-Alarm, welcher schaltet wenn große Mengen auf einmal fließen (z.B. Rohrbruch).
Einen Alarmlevel, welcher in 7 Stufen hochtickt wenn über längeren Zeitraum eine kleine Menge fließt (z.B. tropfender Wasserhahn)

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Auswahl der Wasserzählervariable
* Durchfluss Timer in Minuten
* 7 Stufen für Alarmlevel des kleinen Wasserverlustes
* Alarmanzeige bei Durchfluss

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/paresy/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'WasserAlarm'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name           | Beschreibung
-------------- | ---------------------------------
Zählervariable | Variable, welche den Zählerwert wiedergibt.
Intervall      | Zeitintervall in dem kontrolliert wird, ob zuviel Wasser durchgeflossen ist und möglicherweise ein Rohrbruch vorliegt. Misst das Verhältnis zum "Grenzwert Durchfluss"


### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name               | Typ       | Beschreibung
------------------ | --------- | ----------------
CountAlertBorder   | Float     | Grenzwert, welcher jede Minute überprüft wird und falls dies der Fall ist um 1 erhöht wird. Wenn er unterhalb der Grenze liegt, wird das Alarmlevel resetet. 
CounterAlertStatus | Integer   | 7 Stufenanzeige für den Stand den Alarmlevels.
CounterValueOld    | Float     | Alter Wert, welcher zum Vergleich genutzt wird.
FlowAlertBorder    | Float     | Grenzwert, welcher geprüft wird und gegebenfalls der Auslöser für einen Alarm ist. 
FlowAlertStatus    | Boolean   | Alarm ob der Durchfluss zu hoch ist. 
FlowValueOld       | Float     | Alter Wert, welcher zum Vergleich genutzt wird.

##### Profile:

Bezeichnung     | Beschreibung
--------------- | -----------------
WAA.AlertLevel  | Profil für CountAlertStatus - 7 Alarmstufen mit verschiedenen Symbolen und Farbanzeigen
WAA.BorderValue | Profil für Flow-/CountAlertBorder

### 6. WebFront

Über das WebFront können die Grenzwerte eingestellt werden.  
Es wird zusätzlich angezeigt, ob ein Alarm vorliegt oder nicht.

### 7. PHP-Befehlsreferenz

`boolean WAA_CheckAlert(integer $InstanzID, String $BorderValue, String $OldValue);`
Kontrolliert innerhalb des WasserAlarms mit der InstanzID $InstanzID ob ein Grenzwert überschritten wurde und setzt die Alarmvariablen  
Die Funktion liefert keinerlei Rückgabewert.  
`WAA_CheckAlert(12345, "CountAlertBorder", "CounterValueOld");``