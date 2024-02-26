<?php

namespace App\Services;

use APP\Models\ReceivedMessage;
use Illuminate\Support\Collection;

class RequestParser
{
    private $content;

    public function __construct(String $content)
    {
        $this->content = json_decode(
            $content
        );
    }
    public function getReceivedMessages(): Collection
    {
        $receivedMessages = new Collection;
        if (is_null($this->content) || is_null($this->content->events)) {
            return $receivedMessages;
        }

        foreach ($this->content->events as $event) {
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

            $receivedMessages->add(new ReceivedMessage($replyToken, $messageText));
        }

        return $receivedMessages;
    }
}
