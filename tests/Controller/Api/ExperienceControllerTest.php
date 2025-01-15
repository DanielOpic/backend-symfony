<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ExperienceControllerTest extends WebTestCase{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/experience');

        self::assertResponseIsSuccessful();
    }
}
