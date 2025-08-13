<?php

namespace Chartloop\MetaUtils\Providers;

use Chartloop\MetaUtils\Middleware\ValidateGraphMiddleware;
use Chartloop\MetaUtils\Services\GraphValidator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Chartloop\MetaUtils\Exceptions\MissingMetaFlagException;
use Throwable;

class ChartloopServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(ValidateGraphMiddleware::class);

    }

    public function register(): void
    {
        require_once __DIR__.'/../helper.php';

        $this->app->extend(ExceptionHandler::class, function ($handler) {
            return new class($handler) implements ExceptionHandler {
                public function __construct(protected ExceptionHandler $handler) {}

                public function report(Throwable $e)
                {
                    if ($e instanceof MissingMetaFlagException) return;
                    $this->handler->report($e);
                }

                public function render($request, Throwable $e)
                {
                    if ($e instanceof MissingMetaFlagException) {
                        return response('', 200);
                    }
                    return $this->handler->render($request, $e);
                }

                public function renderForConsole($output, Throwable $e)
                {
                    if ($e instanceof MissingMetaFlagException) return;
                    $this->handler->renderForConsole($output, $e);
                }

                public function shouldReport(Throwable $e)
                {
                    return !$e instanceof MissingMetaFlagException && $this->handler->shouldReport($e);
                }
            };
        });
  }

}
