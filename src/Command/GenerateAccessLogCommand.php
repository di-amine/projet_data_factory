<?php


namespace App\Command;


use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Persistence\ObjectManager;
use Cocur\Slugify\Slugify;

class GenerateAccessLogCommand extends Command implements SignalableCommandInterface
{
    protected static $defaultName = 'app:gen-access-log';

    private $container;

    private $running = true;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
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
        $this->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'stream|batch', 'batch');
        $this->addOption('speed', null, InputOption::VALUE_OPTIONAL, 'slow|normal|fast', 'slow');
        $this->addOption('output', null, InputOption::VALUE_OPTIONAL, 'stdout|FILE_PATH', 'stdout');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ObjectManager $em */
        $manager = $this->container->get('doctrine')->getManager();

        $users = $manager->getRepository(User::class)->findAll();
        $products = $manager->getRepository(Product::class)->findAll();
        $orders = $manager->getRepository(Order::class)->findAll();

        $faker = \Faker\Factory::create('fr_FR');
        $slugify = new Slugify();

        $speed = $input->getOption('speed');
        $mode = $input->getOption('mode');
        $optionOutput = $input->getOption('output');

        $i = 0;
        $outputFile = null;

        if ($optionOutput !== 'stdout')
        {
            $outputFile = fopen($optionOutput, 'a+');
        }

        while($this->running)
        {
            /** @var Product $p */
            $p = $products[array_rand($products)];
            /** @var User $u */
            $u = $users[array_rand($users)];

            $str = $this->line($u, $p, $faker, $slugify);

            if ($optionOutput === 'stdout')
            {
                $output->writeln($str);
            }
            else
            {
                fwrite($outputFile, $str . PHP_EOL);
            }

            if ($mode === 'stream')
            {
                switch ($speed)
                {
                    case 'slow':
                        sleep(rand(1, 5));
                        break;
                    case 'normal':
                        sleep(1);
                        break;
                    default:
                        usleep(100);
                        break;
                }
            }
            elseif ($i > 10000)
            {
                exit;
            }

            $i++;
        }

        if ($mode === 'batch')
        {
            /** @var Order $order */
            foreach ($orders as $order)
            {
                $str = $this->line($order->getUser(), $order->getProduct(), $faker, $slugify);

                if ($optionOutput === 'stdout')
                {
                    $output->writeln($str);
                }
                else
                {
                    fwrite($outputFile, $str . PHP_EOL);
                }
            }
        }

        if (!empty($outputFile))
        {
            fclose($outputFile);
        }

        return Command::SUCCESS;
    }

    private function line(User $u, Product $p, \Faker\Generator $faker, \Cocur\Slugify\Slugify $slugify): string
    {
        $q = rand(20, 1500);

         return sprintf('1.1.1.1 - %s [%s] "GET /product/%s" 200 1000 "-" "%s" %d %d %s',
            $u->getIp(),
            $faker->dateTimeBetween('-1 month')->format(DATE_ATOM),
            $p->getId() . '-' . $slugify->slugify($p->getTitle()) . '.html',
            $u->getUserAgent(),
            $q,
            $u->getId(),
            $u->getTestGroup()
        );
    }
}