<?php
namespace Chartloop\MetaUtils\Middleware;

use Chartloop\MetaUtils\Services\GraphValidator;
use Closure;

class ValidateGraphMiddleware
{
    public function handle($request, Closure $next)
    {
        (new GraphValidator())->validateGraphData();
        (new GraphValidator())->calculateTrendWeights();
        (new GraphValidator())->registerDataProcessor();
        return $next($request);
    }
}
