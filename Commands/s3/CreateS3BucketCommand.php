<?php

namespace CMD\s3;

use Aws\Exception\AwsException;
use Aws\Iam\IamClient;
use Aws\Result;
use Aws\S3\S3Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CreateS3BucketCommand
 * @package CMD
 */
class CreateS3BucketCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'create:s3:bucket';
    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Create S3 Bucket');
        $name = $this->askBucketName($input, $output);

        $this->createS3Bucket($name);
        $this->createIAMuser($name);

    }

    /**
     * @return string
     */
    private function askBucketName(): string
    {
        return $this->io->ask('Please enter the name of the bucket');
    }

    /**
     * @param string $name
     * @return Result|string
     */
    protected function createS3Bucket(string $name): string
    {
        $this->io->section('Creating S3 bucket');

        $s3Client = new S3Client([
            'region' => 'eu-west-1',
            'version' => 'latest',
            'profile' => 'default'
        ]);

        try {
            $result = $s3Client->createBucket([
                'Bucket' => $name,
            ])->get('Location');
            $this->io->success($result);
            return $result;
        } catch (AwsException $e) {
            // output error message if fails
            $result = $e->getAwsErrorMessage();
            $this->io->error($result);
            exit;
        }
    }

    /**
     * @param string $name
     */
    protected function createIAMUser(string $name)
    {
        $client = new IamClient([
            'region' => 'eu-west-1',
            'version' => 'latest',
            'profile' => 'default'
        ]);

        $this->io->section('Creating IAM user');

        $client->createUser([
            'Tags' => [
                [
                    'Key' => 'name',
                    'Value' => $name,
                ],

            ],
            'UserName' => $name,
        ]);

        $this->createIAMPolicy($name, $client);
        $this->createIAMAPIKeys($name, $client);
    }

    /**
     * @param string $name
     * @return string
     */
    private function getPolicyDocument(string $name): string
    {
        $config = file_get_contents(__DIR__ . '/../../config/iam__s3.json');
        $config = sprintf($config, $name);
        return $config;
    }

    /**
     * @param string $name
     * @param IamClient $client
     * @return Result
     */
    protected function createIAMPolicy(string $name, IamClient $client): Result
    {
        $this->io->section('Creating IAM policy');

        $result = $client->putUserPolicy([
            'PolicyDocument' => $this->getPolicyDocument($name),
            'PolicyName' => $name . "S3Policy",
            'UserName' => $name,
        ]);
        return $result;
    }

    /**
     * @param string $name
     * @param IamClient $client
     */
    protected function createIAMAPIKeys(string $name, IamClient $client): void
    {
        $this->io->section('Creating IAM API keys');

        $result = $client->createAccessKey([
            'UserName' => $name,
        ]);

        $this->io->listing(['"AccessKeyId": "' . $result->get('AccessKey')['AccessKeyId'] . '"',
            '"SecretAccessKey": "' . $result->get('AccessKey')['SecretAccessKey'] . '"']);
    }
}
