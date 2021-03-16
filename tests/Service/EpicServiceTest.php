<?php

namespace App\Test\Service;

use App\Service\EpicService;
use DateTime;
use PHPUnit\Framework\TestCase;

class EpicServiceTest extends TestCase
{
    /** @var EpicService */
    private $epicService;
    
    public function setUp(): void
    {
        $this->epicService = new EpicService();
    }

    public function testGetDataByDate()
    {
        $date = DateTime::createFromFormat('Y-m-d', '2021-03-15');
        $data = $this->epicService->getDataByDate($date);

        $this->assertNotNull($data);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('0', $data);
    }

    public function testGetDataByDates()
    {
        $dateStart = DateTime::createFromFormat('Y-m-d', '2021-03-12');
        $dateEnd = DateTime::createFromFormat('Y-m-d', '2021-03-15');
        $data = $this->epicService->getDataByDates($dateStart, $dateEnd);

        $this->assertNotNull($data);
        $this->assertIsArray($data);
        $this->assertArrayHasKey($dateStart->format('Y-m-d'), $data);
    }
}
