<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Deliverer;
use App\Services\ReplyMessageGenerator;
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
        $parser = new RequestParser($request->getContent());
        $receivedMessages = $parser->getReceivedMessages();
        if ($receivedMessages->isEmpty()) {
            return response()->json(['message' => 'received']);
        }

        foreach ($receivedMessages as $receivedMessage) {
            //　2. 受け取ったメッセージの内容から返信するメッセージを作成
            $generator = new ReplyMessageGenerator();
            $replyMessage = $generator->generate($receivedMessage->getText());

            // 3. 返信メッセージを返信先に送信

            $deliverer = new Deliverer(env('LINE_CHANNEL_ACCESS_TOKEN'), env('LINE_CHANNEL_SECRET'));
            $deliverer->reply($receivedMessage->getReplToken(), $replyMessage);
        }

        return response()->json(['message' => 'received']);
    }
}
