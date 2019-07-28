<?php

namespace PhpTaskman\Github\Plugin\Task;

use Github\Client;
use Robo\Result;

final class ReleaseCreateTask extends Github
{
    const ARGUMENTS = [
        'project',
        'tag',
        'token',
        'user',
        'target',
        'name',
        'body',
        'draft',
        'prerelease',
    ];

    const NAME = 'github.release.create';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $arguments = $this->getTaskArguments() +
            [
                'name' => null,
                'body' => null,
                'draft' => null,
                'prerelease' => null,
                'target' => null,
                'tag' => null,
            ];

        $post = [
            'tag_name' => $arguments['tag'],
            'target_commitish' => $arguments['target'],
            'name' => $arguments['name'],
            'body' => file_exists($arguments['body']) ? file_get_contents($arguments['body']) : $arguments['body'],
            'draft' => $arguments['draft'],
            'prerelease' => $arguments['prerelease'],
        ];
        $post = array_filter($post, '\strlen');

        $github = new Client();
        $github->authenticate($arguments['token'], null, Client::AUTH_URL_TOKEN);

        try {
            $github
                ->repo()
                ->releases()
                ->create(
                    $arguments['user'],
                    $arguments['project'],
                    $post
                );
        } catch (\Exception $e) {
            return new Result($this, Result::EXITCODE_ERROR, $e->getMessage());
        }

        $this->printTaskInfo('Release has been created.');

        return new Result($this, Result::EXITCODE_OK);
    }
}
