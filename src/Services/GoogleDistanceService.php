<?php

namespace Vtlabs\Core\Services;

class GoogleDistanceService
{
    private $source_latitude, $source_longitude, $destination_latitude, $destination_longitude;

    public function __construct(
        $source_latitude,
        $source_longitude,
        $destination_latitude,
        $destination_longitude
    ) {
        $this->source_latitude = $source_latitude;
        $this->source_longitude = $source_longitude;
        $this->destination_latitude = $destination_latitude;
        $this->destination_longitude = $destination_longitude;
    }

    public function distanceAndTime()
    {
        try {
            $url = $this->buildGoogleDistanceUrl();

            $response = $this->fetch($url);
            if (!empty($response)) {
                $distance = ceil($response->distance->value / 1000); // meters to km
                $time = ceil($response->duration->value / 60); // seconds to minutes
                return [$distance, $time];
            }
            throw new \Exception("Unable to calculate distance and time");
        } catch (\Exception $ex) {
            return [0, 0];
        }
    }

    private function buildGoogleDistanceUrl()
    {
        $googleKey = env('FIREBASE_KEY');
        
        if(!$googleKey) {
            throw new \Exception('Valid key not present');
        }

        $googleOrigin = $this->source_latitude . ',' . $this->source_longitude;
        $googleDestination = $this->destination_latitude . ',' . $this->destination_longitude;

        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?'
            . 'origins=' . $googleOrigin . '&destinations=' . $googleDestination . '&key=' . $googleKey;
        return $url;
    }

    private function fetch($googleUrl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $googleUrl);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        try {
            return $response->rows[0]->elements[0];
        } catch (\Exception $ex) {
            return [];
        }
    }
}
