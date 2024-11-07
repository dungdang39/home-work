<?php

namespace Core;

use Slim\Http\ServerRequest as Request;

/**
 * 에디터 서비스
 * @todo 'assets' 경로를 일괄 관리하는 클래스를 만들어서 사용하도록 수정
 */
class EditorService
{
    private const EDITOR_PATH = '/assets/library/editor';

    private Request $request;

    public function __construct(
        Request $request
    ) {
        $this->request = $request;
    }

    /**
     * 에디터 목록을 가져오는 함수
     * - EDITOR_PATH에 있는 디렉토리를 읽어와 에디터&버전 목록을 가져온다.
     * @return array
     */
    public function getEditorsByPath(): array
    {
        $base_path = $this->request->getAttribute('base_path');
        $editor_path = $base_path . self::EDITOR_PATH;

        if (!is_dir($editor_path)) {
            return [];
        }

        $editors = [];
        $editor_dirs = scandir($editor_path);

        foreach ($editor_dirs as $editor) {
            $editor_dir_path = $editor_path . '/' . $editor;
            if (!$this->isValidDirectory($editor) || !is_dir($editor_dir_path)) {
                continue;
            }

            $versions = scandir($editor_dir_path);
            foreach ($versions as $version) {
                $version_path = $editor_dir_path . '/' . $version;
                if ($this->isValidDirectory($version) && is_dir($version_path)) {
                    $editors[$editor][] = [
                        'version' => $version,
                        'value' => strtolower($editor . '/' . $version)
                    ];
                }
            }
        }
        return $editors;
    }

    /**
     * 디렉토리가 . 또는 ..이 아닌지 확인
     * @param string $dir
     * @return bool
     */
    private function isValidDirectory($dir)
    {
        return $dir !== '.' && $dir !== '..';
    }
}
