# Anwesenheits-Simulation
Simuliert die Anwesenheit von Personen im Haushalt.
Das Modul bezieht dafür zufällig die Tagesdaten von einem der letzten 4 identischen Wochentagen. Sind an keinem dieser 4 Tage genug Schaltvorgänge geloggt, wird zufällig einer der letzten 30 Tage gewählt.  
Ist auch innerhalb dieser 30 Tage kein gültiger Tagesdatensatz vorhanden, ist keine Simulation möglich. Sollte keine Simulation möglich sein, wird dies als Nachricht in der Stringvariable "Simulationsquelle (Tag)" angezeigt.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Schalten von verlinkten Aktoren/Variablen über geloggte Werte.
* Einstellbarkeit der benötigen durchschnittlichen Schaltvorgänge, bevor ein Tag als Quelle zur Simulation zugelassen wird.
* Ein-/Ausschaltbarkeit via WebFront-Button oder Skript-Funktion.
* Anzeige welcher Tag zur Simulation genutzt wird.
* Automatische Aktualisierung bei Tageswechsel.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/symcon/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'AnwesenheitsSimulation'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  
- Alle zu schaltenden Variablen müssen in der "Targets (Simulation)"-Kategorie verlinkt werden.

__Konfigurationsseite__:

Name          | Beschreibung
------------- | ---------------------------------
Mindestanzahl | Dies beschreibt die durchschnittliche Mindestanzahl von Variablenschaltungen aller verlinkten Variablen vorhanden sein müssen.


### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

Name                    | Typ       | Beschreibung
----------------------- | --------- | ----------------
Targets (Simulation)    | Kategorie | Beinhaltet alle verlinkten Variablen, welche bei der Simulation beachtet werden sollen. (Nur Links erlaubt)
Simulation aktiv        | Boolean   | Zeigt an ob ob die Simulation aktiviert ist oder nicht. True = Aktiviert; False = Deaktiviert;
SimulationData          | String    | Der String beinhaltet die WDDX-kodierten Tagesdaten, welche genutzt wird um die Varaiblen zu schalten.
Simulationsquelle (Tag) | String    | Der String beinhaltet das Datum, nach dem die Simulationsdaten ausgewählt wurden.
UpdateTargetsTimer      | Timer     | Zum automatisch berechneten Zeitpunkt werden alle Variablen geschaltet/aktualisiert.
UpdateTargetTimer       | Timer     | Zum automatisch berechneten Zeitpunkt werden die Tagesdaten um 00:00:01 für den neuen Tag berechnet.

Es werden keine zusätzlichen Profile benötigt.

### 6. WebFront

Über das WebFront kann die Simulation de-/aktiviert werden.  
Es wird zusätzlich die Information angezeigt, welcher Tag zur Simulation genutzt wird.  
Falls nicht genügend oder ungültige Daten vorhanden sind, wird dieses ebenfalls hier angezeigt.

### 7. PHP-Befehlsreferenz

`boolean AS_SetSimulation(integer $InstanzID, boolean $SetActive);`  
$SetActive aktiviert (true) oder deaktiviert (false) die Anwesenheits-Simulation mit der InstanzID $InstanzID.  
Die Funktion liefert keinerlei Rückgabewert.  

Beispiel:  
`AS_SetSimulation(12345, true);`