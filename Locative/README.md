# Locative
Das Modul dient zum Abfragen von Locative Daten. Zusätzliches ist es ein Beispiel, wie man den Symcon Connect Dienst als OAuth Client verwenden kann.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [WebFront](#6-webfront)
6. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)


### 1. Funktionsumfang

* OAuth-Authentifierung über Symcon Connect
* Abfrage der Daten über die Locative API.
* Richtet automatisch den OAuth Endpunkt "/oauth/locative" ein.
 * Dieser kann ausschließlich mit dem Symcon Connect Dienst verwendet werden.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.1
- Locative Account

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.
`git://github.com/paresy/SymconMisc.git`

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Locative'-Modul unter dem Hersteller '(Kern)' aufgeführt.

__Konfigurationsseite__:

Name  | Beschreibung
----- | ---------------------------------
Token | Der von Locative abgerufene Baerer Token. Ein Token kann über den Registrieren Button abgerufen werden.

##### Statusvariablen

Es werden keine Statusvariablen erstellt

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 5. WebFront

Es gibt keine native Darstellung via WebFront oder in den mobilen Apps.
Alle Daten können über die PHP Funktionen abgefragt werden.

### 6. PHP-Befehlsreferenz

`string LOCA_RequestUserInformation(integer $InstanzID);`  
Fragt die Benutzerinformationen ab
Die Funktion liefert einen String, welcher als JSON dekodiert werden kann.  
Beispiel:  
`var_dump(json_decode(LOCA_RequestUserInformation(12345)));`

`string LOCA_RequestFencelogs(integer $InstanzID);`  
Fragt die Fencelogs ab
Die Funktion liefert einen String, welcher als JSON dekodiert werden kann.  
Beispiel:  
`var_dump(json_decode(LOCA_RequestFencelogs(12345)));`

`string LOCA_RequestGeofences(integer $InstanzID);`  
Fragt die Geofences ab
Die Funktion liefert einen String, welcher als JSON dekodiert werden kann.  
Beispiel:  
`var_dump(json_decode(LOCA_RequestGeofences(12345)));`
