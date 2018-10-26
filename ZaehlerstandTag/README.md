# Zählerstand (Tag)
Das Modul erlaubt die Auswahl eines Datums und zeigt dann den jeweiligen (Ersten/Letzten) Zählerwert dieses Tages an.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Bei Änderung des Datums wird je nach Wert-Auswahl der Erste/Letzte gelesene Zählerwert der geloggten Variable ausgegeben.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/symcon/SymconMisc.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Zählerstand (Tag)'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.  

__Konfigurationsseite__:

Name        | Beschreibung
----------- | ---------------------------------
Quelle      | Quellvariable, welche als Datenquelle genutzt werden soll.
Wert        | Erster/Letzter Wert des Tages.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name        | Typ           | Beschreibung
----------- | ------------- | ----------------
Zählerstand | Integer/Float | Wert für das ausgewählte Datum.

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront wird die Variable angezeigt. Es kann ein Datum gewählt werden für das der Zählerstand (jeweils der Erste/Letzte des Tages) angezeigt wird.

### 7. PHP-Befehlsreferenz

`boolean ZST_Calculate(integer $InstanzID);`  
Ermittelt den Wert für das gewählte Datum neu.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`ZST_Calculate(12345);`