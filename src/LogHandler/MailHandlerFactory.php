<?php

namespace eLama\ErrorHandler\LogHandler;

use eLama\ErrorHandler\ConsoleData;
use Monolog\Logger;
use Swift_Mailer;
use Swift_MailTransport;
use Swift_Message;

class MailHandlerFactory
{
    use ConsoleData;

    /**
     * @var SwiftMailerErrorHandler
     */
    protected $mailerHandler;

    /**
     * @return SwiftMailerErrorHandler
     */
    public function createMailHandler()
    {
        $this->mailerHandler = new SwiftMailerErrorHandler(
            Swift_Mailer::newInstance(Swift_MailTransport::newInstance()),
            $this->createTemplateMessage(),
            Logger::DEBUG
        );

        return $this->mailerHandler;
    }

    /**
     * @return Swift_Message
     */
    private function createTemplateMessage()
    {
        $cliUsername = $this->getCliUsername();
        $serverId = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']
            : (php_sapi_name() . " - " . $cliUsername . "@" . php_uname('n'));

        $templateMessage = Swift_Message::newInstance("Ошибка/Error[" . $serverId . "]");
        $templateMessage->setContentType('text/plain');
        $templateMessage->addTo('dev@elama.ru');
        $templateMessage->setFrom('milo@elama.ru');

        return $templateMessage;
    }
}
