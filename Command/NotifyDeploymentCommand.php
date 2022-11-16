<?php

declare(strict_types=1);

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\NewRelicBundle\Command;

use Ekino\NewRelicBundle\NewRelic\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NotifyDeploymentCommand extends Command
{
    public const EXIT_NO_APP_NAMES = 1;
    public const EXIT_UNAUTHORIZED = 2;
    public const EXIT_HTTP_ERROR = 3;

    private $newrelic;

    public function __construct(Config $newrelic)
    {
        $this->newrelic = $newrelic;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDefinition([
                new InputOption(
                    'user', null, InputOption::VALUE_OPTIONAL,
                    'The name of the user/process that triggered this deployment', null
                ),
                new InputOption(
                    'revision', null, InputOption::VALUE_OPTIONAL,
                    'A revision number (e.g., git commit SHA)', null
                ),
                new InputOption(
                    'changelog', null, InputOption::VALUE_OPTIONAL,
                    'A list of changes for this deployment', null
                ),
                new InputOption(
                    'description', null, InputOption::VALUE_OPTIONAL,
                    'Text annotation for the deployment — notes for you', null
                ),
            ])
            ->setDescription('Notifies New Relic that a new deployment has been made')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $appNames = $this->newrelic->getDeploymentNames();

        if (!$appNames) {
            $output->writeLn('<error>No deployment application configured.</error>');

            return self::EXIT_NO_APP_NAMES;
        }

        $exitCode = 0;

        foreach ($appNames as $appName) {
            $response = $this->performRequest($this->newrelic->getApiKey(), $this->createPayload($appName, $input), $this->newrelic->getApiHost());

            switch ($response['status']) {
                case 200:
                case 201:
                    $output->writeLn(sprintf("Recorded deployment to '%s' (%s)", $appName, ($input->getOption('description') ?: date('r'))));
                    break;
                case 403:
                    $output->writeLn(sprintf("<error>Deployment not recorded to '%s': API key invalid</error>", $appName));
                    $exitCode = self::EXIT_UNAUTHORIZED;
                    break;
                case null:
                    $output->writeLn(sprintf("<error>Deployment not recorded to '%s': Did not understand response</error>", $appName));
                    $exitCode = self::EXIT_HTTP_ERROR;
                    break;
                default:
                    $output->writeLn(sprintf("<error>Deployment not recorded to '%s': Received HTTP status %d</error>", $appName, $response['status']));
                    $exitCode = self::EXIT_HTTP_ERROR;
                    break;
            }
        }

        return $exitCode;
    }

    public function performRequest(string $api_key, string $payload, ?string $api_host = null): array
    {
        $headers = [
            sprintf('x-api-key: %s', $api_key),
            'Content-type: application/x-www-form-urlencoded',
        ];

        $context = [
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $payload,
                'ignore_errors' => true,
            ],
        ];

        $level = error_reporting(0);
        $content = file_get_contents(sprintf('https://%s/deployments.xml', $api_host ?? 'api.newrelic.com'), false, stream_context_create($context));
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            throw new \RuntimeException($error['message']);
        }

        $response = [
            'status' => null,
            'error' => null,
        ];

        if (isset($http_response_header[0])) {
            preg_match('/^HTTP\/1.\d (\d+)/', $http_response_header[0], $matches);

            if (isset($matches[1])) {
                $status = $matches[1];

                $response['status'] = $status;

                preg_match('/<error>(.*?)<\/error>/', $content, $matches);

                if (isset($matches[1])) {
                    $response['error'] = $matches[1];
                }
            }
        }

        return $response;
    }

    private function createPayload(string $appName, InputInterface $input): string
    {
        $content_array = [
            'deployment[app_name]' => $appName,
        ];

        if (($user = $input->getOption('user'))) {
            $content_array['deployment[user]'] = $user;
        }

        if (($revision = $input->getOption('revision'))) {
            $content_array['deployment[revision]'] = $revision;
        }

        if (($changelog = $input->getOption('changelog'))) {
            $content_array['deployment[changelog]'] = $changelog;
        }

        if (($description = $input->getOption('description'))) {
            $content_array['deployment[description]'] = $description;
        }

        return http_build_query($content_array);
    }
}
