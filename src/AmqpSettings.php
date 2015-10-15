<?php

namespace eLama\ErrorHandler;

class AmqpSettings
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

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
     * @param string $port
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
        $this->port = $port;
        $this->login = $login;
        $this->password = $password;
        $this->queueName = $queueName;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
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
