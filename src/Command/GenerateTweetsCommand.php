<?php


namespace App\Command;


use App\Entity\Product;
use App\Logging\BusinessLogger;
use Cocur\Slugify\Slugify;
use Doctrine\Persistence\ObjectManager;
use League\Csv\Reader;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTweetsCommand extends Command
{
    protected static $defaultName = 'app:tweets';

    private $container;
    /**
     * @var \Faker\Generator
     */
    private $faker;
    /**
     * @var array
     */
    private $reviews;

    public function __construct(ContainerInterface $container, BusinessLogger $businessLogger)
    {
        parent::__construct();

        $this->container = $container;

        $this->faker = \Faker\Factory::create('fr_FR');
        $this->slugify = new Slugify();

        $csv = Reader::createFromPath('./public/reviews_samples.csv', 'r');
        $csv->setHeaderOffset(0);

        $this->reviews = iterator_to_array($csv->getRecords());
    }

    protected function configure(): void
    {
        $this->addOption('count', null, InputOption::VALUE_OPTIONAL, 'how many tweets', 10);
        $this->addOption('output', null, InputOption::VALUE_OPTIONAL, 'stdout|FILE_PATH', 'stdout');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $optionCount = $input->getOption('count');
        $optionOutput = $input->getOption('output');

        $outputFile = null;

        if ($optionOutput !== 'stdout')
        {
            $outputFile = fopen($optionOutput, 'a+');
        }

        $buffer = new \AppendIterator();

        $buffer->append($this->productTweets($optionCount));
        $buffer->append($this->productRetweets($optionCount));

        foreach($buffer as $tweet)
        {
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

    private function productTweets(int $count): \Generator
    {
        /** @var ObjectManager $em */
        $manager = $this->container->get('doctrine')->getManager();

        $products = $manager->getRepository(Product::class)->findAll();

        for ($i = 0; $i < min($count, count($products)); $i++)
        {
            /** @var Product $p */
            $p = $products[$i];

            $shortUrl = "https://t.co/" . $this->faker->bothify('??##??##?');

            $text = sprintf('venez découvrir notre superbe %s : %s ==> %s à seulement %d € #vin',
                $p->getCategory()->getTitle(),
                $p->getTitle(),
                $shortUrl,
                $p->getPrice()
            );

            yield $this->tweet([
                'id' => crc32($p->getReference()),
                'id_str' => sprintf("%d", crc32($p->getReference())),
                'text' => $text,
                'truncated' => false,
                'entities' => [
                    'hashtags' => ['vin'],
                    'urls' => [
                        'url' => $shortUrl,
                        'expanded_url' => $p->getUrl()
                    ]
                ],
            ]);
        }
    }

    private function productRetweets(int $count): \Generator
    {
        /** @var ObjectManager $em */
        $manager = $this->container->get('doctrine')->getManager();

        $products = $manager->getRepository(Product::class)->findAll();

        for ($i = 0; $i < min($count, count($products)); $i++)
        {
            /** @var Product $p */
            $p = $products[$i];

            for ($j = 0; $j < rand(0, 10); $j++)
            {
                $text = $this->reviews[array_rand($this->reviews)]['review'];

                yield $this->tweet([
                    'text'      => $text,
                    'truncated' => false,
                    "in_reply_to_status_id" => crc32($p->getReference()),
                    "in_reply_to_status_id_str" => sprintf("%d", crc32($p->getReference())),
                    'user' => [
                        'name' => $this->faker->userName()
                    ],
                ]);
            }
        }
    }

    private function tweet(array $in): array
    {
        return array_merge([
            'created_at' => $this->faker->dateTimeBetween('-30 days')->format(DATE_ATOM),
            'id_str' => $this->faker->numerify('#############'),
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
        ], $in);
    }
}