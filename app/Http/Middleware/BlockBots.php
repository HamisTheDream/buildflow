<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockBots
{
    /**
     * Block known AI bots and crawlers to protect content and resources.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = strtolower($request->header('User-Agent'));

        $blocked = [
            'gptbot',
            'chatgpt',
            'openai',
            'anthropic',
            'claude',
            'google-extended',
            'ccbot',
            'omgili',
            'facebookbot'
        ];

        foreach ($blocked as $bot) {
            if (str_contains($userAgent, $bot)) {
                return response('Access Denied', 403);
            }
        }

        return $next($request);
    }
}
