<?php

namespace PhpTaskman\Github\Plugin\Task;

use Robo\Result;

final class ReleaseEditTask extends Github
{
    const ARGUMENTS = [
        'project',
        'tag',
        'token',
        'user',
        'name',
        'body',
        'draft',
        'prerelease',
    ];

    const NAME = 'github.release.edit';

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

        list($code, $release) = $this->getReleaseFromTag(
            $arguments['user'],
            $arguments['project'],
            $arguments['tag']
        );

        if (200 !== $code) {
            return new Result($this, 1, 'Release tag ' . $arguments['tag'] . ' doesn\'t seems to exist.');
        }

        $headers = [
            'Authorization: token ' . $arguments['token'],
            'Content-Type: application/json',
        ];
        $url = \sprintf(
            'https://api.github.com/repos/%s/%s/releases/%s',
            $arguments['user'],
            $arguments['project'],
            $release['id']
        );

        $post = [
            'tag_name' => $arguments['tag'],
            'name' => $arguments['name'],
            'body' => file_exists($arguments['body']) ? file_get_contents($arguments['body']) : $arguments['body'],
            'draft' => $arguments['draft'],
            'prerelease' => $arguments['prerelease'],
        ];
        $post = \array_filter($post, 'strlen');
        $post += [
            'target_commitish' => $release['target_commitish'],
            'name' => $release['name'],
            'body' => $release['body'],
            'draft' => $release['draft'],
            'prerelease' => $release['prerelease'],
        ];

        list($code, $response) = $this->request($url, 'PATCH', $headers, \json_encode($post));

        if (200 !== $code) {
            // Todo: beautify this.
            return new Result($this, 1, 'Error');
        }

        return new Result($this, 0);
    }
}
