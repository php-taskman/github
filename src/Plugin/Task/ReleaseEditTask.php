<?php

declare(strict_types = 1);

namespace PhpTaskman\Github\Plugin\Task;

use Github\Client;
use Robo\Result;

final class ReleaseEditTask extends Github
{
    public const ARGUMENTS = [
        'project',
        'tag',
        'token',
        'user',
        'name',
        'body',
        'draft',
        'prerelease',
    ];

    public const NAME = 'github.release.edit';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $arguments = $this->getTaskArguments() + [
            'name' => null,
            'body' => null,
            'draft' => null,
            'prerelease' => null,
        ];

        try {
            $release = $this->getReleaseFromTag(
                $arguments['user'],
                $arguments['project'],
                $arguments['tag']
            );
        } catch (\Exception $e) {
            return new Result($this, $e->getCode(), $e->getMessage());
        }

        $body_exists = null !== $arguments['body'] && file_exists($arguments['body']);

        $post = [
            'tag_name' => $arguments['tag'],
            'name' => $arguments['name'],
            'body' => $body_exists ? file_get_contents($arguments['body']) : $arguments['body'],
            'draft' => $arguments['draft'],
            'prerelease' => $arguments['prerelease'],
        ];
        $post = array_filter($post, '\strlen');
        $post += [
            'target_commitish' => $release['target_commitish'],
            'name' => $release['name'],
            'body' => $release['body'],
            'draft' => $release['draft'],
            'prerelease' => $release['prerelease'],
        ];

        $github = new Client();
        $github->authenticate($arguments['token'], null, Client::AUTH_URL_TOKEN);

        try {
            $github
                ->repo()
                ->releases()
                ->edit(
                    $arguments['user'],
                    $arguments['project'],
                    $release['id'],
                    $post
                );
        } catch (\Exception $e) {
            return new Result($this, Result::EXITCODE_ERROR, $e->getMessage());
        }

        $this->printTaskInfo('Release tag ' . $arguments['tag'] . ' has been updated.');

        return new Result($this, Result::EXITCODE_OK);
    }
}
