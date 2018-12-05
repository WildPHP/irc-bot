<?php

namespace WildPHP\Core\Entities\Base;

use \DateTime;
use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Runtime\Util\PropelDateTime;
use WildPHP\Core\Entities\IrcChannel as ChildIrcChannel;
use WildPHP\Core\Entities\IrcChannelQuery as ChildIrcChannelQuery;
use WildPHP\Core\Entities\IrcUser as ChildIrcUser;
use WildPHP\Core\Entities\IrcUserQuery as ChildIrcUserQuery;
use WildPHP\Core\Entities\UserChannel as ChildUserChannel;
use WildPHP\Core\Entities\UserChannelQuery as ChildUserChannelQuery;
use WildPHP\Core\Entities\UserModeChannel as ChildUserModeChannel;
use WildPHP\Core\Entities\UserModeChannelQuery as ChildUserModeChannelQuery;
use WildPHP\Core\Entities\Map\IrcUserTableMap;
use WildPHP\Core\Entities\Map\UserChannelTableMap;
use WildPHP\Core\Entities\Map\UserModeChannelTableMap;

/**
 * Base class that represents a row from the 'user' table.
 *
 *
 *
 * @package    propel.generator.WildPHP.Core.Entities.Base
 */
abstract class IrcUser implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\WildPHP\\Core\\Entities\\Map\\IrcUserTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the id field.
     *
     * @var        int
     */
    protected $id;

    /**
     * The value for the nickname field.
     *
     * @var        string
     */
    protected $nickname;

    /**
     * The value for the username field.
     *
     * @var        string
     */
    protected $username;

    /**
     * The value for the realname field.
     *
     * @var        string
     */
    protected $realname;

    /**
     * The value for the hostname field.
     *
     * @var        string
     */
    protected $hostname;

    /**
     * The value for the irc_account field.
     *
     * @var        string
     */
    protected $irc_account;

    /**
     * The value for the last_seen field.
     *
     * @var        DateTime
     */
    protected $last_seen;

    /**
     * @var        ObjectCollection|ChildUserChannel[] Collection to store aggregation of ChildUserChannel objects.
     */
    protected $collUserChannels;
    protected $collUserChannelsPartial;

    /**
     * @var        ObjectCollection|ChildUserModeChannel[] Collection to store aggregation of ChildUserModeChannel objects.
     */
    protected $collUserModeChannels;
    protected $collUserModeChannelsPartial;

    /**
     * @var        ObjectCollection|ChildIrcChannel[] Cross Collection to store aggregation of ChildIrcChannel objects.
     */
    protected $collIrcChannels;

    /**
     * @var bool
     */
    protected $collIrcChannelsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildIrcChannel[]
     */
    protected $ircChannelsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildUserChannel[]
     */
    protected $userChannelsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildUserModeChannel[]
     */
    protected $userModeChannelsScheduledForDeletion = null;

    /**
     * Initializes internal state of WildPHP\Core\Entities\Base\IrcUser object.
     */
    public function __construct()
    {
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>IrcUser</code> instance.  If
     * <code>obj</code> is an instance of <code>IrcUser</code>, delegates to
     * <code>equals(IrcUser)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        if (!$obj instanceof static) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey() || null === $obj->getPrimaryKey()) {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return $this|IrcUser The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        $cls = new \ReflectionClass($this);
        $propertyNames = [];
        $serializableProperties = array_diff($cls->getProperties(), $cls->getProperties(\ReflectionProperty::IS_STATIC));

        foreach($serializableProperties as $property) {
            $propertyNames[] = $property->getName();
        }

        return $propertyNames;
    }

    /**
     * Get the [id] column value.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [nickname] column value.
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * Get the [username] column value.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the [realname] column value.
     *
     * @return string
     */
    public function getRealname()
    {
        return $this->realname;
    }

    /**
     * Get the [hostname] column value.
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Get the [irc_account] column value.
     *
     * @return string
     */
    public function getIrcAccount()
    {
        return $this->irc_account;
    }

    /**
     * Get the [optionally formatted] temporal [last_seen] column value.
     *
     *
     * @param      string|null $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getLastSeen($format = NULL)
    {
        if ($format === null) {
            return $this->last_seen;
        } else {
            return $this->last_seen instanceof \DateTimeInterface ? $this->last_seen->format($format) : null;
        }
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v new value
     * @return $this|\WildPHP\Core\Entities\IrcUser The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[IrcUserTableMap::COL_ID] = true;
        }

        return $this;
    } // setId()

    /**
     * Set the value of [nickname] column.
     *
     * @param string $v new value
     * @return $this|\WildPHP\Core\Entities\IrcUser The current object (for fluent API support)
     */
    public function setNickname($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->nickname !== $v) {
            $this->nickname = $v;
            $this->modifiedColumns[IrcUserTableMap::COL_NICKNAME] = true;
        }

        return $this;
    } // setNickname()

    /**
     * Set the value of [username] column.
     *
     * @param string $v new value
     * @return $this|\WildPHP\Core\Entities\IrcUser The current object (for fluent API support)
     */
    public function setUsername($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->username !== $v) {
            $this->username = $v;
            $this->modifiedColumns[IrcUserTableMap::COL_USERNAME] = true;
        }

        return $this;
    } // setUsername()

    /**
     * Set the value of [realname] column.
     *
     * @param string $v new value
     * @return $this|\WildPHP\Core\Entities\IrcUser The current object (for fluent API support)
     */
    public function setRealname($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->realname !== $v) {
            $this->realname = $v;
            $this->modifiedColumns[IrcUserTableMap::COL_REALNAME] = true;
        }

        return $this;
    } // setRealname()

    /**
     * Set the value of [hostname] column.
     *
     * @param string $v new value
     * @return $this|\WildPHP\Core\Entities\IrcUser The current object (for fluent API support)
     */
    public function setHostname($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->hostname !== $v) {
            $this->hostname = $v;
            $this->modifiedColumns[IrcUserTableMap::COL_HOSTNAME] = true;
        }

        return $this;
    } // setHostname()

    /**
     * Set the value of [irc_account] column.
     *
     * @param string $v new value
     * @return $this|\WildPHP\Core\Entities\IrcUser The current object (for fluent API support)
     */
    public function setIrcAccount($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->irc_account !== $v) {
            $this->irc_account = $v;
            $this->modifiedColumns[IrcUserTableMap::COL_IRC_ACCOUNT] = true;
        }

        return $this;
    } // setIrcAccount()

    /**
     * Sets the value of [last_seen] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this|\WildPHP\Core\Entities\IrcUser The current object (for fluent API support)
     */
    public function setLastSeen($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->last_seen !== null || $dt !== null) {
            if ($this->last_seen === null || $dt === null || $dt->format("Y-m-d H:i:s.u") !== $this->last_seen->format("Y-m-d H:i:s.u")) {
                $this->last_seen = $dt === null ? null : clone $dt;
                $this->modifiedColumns[IrcUserTableMap::COL_LAST_SEEN] = true;
            }
        } // if either are not null

        return $this;
    } // setLastSeen()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : IrcUserTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : IrcUserTableMap::translateFieldName('Nickname', TableMap::TYPE_PHPNAME, $indexType)];
            $this->nickname = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : IrcUserTableMap::translateFieldName('Username', TableMap::TYPE_PHPNAME, $indexType)];
            $this->username = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : IrcUserTableMap::translateFieldName('Realname', TableMap::TYPE_PHPNAME, $indexType)];
            $this->realname = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : IrcUserTableMap::translateFieldName('Hostname', TableMap::TYPE_PHPNAME, $indexType)];
            $this->hostname = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : IrcUserTableMap::translateFieldName('IrcAccount', TableMap::TYPE_PHPNAME, $indexType)];
            $this->irc_account = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : IrcUserTableMap::translateFieldName('LastSeen', TableMap::TYPE_PHPNAME, $indexType)];
            $this->last_seen = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 7; // 7 = IrcUserTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\WildPHP\\Core\\Entities\\IrcUser'), 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(IrcUserTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildIrcUserQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collUserChannels = null;

            $this->collUserModeChannels = null;

            $this->collIrcChannels = null;
        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see IrcUser::setDeleted()
     * @see IrcUser::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(IrcUserTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildIrcUserQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $this->setDeleted(true);
            }
        });
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($this->alreadyInSave) {
            return 0;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(IrcUserTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                IrcUserTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }

            return $affectedRows;
        });
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                $this->resetModified();
            }

            if ($this->ircChannelsScheduledForDeletion !== null) {
                if (!$this->ircChannelsScheduledForDeletion->isEmpty()) {
                    $pks = array();
                    foreach ($this->ircChannelsScheduledForDeletion as $entry) {
                        $entryPk = [];

                        $entryPk[0] = $this->getId();
                        $entryPk[1] = $entry->getId();
                        $pks[] = $entryPk;
                    }

                    \WildPHP\Core\Entities\UserChannelQuery::create()
                        ->filterByPrimaryKeys($pks)
                        ->delete($con);

                    $this->ircChannelsScheduledForDeletion = null;
                }

            }

            if ($this->collIrcChannels) {
                foreach ($this->collIrcChannels as $ircChannel) {
                    if (!$ircChannel->isDeleted() && ($ircChannel->isNew() || $ircChannel->isModified())) {
                        $ircChannel->save($con);
                    }
                }
            }


            if ($this->userChannelsScheduledForDeletion !== null) {
                if (!$this->userChannelsScheduledForDeletion->isEmpty()) {
                    \WildPHP\Core\Entities\UserChannelQuery::create()
                        ->filterByPrimaryKeys($this->userChannelsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->userChannelsScheduledForDeletion = null;
                }
            }

            if ($this->collUserChannels !== null) {
                foreach ($this->collUserChannels as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->userModeChannelsScheduledForDeletion !== null) {
                if (!$this->userModeChannelsScheduledForDeletion->isEmpty()) {
                    \WildPHP\Core\Entities\UserModeChannelQuery::create()
                        ->filterByPrimaryKeys($this->userModeChannelsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->userModeChannelsScheduledForDeletion = null;
                }
            }

            if ($this->collUserModeChannels !== null) {
                foreach ($this->collUserModeChannels as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[IrcUserTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . IrcUserTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(IrcUserTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(IrcUserTableMap::COL_NICKNAME)) {
            $modifiedColumns[':p' . $index++]  = 'nickname';
        }
        if ($this->isColumnModified(IrcUserTableMap::COL_USERNAME)) {
            $modifiedColumns[':p' . $index++]  = 'username';
        }
        if ($this->isColumnModified(IrcUserTableMap::COL_REALNAME)) {
            $modifiedColumns[':p' . $index++]  = 'realname';
        }
        if ($this->isColumnModified(IrcUserTableMap::COL_HOSTNAME)) {
            $modifiedColumns[':p' . $index++]  = 'hostname';
        }
        if ($this->isColumnModified(IrcUserTableMap::COL_IRC_ACCOUNT)) {
            $modifiedColumns[':p' . $index++]  = 'irc_account';
        }
        if ($this->isColumnModified(IrcUserTableMap::COL_LAST_SEEN)) {
            $modifiedColumns[':p' . $index++]  = 'last_seen';
        }

        $sql = sprintf(
            'INSERT INTO user (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'id':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case 'nickname':
                        $stmt->bindValue($identifier, $this->nickname, PDO::PARAM_STR);
                        break;
                    case 'username':
                        $stmt->bindValue($identifier, $this->username, PDO::PARAM_STR);
                        break;
                    case 'realname':
                        $stmt->bindValue($identifier, $this->realname, PDO::PARAM_STR);
                        break;
                    case 'hostname':
                        $stmt->bindValue($identifier, $this->hostname, PDO::PARAM_STR);
                        break;
                    case 'irc_account':
                        $stmt->bindValue($identifier, $this->irc_account, PDO::PARAM_STR);
                        break;
                    case 'last_seen':
                        $stmt->bindValue($identifier, $this->last_seen ? $this->last_seen->format("Y-m-d H:i:s.u") : null, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = IrcUserTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getNickname();
                break;
            case 2:
                return $this->getUsername();
                break;
            case 3:
                return $this->getRealname();
                break;
            case 4:
                return $this->getHostname();
                break;
            case 5:
                return $this->getIrcAccount();
                break;
            case 6:
                return $this->getLastSeen();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {

        if (isset($alreadyDumpedObjects['IrcUser'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['IrcUser'][$this->hashCode()] = true;
        $keys = IrcUserTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getNickname(),
            $keys[2] => $this->getUsername(),
            $keys[3] => $this->getRealname(),
            $keys[4] => $this->getHostname(),
            $keys[5] => $this->getIrcAccount(),
            $keys[6] => $this->getLastSeen(),
        );
        if ($result[$keys[6]] instanceof \DateTimeInterface) {
            $result[$keys[6]] = $result[$keys[6]]->format('c');
        }

        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collUserChannels) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'userChannels';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'user_channels';
                        break;
                    default:
                        $key = 'UserChannels';
                }

                $result[$key] = $this->collUserChannels->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collUserModeChannels) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'userModeChannels';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'user_mode_channels';
                        break;
                    default:
                        $key = 'UserModeChannels';
                }

                $result[$key] = $this->collUserModeChannels->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param  string $name
     * @param  mixed  $value field value
     * @param  string $type The type of fieldname the $name is of:
     *                one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                Defaults to TableMap::TYPE_PHPNAME.
     * @return $this|\WildPHP\Core\Entities\IrcUser
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = IrcUserTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\WildPHP\Core\Entities\IrcUser
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setNickname($value);
                break;
            case 2:
                $this->setUsername($value);
                break;
            case 3:
                $this->setRealname($value);
                break;
            case 4:
                $this->setHostname($value);
                break;
            case 5:
                $this->setIrcAccount($value);
                break;
            case 6:
                $this->setLastSeen($value);
                break;
        } // switch()

        return $this;
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = IrcUserTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setNickname($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setUsername($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setRealname($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setHostname($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setIrcAccount($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setLastSeen($arr[$keys[6]]);
        }
    }

     /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     * @param string $keyType The type of keys the array uses.
     *
     * @return $this|\WildPHP\Core\Entities\IrcUser The current object, for fluid interface
     */
    public function importFrom($parser, $data, $keyType = TableMap::TYPE_PHPNAME)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), $keyType);

        return $this;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(IrcUserTableMap::DATABASE_NAME);

        if ($this->isColumnModified(IrcUserTableMap::COL_ID)) {
            $criteria->add(IrcUserTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(IrcUserTableMap::COL_NICKNAME)) {
            $criteria->add(IrcUserTableMap::COL_NICKNAME, $this->nickname);
        }
        if ($this->isColumnModified(IrcUserTableMap::COL_USERNAME)) {
            $criteria->add(IrcUserTableMap::COL_USERNAME, $this->username);
        }
        if ($this->isColumnModified(IrcUserTableMap::COL_REALNAME)) {
            $criteria->add(IrcUserTableMap::COL_REALNAME, $this->realname);
        }
        if ($this->isColumnModified(IrcUserTableMap::COL_HOSTNAME)) {
            $criteria->add(IrcUserTableMap::COL_HOSTNAME, $this->hostname);
        }
        if ($this->isColumnModified(IrcUserTableMap::COL_IRC_ACCOUNT)) {
            $criteria->add(IrcUserTableMap::COL_IRC_ACCOUNT, $this->irc_account);
        }
        if ($this->isColumnModified(IrcUserTableMap::COL_LAST_SEEN)) {
            $criteria->add(IrcUserTableMap::COL_LAST_SEEN, $this->last_seen);
        }

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @throws LogicException if no primary key is defined
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = ChildIrcUserQuery::create();
        $criteria->add(IrcUserTableMap::COL_ID, $this->id);

        return $criteria;
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        $validPk = null !== $this->getId();

        $validPrimaryKeyFKs = 0;
        $primaryKeyFKs = [];

        if ($validPk) {
            return crc32(json_encode($this->getPrimaryKey(), JSON_UNESCAPED_UNICODE));
        } elseif ($validPrimaryKeyFKs) {
            return crc32(json_encode($primaryKeyFKs, JSON_UNESCAPED_UNICODE));
        }

        return spl_object_hash($this);
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {
        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \WildPHP\Core\Entities\IrcUser (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setNickname($this->getNickname());
        $copyObj->setUsername($this->getUsername());
        $copyObj->setRealname($this->getRealname());
        $copyObj->setHostname($this->getHostname());
        $copyObj->setIrcAccount($this->getIrcAccount());
        $copyObj->setLastSeen($this->getLastSeen());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getUserChannels() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addUserChannel($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getUserModeChannels() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addUserModeChannel($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param  boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return \WildPHP\Core\Entities\IrcUser Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('UserChannel' == $relationName) {
            $this->initUserChannels();
            return;
        }
        if ('UserModeChannel' == $relationName) {
            $this->initUserModeChannels();
            return;
        }
    }

    /**
     * Clears out the collUserChannels collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addUserChannels()
     */
    public function clearUserChannels()
    {
        $this->collUserChannels = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collUserChannels collection loaded partially.
     */
    public function resetPartialUserChannels($v = true)
    {
        $this->collUserChannelsPartial = $v;
    }

    /**
     * Initializes the collUserChannels collection.
     *
     * By default this just sets the collUserChannels collection to an empty array (like clearcollUserChannels());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initUserChannels($overrideExisting = true)
    {
        if (null !== $this->collUserChannels && !$overrideExisting) {
            return;
        }

        $collectionClassName = UserChannelTableMap::getTableMap()->getCollectionClassName();

        $this->collUserChannels = new $collectionClassName;
        $this->collUserChannels->setModel('\WildPHP\Core\Entities\UserChannel');
    }

    /**
     * Gets an array of ChildUserChannel objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildIrcUser is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildUserChannel[] List of ChildUserChannel objects
     * @throws PropelException
     */
    public function getUserChannels(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collUserChannelsPartial && !$this->isNew();
        if (null === $this->collUserChannels || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collUserChannels) {
                // return empty collection
                $this->initUserChannels();
            } else {
                $collUserChannels = ChildUserChannelQuery::create(null, $criteria)
                    ->filterByIrcUser($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collUserChannelsPartial && count($collUserChannels)) {
                        $this->initUserChannels(false);

                        foreach ($collUserChannels as $obj) {
                            if (false == $this->collUserChannels->contains($obj)) {
                                $this->collUserChannels->append($obj);
                            }
                        }

                        $this->collUserChannelsPartial = true;
                    }

                    return $collUserChannels;
                }

                if ($partial && $this->collUserChannels) {
                    foreach ($this->collUserChannels as $obj) {
                        if ($obj->isNew()) {
                            $collUserChannels[] = $obj;
                        }
                    }
                }

                $this->collUserChannels = $collUserChannels;
                $this->collUserChannelsPartial = false;
            }
        }

        return $this->collUserChannels;
    }

    /**
     * Sets a collection of ChildUserChannel objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $userChannels A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildIrcUser The current object (for fluent API support)
     */
    public function setUserChannels(Collection $userChannels, ConnectionInterface $con = null)
    {
        /** @var ChildUserChannel[] $userChannelsToDelete */
        $userChannelsToDelete = $this->getUserChannels(new Criteria(), $con)->diff($userChannels);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->userChannelsScheduledForDeletion = clone $userChannelsToDelete;

        foreach ($userChannelsToDelete as $userChannelRemoved) {
            $userChannelRemoved->setIrcUser(null);
        }

        $this->collUserChannels = null;
        foreach ($userChannels as $userChannel) {
            $this->addUserChannel($userChannel);
        }

        $this->collUserChannels = $userChannels;
        $this->collUserChannelsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related UserChannel objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related UserChannel objects.
     * @throws PropelException
     */
    public function countUserChannels(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collUserChannelsPartial && !$this->isNew();
        if (null === $this->collUserChannels || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collUserChannels) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getUserChannels());
            }

            $query = ChildUserChannelQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByIrcUser($this)
                ->count($con);
        }

        return count($this->collUserChannels);
    }

    /**
     * Method called to associate a ChildUserChannel object to this object
     * through the ChildUserChannel foreign key attribute.
     *
     * @param  ChildUserChannel $l ChildUserChannel
     * @return $this|\WildPHP\Core\Entities\IrcUser The current object (for fluent API support)
     */
    public function addUserChannel(ChildUserChannel $l)
    {
        if ($this->collUserChannels === null) {
            $this->initUserChannels();
            $this->collUserChannelsPartial = true;
        }

        if (!$this->collUserChannels->contains($l)) {
            $this->doAddUserChannel($l);

            if ($this->userChannelsScheduledForDeletion and $this->userChannelsScheduledForDeletion->contains($l)) {
                $this->userChannelsScheduledForDeletion->remove($this->userChannelsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildUserChannel $userChannel The ChildUserChannel object to add.
     */
    protected function doAddUserChannel(ChildUserChannel $userChannel)
    {
        $this->collUserChannels[]= $userChannel;
        $userChannel->setIrcUser($this);
    }

    /**
     * @param  ChildUserChannel $userChannel The ChildUserChannel object to remove.
     * @return $this|ChildIrcUser The current object (for fluent API support)
     */
    public function removeUserChannel(ChildUserChannel $userChannel)
    {
        if ($this->getUserChannels()->contains($userChannel)) {
            $pos = $this->collUserChannels->search($userChannel);
            $this->collUserChannels->remove($pos);
            if (null === $this->userChannelsScheduledForDeletion) {
                $this->userChannelsScheduledForDeletion = clone $this->collUserChannels;
                $this->userChannelsScheduledForDeletion->clear();
            }
            $this->userChannelsScheduledForDeletion[]= clone $userChannel;
            $userChannel->setIrcUser(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this IrcUser is new, it will return
     * an empty collection; or if this IrcUser has previously
     * been saved, it will retrieve related UserChannels from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in IrcUser.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildUserChannel[] List of ChildUserChannel objects
     */
    public function getUserChannelsJoinIrcChannel(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildUserChannelQuery::create(null, $criteria);
        $query->joinWith('IrcChannel', $joinBehavior);

        return $this->getUserChannels($query, $con);
    }

    /**
     * Clears out the collUserModeChannels collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addUserModeChannels()
     */
    public function clearUserModeChannels()
    {
        $this->collUserModeChannels = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collUserModeChannels collection loaded partially.
     */
    public function resetPartialUserModeChannels($v = true)
    {
        $this->collUserModeChannelsPartial = $v;
    }

    /**
     * Initializes the collUserModeChannels collection.
     *
     * By default this just sets the collUserModeChannels collection to an empty array (like clearcollUserModeChannels());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initUserModeChannels($overrideExisting = true)
    {
        if (null !== $this->collUserModeChannels && !$overrideExisting) {
            return;
        }

        $collectionClassName = UserModeChannelTableMap::getTableMap()->getCollectionClassName();

        $this->collUserModeChannels = new $collectionClassName;
        $this->collUserModeChannels->setModel('\WildPHP\Core\Entities\UserModeChannel');
    }

    /**
     * Gets an array of ChildUserModeChannel objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildIrcUser is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildUserModeChannel[] List of ChildUserModeChannel objects
     * @throws PropelException
     */
    public function getUserModeChannels(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collUserModeChannelsPartial && !$this->isNew();
        if (null === $this->collUserModeChannels || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collUserModeChannels) {
                // return empty collection
                $this->initUserModeChannels();
            } else {
                $collUserModeChannels = ChildUserModeChannelQuery::create(null, $criteria)
                    ->filterByIrcUser($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collUserModeChannelsPartial && count($collUserModeChannels)) {
                        $this->initUserModeChannels(false);

                        foreach ($collUserModeChannels as $obj) {
                            if (false == $this->collUserModeChannels->contains($obj)) {
                                $this->collUserModeChannels->append($obj);
                            }
                        }

                        $this->collUserModeChannelsPartial = true;
                    }

                    return $collUserModeChannels;
                }

                if ($partial && $this->collUserModeChannels) {
                    foreach ($this->collUserModeChannels as $obj) {
                        if ($obj->isNew()) {
                            $collUserModeChannels[] = $obj;
                        }
                    }
                }

                $this->collUserModeChannels = $collUserModeChannels;
                $this->collUserModeChannelsPartial = false;
            }
        }

        return $this->collUserModeChannels;
    }

    /**
     * Sets a collection of ChildUserModeChannel objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $userModeChannels A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildIrcUser The current object (for fluent API support)
     */
    public function setUserModeChannels(Collection $userModeChannels, ConnectionInterface $con = null)
    {
        /** @var ChildUserModeChannel[] $userModeChannelsToDelete */
        $userModeChannelsToDelete = $this->getUserModeChannels(new Criteria(), $con)->diff($userModeChannels);


        $this->userModeChannelsScheduledForDeletion = $userModeChannelsToDelete;

        foreach ($userModeChannelsToDelete as $userModeChannelRemoved) {
            $userModeChannelRemoved->setIrcUser(null);
        }

        $this->collUserModeChannels = null;
        foreach ($userModeChannels as $userModeChannel) {
            $this->addUserModeChannel($userModeChannel);
        }

        $this->collUserModeChannels = $userModeChannels;
        $this->collUserModeChannelsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related UserModeChannel objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related UserModeChannel objects.
     * @throws PropelException
     */
    public function countUserModeChannels(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collUserModeChannelsPartial && !$this->isNew();
        if (null === $this->collUserModeChannels || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collUserModeChannels) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getUserModeChannels());
            }

            $query = ChildUserModeChannelQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByIrcUser($this)
                ->count($con);
        }

        return count($this->collUserModeChannels);
    }

    /**
     * Method called to associate a ChildUserModeChannel object to this object
     * through the ChildUserModeChannel foreign key attribute.
     *
     * @param  ChildUserModeChannel $l ChildUserModeChannel
     * @return $this|\WildPHP\Core\Entities\IrcUser The current object (for fluent API support)
     */
    public function addUserModeChannel(ChildUserModeChannel $l)
    {
        if ($this->collUserModeChannels === null) {
            $this->initUserModeChannels();
            $this->collUserModeChannelsPartial = true;
        }

        if (!$this->collUserModeChannels->contains($l)) {
            $this->doAddUserModeChannel($l);

            if ($this->userModeChannelsScheduledForDeletion and $this->userModeChannelsScheduledForDeletion->contains($l)) {
                $this->userModeChannelsScheduledForDeletion->remove($this->userModeChannelsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildUserModeChannel $userModeChannel The ChildUserModeChannel object to add.
     */
    protected function doAddUserModeChannel(ChildUserModeChannel $userModeChannel)
    {
        $this->collUserModeChannels[]= $userModeChannel;
        $userModeChannel->setIrcUser($this);
    }

    /**
     * @param  ChildUserModeChannel $userModeChannel The ChildUserModeChannel object to remove.
     * @return $this|ChildIrcUser The current object (for fluent API support)
     */
    public function removeUserModeChannel(ChildUserModeChannel $userModeChannel)
    {
        if ($this->getUserModeChannels()->contains($userModeChannel)) {
            $pos = $this->collUserModeChannels->search($userModeChannel);
            $this->collUserModeChannels->remove($pos);
            if (null === $this->userModeChannelsScheduledForDeletion) {
                $this->userModeChannelsScheduledForDeletion = clone $this->collUserModeChannels;
                $this->userModeChannelsScheduledForDeletion->clear();
            }
            $this->userModeChannelsScheduledForDeletion[]= clone $userModeChannel;
            $userModeChannel->setIrcUser(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this IrcUser is new, it will return
     * an empty collection; or if this IrcUser has previously
     * been saved, it will retrieve related UserModeChannels from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in IrcUser.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildUserModeChannel[] List of ChildUserModeChannel objects
     */
    public function getUserModeChannelsJoinIrcChannel(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildUserModeChannelQuery::create(null, $criteria);
        $query->joinWith('IrcChannel', $joinBehavior);

        return $this->getUserModeChannels($query, $con);
    }

    /**
     * Clears out the collIrcChannels collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addIrcChannels()
     */
    public function clearIrcChannels()
    {
        $this->collIrcChannels = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Initializes the collIrcChannels crossRef collection.
     *
     * By default this just sets the collIrcChannels collection to an empty collection (like clearIrcChannels());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @return void
     */
    public function initIrcChannels()
    {
        $collectionClassName = UserChannelTableMap::getTableMap()->getCollectionClassName();

        $this->collIrcChannels = new $collectionClassName;
        $this->collIrcChannelsPartial = true;
        $this->collIrcChannels->setModel('\WildPHP\Core\Entities\IrcChannel');
    }

    /**
     * Checks if the collIrcChannels collection is loaded.
     *
     * @return bool
     */
    public function isIrcChannelsLoaded()
    {
        return null !== $this->collIrcChannels;
    }

    /**
     * Gets a collection of ChildIrcChannel objects related by a many-to-many relationship
     * to the current object by way of the user_channel cross-reference table.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildIrcUser is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria Optional query object to filter the query
     * @param      ConnectionInterface $con Optional connection object
     *
     * @return ObjectCollection|ChildIrcChannel[] List of ChildIrcChannel objects
     */
    public function getIrcChannels(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collIrcChannelsPartial && !$this->isNew();
        if (null === $this->collIrcChannels || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collIrcChannels) {
                    $this->initIrcChannels();
                }
            } else {

                $query = ChildIrcChannelQuery::create(null, $criteria)
                    ->filterByIrcUser($this);
                $collIrcChannels = $query->find($con);
                if (null !== $criteria) {
                    return $collIrcChannels;
                }

                if ($partial && $this->collIrcChannels) {
                    //make sure that already added objects gets added to the list of the database.
                    foreach ($this->collIrcChannels as $obj) {
                        if (!$collIrcChannels->contains($obj)) {
                            $collIrcChannels[] = $obj;
                        }
                    }
                }

                $this->collIrcChannels = $collIrcChannels;
                $this->collIrcChannelsPartial = false;
            }
        }

        return $this->collIrcChannels;
    }

    /**
     * Sets a collection of IrcChannel objects related by a many-to-many relationship
     * to the current object by way of the user_channel cross-reference table.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param  Collection $ircChannels A Propel collection.
     * @param  ConnectionInterface $con Optional connection object
     * @return $this|ChildIrcUser The current object (for fluent API support)
     */
    public function setIrcChannels(Collection $ircChannels, ConnectionInterface $con = null)
    {
        $this->clearIrcChannels();
        $currentIrcChannels = $this->getIrcChannels();

        $ircChannelsScheduledForDeletion = $currentIrcChannels->diff($ircChannels);

        foreach ($ircChannelsScheduledForDeletion as $toDelete) {
            $this->removeIrcChannel($toDelete);
        }

        foreach ($ircChannels as $ircChannel) {
            if (!$currentIrcChannels->contains($ircChannel)) {
                $this->doAddIrcChannel($ircChannel);
            }
        }

        $this->collIrcChannelsPartial = false;
        $this->collIrcChannels = $ircChannels;

        return $this;
    }

    /**
     * Gets the number of IrcChannel objects related by a many-to-many relationship
     * to the current object by way of the user_channel cross-reference table.
     *
     * @param      Criteria $criteria Optional query object to filter the query
     * @param      boolean $distinct Set to true to force count distinct
     * @param      ConnectionInterface $con Optional connection object
     *
     * @return int the number of related IrcChannel objects
     */
    public function countIrcChannels(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collIrcChannelsPartial && !$this->isNew();
        if (null === $this->collIrcChannels || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collIrcChannels) {
                return 0;
            } else {

                if ($partial && !$criteria) {
                    return count($this->getIrcChannels());
                }

                $query = ChildIrcChannelQuery::create(null, $criteria);
                if ($distinct) {
                    $query->distinct();
                }

                return $query
                    ->filterByIrcUser($this)
                    ->count($con);
            }
        } else {
            return count($this->collIrcChannels);
        }
    }

    /**
     * Associate a ChildIrcChannel to this object
     * through the user_channel cross reference table.
     *
     * @param ChildIrcChannel $ircChannel
     * @return ChildIrcUser The current object (for fluent API support)
     */
    public function addIrcChannel(ChildIrcChannel $ircChannel)
    {
        if ($this->collIrcChannels === null) {
            $this->initIrcChannels();
        }

        if (!$this->getIrcChannels()->contains($ircChannel)) {
            // only add it if the **same** object is not already associated
            $this->collIrcChannels->push($ircChannel);
            $this->doAddIrcChannel($ircChannel);
        }

        return $this;
    }

    /**
     *
     * @param ChildIrcChannel $ircChannel
     */
    protected function doAddIrcChannel(ChildIrcChannel $ircChannel)
    {
        $userChannel = new ChildUserChannel();

        $userChannel->setIrcChannel($ircChannel);

        $userChannel->setIrcUser($this);

        $this->addUserChannel($userChannel);

        // set the back reference to this object directly as using provided method either results
        // in endless loop or in multiple relations
        if (!$ircChannel->isIrcUsersLoaded()) {
            $ircChannel->initIrcUsers();
            $ircChannel->getIrcUsers()->push($this);
        } elseif (!$ircChannel->getIrcUsers()->contains($this)) {
            $ircChannel->getIrcUsers()->push($this);
        }

    }

    /**
     * Remove ircChannel of this object
     * through the user_channel cross reference table.
     *
     * @param ChildIrcChannel $ircChannel
     * @return ChildIrcUser The current object (for fluent API support)
     */
    public function removeIrcChannel(ChildIrcChannel $ircChannel)
    {
        if ($this->getIrcChannels()->contains($ircChannel)) {
            $userChannel = new ChildUserChannel();
            $userChannel->setIrcChannel($ircChannel);
            if ($ircChannel->isIrcUsersLoaded()) {
                //remove the back reference if available
                $ircChannel->getIrcUsers()->removeObject($this);
            }

            $userChannel->setIrcUser($this);
            $this->removeUserChannel(clone $userChannel);
            $userChannel->clear();

            $this->collIrcChannels->remove($this->collIrcChannels->search($ircChannel));

            if (null === $this->ircChannelsScheduledForDeletion) {
                $this->ircChannelsScheduledForDeletion = clone $this->collIrcChannels;
                $this->ircChannelsScheduledForDeletion->clear();
            }

            $this->ircChannelsScheduledForDeletion->push($ircChannel);
        }


        return $this;
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     */
    public function clear()
    {
        $this->id = null;
        $this->nickname = null;
        $this->username = null;
        $this->realname = null;
        $this->hostname = null;
        $this->irc_account = null;
        $this->last_seen = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references and back-references to other model objects or collections of model objects.
     *
     * This method is used to reset all php object references (not the actual reference in the database).
     * Necessary for object serialisation.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collUserChannels) {
                foreach ($this->collUserChannels as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collUserModeChannels) {
                foreach ($this->collUserModeChannels as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collIrcChannels) {
                foreach ($this->collIrcChannels as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collUserChannels = null;
        $this->collUserModeChannels = null;
        $this->collIrcChannels = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(IrcUserTableMap::DEFAULT_STRING_FORMAT);
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preSave')) {
            return parent::preSave($con);
        }
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postSave')) {
            parent::postSave($con);
        }
    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preInsert')) {
            return parent::preInsert($con);
        }
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postInsert')) {
            parent::postInsert($con);
        }
    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preUpdate')) {
            return parent::preUpdate($con);
        }
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postUpdate')) {
            parent::postUpdate($con);
        }
    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preDelete')) {
            return parent::preDelete($con);
        }
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postDelete')) {
            parent::postDelete($con);
        }
    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
