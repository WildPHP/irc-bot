<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Commands;

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WildPHP\Commands\Command;
use WildPHP\Commands\CommandProcessor;
use WildPHP\Commands\Parameters\StringParameter;
use WildPHP\Commands\ParameterStrategy;
use WildPHP\Commands\ProcessedCommand;
use WildPHP\Core\Commands\CommandRunner;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Configuration\MemoryBackend;
use WildPHP\Core\Events\CommandEvent;
use WildPHP\Core\Events\EventEmitter;
use WildPHP\Core\Queue\IrcMessageQueue;
use WildPHP\Core\Storage\IrcChannelStorage;
use WildPHP\Core\Storage\IrcUserStorage;
use WildPHP\Core\Storage\Providers\MemoryStorageProvider;
use WildPHP\Messages\Generics\IrcMessage;
use WildPHP\Messages\Privmsg;

class CommandRunnerTest extends TestCase
{

    /**
     * @var IrcUserStorage
     */
    private $users;

    /**
     * @var IrcChannelStorage
     */
    private $channels;

    /**
     * @var CommandProcessor
     */
    private $processor;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var CommandRunner
     */
    private $object;

    /**
     * @var MockObject|IrcMessageQueue
     */
    private $queue;

    public function getValidCommand(callable $callback): Command
    {
        return new Command(
            $callback,
            [
                new ParameterStrategy(
                    1, 1, [
                         new StringParameter()
                     ]
                )
            ]
        );
    }

    protected function setUp(): void
    {
        $this->users = new IrcUserStorage(new MemoryStorageProvider());
        $this->users->getOrCreateOneByNickname('Test');

        $this->channels = new IrcChannelStorage(new MemoryStorageProvider());
        $this->channels->getOrCreateOneByName('#test');

        $this->processor = new CommandProcessor();

        $this->configuration = new Configuration(new MemoryBackend('memory'));
        $this->configuration['prefix'] = '!';

        $this->queue = $this->createMock(IrcMessageQueue::class);

        $this->object = new CommandRunner(
            new EventEmitter(),
            $this->configuration,
            $this->processor,
            new Logger('test'),
            $this->channels,
            $this->users,
            $this->queue
        );
    }

    public function testProcessPrivmsg()
    {
        $command = $this->getValidCommand(
            function (CommandEvent $event) {
                $expectedUser = $this->users->getOneByNickname('Test');
                $expectedChannel = $this->channels->getOneByName('#test');

                self::assertEquals('test', $event->getCommand());
                self::assertEquals($expectedUser, $event->getUser());
                self::assertEquals($expectedChannel, $event->getChannel());
                self::assertEquals(['testing'], $event->getParameters());
            }
        );
        $this->processor->registerCommand('test', $command);

        $this->queue->expects(self::exactly(0))->method('__call');

        $prefix = 'Test!username@hostname';
        $verb = 'PRIVMSG';
        $args = ['#test', '!test testing'];
        $incoming = new IrcMessage($prefix, $verb, $args);
        $this->object->processPrivmsg(Privmsg::fromIncomingMessage($incoming));
    }

    public function testProcessPrivmsgWithoutCommand()
    {
        $failingCommand = $this->createMock(Command::class);
        $failingCommand->expects(self::exactly(0))
            ->method('getCallback');

        $this->processor->registerCommand('fail', $failingCommand);

        $prefix = 'Test!username@hostname';
        $verb = 'PRIVMSG';
        $args = ['#test', 'fail'];
        $incoming = new IrcMessage($prefix, $verb, $args);
        $this->object->processPrivmsg(Privmsg::fromIncomingMessage($incoming));
    }

    public function testProcessPrivmsgWithInvalidParameters()
    {
        $command = $this->getValidCommand(
            function () {
                self::fail('This command should not be called.');
            }
        );

        $this->processor->registerCommand('test', $command);
        $this->queue->expects(self::once())
            ->method('__call')
            ->with(
                self::equalTo('privmsg'),
                self::equalTo(['#test', 'Invalid parameters.'])
            );

        $prefix = 'Test!username@hostname';
        $verb = 'PRIVMSG';
        $args = ['#test', '!test testing too many parameters'];
        $incoming = new IrcMessage($prefix, $verb, $args);
        $this->object->processPrivmsg(Privmsg::fromIncomingMessage($incoming));
    }

    public function testRunProcessedCommand()
    {
        $processed = new ProcessedCommand(
            'test', ['testing'], new ParameterStrategy(1, 1), ['testing'],
            function () {
                self::assertTrue(true);
            }
        );

        $this->object->runProcessedCommand(
            $processed,
            $this->users->getOneByNickname('Test'),
            $this->channels->getOneByName('#test')
        );
    }

    public function testProcessCommandLine()
    {
        $command = $this->getValidCommand(function () {});
        $this->processor->registerCommand('test', $command);

        $line = '!test testing';
        $processed = $this->object->processCommandLine($line, '!');

        self::assertEquals('test', $processed->getCommand());
        self::assertEquals(['testing'], $processed->getConvertedParameters());
    }
}
