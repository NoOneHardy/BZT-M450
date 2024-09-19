<?php

namespace App\Controller;

use App\Service\DB;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApiController {
    public function getWeather(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $plz = $request->getQueryParams()['zip'] ?? null;
        $date = $request->getQueryParams()['date'] ?? null;
        $time = $request->getQueryParams()['time'] ?? null;
        $dbConn = DB::conn();

        $timestamp = strtotime("{$date} {$time}");
        $dateStr = date('Y-m-d H:i', $timestamp);

        // Wir suchen den einen Eintrag der gegebenen PLZ, welcher am nächsten zum gegebenen
        // Datum/Zeitstempel ist, aber nur innerhalb einer 30min-Distanz:
        $query = "
            SELECT * FROM weather WHERE
            zip = :zip
            AND datetime(ts, 'localtime') >= datetime(:ts, '-900 second', 'localtime')
            AND datetime(ts, 'localtime') <= datetime(:ts, '+900 second', 'localtime')
            ORDER BY ts DESC
            LIMIT 1
        ";
        $stm = $dbConn->prepare($query);
        $stm->execute(['zip' => $plz, 'ts' => $dateStr]);
        $weatherdata = $stm->fetchAll(PDO::FETCH_ASSOC);

        // ... dasselbe suchen wir für die Luft-Daten:
        $query = "
            SELECT * FROM air_pollution WHERE
            zip = :zip
            AND datetime(ts, 'localtime') >= datetime(:ts, '-900 second', 'localtime')
            AND datetime(ts, 'localtime') <= datetime(:ts, '+900 second', 'localtime')
            ORDER BY ts DESC
            LIMIT 1
        ";
        $stm = $dbConn->prepare($query);
        $stm->execute(['zip' => $plz, 'ts' => $dateStr]);
        $airdata = $stm->fetchAll(PDO::FETCH_ASSOC);


        // Wir geben die Daten als JSON aus:
        $response->getBody()->write(json_encode([
            'weather' => !empty($weatherdata) ? $weatherdata[0] : null,
            'air_pollution' => !empty($airdata) ? $airdata[0] : null,
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
