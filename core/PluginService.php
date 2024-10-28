<?php

namespace Core;

use App\Base\Service\ConfigService;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\ServerRequest as Request;

class PluginService
{
    public const PLUGIN_DIRECTORY = 'plugin';
    public const PLUGIN_DIR = __DIR__ . '/../plugin';
    public const PLUGIN_DATA_FILE = 'plugin.json';
    public const PLUGIN_ICON_FILE = 'icon.png';
    public const REQUIRED_INSTALL = ['index.php', 'plugin.json', 'router/'];

    public $default_data = array(
        'name' => 'Plugin Name',
        'plugin_uri' => 'Plugin URI',
        'version' => 'Version',
        'description' => 'Description',
        'author' => 'Author',
        'author_uri' => 'Author URI',
        'license' => 'License',
        'license_uri' => 'License URI',
    );

    public array $cache = [];
    private Request $request;
    private ConfigService $config_service;
    private FileService $file_service;

    public function __construct(
        Request $request,
        ConfigService $config_service,
        FileService $file_service
    ) {
        $this->request = $request;
        $this->config_service = $config_service;
        $this->file_service = $file_service;
    }

    /**
     * 활성화된 플러그인 설정파일 실행
     * @param App $app
     * @return void
     */
    public static function runActivePlugins(App $app)
    {
        $config_service = $app->getContainer()->get(ConfigService::class);
        $active_plugins = $config_service->getActivePlugins();

        foreach ($active_plugins as $plugin) {
            $plugin_file = join(DIRECTORY_SEPARATOR, [self::PLUGIN_DIR, $plugin, 'index.php']);
            if (is_readable($plugin_file)) {
                include_once $plugin_file;
            }
        }
    }

    /**
     * 플러그인 총 개수 조회
     * @param array|null $params 검색 조건
     * @return int
     */
    public function getPluginsTotalCount(?array $params = [])
    {
        return count($this->getPlugins($params));
    }

    /**
     * 플러그인 목록 조회
     * @param array|null $params 검색 조건
     * @return array
     */
    public function getPlugins(?array $params = [])
    {
        if (!empty($this->cache)) {
            return $this->cache;
        }

        $plugins = [];
        $plugin_dirs = scandir(self::PLUGIN_DIR);
        $plugin_dirs = array_diff($plugin_dirs, array('.', '..'));

        foreach ($plugin_dirs as $plugin) {
            $plugin_data = $this->getPluginData($plugin);
            if (empty($plugin_data)) {
                continue;
            }
            if (!$this->filteringSearchData($plugin_data, $params)) {
                continue;
            }

            $plugins[$plugin] = $plugin_data;
        }

        $this->cache = $plugins;

        return $plugins;
    }

    /**
     * 플러그인 조회
     * @param string $plugin
     * @return array
     * @throws HttpNotFoundException
     */
    public function getPlugin(string $plugin)
    {
        $plugin_data = $this->getPluginData($plugin);
        if (empty($plugin_data)) {
            throw new HttpNotFoundException($this->request, '플러그인이 존재하지 않습니다.');
        }
        return $plugin_data;
    }

    /**
     * 플러그인 활성화
     */
    public function activatePlugin(string $plugin)
    {
        if (!$this->isPluginActive($plugin)) {
            $active_plugins = $this->config_service->getActivePlugins();
            array_push($active_plugins, $plugin);

            $this->config_service->upsertConfigs('config', ['active_plugins' => join(',', $active_plugins)]);
        }
    }

    /**
     * 플러그인 비활성화
     */
    public function deactivatePlugin(string $plugin)
    {
        if ($this->isPluginActive($plugin)) {
            $active_plugins = $this->config_service->getActivePlugins();
            $active_plugins = array_diff($active_plugins, [$plugin]);

            if (empty($active_plugins)) {
                $this->config_service->deleteConfig('config', 'active_plugins');
            } else {
                $this->config_service->upsertConfigs('config', ['active_plugins' => join(',', $active_plugins)]);
            }
        }
    }

    /**
     * 플러그인 파일 압축 해제
     */
    public function extractPlugin($uploaded_file): bool
    {
        $plugin_folder_name = pathinfo($uploaded_file->getClientFilename(), PATHINFO_FILENAME);
        $plugin_folder_path = self::PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_folder_name;
        $zip_file_path = $uploaded_file->getFilePath();
        $file_extension = getExtension($uploaded_file);

        if (is_dir($plugin_folder_path)) {
            throw new \Exception('플러그인 폴더가 이미 존재합니다.');
        }

        switch ($file_extension) {
            case 'zip':
                return $this->file_service->extractZip($zip_file_path, $plugin_folder_path);

            case 'tar':
            case 'gz':
            case 'tgz':
                return $this->file_service->extractTar($zip_file_path, $plugin_folder_path);

            default:
                throw new \Exception('지원되지 않는 압축 파일 형식입니다.');
        }
    }

    /**
     * 필수 파일 확인
     * @param string $plugin_folder_path 플러그인 폴더 경로
     * @return bool
     * @throws \Exception
     */
    public function checkRequiredFiles(string $plugin_folder_path): bool
    {
        foreach (self::REQUIRED_INSTALL as $file) {
            if (!file_exists($plugin_folder_path . DIRECTORY_SEPARATOR . $file)) {
                $this->file_service->clearDirectory($plugin_folder_path);
                throw new \Exception($file . ' 파일이 누락되었습니다.');
            }
        }
        return true;
    }

    /**
     * 플러그인 데이터 조회
     * @param string $path
     * @return array
     */
    private function getPluginData(string $plugin)
    {
        $data_file = join(DIRECTORY_SEPARATOR, [self::PLUGIN_DIR, $plugin, self::PLUGIN_DATA_FILE]);
        if (!is_readable($data_file)) {
            return [];
        }

        $file_data = json_decode(file_get_contents($data_file), true);
        $data = array_map(function ($header) use ($file_data) {
            return $file_data[$header] ?? '';
        }, $this->default_data);

        if (empty($data)) {
            return [];
        }

        // 플러그인 이름 & 아이콘 & 활성화 여부 설정
        $data['plugin'] = $plugin;
        $data['icon'] = join('/', ['', 'plugin', $plugin, self::PLUGIN_ICON_FILE]);
        $data['is_active'] = $this->isPluginActive($plugin);

        return $data;
    }

    /**
     * 플러그인 활성화 여부 확인
     * @param string $plugin_name
     * @return bool
     */
    private function isPluginActive(string $plugin_name)
    {
        $active_plugins = $this->config_service->getActivePlugins();

        return in_array($plugin_name, $active_plugins);
    }

    /**
     * 플러그인 검색조건 필터링
     * @param array $plugin_data 플러그인 데이터
     * @param array $params 검색 조건
     * @return bool
     */
    private function filteringSearchData(array $plugin_data, array $params)
    {
        // 검색어 필터링
        if (
            !empty($params['search_text'])
            && (strpos($plugin_data['plugin'], $params['search_text'])) === false
            && (strpos($plugin_data['name'], $params['search_text'])) === false
        ) {
            return false;
        }
        // 활성화 조건 필터링
        if (isset($params['status'])) {
            if ($params['status'] === 'active' && !$plugin_data['is_active']) {
                return false;
            }
            if ($params['status'] === 'inactive' && $plugin_data['is_active']) {
                return false;
            }
        }

        return true;
    }
}
