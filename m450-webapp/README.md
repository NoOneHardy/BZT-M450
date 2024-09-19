# ICT Modul 450 - Applikationen testen

Dies ist das Starter-Projekt für das Modul M450 - Applikationen testen.
Es stellt ein Starter-Projekt, eine kleine Web-Applikation, zur Verfügung,
welche als Anschauungs- und Übungsobjekt für das Modul dient.

## Für die Ungeduldigen: Schnellstart

Die Applikation wird als docker compose-Projekt vorkonfiguriert ausgeliefert. Start des gesamten Stacks mit:

```sh
cd pfad/zum/projekt
docker compose up -d
```

Nun läuft die Applikation unter http://localhost:8020/


## Die Applikation

Abgegeben wird eine kleine Web-Applikation auf Basis [Slim PHP Framework](https://www.slimframework.com/):

* die Applikation stellt Wetterdaten zur Verfügung, welche sie von einer externen API (https://openweathermap.org/) holt
* Das Abholen wird mittels eines Komandozeilen-Clients gemacht, welcher periodisch oder manuell ausgeführt wird.
* Die Daten werden in einer lokalen SQLite-Datenbank gespeichert
* Das Web-UI stellt zu Beginn nur Einzeldaten für einen Tag und einen Ort (PLZ) zur Verfügung

**Die Applikation ist absichtlich unschön und an einigen Stellen buggy implementiert**: Ziel des Moduls M450 ist das Erlernen
von Software-Testpraktiken: Dazu gehört auch Debugging, Refactoring.

### System-Architektur

Die Applikation wird als Docker-Compose-Projekt abgegeben: Es werden folgende Container hochgefahren:

* `web`: Ein Apache / PHP-Docker-Container für das Ausliefern der Web-Applikation und des
   Kommandozeilen-Clients, hört auf Port `8020`

Die **Web-Applikation** ist eine kleine [Slim PHP](https://www.slimframework.com/)-Applikation, welches in einer ersten
Version Wetterdaten zu einem ausgewählten Zeitpunkt anzeigt.

### Konfiguration und Start

Die Applikation wird über ein `.env`-File (Umgebungsvariablen) konfiguriert: Das `.env`-File wird im Hauptverzeichnis abgelegt (Stammordner, am selben Ort wie `docker-compose.yml`), und definiert die relevanten Parameter als Umgebungsvariablen:

```
#.env:
COMPOSE_PROJECT_NAME=m450
# Openweather API Key: siehe https://openweathermap.org/price, "Free"-Tier:
OPENWEATHER_KEY=xxxxxxxxxxxxxxxxxxx
```

Die Datenbankparameter sind bewusst hardcodiert als Umgebungsvariablen in `docker-compose.yml` gesetzt:

- `SQLITE_DB_PATH=/data/weather.db`

Diese Umgebungsvariable definiert den Pfad (innerhalb des Docker-Containers) zur SQLite-Datenbank.

Danach wird das Projekt mittels `docker compose` gestartet:

```
cd pfad/zum/projekt
docker compose up -d
```

Nun läuft die Applikation unter http://localhost:8020/

## Was ist umgesetzt?

Die Applikation ist ein kleines Anschauungsbeispiel für das Modul M450.
Es beinhaltet eine kleine Wetter-Applikation:

* welche Wetterdaten von <http://openweathermap.org> in die Datenbank importieren kann:
  * Script `webroot/scripts/import_weather.php`
  * Script `webroot/scripts/import_air_pollution.php`
* welche via Webseite (<http://localhost:8020>) die gesammelten Daten eines bestimmten Zeitpunktes abrufen kann
* Die Anforderung ist durch ein paar rudimentäre Use-Cases (`./use-cases/`) beschrieben.

**Ziel des Moduls ist es:**

* für die Applikation Test-Konzepte und -Pläne zu entwickeln
* Tests aus gegebenen Use-Cases abzuleiten
* automatisierte Tests für Code-Teile schreiben
* Code nach Clean-Code-Prinzipien zu refactoren, um den Code überhaupt
  testbar zu machen.

Der Code hat **bewusst** folgende Macken:

* er ist schlecht testbar
* er beinhaltet Fehler
* er ist wüst programmiert (grosse, unhandliche Funktionen, Duplikate, ...)
* er setzt noch nicht alles um, was die Use-Cases beschreiben

Dies soll im Verlauf des Moduls verbessert werden.

---
&copy; 2023 Alexander Schenkel, <mailto:alexander.schenkel@bztf.ch>