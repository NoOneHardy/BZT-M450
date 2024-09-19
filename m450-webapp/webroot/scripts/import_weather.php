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
 * Diese Kommandozeilen-App importiert aktuelle Wetterdaten von
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
    ->setName('Importieren aktueller Wetterdaten per PLZ von openweathermap.org')
    ->addArgument('zip', InputArgument::REQUIRED, 'PLZ des Ortes')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        // Postleitzahl von Konsole:
        $zip = $input->getArgument('zip');

        // Openweather Key: von Env-Variable (siehe docker-compose.yml)
        $apiKey = getenv('OPENWEATHER_KEY');
        $apiUrl = 'https://api.openweathermap.org/data/2.5/weather';
        $lang = 'de';
        $units = 'metric';
        $country = 'CH';

        $output->writeln("Hole Wetterdaten für PLZ: {$zip}");

        // Wetterdaten mit HTTP-Client holen:
        $client = new Client();
        $response = $client->get($apiUrl, [
            'query' => [
                'zip' => "{$zip},{$country}",
                'units' => $units,
                'lang' => $lang,
                'appid' => $apiKey,
            ]
        ]);

        if ($response->getStatusCode() === 200) {
            $data = json_decode((string)$response->getBody());

            // Daten auslesen, normalisieren:
            $ts = date(DATE_W3C, $data->dt ?? null) ?: null;
            $city = $data->name ?? null;
            $longitude = $data->coord->lon ?? null;
            $latitude = $data->coord->lat ?? null;
            $description = $data->weather[0]->description ?? null;
            $icon = $data->weather[0]->icon ?? null;
            $temp = $data->main->temp ?? null;
            $temp_feels_like = $data->main->feels_like ?? null;
            $temp_min = $data->main->temp_min ?? null;
            $temp_max = $data->main->temp_max ?? null;
            $pressure = $data->main->pressure ?? null;
            $humidity = $data->main->humidity ?? null;
            $wind_speed = $data->wind->speed ?? null;
            $wind_degree = $data->wind->deg ?? null;
            $wind_gust = $data->wind->gust ?? null;
            $clouds_percentage = $data->clouds->all ?? null;
            $sunrise = date(DATE_W3C, $data->sys->sunrise ?? 0);
            $sunset = date(DATE_W3C, $data->sys->sunset ?? 0);

            // Daten in Datenbank speichern
            $conn = DB::conn();
            $stm = $conn->prepare("
			INSERT INTO weather
			(ts, country, zip, city, longitude, latitude, description, icon, temp, temp_feels_like, temp_min, temp_max,
			pressure, humidity, wind_speed, wind_degree, wind_gust, clouds_percentage, sunrise, sunset)
			VALUES (
				datetime(:ts, 'localtime'), :country, :zip, :city, :longitude, :latitude, :description, :icon,
				:temp, :temp_feels_like, :temp_min, :temp_max,
				:pressure, :humidity, :wind_speed, :wind_degree, :wind_gust, :clouds_percentage, :sunrise, :sunset
			)
		");
            $stm->execute([
                'ts' => $ts,
                'country' => $country,
                'zip' => $zip,
                'city' => $city,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'description' => $description,
                'icon' => $icon,
                'temp' => $temp,
                'temp_feels_like' => $temp_feels_like,
                'temp_min' => $temp_min,
                'temp_max' => $temp_max,
                'pressure' => $pressure,
                'humidity' => $humidity,
                'wind_speed' => $wind_speed,
                'wind_degree' => $wind_degree,
                'wind_gust' => $wind_gust,
                'clouds_percentage' => $clouds_percentage,
                'sunrise' => $sunrise,
                'sunset' => $sunset
            ]);

            $output->writeln("Wetter importiert für {$zip} {$city}, {$ts}");
        } else {
            $output->writeln("ERROR: " . $response->getReasonPhrase());
            return Command::FAILURE;
        }
    })
    ->run();
