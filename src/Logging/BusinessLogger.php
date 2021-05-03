<?php


namespace App\Logging;


use App\Entity\User;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Msschl\Monolog\Handler\HttpHandler;

class BusinessLogger
{
    private $log;

    public function __construct()
    {
        $this->log = new Logger('business');

        $url = getenv('BUSINESS_LOGGER_URL');

        $faker = \Faker\Factory::create('fr_FR');

        $this->log->pushProcessor(function(array $record) use ($faker)
        {
            unset($record['extras']);

            $record['datetime'] = $faker->dateTimeBetween('-10 hour')->format(DATE_ATOM);

            return $record;
        });

        if (!empty($url))
        {
            $this->log->pushHandler(new HttpHandler([
                'uri'     => $url,// 'https://hookb.in/b9q7XzqxQES3DDogXrkz',
                'method'  => 'POST',
            ]));
        }

        $this->log->pushHandler(new StreamHandler('php://stdout'));
    }

    public function log(string $event, User $user, array $payload): string
    {
        $message = sprintf('User %s (%s) --(%s)--> ', $user->getId(), $user->getTitle(), $event);

        $this->log->info($message, [
            'event' => $event,
            'app' => [
                'name' => 'vignestore',
                'version' => '0.1.0'
            ],
            'user' => [
                'id' => $user->getId(),
                'title' => $user->getTitle()
            ],
            'data' => $payload
        ]);

        return $message;
    }
}