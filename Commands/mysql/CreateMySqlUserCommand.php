<?php

namespace CMD\mysql;

use Aws\Rds\RdsClient;
use Hackzilla\PasswordGenerator\Exception\FileNotFoundException;
use Hackzilla\PasswordGenerator\Exception\ImpossiblePasswordLengthException;
use Hackzilla\PasswordGenerator\Exception\WordsNotFoundException;
use Hackzilla\PasswordGenerator\Generator\HumanPasswordGenerator;
use Hackzilla\PasswordGenerator\Generator\HybridPasswordGenerator;
use Hackzilla\PasswordGenerator\RandomGenerator\Php7RandomGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;


/**
 * Class CreateUserCommand
 * @package CMD
 */
class CreateMySqlUserCommand extends Command
{
    // the name of the command (the part after "bin/console")
    /**
     * @var string
     */
    protected static $defaultName = 'create:mysql:user';
    /**
     * @var \PDO $db
     */
    private $db;
    /**
     * @var string $instance
     */
    private $instance;

    /**
     *
     */
    protected function configure(): void
    {

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {

        $this->instance = $this->getRequiredInstance($input, $output);
        $config = json_decode(file_get_contents(__DIR__ . '/../../config/rds.json'))->rds;
        $this->db = new \PDO("mysql:host=$this->instance;", $config->username, $config->password);

        $dbName = $this->getRequiredDb($input, $output);
        $username = $this->generateUsername();
        $password = $this->generatePassword();

        $this->createUser($dbName, $username, $password);

        $output->writeln("DB_NAME=\"$dbName\"");
        $output->writeln("DB_HOST=\"$this->instance\"");
        $output->writeln("DB_USER=\"$username\"");
        $output->writeln("DB_PASSWORD=\"$password\"");

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    private function getRequiredInstance(InputInterface $input, OutputInterface $output): string
    {
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'Please select instance',
            $this->getInstanceEndpointList()
        );

        $question->setErrorMessage('Instance %s is invalid.');

        $instance = $helper->ask($input, $output, $question);
        return $instance;
    }

    /**
     * @return array
     */
    private function getInstanceEndpointList(): array
    {
        $client = new RdsClient([
            'region' => 'eu-west-1',
            'version' => 'latest',
            'profile' => 'default'
        ]);

        $instances = $client->describeDBInstances()['DBInstances'];
        $list = [];

        foreach ($instances as $instance) {
            $list[] = $instance['Endpoint']['Address'];
        }
        return $list;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    private function getRequiredDb(InputInterface $input, OutputInterface $output): string
    {
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'Please select DB',
            $this->getDbList()
        );

        $question->setErrorMessage('DB %s is invalid.');

        $instance = $helper->ask($input, $output, $question);
        return $instance;
    }

    /**
     * @return array
     */
    private function getDbList(): array
    {
        $res = $this->db->query('SHOW DATABASES');
        $options = [];

        foreach ($res->fetchAll() as $result) {
            $options[] = $result['Database'];
        }

        return $options;
    }

    /**
     * @return string
     */
    private function generateUsername(): string
    {
        /** max length 16 characters */


        $generator = new HumanPasswordGenerator();

        try {
            $generator
                ->setRandomGenerator(new Php7RandomGenerator())
                ->setWordList('/usr/share/dict/words')
                ->setWordCount(2)
                ->setMinWordLength(3)
                ->setMaxWordLength(7);
        } catch (FileNotFoundException $e) {

        }

        try {
            return $generator->generatePassword();
        } catch (ImpossiblePasswordLengthException $e) {
            return null;
        } catch (WordsNotFoundException $e) {
            return null;
        }

    }

    /**
     * @return string
     */
    private function generatePassword(): string
    {
        $generator = new HybridPasswordGenerator();

        try {
            $generator
                ->setUppercase()
                ->setLowercase()
                ->setNumbers()
                ->setSymbols(false)
                ->setSegmentLength(4)
                ->setSegmentCount(4)
                ->setSegmentSeparator(chr(random_int(64, 79)));
        } catch (\Exception $e) {
            print $e->getMessage();
        }

        return $generator->generatePassword();
    }

    /**
     * @param string $dbName
     * @param string $username
     * @param string $password
     */
    private function createUser(string $dbName, string $username, string $password)
    {
        $this->db->exec(
            "GRANT ALTER, SELECT, UPDATE, DELETE, DROP, CREATE, INDEX, INSERT ON $dbName.* TO '$username'@'%' IDENTIFIED BY '$password'"
        );
    }
}
