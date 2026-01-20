<?php

/**
 * Renderē skatu un (ja vajag) ietin layout.
 *
 * @param string $view     Skata fails bez .php (piem., 'default/index')
 * @param array  $data     Dati, kas pieejami skatā kā mainīgie
 * @param string $layout   Layout nosaukums (no theme/layouts), vai '' lai nelietotu layout (noklusējums: bez layout)
 * @return string          Renderētais HTML
 */
function view(string $view, array $data = [], string $layout = ''): string
{
    $content = renderView($view, $data);

    if ($layout === '' || $layout === null) {
        // Ja nav layout, norāda, ka nevajag layout (Router to pārbaudīs)
        $GLOBALS['__gc_no_layout'] = true;
        return $content;
    }

    // Ja ir layout, noņem marķieri un atgriež pilnu HTML
    $GLOBALS['__gc_no_layout'] = false;
    $pageTitle = $data['title'] ?? ($data['pageTitle'] ?? 'GameCore');
    $additionalScripts = $data['scripts'] ?? ($data['additionalScripts'] ?? null);

    ob_start();
    include __DIR__ . '/../theme/layouts/' . $layout . '.php';
    return ob_get_clean();
}

/**
 * Renderē tikai skatu (bez layout).
 *
 * @param string $view Skata fails bez .php (piem., 'default/index' vai 'errors/404')
 * @param array $data
 * @return string
 */
function renderView(string $view, array $data = []): string
{
    // Norāda, ka nevajag layout (Router to pārbaudīs)
    $GLOBALS['__gc_no_layout'] = true;
    
    $request = $GLOBALS['__gc_request'] ?? null;
    $moduleFromRequest = $request['module'] ?? null;

    // Ja view nav ar slīpsvītru, mēģinām modulīgo formu: "{module}/{view}"
    $hasModule = strpos($view, '/') !== false;
    $resolvedView = $view;
    $pathsToTry = [];

    if ($hasModule) {
        // tieši norādīts ceļš (piem., default/index)
        $pathsToTry[] = __DIR__ . '/../theme/view/' . $resolvedView . '.php';
    } else {
        // mēģinām ar aktīvo moduli, ja pieejams
        if ($moduleFromRequest) {
            $pathsToTry[] = __DIR__ . '/../theme/view/' . $moduleFromRequest . '/' . $resolvedView . '.php';
        }
        // universālais ceļš
        $pathsToTry[] = __DIR__ . '/../theme/view/' . $resolvedView . '.php';
    }

    $viewFile = null;
    foreach ($pathsToTry as $path) {
        if (is_file($path)) {
            $viewFile = $path;
            break;
        }
    }

    if ($viewFile === null) {
        return "<div class=\"alert alert-warning\">Skats nav atrasts: {$view}</div>";
    }

    extract($data, EXTR_SKIP);
    ob_start();
    include $viewFile;
    return ob_get_clean();
}
?>

