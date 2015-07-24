<?php

require_once __DIR__.'/../../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

define('TMP_DIR', sys_get_temp_dir().'/testarstatic');

$app->get('/{slug}', function($slug) use($app) {
    $file = TMP_DIR.'/'.$slug;

    if (!file_exists($file)) {
        return $app->json(null, 404);
    }

    return new Symfony\Component\HttpFoundation\BinaryFileResponse($file);
});

$app->delete('/{slug}', function($slug) use($app) {
    $file = TMP_DIR.'/'.$slug;

    if (!file_exists($file)) {
        return $app->json('', 404);
    }

    unlink($file);

    return $app->json('', 204);
});

$app->post('/', function() use($app) {
    $request = $app['request'];
    @mkdir(TMP_DIR);

    $request->files->get('file')->move(TMP_DIR, $request->request->get('slug'));

    return '';
});

$app->run();
