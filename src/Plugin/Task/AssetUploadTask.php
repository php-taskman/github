<?php

declare(strict_types = 1);

namespace PhpTaskman\Github\Plugin\Task;

use Github\Client;
use Robo\Result;

final class AssetUploadTask extends Github
{
    public const ARGUMENTS = [
        'file',
        'project',
        'tag',
        'token',
        'user',
    ];

    public const NAME = 'github.asset.upload';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $arguments = $this->getTaskArguments();

        try {
            $release = $this->getReleaseFromTag(
                $arguments['user'],
                $arguments['project'],
                $arguments['tag']
            );
        } catch (\Exception $e) {
            return new Result($this, $e->getCode(), $e->getMessage());
        }

        $github = new Client();
        $github->authenticate($arguments['token'], null, Client::AUTH_URL_TOKEN);

        if (false === $file = realpath($arguments['file'])) {
            return new Result($this, '1', 'Unable to access the file ' . $arguments['file']);
        }

        if (false === $mime = mime_content_type($file)) {
            return new Result($this, '1', 'Unable to get mimetype of file ' . $arguments['file']);
        }

        if (false === $content = file_get_contents($file)) {
            return new Result($this, '1', 'Unable to get content of file ' . $arguments['file']);
        }

        try {
            $github->repo()->releases()->assets()->create(
                $arguments['user'],
                $arguments['project'],
                $release['id'],
                $arguments['file'],
                $mime,
                $content
            );
        } catch (\Exception $e) {
            return new Result($this, Result::EXITCODE_ERROR, $e->getMessage());
        }

        $this->printTaskInfo('Asset ' . $arguments['file'] . ' has been uploaded.');

        return new Result($this, Result::EXITCODE_OK);
    }
}
