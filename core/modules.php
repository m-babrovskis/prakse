<?php

class Router
{
    public function dispatch(): void
    {
        $url = parseUrl();

        $module = $url['module'] ?: 'default';
        $action = $url['action'] ?: 'index';
        $params = $url['params'];
        $query = $url['query'];

        // Saglabā request globāli, lai view/helperi var piekļūt
        $GLOBALS['__gc_request'] = [
            'module' => $module,
            'action' => $action,
            'params' => $params,
            'query' => $query,
        ];

        $file = __DIR__ . "/../modules/{$module}/{$module}.php";
        if (!is_file($file)) {
            $this->renderNotFound("Nav atrasts moduļa fails: {$module}");
            return;
        }

        require_once $file;

        $className = ucfirst($module) . 'Controller';
        if (!class_exists($className)) {
            $this->renderNotFound("Nav atrasta klase: {$className}");
            return;
        }

        $controller = new $className();

        if (!method_exists($controller, $action)) {
            $this->renderNotFound("Nav atrasta action metode: {$className}::{$action}()");
            return;
        }

        // Izsauc kontrolieri ar parametriem no URL
        $content = call_user_func_array([$controller, $action], $params);

        // Ja kontrolieris neatgrieza saturu, nekas netiek renderēts
        if ($content === null) {
            return;
        }

        // Pārbauda, vai kontrolieris norādīja, ka nevajag layout
        $noLayout = $GLOBALS['__gc_no_layout'] ?? false;
        
        // Ja $content jau ir pilns HTML (ar layout), izvadām to un beidzam
        if (is_string($content) && strpos($content, '<!DOCTYPE html>') === 0) {
            echo $content;
            return;
        }
        
        // Ja kontrolieris norādīja, ka nevajag layout, izvadām tīru saturu
        if ($noLayout) {
            echo $content;
            return;
        }
        
        // Pretējā gadījumā ielādējam layout ar $content
        $pageTitle = ucfirst($module) . ' - ' . ucfirst($action);
        $additionalScripts = null;
        include __DIR__ . '/../theme/layouts/default.php';
    }

    private function renderNotFound(string $message): void
    {
        http_response_code(404);
        $content = renderView('errors/404', ['message' => $message]);
        $pageTitle = '404 - Nav atrasts';
        include __DIR__ . '/../theme/layouts/default.php';
    }
}
?>
