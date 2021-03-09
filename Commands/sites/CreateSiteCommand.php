<?php

namespace CMD\sites;

use Aws\Ec2\Ec2Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class CreateSiteCommand
 * @package CMD
 */
class CreateSiteCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'create:site';
    /**
     * @var
     */
    private $webRoot;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->setDecorated(true);
        $this->getRequiredInstance($input, $output);
//        $name = $this->askSiteName($input, $output);
//
//        $this->webRoot = '/Users/jez/Sites/' . str_replace('.', '_', $name);
//        $this->createWebRoot($this->webRoot);
//
//        $repo = $this->askRepo($input, $output);
//        $output->write($this->cloneRepo($repo), true);
//
//        $output->write('Site has been created and can be accessed at http://' . $name, true);
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
        $client = new Ec2Client([
            'region' => 'eu-west-1',
            'version' => 'latest',
            'profile' => 'default'
        ]);

        $instances = $client->describeInstances();
        var_dump($instances);
        die;
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
    private function askSiteName(InputInterface $input, OutputInterface $output): string
    {
//        $helper = $this->getHelper('question');
//        $question = new Question('Domain for new site? [ e.g. sitename.tjlabs.co.uk ]: ', 'sitename.tjlabs.co.uk');

//        return $helper->ask($input, $output, $question);
    }

    /**
     * @param string $webRoot
     */
    private function createWebRoot(string $webRoot)
    {
        mkdir($webRoot, 0755, true);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    private function askRepo(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new Question('git repo for site? [ e.g. git@bitbucket.org:strawberrysoup/harwin.git ]: ', '');

        return $helper->ask($input, $output, $question);
    }

    /**
     * @param $repo
     * @return string|null
     */
    private function cloneRepo($repo)
    {
        return shell_exec('git clone ' . $repo . ' ' . $this->webRoot);
    }
}
