# Watchdog
Checkt ob verlinkte Variablen überfällig sind.
Sind Variablen überfällig, wird ein Alarm gesetzt und eine Liste dieser im WebFront angezeigt.


### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Überwachen von verlinkten Variablen.
* Einstellbarkeit wie lange die verlinkten überfällig sein dürfen.
* Ein-/Ausschaltbarkeit via WebFront-Button oder Skript-Funktion.
* Anzeige wann die verlinkten Variablen zuletzt überprüft wurden.
* Einstellbare automatische Überprüfung.
* Darstellung des Originalpfades, wenn der Link den gleichen Namen hat wie die Ursprungsvariable. Ansonsten Anzeige des Linknamens

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/symcon/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Watchdog'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  
- Alle zu schaltenden Variablen müssen in der "Targets (Watchdog)"-Kategorie verlinkt werden.

__Konfigurationsseite__:

Name       | Beschreibung
---------- | ---------------------------------
Zeit Basis | Ob der Zweitwert als Sekunden/Minuten/Stunden/Tage interpretiert werden soll.
Zeitwert   | Zeitwert bei dem, bei Überfälligkeit einer verlinkten Variable, der Alarm ausgelöst werden soll.
Intervall  | Intervall bei dem die verlinkten Veriablen überprüft werden sollen.


### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

Name               | Typ       | Beschreibung
------------------ | --------- | ----------------
Targets (Watchdog) | Kategorie | Beinhaltet alle verlinkten Variablen, welche bei der Überprüfung beachtet werden sollen. (Nur Links erlaubt)
Aktive Alarme      | String    | Beinhaltet die Tabelle für die Darstellung im WebFront.
Alarm              | Boolean   | Die Variable zeigt an ob ein Alarm vorhanden ist. True = Alarm; False = OK;
Letzte Überprüfung | Integer   | UnixTimestamp der den Zeitpunkt angibt zu dem zuletzt überprüft wurde.
Watchdog aktiv     | Timer     | Zeigt an ob der Watchdog aktiviert ist oder nicht. True = Aktiviert; False = Deaktiviert;
UpdateTargetTimer  | Timer     | Automatische Überprüfung im eingestellten Intervall.

Es werden keine zusätzlichen Profile benötigt.

### 6. WebFront

Über das WebFront kann der Watchdog de-/aktiviert werden.  
Es wird zusätzlich die Information angezeigt, zu welchem Zeitpunkt zuletzt überprüft wurde.  

### 7. PHP-Befehlsreferenz

`boolean WD_SetActive(integer $InstanzID, boolean $SetActive);`  
$SetActive aktiviert (true) oder deaktiviert (false) den Watchdog mit der InstanzID $InstanzID.  
Die Funktion liefert keinerlei Rückgabewert.  

Beispiel:  
`WD_SetActive(12345, true);`

`array WD_GetAlertTargets(integer $InstanzID, boolean $SetActive);`  
Die Funktion liefert ein Array mit den aktiven Alarmen der Watchdoginstanz mit der InstanzID $InstanzID.  
Die Funktion liefert ein Array mit überfälligen Objekten. Es beinhaltet die LinkID, VariablenID und den letzten Zeitpunkt (UnixTimestamp) des Updates.

Beispiel:  
`WD_GetAlertTargets(12345);`