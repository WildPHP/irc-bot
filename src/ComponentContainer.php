<?php
/**
 * Created by PhpStorm.
 * User: rick2
 * Date: 30-4-2017
 * Time: 21:21
 */

namespace WildPHP\Core;


use Evenement\EventEmitter;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use WildPHP\Core\Channels\ChannelCollection;
use WildPHP\Core\Commands\CommandHandler;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\CapabilityHandler;
use WildPHP\Core\Connection\IrcConnection;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Security\PermissionGroupCollection;
use WildPHP\Core\Security\Validator;
use WildPHP\Core\Tasks\TaskController;
use WildPHP\Core\Users\UserCollection;

class ComponentContainer
{
	/**
	 * @var LoopInterface
	 */
	protected $loop = null;

	/**
	 * @var LoggerInterface
	 */
	protected $logger = null;

	/**
	 * @var Configuration
	 */
	protected $configuration = null;

	/**
	 * @var IrcConnection
	 */
	protected $ircConnection = null;

	/**
	 * @var Queue
	 */
	protected $queue = null;

	/**
	 * @var CapabilityHandler
	 */
	protected $capabilityHandler = null;

	/**
	 * @var TaskController
	 */
	protected $taskController = null;

	/**
	 * @var CommandHandler
	 */
	protected $commandHandler = null;

	/**
	 * @var EventEmitter
	 */
	protected $eventEmitter = null;

	/**
	 * @var PermissionGroupCollection
	 */
	protected $permissionGroupCollection = null;

	/**
	 * @var ChannelCollection
	 */
	protected $channelCollection = null;

	/**
	 * @var UserCollection
	 */
	protected $userCollection = null;

	/**
	 * @var Validator
	 */
	protected $validator = null;

	/**
	 * @return LoopInterface
	 */
	public function getLoop(): LoopInterface
	{
		return $this->loop;
	}

	/**
	 * @param LoopInterface $loop
	 */
	public function setLoop(LoopInterface $loop)
	{
		$this->loop = $loop;
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface
	{
		return $this->logger;
	}

	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @return Configuration
	 */
	public function getConfiguration(): Configuration
	{
		return $this->configuration;
	}

	/**
	 * @param Configuration $configuration
	 */
	public function setConfiguration(Configuration $configuration)
	{
		$this->configuration = $configuration;
	}

	/**
	 * @return IrcConnection
	 */
	public function getIrcConnection(): IrcConnection
	{
		return $this->ircConnection;
	}

	/**
	 * @param IrcConnection $ircConnection
	 */
	public function setIrcConnection(IrcConnection $ircConnection)
	{
		$this->ircConnection = $ircConnection;
	}

	/**
	 * @return Queue
	 */
	public function getQueue(): Queue
	{
		return $this->queue;
	}

	/**
	 * @param Queue $queue
	 */
	public function setQueue(Queue $queue)
	{
		$this->queue = $queue;
	}

	/**
	 * @return CapabilityHandler
	 */
	public function getCapabilityHandler(): CapabilityHandler
	{
		return $this->capabilityHandler;
	}

	/**
	 * @param CapabilityHandler $capabilityHandler
	 */
	public function setCapabilityHandler(CapabilityHandler $capabilityHandler)
	{
		$this->capabilityHandler = $capabilityHandler;
	}

	/**
	 * @return TaskController
	 */
	public function getTaskController(): TaskController
	{
		return $this->taskController;
	}

	/**
	 * @param TaskController $taskController
	 */
	public function setTaskController(TaskController $taskController)
	{
		$this->taskController = $taskController;
	}

	/**
	 * @return CommandHandler
	 */
	public function getCommandHandler(): CommandHandler
	{
		return $this->commandHandler;
	}

	/**
	 * @param CommandHandler $commandHandler
	 */
	public function setCommandHandler(CommandHandler $commandHandler)
	{
		$this->commandHandler = $commandHandler;
	}

	/**
	 * @return EventEmitter
	 */
	public function getEventEmitter()
	{
		return $this->eventEmitter;
	}

	/**
	 * @param EventEmitter $eventEmitter
	 */
	public function setEventEmitter($eventEmitter)
	{
		$this->eventEmitter = $eventEmitter;
	}

	/**
	 * @return PermissionGroupCollection
	 */
	public function getPermissionGroupCollection()
	{
		return $this->permissionGroupCollection;
	}

	/**
	 * @param PermissionGroupCollection $permissionGroupCollection
	 */
	public function setPermissionGroupCollection($permissionGroupCollection)
	{
		$this->permissionGroupCollection = $permissionGroupCollection;
	}

	/**
	 * @return ChannelCollection
	 */
	public function getChannelCollection()
	{
		return $this->channelCollection;
	}

	/**
	 * @param ChannelCollection $channelCollection
	 */
	public function setChannelCollection($channelCollection)
	{
		$this->channelCollection = $channelCollection;
	}

	/**
	 * @return UserCollection
	 */
	public function getUserCollection()
	{
		return $this->userCollection;
	}

	/**
	 * @param UserCollection $userCollection
	 */
	public function setUserCollection($userCollection)
	{
		$this->userCollection = $userCollection;
	}

	/**
	 * @return mixed
	 */
	public function getValidator()
	{
		return $this->validator;
	}

	/**
	 * @param mixed $validator
	 */
	public function setValidator($validator)
	{
		$this->validator = $validator;
	}
}