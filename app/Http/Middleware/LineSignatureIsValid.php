<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LineSignatureIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $channelSecret = env('LINE_CHANNEL_SECRET'); // Channel secret string
        $httpRequestBody = $request->getContent(); // Request body string
        $hash = hash_hmac('sha256', $httpRequestBody, $channelSecret, true);
        $signature = base64_encode($hash);
        // Compare x-line-signature request header string and the signature
        if ($request->header('x-line-signature') !== $signature) {
            // 一致しない場合は403 Forbidden(認証拒否)
            return response()->json([
                'message' => 'invalid request',
            ], 403);
        }
        return $next($request);
    }
}
