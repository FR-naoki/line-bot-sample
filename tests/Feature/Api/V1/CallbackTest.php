<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallbackTest extends TestCase
{
    /**
     * 署名検証が通過すること
     *
     * @return void
     */
    public function testValidSignature()
    {
        //　署名の文字列を生成
        $channelSecret = env('LINE_CHANNEL_SECRET');
        $httpRequestBody = '';
        $hash = hash_hmac('sha256', $httpRequestBody, $channelSecret, true);
        $signature = base64_encode($hash);

        //　リクエストを実行
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'x-line-signature' => $signature,
        ])->post('/api/v1/callback');

        // レスポンスのhttpステータスコードを検証
        $response->assertStatus(200);
    }
}
