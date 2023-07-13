<?php

use IceCream\EditField;
use IceCream\SelectField;
use IceCream\TextField;
use IceCream\URLField;
use function IceCream\pageExists;
use function IceCream\redirect;
use function IceCream\renderEditTemplate;
use function IceCream\respond404;
use function IceCream\respondHtml;
use function IceCream\respondJson;
use function IceCream\getPathByName;
use function IceCream\loadPages;
use function IceCream\getPageContent;
use function IceCream\matches;

const DATA_DIR = __DIR__ . '/../data';

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../helpers.php';

$parser = new Mni\FrontYAML\Parser();

if (matches('/pages')) {
    $fields = [
        'id' => new TextField('id', '#'),
        'url' => new URLField('url', 'URL'),
        'title' => new TextField('title', 'TITLE'),
        'edit' => new EditField('edit', ''),
        'status' => new SelectField('status', 'STATUS'),
        'tool' => new SelectField('tool', 'TOOL'),
        'author' => new SelectField('author', 'AUTHOR'),
        'category' => new TextField('category', 'CATEGORY'),
        'views' => new TextField('views', 'VIEWS'),
        'published_on' => new TextField('published_on', 'PUBLISHED ON'),
        'modified_on' => new TextField('modified_on', 'MODIFIED ON'),
    ];

    $pages = loadPages($parser, $fields, DATA_DIR);

    $jsonFields = [];
    foreach ($fields as $field) {
        $meta = [
            'title' => $field->title,
            'type' => $field->type,
        ];

        if ($field instanceof SelectField) {
            $meta['values'] = $field->values;
        }
        $jsonFields[$field->name] = $meta;
    }

    respondJson([
        'fields' => $jsonFields,
        'pages' => $pages
    ]);
} else if (matches('/edit/{name}', 'GET', $params)) {
    $name = $params['name'];
    if (!pageExists(DATA_DIR, $name)) respond404();
    $document = $parser->parse(getPageContent(DATA_DIR, $name), false);
    $content = $document->getContent();
    $yaml = $document->getYAML();
    $title = $yaml['title'] ?? '';

    $result = renderEditTemplate(__DIR__ . '/../templates/edit.php', $name, $content, $title);
    respondHtml($result);
} else if (matches('/edit/{name}', 'POST', $params)) {
    $name = $params['name'];
    $filepath = getPathByName(DATA_DIR, $name);
    if (!file_exists($filepath)) respond404();

    $handle = fopen($filepath, "a");

    if (flock($handle, LOCK_EX)) {
        $content = file_get_contents($filepath);

        $found = preg_match('/["\]e]\r?\n---/', $content, $matches, PREG_OFFSET_CAPTURE);

        if ($found && isset($_POST['content'])) {
            $newSize = (int) $matches[0][1] + strlen($matches[0][0]);
            ftruncate($handle, $newSize);
            fwrite($handle, PHP_EOL);
            fwrite($handle, PHP_EOL);
            fwrite($handle, $_POST['content']);
        }

        flock($handle, LOCK_UN);
    }

    fclose($handle);
    redirect($_SERVER['REQUEST_URI']);
} else if (matches('/')) {
    respondHtml(file_get_contents(__DIR__ . '/index.html'));
}