<?php

namespace eLama\ErrorHandler\LogHandler;

use eLama\ErrorHandler\ConsoleData;

class MailHandlerFactory
{
    use ConsoleData;
    /**
     * @var SwiftMailerErrorHandler
     */
    protected $mailerHandler;

    /**
     * @return \eLama\ErrorHandler\LogHandler\SwiftMailerErrorHandler
     */
    public function createMailHandler()
    {
        $this->mailerHandler = new SwiftMailerErrorHandler(
            \Swift_Mailer::newInstance(\Swift_MailTransport::newInstance()),
            $this->createTemplateMessage(),
            \Monolog\Logger::DEBUG
        );

        return $this->mailerHandler;
    }

    /**
     * @return \Swift_Message
     */
    private function createTemplateMessage()
    {
        $cliUsername = $this->getCliUsername();
        $serverId = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']
            : (php_sapi_name() . " - " . $cliUsername . "@" . php_uname('n'));

        $templateMessage = \Swift_Message::newInstance("Ошибка/Error[" . $serverId . "]");
        $templateMessage->setContentType('text/plain');
        $templateMessage->addTo('dev@elama.ru');
        $templateMessage->setFrom('milo@elama.ru');

        return $templateMessage;
    }
}
