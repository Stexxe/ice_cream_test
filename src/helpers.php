<?php

namespace IceCream;

use Mni\FrontYAML\Parser;

function matches(string $pattern, string $method = 'GET', array &$parameters = null): bool {
    if ($_SERVER['REQUEST_METHOD'] != $method) return false;

    $segments = explode('/', $_SERVER['REQUEST_URI']);

    $patternSegments = explode('/', $pattern);

    foreach ($patternSegments as $i => $patternSegment) {
        $isParameter = preg_match('/\\{(\w+?)\\}/', $patternSegment, $matches);
        if ($isParameter) {
            $parameters[$matches[1]] = $segments[$i];
            continue;
        }

        if ($patternSegment != $segments[$i]) return false;
    }

    return true;
}
function pageExists(string $directory, string $name): bool {
    return file_exists(getPathByName($directory, $name));
}

function getPageContent(string $directory, string $name): string {
    return file_get_contents(getPathByName($directory, $name));
}

function getPathByName(string $directory, string $name): string {
    return $directory . '/' . $name . '.md';
}

function loadPages(Parser $parser, array $fields, string $directory): array {
    $pages = [];
    foreach (readFiles($directory) as $name => $content) {
        $document = $parser->parse($content);
        $pageInfo = $document->getYAML();

        $pageInfo['url'] = [
            'title' => $name,
            'link' => '/' . $name,
        ];

        $pageInfo['edit'] = '/edit/' . $name;
        $pages[] = $pageInfo;

        foreach ($pageInfo as $fieldName => $value) {
            if (!isset($fields[$fieldName])) continue;
            $field = $fields[$fieldName];

            if (in_array($fieldName, ['tool', 'author', 'status'])) {
                if ($field instanceof SelectField) {
                    $field->values[] = $value;
                    $field->values = array_unique($field->values);
                }
            }
        }
    }

    return $pages;
}

function readFiles(string $directory): array {
    $result = [];
    foreach (scandir($directory) as $filename) {
        if ($filename == '.' || $filename == '..') continue;
        $path = $directory . '/' . $filename;
        $content = file_get_contents($path);
        if (!$content) continue;
        $name = pathinfo($path, PATHINFO_FILENAME);
        $result[$name] = $content;
    }

    return $result;
}

function renderEditTemplate(string $path, string $name, string $content, string $title): string {
    ob_start();
    include $path;
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

function respond404() {
    http_response_code(404);
    die();
}
function respondJson(array $json) {
    header('Content-Type: application/json');
    echo json_encode($json);
    die();
}

function redirect(string $to) {
    header('Location: ' . $to);
    die();
}

function respondHtml(string $html) {
    header('Content-Type: text/html');
    echo $html;
    die();
}