<?php

namespace App\Service;

use DateInterval;
use DateTime;
use DateTimeZone;

class EpicService
{
    public const API = "https://epic.gsfc.nasa.gov/api/natural";
    public const ARCHIVE = "https://epic.gsfc.nasa.gov/archive/natural";

    /**
     * Send a curl request
     * @param string $api URI to request to
     * @return array|null Json decoded response
     */
    private function request(string $api): ?array
    {
        $ch = \curl_init();

        \curl_setopt($ch, CURLOPT_URL, $api);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        return \json_decode(curl_exec($ch), true);
    }

    /**
     * Get all the available dates in the EPIC API
     * @return array|null
     */
    public function getAvailableDates(): ?array
    {
        $api = self::API . '/all';

        return $this->request($api);
    }

    /**
     * Get all the available dates in the EPIC API between two dates
     * @param DateTime $dateStart
     * @param DateTime $dateEnd
     * @return array
     */
    public function getAvailableDatesByDates(DateTime $dateStart, DateTime $dateEnd): array
    {
        $available = $this->getAvailableDates();

        $dates = [];
        foreach ($available as $key => $value) {
            $date = DateTime::createFromFormat('Y-m-d', $value['date']);

            if ($date < $dateStart) break;

            if ($date > $dateStart && $date < $dateEnd) {
                array_push($dates, $date);
            }
        }

        return $dates;
    }

    /**
     * Get all images data from the EPIC API for a given date
     * @param DateTime $date
     * @return array|null
     */
    public function getDataByDate(DateTime $date): ?array
    {
        $api = self::API . '/date/' . $date->format('Y-m-d');
        
        return $this->request($api);
    }

    /**
     * Get all the images data that are between two dates
     * @param DateTime $dateStart
     * @param DateTime $dateEnd
     * @return array
     */
    public function getDataByDates(DateTime $dateStart, DateTime $dateEnd): array
    {
        $available = $this->getAvailableDatesByDates($dateStart, $dateEnd);

        $data = [];
        foreach ($available as $date) {
            $data = array_merge($data, $this->getDataByDate($date));
        }

        return $data;
    }

    /**
     * Filter the images data from a list of images that do not match the given timezone by longitude
     * @param array $images
     * @param DateTimeZone $timezone
     * @param int $margin Degrees of deviation from the timezone to take images data in
     * @return array
     */
    public function filterDataByTimezone(array $images, DateTimeZone $timezone, int $margin = 10): array
    {
        $target = $timezone->getLocation()['longitude'];
        $lowerMargin = $target - $margin;
        $upperMargin = $target + $margin;

        $filtered = [];
        foreach ($images as $key => $data) {
            $location = $data['centroid_coordinates']['lon'];
            
            if ($location > $lowerMargin && $location < $upperMargin) {
               array_push($filtered, $data);
            }       
        }

        return $filtered;
    }

    /**
     * Get the image URL from an image data array
     * @param array $data
     * @param string $type
     * @return string
     */
    public function getImageFromData(array $data, string $type = 'png'): string
    {
        $date = new DateTime($data['date']);

        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');

        $name = $data['image'] . "." . $type;

        return self::ARCHIVE . "/$year/$month/$day/$type/$name";
    }
}
