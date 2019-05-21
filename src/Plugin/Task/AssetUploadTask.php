<?php

namespace PhpTaskman\Github\Plugin\Task;

use Robo\Result;

final class AssetUploadTask extends Github
{
    const ARGUMENTS = [
        'file',
        'project',
        'tag',
        'token',
        'user',
    ];

    const NAME = 'github.asset.upload';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $arguments = $this->getTaskArguments();

        list($code, $response) = $this->getReleaseFromTag(
            $arguments['user'],
            $arguments['project'],
            $arguments['tag']
        );

        if (200 !== $code) {
            return new Result($this, 1, 'Release tag ' . $arguments['tag'] . ' doesn\'t seems to exist.');
        }

        if (\function_exists('curl_file_create')) {
            $cFile = \curl_file_create($arguments['file']);
        } else {
            $cFile = '@' . \realpath($arguments['file']);
        }

        $headers = [
            'Transfer-Encoding: identity',
            'Authorization: token ' . $arguments['token'],
            'Content-Type: ' . \mime_content_type($arguments['file']),
        ];
        $url = \sprintf(
            'https://uploads.github.com/repos/%s/%s/releases/%s/assets?name=%s',
            $arguments['user'],
            $arguments['project'],
            $response['id'],
            $arguments['file']
        );
        $post = ['file_contents' => $cFile];

        list($code, $response) = $this->request($url, 'POST', $headers, $post);

        if (isset($result['errors'])) {
            // Todo: beautify this.
            return new Result($this, 1, 'error');
        }

        return new Result($this, 0);
    }

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
}
