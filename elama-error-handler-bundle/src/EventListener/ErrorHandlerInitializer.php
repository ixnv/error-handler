<?php

namespace eLama\ErrorHandler\Bundle\EventListener;

use eLama\ErrorHandler\ErrorCodesCatalog;
use eLama\ErrorHandler\ErrorHandlerContainer;
use eLama\ErrorHandler\Matcher\CodeMessageMatcher;
use eLama\ErrorHandler\Matcher\ExceptionMatcher;
use eLama\ErrorHandler\Matcher\FatalErrorMatcher;
use eLama\ErrorHandler\Matcher\FilePathMatcher;
use eLama\ErrorHandler\Matcher\Matcher;
use eLama\ErrorHandler\Matcher\UnknownFileMatcher;
use eLama\ErrorHandler\Matcher\UserErrorMatcher;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class ErrorHandlerInitializer
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->initializeErrorHandler(false);
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onCommand(ConsoleCommandEvent $event)
    {
        $this->initializeErrorHandler(true);
    }

    /**
     * @param $debugMode
     */
    private function initializeErrorHandler($debugMode)
    {
        ErrorHandlerContainer::init(
            $this->container->getParameter('kernel.root_dir') . '/logs/elama_logs/ErrorHandler',
            $this->createMatchers($this->container->getParameter('error_handler.matchers')),
            $debugMode
        );
    }

    /**
     * @param array $matchesConfig
     * @return Matcher[]
     */
    private function createMatchers(array $matchesConfig)
    {
        $errorCodesCatalog = new ErrorCodesCatalog();

        // Конфиги позволяют игноировать фаталы / exception'ы, но в этом сейчас толку нет, т.к нет возможности в библиотеке
        $matchers = [
            new FatalErrorMatcher($errorCodesCatalog),
            new ExceptionMatcher($errorCodesCatalog),
            new UserErrorMatcher($errorCodesCatalog),
            new UnknownFileMatcher($this->createMatcher($matchesConfig['unknown_file']['handle']))
        ];

        foreach ($matchesConfig['code_messages'] as $codeMessageMatcher) {
            $matchers[] = new CodeMessageMatcher(
                $errorCodesCatalog->getErrorCodeFromString($codeMessageMatcher['error_code']),
                $codeMessageMatcher['message'],
                $this->createMatcher($codeMessageMatcher['handle'])
            );
        }

        foreach ($matchesConfig['file_paths'] as $filePathMatcher) {
            $matchers[] = new FilePathMatcher($filePathMatcher['path'], $this->createMatcher($filePathMatcher['handle']));
        }

        return $matchers;
    }

    /**
     * @param bool $handle
     * @return int
     */
    private function createMatcher($handle)
    {
        return ($handle) ? Matcher::HANDLE : Matcher::IGNORE;
    }
}
