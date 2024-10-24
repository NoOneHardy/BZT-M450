<?php

namespace App\Controller;

use App\Model\WeatherData;
use App\Service\DB;
use GuzzleHttp\Client;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;


/**
 * Diese Controller-Klasse liefert die Demo-Webseite und die zugehörigen Endpoints für
 * die Wetter-Demo-App.
 *
 * Dieser Controller ist ABSICHTLICH "grausam" programmiert:
 *
 * - riesige, unübersichtliche Methode(n)
 * - kein Separation of Concern
 * - Config-Daten direkt im Code
 * - Spaghetti-Code
 * - fast untestbar
 *
 *
 * Ziel ist, dass diese Klasse im Verlauf des Moduls M450 auseinandergenommen und testbar
 * gemacht wird.
 *
 * @package App\Controller
 */
class HomeController {
    /**
     * Liefert die Index (Home)-Seite aus: liefert das User-Interface mit dem HTML-Form
     * aus dem Template home/index.tpl.html
     */
    public function index(ServerRequestInterface $req, ResponseInterface $response, array $args): ResponseInterface {
        // Set up Twig, the Template engine:
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $twig = new Environment($loader, [
            'cache' => false,
        ]);
        $template = $twig->load('home/index.tpl.html');

        $dbConn = DB::conn();

        // Laden der PLZ-Einträge für Select
        $zips = $dbConn
            ->query("SELECT DISTINCT zip, city FROM weather ORDER BY zip")
            ->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write($template->render([
            'now' => time(),
            'zips' => $zips
        ]));
        return $response;
    }

    public function getQueryParams(ServerRequestInterface $request): array {
        $params = $request->getQueryParams();
        return [
            'mode' => $params['mode'] ?? 'historic',
            'zip' => $params['zip'] ?? null,
            'date' => $params['date'] ?? null,
            'time' => $params['time'] ?? null
        ];
    }

    public function getHistoricData(array $params): array {
        $date = $params['date'];
        $time = $params['time'];
        $zip = $params['zip'];

        $timestamp = date('Y-m-d H:i', strtotime("{$date} {$time}"));

        return [
            'weather' => $this->getHistoricWeatherData($timestamp, $zip),
            'air' => $this->getHistoricAirData($timestamp, $zip)
        ];
    }

    public function getHistoricWeatherData(string $timestamp, string $zip): array {
        $db = DB::conn();

        $query = "SELECT * FROM weather WHERE zip = :zip
        AND datetime(ts, 'localtime') >= datetime(:ts, '-900 second', 'localtime')
        AND datetime(ts, 'localtime') <= datetime(:ts, '+900 second', 'localtime')
        ORDER BY ts DESC
        LIMIT 1";

        $stm = $db->prepare($query);
        $stm->execute([
            'zip' => $zip,
            'ts' => $timestamp
        ]);
        $weatherdata = $stm->fetchAll(PDO::FETCH_ASSOC);
        // 1. Record aus Result extrahieren:
        if (!empty($weatherdata)) {
            return $weatherdata[0];
        }
        return [];
    }

    public function getHistoricAirData(string $timestamp, string $zip): array {
        $db = DB::conn();
        $query = "SELECT * FROM air_pollution WHERE zip = :zip
        AND datetime(ts, 'localtime') >= datetime(:ts, '-900 second', 'localtime')
        AND datetime(ts, 'localtime') <= datetime(:ts, '+900 second', 'localtime')
        ORDER BY ts DESC
        LIMIT 1";
        $stm = $db->prepare($query);
        $stm->execute([
            'zip' => $zip,
            'ts' => $timestamp
        ]);

        $airdata = $stm->fetchAll(PDO::FETCH_ASSOC);
        // 1. Record aus Result extrahieren:
        if (!empty($airdata)) {
            return $airdata[0];
        }

        return [];
    }

    public function getCurrentData(string $zip): array {
        $weather = $this->getCurrentWeatherData($zip);
        return [
            'weather' => $weather->toArray(),
            'air' => $this->getCurrentAirData(
                $zip, $weather->getLatitude(),
                $weather->getLongitude(),
                $weather->getCity()
            )
        ];
    }

    public function getCurrentWeatherData(string $zip): WeatherData {
        $apiKey = getenv('OPENWEATHER_KEY');
        $apiUrl = 'https://api.openweathermap.org/data/2.5/weather';
        $lang = 'de';
        $units = 'metric';
        $client = new Client();
        $apiResponse = $client->get($apiUrl, [
            'query' => [
                'zip' => "{$zip},CH",
                'units' => $units,
                'lang' => $lang,
                'appid' => $apiKey,
            ]
        ]);

        if ($apiResponse->getStatusCode() === 200) {
            $data = json_decode((string)$apiResponse->getBody());
            return $this->normalizeWeatherData($data);
        }
        return new WeatherData();
    }

    public function normalizeWeatherData(object $data): WeatherData {
        $weatherData = new WeatherData();
        $weatherData->setTs(date(DATE_W3C, $data->dt ?? null) ?: null);
        $weatherData->setCity($data->name ?? null);
        $weatherData->setLongitude($data->coord->lon ?? null);
        $weatherData->setLatitude($data->coord->lat ?? null);
        $weatherData->setDescription($data->weather[0]->description ?? null);
        $weatherData->setIcon($data->weather[0]->icon ?? null);
        $weatherData->setTemp($data->main->temp ?? null);
        $weatherData->setTempFeelsLike($data->main->feels_like ?? null);
        $weatherData->setTempMin($data->main->temp_min ?? null);
        $weatherData->setTempMax($data->main->temp_max ?? null);
        $weatherData->setPressure($data->main->pressure ?? null);
        $weatherData->setHumidity($data->main->humidity ?? null);
        $weatherData->setWindSpeed($data->wind->speed ?? null);
        $weatherData->setWindDegree($data->wind->deg ?? null);
        $weatherData->setWindGust($data->wind->gust ?? null);
        $weatherData->setCloudsPercentage($data->clouds->all ?? null);
        $weatherData->setSunrise(date(DATE_W3C, $data->sys->sunrise ?? 0));
        $weatherData->setSunset(date(DATE_W3C, $data->sys->sunset ?? 0));

        return $weatherData;
    }

    public function getCurrentAirData(string $zip, string $latitude, string $longitude, string $city): array {
        $airPollutionApiUrl = 'http://api.openweathermap.org/data/2.5/air_pollution';
        $apiKey = getenv('OPENWEATHER_KEY');

        $client = new Client();
        $apiResponse = $client->get($airPollutionApiUrl, [
            'query' => [
                'lat' => $latitude,
                'lon' => $longitude,
                'appid' => $apiKey,
            ]
        ]);

        if ($apiResponse->getStatusCode() === 200) {
            $data = json_decode((string)$apiResponse->getBody());
            return $this->normalizeAirData($data, $zip, $latitude, $longitude, $city);
        }
        return [];
    }

    public function normalizeAirData(object $data, string $zip, string $latitude, string $longitude, string $city): array {
        return [
            'zip' => $zip,
            'city' => $city,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'ts' => date(DATE_W3C, $data->list[0]->dt ?? null) ?: null,
            'aqi' => $data->list[0]->main->aqi ?? null,
            'co' => $data->list[0]->components->co ?? null,
            'no' => $data->list[0]->components->no ?? null,
            'no2' => $data->list[0]->components->no2 ?? null,
            'o3' => $data->list[0]->components->o3 ?? null,
            'so2' => $data->list[0]->components->so2 ?? null,
            'pm2_5' => $data->list[0]->components->pm2_5 ?? null,
            'pm10' => $data->list[0]->components->pm10 ?? null,
            'nh3' => $data->list[0]->components->nh3 ?? null,
        ];
    }

    /**
     * Endpoint für den Ajax-Call vom Frontend: Lädt Wetter- und
     * Luftdaten, entweder aus der Datenbank oder live von der API,
     * und liefert ein HTML-Snipped mit dem Ergebnis zurück.
     * HTML-Template: home/weatherdata.tpl.html
     *
     * Request GET parameter:
     * - mode: historic oder actual (Daten aus DB oder von API)
     * - zip: Postleitzahl
     * - date: Datum für historische Daten
     * - time: Zeit für historische Daten
     *
     * als Land wird immer CH angenommen.
     */
    public function getWeatherDataHtml(
        ServerRequestInterface $request,
        ResponseInterface      $response,
        array                  $args
    ): ResponseInterface {
        $params = $this->getQueryParams($request);

        $data = match ($params['mode']) {
            'actual' => $this->getCurrentData($params['zip']),
            default => $this->getHistoricData($params),
        };

        $response->getBody()->write($this->renderHTML($data));
        return $response;
    }

    private function renderHTML(array $data): string {
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $twig = new Environment($loader, [
            'cache' => false,
        ]);
        $template = $twig->load('home/weatherdata.tpl.html');

        return $template->render([
            'weatherdata' => $data['weather'],
            'airdata' => $data['air'],
            'aqi_map' => [
                1 => "Gut",
                2 => "Angemessen",
                3 => "Mässig",
                4 => "Schlecht",
                5 => "Sehr schlecht",
            ]
        ]);
    }
}
