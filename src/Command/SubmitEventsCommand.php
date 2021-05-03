<?php


namespace App\Command;


use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Enums\Events;
use App\Logging\BusinessLogger;
use Laminas\EventManager\Event;
use League\Csv\Reader;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Persistence\ObjectManager;
use Cocur\Slugify\Slugify;
use UAParser\Parser;

class SubmitEventsCommand extends Command implements SignalableCommandInterface
{
    protected static $defaultName = 'app:submit-events';

    private $container;

    private $running = true;

    private $businessLogger;

    /**
     * @var array
     */
    private $reviews;

    /**
     * @var array
     */
    private $referers;

    public function __construct(ContainerInterface $container, BusinessLogger $businessLogger)
    {
        parent::__construct();

        $this->container = $container;
        $this->businessLogger = $businessLogger;

        $csv = Reader::createFromPath('./public/reviews_samples.csv', 'r');
        $csv->setHeaderOffset(0);

        $this->reviews = iterator_to_array($csv->getRecords());

        $this->referers = [
            '',
            '',
            'https://www.facebook.com?campaign=1',
            'https://www.facebook.com?campaign=2',
            'https://www.facebook.com?campaign=3',
            'https://www.twitter.com?campaign=1',
            'https://www.twitter.com?campaign=2',
            'https://www.twitter.com?campaign=3',
        ];
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal): void
    {
        $this->running = false;
    }

    protected function configure(): void
    {
        $this->addOption('speed', null, InputOption::VALUE_OPTIONAL, 'slow|normal|fast', 'slow');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ObjectManager $em */
        $manager = $this->container->get('doctrine')->getManager();

        $users = $manager->getRepository(User::class)->findAll();
        $products = $manager->getRepository(Product::class)->findAll();
        $orders = $manager->getRepository(Order::class)->findAll();
        $events = Events::getEvents();

        $faker = \Faker\Factory::create('fr_FR');
        $slugify = new Slugify();

        $speed = $input->getOption('speed');

        $parser = Parser::create();

        while($this->running)
        {
            /** @var Product $p */
            $p = $products[array_rand($products)];
            /** @var User $u */
            $u = $users[array_rand($users)];

            $event = $events[array_rand($events)];

            if ($event === Events::$ORDER_PAID)
            {
                if (strpos($u->getUserAgent(), 'Mobile') !== false ||
                    strpos($u->getUserAgent(), 'Android') !== false ||
                    strpos($u->getUserAgent(), 'Phone') !== false)
                {
                    continue;
                }
            }

            $payload = $this->getPayload($u, $p, $event);

            $this->businessLogger->log($event, $u, $payload);

            switch ($speed)
            {
                case 'slow':
                    sleep(rand(5, 10));
                break;
                case 'normal':
                    sleep(rand(1, 3));
                break;
                default:
                    usleep(100);
                break;
            }

        }


        return Command::SUCCESS;
    }

    private function getPayload(User $u, Product $p, string $event): array
    {
        switch ($event)
        {
            case Events::$USER_LOGIN:
                return [];
            case Events::$USER_REGISTER:
                return [
                    'referer' => $this->referers[array_rand($this->referers)]
                ];
            case Events::$USER_REVIEW:
                $r = $this->reviews[array_rand($this->reviews)];
                return [
                    'product' => [
                        'reference' => $p->getReference(),
                        'title' => $p->getTitle()
                    ],
                    'rating' => $r['rating'],
                    'review' => $r['review']
                ];
            case Events::$ORDER_BASKET_ADD:
                return [
                    'product' => [
                        'reference' => $p->getReference(),
                        'title' => $p->getTitle()
                    ]
                ];
            case Events::$ORDER_PAID:
                $q = rand(1, 12);
                return [
                    'product' => [
                        'reference' => $p->getReference(),
                        'title' => $p->getTitle()
                    ],
                    'quantity' => $q,
                    'price' => $q * $p->getPrice()
                ];
        }
    }
}