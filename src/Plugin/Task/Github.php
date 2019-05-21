<?php

namespace PhpTaskman\Github\Plugin\Task;

use PhpTaskman\CoreTasks\Plugin\BaseTask;

abstract class Github extends BaseTask
{
    /**
     * Base Github API url.
     */
    const GITHUB_URL = 'https://api.github.com';

    /**
     * @param string $user
     * @param string $project
     * @param string $tag
     *
     * @return array
     *   Todo
     */
    protected function getReleaseFromTag($user, $project, $tag)
    {
        $url = \sprintf(
            '%s/repos/%s/%s/%s/%s',
            self::GITHUB_URL,
            $user,
            $project,
            'releases/tags',
            $tag
        );

        return $this->request($url, 'GET');
    }

    /**
     * @param $url
     * @param $type
     * @param array $headers
     * @param null $data
     *
     * @return array
     */
    protected function request($url, $type, array $headers = [], $data = null)
    {
        $ch = \curl_init();

        $type = \strtoupper($type);

        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'phptaskman/github',
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_HTTPHEADER => $headers,
        ];

        if (null !== $data) {
            $opts[CURLOPT_POSTFIELDS] = $data;
        }

        // Set query data here with the URL
        \curl_setopt_array(
            $ch,
            $opts
        );

        $output = \curl_exec($ch);

        $code = \curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (0 === \strpos($code, '2')) {
            $this->printTaskInfo('Request successful (' . $code . ' ' . $type . ' ' . $url . ')');
        } else {
            $this->printTaskInfo('Request failed (' . $code . ' ' . $type . ' ' . $url . ')');
        }

        return [$code, \json_decode($output, true)];
    }
}
