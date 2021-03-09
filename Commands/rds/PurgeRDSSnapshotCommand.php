<?php

namespace CMD\rds;


use Aws\Rds\RdsClient;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PurgeRDSSnapshotCommand extends Command
{
    // the name of the command (the part after "bin/console")
    /**
     * @var string
     */
    protected static $defaultName = 'purge:rds:snapshots';

    protected function configure(): void
    {

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {

        $output->setDecorated(true);
        $client = new RdsClient([
            'region' => 'eu-west-1',
            'version' => 'latest',
            'profile' => 'default'
        ]);

        $result = $client->describeDBSnapshots();

        foreach ($result->get('DBSnapshots') as $snapshot) {

            $date = new DateTime($snapshot['InstanceCreateTime']->jsonSerialize());

        }

    }
}
