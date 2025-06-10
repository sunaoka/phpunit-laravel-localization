<?php

declare(strict_types=1);

namespace Tests\stubs\app\Http\Controllers;

use Illuminate\Http\JsonResponse;

class Controller extends \Illuminate\Routing\Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(__('messages.welcome'));
    }
}
