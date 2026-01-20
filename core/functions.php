<?php

/**
 * Parsē URL un sadala to daļās priekš routing sistēmas.
 * 
 * Piemēri:
 *   parseUrl() → ['module' => 'default', 'action' => 'index', 'params' => [], 'query' => []]
 *   parseUrl('users/profile') → ['module' => 'users', 'action' => 'profile', 'params' => [], 'query' => []]
 *   parseUrl('users/profile/123') → ['module' => 'users', 'action' => 'profile', 'params' => ['123'], 'query' => []]
 *   parseUrl('game/start?level=5') → ['module' => 'game', 'action' => 'start', 'params' => [], 'query' => ['level' => '5']]
 * 
 * @param string|null $url URL string (ja null, izmanto $_GET['url'])
 * @return array{module: string, action: string, params: array<int,string>, query: array<string,string>}
 */
function parseUrl(?string $url = null): array
{
    // Ja URL nav padots, mēģina ņemt no $_GET
    if ($url === null) {
        $url = $_GET['url'] ?? '';
    }
    
    // Noņem query string un saglabā to atsevišķi
    $query = [];
    if (($pos = strpos($url, '?')) !== false) {
        parse_str(substr($url, $pos + 1), $query);
        $url = substr($url, 0, $pos);
    }
    
    // Noņem sākuma un beigu slīpsvītras
    $url = trim($url, '/');
    
    // Ja URL ir tukšs, atgriež default
    if (empty($url)) {
        return [
            'module' => 'default',
            'action' => 'index',
            'params' => [],
            'query' => $query
        ];
    }
    
    // Sadala URL pa segmentiem
    $segments = explode('/', $url);
    $segments = array_filter($segments, function($seg) {
        return $seg !== '';
    });
    $segments = array_values($segments);
    
    // Modulis (pirmais segments) vai default
    $module = $segments[0] ?? 'default';
    
    // Action (otrais segments) vai index
    $action = $segments[1] ?? 'index';
    
    // Pārējie parametri (trešais segments un tālāk)
    $params = array_slice($segments, 2);
    
    return [
        'module' => $module,
        'action' => $action,
        'params' => $params,
        'query' => $query
    ];
}

/**
 * Iegūst URL moduli.
 * 
 * @param string|null $url URL string (ja null, izmanto $_GET['url'])
 * @return string
 */
function getUrlModule(?string $url = null): string
{
    return parseUrl($url)['module'];
}

/**
 * Iegūst URL action.
 * 
 * @param string|null $url URL string (ja null, izmanto $_GET['url'])
 * @return string
 */
function getUrlAction(?string $url = null): string
{
    return parseUrl($url)['action'];
}

/**
 * Iegūst URL parametrus.
 * 
 * @param string|null $url URL string (ja null, izmanto $_GET['url'])
 * @return array<int,string>
 */
function getUrlParams(?string $url = null): array
{
    return parseUrl($url)['params'];
}

/**
 * Iegūst konkrētu URL parametru pēc indeksa.
 * 
 * @param int $index Parametra indekss (0 = pirmais params)
 * @param string|null $default Default vērtība, ja parametrs nav atrasts
 * @param string|null $url URL string (ja null, izmanto $_GET['url'])
 * @return string|null
 */
function getUrlParam(int $index, ?string $default = null, ?string $url = null): ?string
{
    $params = getUrlParams($url);
    return $params[$index] ?? $default;
}

/**
 * Iegūst query string parametrus.
 * 
 * @param string|null $url URL string (ja null, izmanto $_GET['url'])
 * @return array<string,string>
 */
function getUrlQuery(?string $url = null): array
{
    return parseUrl($url)['query'];
}

/**
 * Iegūst konkrētu query string parametru.
 * 
 * @param string $key Query parametra atslēga
 * @param string|null $default Default vērtība, ja parametrs nav atrasts
 * @param string|null $url URL string (ja null, izmanto $_GET['url'])
 * @return string|null
 */
function getUrlQueryParam(string $key, ?string $default = null, ?string $url = null): ?string
{
    $query = getUrlQuery($url);
    return $query[$key] ?? $default;
}

/**
 * Ģenerē URL no moduļa, action un parametriem.
 * 
 * Piemēri:
 *   buildUrl('users', 'profile') → 'users/profile'
 *   buildUrl('users', 'profile', ['123']) → 'users/profile/123'
 *   buildUrl('users', 'profile', ['123'], ['tab' => 'info']) → 'users/profile/123?tab=info'
 * 
 * @param string $module Moduļa nosaukums
 * @param string $action Action nosaukums
 * @param array<int,string> $params Parametru masīvs
 * @param array<string,string> $query Query string parametri
 * @return string
 */
function buildUrl(string $module, string $action = 'index', array $params = [], array $query = []): string
{
    $url = $module;
    
    if ($action !== 'index') {
        $url .= '/' . $action;
    }
    
    if (!empty($params)) {
        $url .= '/' . implode('/', $params);
    }
    
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }
    
    return $url;
}

/**
 * Atgriež JSON response ar pareizu header.
 * 
 * Piemēri:
 *   jsonResponse(['success' => true, 'data' => $users]);
 *   jsonResponse(['error' => 'Kļūda'], 400);
 *   jsonResponse($users); // vienkārši masīvs
 * 
 * @param mixed $data Dati, kas jākonvertē uz JSON
 * @param int $code HTTP status kods (200, 400, 404, utt.)
 * @param int $flags JSON encode flags (JSON_PRETTY_PRINT, utt.)
 * @return void
 */
function jsonResponse($data, int $code = 200, int $flags = 0): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    
    // Ja vajag pretty print (attīstībai)
    if ($flags === 0 && (defined('JSON_PRETTY_PRINT') && (php_sapi_name() === 'cli' || isset($_GET['pretty'])))) {
        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
    }
    
    echo json_encode($data, $flags);
    exit;
}

/**
 * Hashē paroli izmantojot drošu algoritmu (bcrypt).
 * 
 * Piemēri:
 *   $hash = hashPassword('manaParole123');
 *   // Saglabā $hash datubāzē
 * 
 * @param string $password Parole, kas jāhashē
 * @return string Hashēta parole (60 rakstzīmes)
 */
function hashPassword(string $password): string
{
    if (empty($password)) {
        throw new InvalidArgumentException('Parole nedrīkst būt tukša');
    }
    
    // Izmanto bcrypt algoritmu (PASSWORD_DEFAULT var mainīties nākotnē)
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    if ($hash === false) {
        throw new RuntimeException('Neizdevās hashēt paroli');
    }
    
    return $hash;
}

/**
 * Pārbauda, vai parole atbilst hash.
 * 
 * Piemēri:
 *   if (verifyPassword('manaParole123', $user['password'])) {
 *       // parole pareiza
 *   }
 * 
 * @param string $password Ievadītā parole
 * @param string $hash Hash no datubāzes
 * @return bool True, ja parole atbilst, false citādi
 */
function verifyPassword(string $password, string $hash): bool
{
    if (empty($password) || empty($hash)) {
        return false;
    }
    
    return password_verify($password, $hash);
}

/**
 * Novirza uz citu URL (redirect).
 * 
 * @param string $url URL uz kuru novirzīt
 * @param int $code HTTP status kods (301, 302, utt.)
 * @return void
 */
function redirect(string $url, int $code = 302): void
{
    // Nodrošina vienu sākuma slīpsvītru (un atbalsta pilnu URL)
    if (!preg_match('#^https?://#i', $url)) {
        $url = '/' . ltrim($url, '/');
    }

    http_response_code($code);
    header("Location: {$url}");
    exit;
}
?>