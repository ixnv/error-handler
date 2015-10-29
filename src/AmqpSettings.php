<?php

namespace eLama\ErrorHandler;

class AmqpSettings
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port = 5672;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @param string $host
     * @param int $port
     * @param string $login
     * @param string $password
     * @param string $queueName
     */
    public function __construct(
        $host,
        $port,
        $login,
        $password,
        $queueName = 'log-messages'
    ) {
        $this->host = $host;
        $this->login = $login;
        $this->password = $password;
        $this->queueName = $queueName;

        if (!empty($port)) {
            $this->port = $port;
        }
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->queueName;
    }
}
