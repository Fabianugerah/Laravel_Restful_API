<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nama = $request->input('name');

        if($nama == 'test'){

            return response()->json([
                "errors" => [
                    "message" => [
                        "nama tak kucilkan"
                        ]
                        ]
                        ])->setStatusCode(401);
                    }

        return $next($request);

    }
}
