<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Deliverer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;


class LineController extends Controller
{
    // メッセージ送信
    public function delivery()
    {
        $deliverer = new Deliverer(env('LINE_CHANNEL_ACCESS_TOKEN'), env('LINE_CHANNEL_SECRET'));
        $deliverer->deliveryAll('test');

        return response()->json(['message' => 'sent']);
    }

    // メッセージを受け取って返信
    public function callback(Request $request)
    {
        // TODO: ここに具体的に実装

        // 1. 受け取った情報からメッセージの情報を取り出す
        Log::debug($request->getContent());
        $eventsObj = json_decode($request->getContent());
        if (is_null($eventsObj) || is_null($eventsObj->events)) {
            return response()->json(['message' => 'received(no events)']);
        }

        foreach ($eventsObj->events as $event) {
            // eventのtypeのチェック（messageかどうか）
            if ($event->type !== 'message') {
                continue;
            }
            // messageのtypeのチェック（textかどうか）
            if ($event->message->type !== 'text') {
                continue;
            }

            $replyToken = $event->replyToken;
            $messageText = $event->message->text;
            Log::debug($replyToken);

            switch ($messageText) {
                case '今日の天気は？':
                    // 天気APIを使って情報を取得してきたら正しい情報にできる
                    $replyMessage = 'は、晴れかな・・・（しらんけど）';
                    break;
                case '元気？':
                    $replyMessage = 'はい、元気です。あなたは？';
                    break;
                case '後ウマイヤ朝の最盛期王は？':
                    $replyMessage = 'アブド＝アッラフマーン３世';
                    break;
                default:
                    if (strpos($messageText, '？') !== false) {
                        // 疑問符が含まれている場合(部分一致)
                        $replyMessage = '「今日の天気は？」という質問に答える事ができますよ！';
                    } else {
                        $replyMessage = 'すみません、よくわかりません';
                    }
            }
            // 3. 返信メッセージを返信先に送信

            $deliverer = new Deliverer(env('LINE_CHANNEL_ACCESS_TOKEN', env('LINE_CHANNEL_SECRET')));
            $deliverer->reply($replyToken, $replyMessage);
        }

        return response()->json(['message' => 'received']);
    }
}
