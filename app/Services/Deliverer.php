<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class Deliverer
{
    private $bot;

    public function __construct($replyToken, $secret)
    {
        // 1. 登録されている友だちにメッセージを送信
        $httpClient = new CurlHTTPClient(env('LINE_CHANNEL_ACCESS_TOKEN'));
        $this->bot = new LINEBot($httpClient, ['channelSecret' => $secret]);
    }

    public function deliveryAll($message)
    {
        $textBuilder = new TextMessageBuilder($message);
        $response = $this->bot->broadcast($textBuilder);

        if (!$response->isSucceeded()) {
            Log::error($response->getRawBody());
        }
    }

    public function reply($replyToken, $message)
    {
        $textMessageBuilder = new TextMessageBuilder($message);
        $response = $this->bot->replyMessage($replyToken, $textMessageBuilder);

        if (!$response->isSucceeded()) {
            Log::error($response->getRawBody());
        }
    }
}



// //たい焼きの型を定義
// class Taiyaki {
//       private $ nakami;
//       public function __construct($nakami)
//      {
//           $this-> = $nakami;
//      }
// }

// //たい焼きを作成
// $taiyakiTsubuan = new taiyaki('つぶあん');
// $taiyakiKoshian = new taiyaki('こしあん');
