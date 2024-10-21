<?php

namespace Core\Lib;

use Slim\Flash\Messages;

class FlashMessage extends Messages
{
    public const KEY_MESSAGE = 'message';
    public const KEY_CONFIRM = 'confirm';
    public const KEY_CONFIRM_URL = 'confirm_url';
    public const KEY_OLD = 'old';

    public function setConfirm($message = null, $next_url = null)
    {
        if ($message) {
            $this->addMessage(self::KEY_CONFIRM, $message);
            if ($next_url) {
                $this->addMessage(self::KEY_CONFIRM_URL, $next_url);
            }
        }
    }

    public function setMessage($message = null)
    {
        if ($message) {
            $this->addMessage(self::KEY_MESSAGE, $message);
        }
    }

    public function setOld($parsedBody)
    {
        $this->addMessage(self::KEY_OLD, $parsedBody);
    }
}