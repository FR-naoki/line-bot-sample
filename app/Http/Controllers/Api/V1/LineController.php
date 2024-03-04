<?php

namespace App\Http\Controllers\Api\V1;


use App\ExternalApis\WeatherForecastApi\OpenWeatherMap;
use App\Http\Controllers\Controller;
use App\Services\Deliverer;
use App\Services\ReplyMessageGenerator;
use App\Services\RequestParser;
use App\Services\WeatherForecaster;
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

        $replyMessageGenerator = $this->makeReplyMessageGenerator();
        $deliverer = new Deliverer(env('LINE_CHANNEL_ACCESS_TOKEN'), env('LINE_CHANNEL_SECRET'));

        foreach ($receivedMessages as $receivedMessage) {
            //　2. 受け取ったメッセージの内容から返信するメッセージを作成

            $replyMessage = $replyMessageGenerator->generate($receivedMessage->getText());

            // 3. 返信メッセージを返信先に送信

            $deliverer->reply($receivedMessage->getReplyToken(), $replyMessage);
        }

        return response()->json(['message' => 'received']);
    }
    public function makeReplyMessageGenerator()
    {
        //ここを入れ替えると別の天気予報APIを使うこともできる
        $openWeatherMap = new OpenWeatherMap(env('OPENWEATHERMAP_API_KEY'));

        // FIXME: メッセージを受け取った人ごとに変更
        // (今は環境変数に依存しているので固定の場所の天気しか取得できない)
        $weatherForecaster = new WeatherForecaster(
            $openWeatherMap,
            env('OPENWEATHERMAP_LATITUDE'),
            env('OPENWEATHERMAP_LONGITUDE')
        );

        return new ReplyMessageGenerator($weatherForecaster);
    }
}
