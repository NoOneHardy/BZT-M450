<?php

namespace App\Model;

class WeatherData {
    private ?string $ts;
    private ?string $city;
    private ?string $longitude;
    private ?string $latitude;
    private ?string $description;
    private ?string $icon;
    private ?string $temp;
    private ?string $temp_feels_like;
    private ?string $temp_min;
    private ?string $temp_max;
    private ?string $pressure;

    public function getTs(): ?string {
        return $this->ts;
    }

    public function setTs(?string $ts): void {
        $this->ts = $ts;
    }

    public function getCity(): ?string {
        return $this->city;
    }

    public function setCity(?string $city): void {
        $this->city = $city;
    }

    public function getLongitude(): ?string {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): void {
        $this->longitude = $longitude;
    }

    public function getLatitude(): ?string {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): void {
        $this->latitude = $latitude;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getIcon(): ?string {
        return $this->icon;
    }

    public function setIcon(?string $icon): void {
        $this->icon = $icon;
    }

    public function getTemp(): ?string {
        return $this->temp;
    }

    public function setTemp(?string $temp): void {
        $this->temp = $temp;
    }

    public function getTempFeelsLike(): ?string {
        return $this->temp_feels_like;
    }

    public function setTempFeelsLike(?string $temp_feels_like): void {
        $this->temp_feels_like = $temp_feels_like;
    }

    public function getTempMin(): ?string {
        return $this->temp_min;
    }

    public function setTempMin(?string $temp_min): void {
        $this->temp_min = $temp_min;
    }

    public function getTempMax(): ?string {
        return $this->temp_max;
    }

    public function setTempMax(?string $temp_max): void {
        $this->temp_max = $temp_max;
    }

    public function getPressure(): ?string {
        return $this->pressure;
    }

    public function setPressure(?string $pressure): void {
        $this->pressure = $pressure;
    }

    public function getHumidity(): ?string {
        return $this->humidity;
    }

    public function setHumidity(?string $humidity): void {
        $this->humidity = $humidity;
    }

    public function getWindSpeed(): ?string {
        return $this->wind_speed;
    }

    public function setWindSpeed(?string $wind_speed): void {
        $this->wind_speed = $wind_speed;
    }

    public function getWindDegree(): ?string {
        return $this->wind_degree;
    }

    public function setWindDegree(?string $wind_degree): void {
        $this->wind_degree = $wind_degree;
    }

    public function getWindGust(): ?string {
        return $this->wind_gust;
    }

    public function setWindGust(?string $wind_gust): void {
        $this->wind_gust = $wind_gust;
    }

    public function getCloudsPercentage(): ?string {
        return $this->clouds_percentage;
    }

    public function setCloudsPercentage(?string $clouds_percentage): void {
        $this->clouds_percentage = $clouds_percentage;
    }

    public function getSunrise(): ?string {
        return $this->sunrise;
    }

    public function setSunrise(?string $sunrise): void {
        $this->sunrise = $sunrise;
    }

    public function getSunset(): ?string {
        return $this->sunset;
    }

    public function setSunset(?string $sunset): void {
        $this->sunset = $sunset;
    }
    private ?string $humidity;
    private ?string $wind_speed;
    private ?string $wind_degree;
    private ?string $wind_gust;
    private ?string $clouds_percentage;
    private ?string $sunrise;
    private ?string $sunset;

    public function toArray(): array {
        return [
            'ts' => $this->getTs(),
            'city' => $this->getCity(),
            'longitude' => $this->getLongitude(),
            'latitude' => $this->getLatitude(),
            'description' => $this->getDescription(),
            'icon' => $this->getIcon(),
            'temp' => $this->getTemp(),
            'temp_feels_like' => $this->getTempFeelsLike(),
            'temp_min' => $this->getTempMin(),
            'temp_max' => $this->getTempMax(),
            'pressure' => $this->getPressure(),
            'humidity' => $this->getHumidity(),
            'wind_speed' => $this->getWindSpeed(),
            'wind_degree' => $this->getWindDegree(),
            'wind_gust' => $this->getWindGust(),
            'clouds_percentage' => $this->getCloudsPercentage(),
            'sunrise' => $this->getSunrise(),
            'sunset' => $this->getSunset()
        ];
    }
}