<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route as RouteFacade;

class RoutesController extends Controller
{
    /**
     * Return a JSON list of application routes (method, uri, name, middleware).
     */
    public function __invoke(): JsonResponse
    {
        $routes = collect(RouteFacade::getRoutes())->map(function ($route) {
            $methods = $route->methods();
            if (in_array('GET', $methods, true) && in_array('HEAD', $methods, true)) {
                $methods = array_values(array_filter($methods, fn($m) => $m !== 'HEAD'));
            }

            return [
                'method' => implode('/', $methods),
                'uri' => '/' . ltrim($route->uri(), '/'),
                'name' => $route->getName(),
                'action' => is_string($route->getActionName()) ? $route->getActionName() : 'Closure',
                'middleware' => $route->gatherMiddleware(),
            ];
        })->values();

        return response()->json($routes);
    }
}
