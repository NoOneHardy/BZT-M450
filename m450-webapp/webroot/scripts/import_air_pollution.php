#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Service\DB;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

/**
 * Diese Kommandozeilen-App importiert aktuelle Lutfqualitätsdaten von
 * openweatherapi.org in die Datenbank.
 * 
 * Diese App ist ABSICHTLICH "grausam" programmiert:
 * 
 * - riesige, unübersichtliche Methode(n)
 * - kein Separation of Concern
 * - Config-Daten direkt im Code
 * - Spaghetti-Code 
 * - fast untestbar
 * 
 * 
 * Ziel ist, dass dieser Code im Verlauf des Moduls M450 auseinandergenommen und testbar
 * gemacht wird.
 * 
 * @package App\Controller
 */
(new SingleCommandApplication())
    ->setName('Importieren aktueller Luft-Daten per PLZ von openweathermap.org')
    ->addArgument('zip', InputArgument::REQUIRED, 'PLZ des Ortes')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        // Postleitzahl von Konsole:
        $zip = $input->getArgument('zip');
        $country = 'CH';

        // Openweather Key: von Env-Variable (siehe docker-compose.yml)
        $apiKey = getenv('OPENWEATHER_KEY');
        $geocodingApiUrl = 'http://api.openweathermap.org/geo/1.0/zip';
        $airPollutionApiUrl = 'http://api.openweathermap.org/data/2.5/air_pollution';

        // Koordinaten mit HTTP-Client holen:
        $client = new Client();
        $response = $client->get($geocodingApiUrl, [
            'query' => [
                'zip' => "{$zip},{$country}",
                'appid' => $apiKey,
            ]
        ]);

        $city = null;
        $latitude = null;
        $longitude = null;

        if ($response->getStatusCode() === 200) {
            $data = json_decode((string)$response->getBody());
            $city = $data->name;
            $longitude = $data->lon;
            $latitude = $data->lat;
        } else {
            $output->writeln("ERROR: " . $response->getReasonPhrase());
            return Command::FAILURE;
        }


        // Luft-Daten mit HTTP-Client holen:
        $output->writeln("Hole Luftdaten für PLZ: {$zip}");
        $client = new Client();
        $response = $client->get($airPollutionApiUrl, [
            'query' => [
                'lat' => $latitude,
                'lon' => $longitude,
                'appid' => $apiKey,
            ]
        ]);

        if ($response->getStatusCode() === 200) {
            $data = json_decode((string)$response->getBody());

            // Daten auslesen, normalisieren:
            $ts = date(DATE_W3C, $data->list[0]->dt ?? null) ?: null;
            $aqi = $data->list[0]->main->aqi ?? null;
            $co = $data->list[0]->components->co ?? null;
            $no = $data->list[0]->components->no ?? null;
            $no2 = $data->list[0]->components->no2 ?? null;
            $o3 = $data->list[0]->components->o3 ?? null;
            $so2 = $data->list[0]->components->so2 ?? null;
            $pm2_5 = $data->list[0]->components->pm2_5 ?? null;
            $pm10 = $data->list[0]->components->pm10 ?? null;
            $nh3 = $data->list[0]->components->nh3 ?? null;

            // Daten in Datenbank speichern
            $conn = DB::conn();
            $stm = $conn->prepare("
			INSERT INTO air_pollution
			(ts, country, zip, city, longitude, latitude, air_quality_index,
			co, no, no2, o3, so2, pm2_5, pm10, nh3)
			VALUES (
				datetime(:ts, 'localtime'), :country, :zip, :city, :longitude, :latitude, :air_quality_index,
				:co, :no, :no2, :o3, :so2, :pm2_5, :pm10, :nh3
			)
		");
            $stm->execute([
                'ts' => $ts,
                'country' => $country,
                'zip' => $zip,
                'city' => $city,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'air_quality_index' => $aqi,
                'co' => $co,
                'no' => $no,
                'no2' => $no2,
                'o3' => $o3,
                'so2' => $so2,
                'pm2_5' => $pm2_5,
                'pm10' => $pm10,
                'nh3' => $nh3,
            ]);

            $output->writeln("Luftdaten importiert für {$zip} {$city}, {$ts}");
        } else {
            $output->writeln("ERROR: " . $response->getReasonPhrase());
            return Command::FAILURE;
        }
    })
    ->run();
