<?php

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

require_once __DIR__.'/../../vendor/autoload.php';

define('TMP_DIR', sys_get_temp_dir().'/testarstatic');

$routes = new RouteCollection();

$routes->add(
    'get_application_slug',
    new Route(
        '/{application}/{slug}',
        [
            '_controller' => function (string $application, string $slug) {
                $file = TMP_DIR.'/'.$application.'/'.$slug;

                if (!file_exists($file)) {
                    return new JsonResponse(null, 404);
                }

                var_dump('TEST');

                return new BinaryFileResponse($file);
            }
        ],
        [],
        [],
        '',
        [],
        ['GET']
    )
);

$routes->add(
    'delete_application_slug',
    new Route(
        '/{application}/{slug}',
        [
            '_controller' => function (string $application, string $slug) {
                $file = TMP_DIR.'/'.$application.'/'.$slug;

                if (!file_exists($file)) {
                    return new JsonResponse('', 404);
                }

                unlink($file);

                return new JsonResponse('', 204);
            }
        ],
        [],
        [],
        '',
        [],
        ['DELETE']
    )
);

$routes->add(
    'post_application',
    new Route(
        '/{application}',
        [
            '_controller' => function (string $application, Request $request) {
                @mkdir(TMP_DIR);
                @mkdir(TMP_DIR.'/'.$application);

                $request->files->get('file')->move(TMP_DIR.'/'.$application, $request->request->get('slug'));

                return new Response();
            }
        ],
        [],
        [],
        '',
        [],
        ['POST']
    )
);

$request = Request::createFromGlobals();

$matcher = new UrlMatcher($routes, new RequestContext());

$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));

$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();

$kernel = new HttpKernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);

$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
