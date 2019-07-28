<?php

namespace PhpTaskman\Github\Plugin\Task;

use Github\Client;
use Robo\Result;

final class AssetRemoveTask extends Github
{
    const ARGUMENTS = [
        'file',
        'project',
        'tag',
        'token',
        'user',
    ];

    const NAME = 'github.asset.remove';

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

        $release += ['assets' => []];

        $asset = null;

        foreach ($release['assets'] as $releaseAsset) {
            if ($releaseAsset['name'] !== $arguments['file']) {
                continue;
            }

            $asset = $releaseAsset;
        }

        if (null === $asset) {
            $this->printTaskInfo('No such asset was found, nothing to be removed.');

            return new Result($this, Result::EXITCODE_OK);
        }

        try {
            $github
                ->repo()
                ->releases()
                ->remove(
                    $arguments['user'],
                    $arguments['project'],
                    $asset['id']
                );
        } catch (\Exception $e) {
            return new Result($this, Result::EXITCODE_ERROR, $e->getMessage());
        }

        $this->printTaskInfo('Asset has been removed.');

        return new Result($this, Result::EXITCODE_OK);
    }
}
