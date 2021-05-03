<?php


namespace App\Command;


use App\Entity\Product;
use App\Logging\BusinessLogger;
use Cocur\Slugify\Slugify;
use Doctrine\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTweetsCommand extends Command
{
    protected static $defaultName = 'app:tweets';

    private $container;

    public function __construct(ContainerInterface $container, BusinessLogger $businessLogger)
    {
        parent::__construct();

        $this->container = $container;
    }

    protected function configure(): void
    {
        $this->addOption('count', null, InputOption::VALUE_OPTIONAL, 'how many tweets', 10);
        $this->addOption('output', null, InputOption::VALUE_OPTIONAL, 'stdout|FILE_PATH', 'stdout');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ObjectManager $em */
        $manager = $this->container->get('doctrine')->getManager();

        $optionCount = $input->getOption('count');
        $optionOutput = $input->getOption('output');

        $products = $manager->getRepository(Product::class)->findAll();

        $tweets = [];

        $faker = \Faker\Factory::create('fr_FR');
        $slugify = new Slugify();

        $outputFile = null;

        if ($optionOutput !== 'stdout')
        {
            $outputFile = fopen($optionOutput, 'a+');
        }

        for ($i = 0; $i < $optionCount; $i++)
        {
            /** @var Product $p */
            $p = $products[array_rand($products)];

            $shortUrl = "https://t.co/" . $faker->bothify('??##??##?');

            $text = sprintf('venez découvrir notre superbe %s : %s ==> %s à seulement %d € #vin',
                $p->getCategory()->getTitle(),
                $p->getTitle(),
                $shortUrl,
                $p->getPrice()
            );

            $tweet = [
                'created_at' => $faker->dateTimeBetween('-30 days')->format(DATE_ATOM),
                'id' => $faker->numerify('#############'),
                'id_str' => $faker->numerify('#############'),
                'text' => $text,
                'truncated' => false,
                'entities' => [
                    'hashtags' => ['vin'],
                    'urls' => [
                        'url' => $shortUrl,
                        'expanded_url' => $p->getUrl()
                    ]
                ],
                'metadata' => [
                    'iso_language_code' => 'fr'
                ],
                "in_reply_to_status_id" => null,
                "in_reply_to_status_id_str" => null,
                "in_reply_to_user_id" => null,
                "in_reply_to_user_id_str" => null,
                "in_reply_to_screen_name" => null,
                'user' => [
                    'name' => 'vignestore'
                ],
                "geo" => null,
                "coordinates" => null,
                "place" => null,
                "contributors" => null,
                "is_quote_status" => false,
                "retweet_count" => rand(10, 1000),
                "favorite_count" => rand(5, 100),
                "favorited" => false,
                "retweeted" => false,
                "possibly_sensitive" => false,
                "lang" => "fr"
            ];

            $tweetJSON = json_encode($tweet);

            if ($optionOutput === 'stdout')
            {
                $output->writeln($tweetJSON);
            }
            else
            {
                fwrite($outputFile, $tweetJSON . PHP_EOL);
            }
        }

        return Command::SUCCESS;
    }
}