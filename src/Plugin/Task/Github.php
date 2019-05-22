<?php

namespace PhpTaskman\Github\Plugin\Task;

use Github\Client;
use PhpTaskman\CoreTasks\Plugin\BaseTask;

abstract class Github extends BaseTask
{
    /**
     * @param string $user
     * @param string $project
     * @param string $tag
     *
     * @throws \Exception
     *
     * @return array
     *   Todo
     */
    protected function getReleaseFromTag($user, $project, $tag)
    {
        $github = new Client();
        $releases = $github->repo()->releases()->all($user, $project);

        foreach ($releases as $release) {
            if ($tag === $release['tag_name']) {
                return $release;
            }
        }

        throw new \Exception('Release tag ' . $tag . ' doesn\'t seems to exist.', 404);
    }
}
