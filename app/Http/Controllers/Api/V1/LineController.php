<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Deliverer;
use App\Services\ReplyMessageGenerator;
use App\Services\RequestParser;
use Illuminate\Http\Request;


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

        $generator = new ReplyMessageGenerator();
        $deliverer = new Deliverer(env('LINE_CHANNEL_ACCESS_TOKEN'), env('LINE_CHANNEL_SECRET'));

        foreach ($receivedMessages as $receivedMessage) {
            //　2. 受け取ったメッセージの内容から返信するメッセージを作成

            $replyMessage = $generator->generate($receivedMessage->getText());

            // 3. 返信メッセージを返信先に送信

            $deliverer->reply($receivedMessage->getReplToken(), $replyMessage);
        }

        return response()->json(['message' => 'received']);
    }
}
