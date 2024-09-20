<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Core\Exception\Renderers;

use Core\Database\PDO\Exception\DbConnectException;
use PDOException;
use Slim\Error\AbstractErrorRenderer;
use Throwable;

use function get_class;
use function json_encode;

/**
 * Default Slim application JSON Error Renderer
 */
class JsonErrorRenderer extends AbstractErrorRenderer
{
    protected string $defaultErrorDescription = 'An internal error has occurred while processing your request.';
    
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $error = [
            'statusCode' => $exception->getCode(),
            'error' => [
                'type' => get_class($exception),
                'message' => $this->getErrorMessage($exception, $displayErrorDetails), 
                'title' => $this->getErrorTitle($exception),
                'description' => $displayErrorDetails ? $this->getErrorDescription($exception) : $this->defaultErrorDescription,
            ],
        ];
        
        return (string) json_encode($error, JSON_UNESCAPED_UNICODE);
    }

    private function getErrorMessage(Throwable $exception, bool $displayErrorDetails): string
    {
        if ($exception instanceof DbConnectException) {
            return 'DB Connection error' . ($displayErrorDetails ? ': ' . $exception->getMessage() : '');
        }

        if ($exception instanceof PDOException) {
            return 'DB operator error' . ($displayErrorDetails ? ': ' . $exception->getMessage() : '');
        }

        return $exception->getMessage();
    }
}
