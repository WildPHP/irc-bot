<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use ValidationClosures\Types;
use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Channels\ChannelCollection;
use WildPHP\Core\Channels\ChannelModes;
use WildPHP\Core\Commands\Command;
use WildPHP\Core\Commands\CommandHandler;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Configuration\NeonBackend;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Permissions\PermissionCommands;
use WildPHP\Core\Permissions\PermissionGroup;
use WildPHP\Core\Permissions\PermissionGroupCollection;
use WildPHP\Core\Permissions\PermissionGroupCommands;
use WildPHP\Core\Permissions\PermissionMembersCommands;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserCollection;
use Yoshi2889\Collections\Collection;

class PermissionGroupCommandsTest extends TestCase
{
	/**
	 * @var Channel
	 */
	protected $channel;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var ComponentContainer
	 */
	protected $container;

	public function setUp()
	{
		if (!defined('WPHP_ROOT_DIR'))
			define('WPHP_ROOT_DIR', dirname(__FILE__));
		$this->container = new ComponentContainer();
		$this->container->add(new EventEmitter());
		$this->container->add(new Logger('wildphp'));
		$this->container->add(new CommandHandler($this->container, new Collection(Types::instanceof(Command::class))));
		$permissionGroupsCollection = new PermissionGroupCollection();
		$this->container->add($permissionGroupsCollection);
		
		$validator = new \WildPHP\Core\Permissions\Validator(EventEmitter::fromContainer($this->container), $permissionGroupsCollection, 'tester2');
		$this->container->add($validator);
		
		$permissionGroupsCollection->offsetSet('testGroup', new PermissionGroup($permissionGroupsCollection->getStoredGroupData('testGroup')));

		$neonBackend = new NeonBackend(dirname(__FILE__) . '/emptyconfig.neon');
		$configuration = new Configuration($neonBackend);
		$configuration['serverConfig']['chantypes'] = '#';
		$this->container->add($configuration);

		$this->container->add(new Queue());

		$channelCollection = new ChannelCollection();
		$this->channel = new Channel('#test', new UserCollection(), new ChannelModes(''));
		$channelCollection->append($this->channel);
		$this->container->add($channelCollection);

		$this->user = new User('Tester', '', '', 'testUser');
		$this->channel->getUserCollection()->append(new User('Tester2', '', '', 'testUser2'));
		$this->channel->getUserCollection()->append($this->user);
	}

	public function testIsCompatible()
	{
		if (!defined('WPHP_VERSION'))
			define('WPHP_VERSION', '3.0.0');

		self::assertEquals(WPHP_VERSION, PermissionGroupCommands::getSupportedVersionConstraint());
	}

	public function testAllowCommand()
	{
		$permissionGroupCommands = new PermissionCommands($this->container);
		/** @var PermissionGroup $group */
		$group = PermissionGroupCollection::fromContainer($this->container)->offsetGet('testGroup');
		$group->getAllowedPermissions()->exchangeArray([]);
		
		self::assertFalse($group->getAllowedPermissions()->contains('testPermission'));
		$permissionGroupCommands->allowCommand($this->channel, $this->user, ['testGroup', 'testPermission'], $this->container);
		self::assertTrue($group->getAllowedPermissions()->contains('testPermission'));
		Queue::fromContainer($this->container)->clear();
	}

	public function testDenyCommand()
	{
		$permissionGroupCommands = new PermissionCommands($this->container);
		/** @var PermissionGroup $group */
		$group = PermissionGroupCollection::fromContainer($this->container)->offsetGet('testGroup');
		$group->getAllowedPermissions()->exchangeArray(['testPermission']);
		
		self::assertTrue($group->getAllowedPermissions()->contains('testPermission'));
		$permissionGroupCommands->denyCommand($this->channel, $this->user, ['testGroup', 'testPermission'], $this->container);
		self::assertFalse($group->getAllowedPermissions()->contains('testPermission'));
		Queue::fromContainer($this->container)->clear();
	}

	public function testLspermsCommand()
	{
		$permissionGroupCommands = new PermissionCommands($this->container);

		$permissionGroupCommands->lspermsCommand($this->channel, $this->user, ['groupName' => 'testGroup'], $this->container);
		
		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testLsmembersCommand()
	{
		$permissionGroupCommands = new PermissionMembersCommands($this->container);

		$permissionGroupCommands->lsmembersCommand($this->channel, $this->user, ['groupName' => 'testGroup'], $this->container);

		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testAddMemberCommand()
	{
		$permissionGroupCommands = new PermissionMembersCommands($this->container);
		/** @var PermissionGroup $group */
		$group = PermissionGroupCollection::fromContainer($this->container)->offsetGet('testGroup');
		$group->getUserCollection()->exchangeArray(['testUser']);

		$permissionGroupCommands->addmemberCommand($this->channel, $this->user, ['testGroup', 'Tester2'], $this->container);
		self::assertTrue($group->getUserCollection()->contains('testUser2'));

		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testDelMemberCommand()
	{
		$permissionGroupCommands = new PermissionMembersCommands($this->container);
		/** @var PermissionGroup $group */
		$group = PermissionGroupCollection::fromContainer($this->container)->offsetGet('testGroup');
		$group->getUserCollection()->exchangeArray(['testUser']);

		$permissionGroupCommands->delmemberCommand($this->channel, $this->user, ['testGroup', 'Tester'], $this->container);
		self::assertFalse($group->getUserCollection()->contains('testUser'));

		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testValidateCommand()
	{
		$permissionGroupCommands = new PermissionGroupCommands($this->container);
		/** @var PermissionGroup $group */
		$group = PermissionGroupCollection::fromContainer($this->container)->offsetGet('testGroup');
		$group->getUserCollection()->exchangeArray(['testUser']);
		$group->getAllowedPermissions()->exchangeArray(['testing']);

		$permissionGroupCommands->validateCommand($this->channel, $this->user, ['permission' => 'testing'], $this->container);
		$permissionGroupCommands->validateCommand($this->channel, $this->user, ['permission' => 'testing', 'username' => 'Tester2'], $this->container);

		self::assertEquals(2, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testLsGroupsCommand()
	{
		$permissionGroupCommands = new PermissionGroupCommands($this->container);

		$permissionGroupCommands->lsgroupsCommand($this->channel, $this->user, [], $this->container);

		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testCreategroupCommand()
	{
		$permissionGroupCommands = new PermissionGroupCommands($this->container);

		$permissionGroupCommands->creategroupCommand($this->channel, $this->user, ['groupName' => 'testGroup2'], $this->container);
		self::assertTrue(PermissionGroupCollection::fromContainer($this->container)->offsetExists('testGroup2'));

		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testDelgroupCommand()
	{
		$permissionGroupCommands = new PermissionGroupCommands($this->container);
		$group = new PermissionGroup();
		PermissionGroupCollection::fromContainer($this->container)->offsetSet('testGroup2', $group);

		$permissionGroupCommands->delgroupCommand($this->channel, $this->user, ['group' => $group], $this->container);
		self::assertFalse(PermissionGroupCollection::fromContainer($this->container)->offsetExists('testGroup2'));

		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testLinkGroupCommand()
	{
		$permissionGroupCommands = new PermissionGroupCommands($this->container);
		/** @var PermissionGroup $group */
		$group = PermissionGroupCollection::fromContainer($this->container)->offsetGet('testGroup');
		$group->getChannelCollection()->exchangeArray([]);

		$permissionGroupCommands->linkgroupCommand($this->channel, $this->user, ['group' => $group, 'channel' => $this->channel], $this->container);
		self::assertTrue($group->getChannelCollection()->contains('#test'));

		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testUnlinkGroupCommand()
	{
		$permissionGroupCommands = new PermissionGroupCommands($this->container);
		/** @var PermissionGroup $group */
		$group = PermissionGroupCollection::fromContainer($this->container)->offsetGet('testGroup');
		$group->getChannelCollection()->exchangeArray(['#test']);

		$permissionGroupCommands->unlinkgroupCommand($this->channel, $this->user, ['group' => $group, 'channel' => $this->channel], $this->container);
		self::assertFalse($group->getChannelCollection()->contains('#test'));

		self::assertEquals(1, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}

	public function testGroupInfoCommand()
	{
		$permissionGroupCommands = new PermissionGroupCommands($this->container);
		/** @var PermissionGroup $group */
		$group = PermissionGroupCollection::fromContainer($this->container)->offsetGet('testGroup');
		$group->getUserCollection()->exchangeArray(['testUser']);
		$group->getAllowedPermissions()->exchangeArray(['testing']);
		$group->getChannelCollection()->exchangeArray(['#test']);

		$permissionGroupCommands->groupinfoCommand($this->channel, $this->user, ['group' => $group], $this->container);

		self::assertEquals(6, Queue::fromContainer($this->container)->count());
		Queue::fromContainer($this->container)->clear();
	}
}
