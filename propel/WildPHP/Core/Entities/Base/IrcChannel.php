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
use WildPHP\Core\Entities\GroupPolicyRestriction as ChildGroupPolicyRestriction;
use WildPHP\Core\Entities\GroupPolicyRestrictionQuery as ChildGroupPolicyRestrictionQuery;
use WildPHP\Core\Entities\IrcChannel as ChildIrcChannel;
use WildPHP\Core\Entities\IrcChannelQuery as ChildIrcChannelQuery;
use WildPHP\Core\Entities\IrcUser as ChildIrcUser;
use WildPHP\Core\Entities\IrcUserQuery as ChildIrcUserQuery;
use WildPHP\Core\Entities\UserChannel as ChildUserChannel;
use WildPHP\Core\Entities\UserChannelQuery as ChildUserChannelQuery;
use WildPHP\Core\Entities\UserModeChannel as ChildUserModeChannel;
use WildPHP\Core\Entities\UserModeChannelQuery as ChildUserModeChannelQuery;
use WildPHP\Core\Entities\UserPolicyRestriction as ChildUserPolicyRestriction;
use WildPHP\Core\Entities\UserPolicyRestrictionQuery as ChildUserPolicyRestrictionQuery;
use WildPHP\Core\Entities\Map\GroupPolicyRestrictionTableMap;
use WildPHP\Core\Entities\Map\IrcChannelTableMap;
use WildPHP\Core\Entities\Map\UserChannelTableMap;
use WildPHP\Core\Entities\Map\UserModeChannelTableMap;
use WildPHP\Core\Entities\Map\UserPolicyRestrictionTableMap;

/**
 * Base class that represents a row from the 'channel' table.
 *
 *
 *
 * @package    propel.generator.WildPHP.Core.Entities.Base
 */
abstract class IrcChannel implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\WildPHP\\Core\\Entities\\Map\\IrcChannelTableMap';


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
     * The value for the name field.
     *
     * @var        string
     */
    protected $name;

    /**
     * The value for the topic field.
     *
     * @var        string
     */
    protected $topic;

    /**
     * The value for the created_time field.
     *
     * @var        DateTime
     */
    protected $created_time;

    /**
     * The value for the created_by field.
     *
     * @var        string
     */
    protected $created_by;

    /**
     * The value for the modes field.
     *
     * @var        string
     */
    protected $modes;

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
     * @var        ObjectCollection|ChildUserPolicyRestriction[] Collection to store aggregation of ChildUserPolicyRestriction objects.
     */
    protected $collUserPolicyRestrictions;
    protected $collUserPolicyRestrictionsPartial;

    /**
     * @var        ObjectCollection|ChildGroupPolicyRestriction[] Collection to store aggregation of ChildGroupPolicyRestriction objects.
     */
    protected $collGroupPolicyRestrictions;
    protected $collGroupPolicyRestrictionsPartial;

    /**
     * @var        ObjectCollection|ChildIrcUser[] Cross Collection to store aggregation of ChildIrcUser objects.
     */
    protected $collIrcUsers;

    /**
     * @var bool
     */
    protected $collIrcUsersPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildIrcUser[]
     */
    protected $ircUsersScheduledForDeletion = null;

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
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildUserPolicyRestriction[]
     */
    protected $userPolicyRestrictionsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildGroupPolicyRestriction[]
     */
    protected $groupPolicyRestrictionsScheduledForDeletion = null;

    /**
     * Initializes internal state of WildPHP\Core\Entities\Base\IrcChannel object.
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
     * Compares this with another <code>IrcChannel</code> instance.  If
     * <code>obj</code> is an instance of <code>IrcChannel</code>, delegates to
     * <code>equals(IrcChannel)</code>.  Otherwise, returns <code>false</code>.
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
     * @return $this|IrcChannel The current object, for fluid interface
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
     * Get the [name] column value.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the [topic] column value.
     *
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Get the [optionally formatted] temporal [created_time] column value.
     *
     *
     * @param      string|null $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getCreatedTime($format = NULL)
    {
        if ($format === null) {
            return $this->created_time;
        } else {
            return $this->created_time instanceof \DateTimeInterface ? $this->created_time->format($format) : null;
        }
    }

    /**
     * Get the [created_by] column value.
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * Get the [modes] column value.
     *
     * @return string
     */
    public function getModes()
    {
        return $this->modes;
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v new value
     * @return $this|\WildPHP\Core\Entities\IrcChannel The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[IrcChannelTableMap::COL_ID] = true;
        }

        return $this;
    } // setId()

    /**
     * Set the value of [name] column.
     *
     * @param string $v new value
     * @return $this|\WildPHP\Core\Entities\IrcChannel The current object (for fluent API support)
     */
    public function setName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->name !== $v) {
            $this->name = $v;
            $this->modifiedColumns[IrcChannelTableMap::COL_NAME] = true;
        }

        return $this;
    } // setName()

    /**
     * Set the value of [topic] column.
     *
     * @param string $v new value
     * @return $this|\WildPHP\Core\Entities\IrcChannel The current object (for fluent API support)
     */
    public function setTopic($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->topic !== $v) {
            $this->topic = $v;
            $this->modifiedColumns[IrcChannelTableMap::COL_TOPIC] = true;
        }

        return $this;
    } // setTopic()

    /**
     * Sets the value of [created_time] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this|\WildPHP\Core\Entities\IrcChannel The current object (for fluent API support)
     */
    public function setCreatedTime($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->created_time !== null || $dt !== null) {
            if ($this->created_time === null || $dt === null || $dt->format("Y-m-d H:i:s.u") !== $this->created_time->format("Y-m-d H:i:s.u")) {
                $this->created_time = $dt === null ? null : clone $dt;
                $this->modifiedColumns[IrcChannelTableMap::COL_CREATED_TIME] = true;
            }
        } // if either are not null

        return $this;
    } // setCreatedTime()

    /**
     * Set the value of [created_by] column.
     *
     * @param string $v new value
     * @return $this|\WildPHP\Core\Entities\IrcChannel The current object (for fluent API support)
     */
    public function setCreatedBy($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->created_by !== $v) {
            $this->created_by = $v;
            $this->modifiedColumns[IrcChannelTableMap::COL_CREATED_BY] = true;
        }

        return $this;
    } // setCreatedBy()

    /**
     * Set the value of [modes] column.
     *
     * @param string $v new value
     * @return $this|\WildPHP\Core\Entities\IrcChannel The current object (for fluent API support)
     */
    public function setModes($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->modes !== $v) {
            $this->modes = $v;
            $this->modifiedColumns[IrcChannelTableMap::COL_MODES] = true;
        }

        return $this;
    } // setModes()

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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : IrcChannelTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : IrcChannelTableMap::translateFieldName('Name', TableMap::TYPE_PHPNAME, $indexType)];
            $this->name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : IrcChannelTableMap::translateFieldName('Topic', TableMap::TYPE_PHPNAME, $indexType)];
            $this->topic = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : IrcChannelTableMap::translateFieldName('CreatedTime', TableMap::TYPE_PHPNAME, $indexType)];
            $this->created_time = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : IrcChannelTableMap::translateFieldName('CreatedBy', TableMap::TYPE_PHPNAME, $indexType)];
            $this->created_by = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : IrcChannelTableMap::translateFieldName('Modes', TableMap::TYPE_PHPNAME, $indexType)];
            $this->modes = (null !== $col) ? (string) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 6; // 6 = IrcChannelTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\WildPHP\\Core\\Entities\\IrcChannel'), 0, $e);
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
            $con = Propel::getServiceContainer()->getReadConnection(IrcChannelTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildIrcChannelQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collUserChannels = null;

            $this->collUserModeChannels = null;

            $this->collUserPolicyRestrictions = null;

            $this->collGroupPolicyRestrictions = null;

            $this->collIrcUsers = null;
        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see IrcChannel::setDeleted()
     * @see IrcChannel::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(IrcChannelTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildIrcChannelQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(IrcChannelTableMap::DATABASE_NAME);
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
                IrcChannelTableMap::addInstanceToPool($this);
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

            if ($this->ircUsersScheduledForDeletion !== null) {
                if (!$this->ircUsersScheduledForDeletion->isEmpty()) {
                    $pks = array();
                    foreach ($this->ircUsersScheduledForDeletion as $entry) {
                        $entryPk = [];

                        $entryPk[1] = $this->getId();
                        $entryPk[0] = $entry->getId();
                        $pks[] = $entryPk;
                    }

                    \WildPHP\Core\Entities\UserChannelQuery::create()
                        ->filterByPrimaryKeys($pks)
                        ->delete($con);

                    $this->ircUsersScheduledForDeletion = null;
                }

            }

            if ($this->collIrcUsers) {
                foreach ($this->collIrcUsers as $ircUser) {
                    if (!$ircUser->isDeleted() && ($ircUser->isNew() || $ircUser->isModified())) {
                        $ircUser->save($con);
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

            if ($this->userPolicyRestrictionsScheduledForDeletion !== null) {
                if (!$this->userPolicyRestrictionsScheduledForDeletion->isEmpty()) {
                    \WildPHP\Core\Entities\UserPolicyRestrictionQuery::create()
                        ->filterByPrimaryKeys($this->userPolicyRestrictionsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->userPolicyRestrictionsScheduledForDeletion = null;
                }
            }

            if ($this->collUserPolicyRestrictions !== null) {
                foreach ($this->collUserPolicyRestrictions as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->groupPolicyRestrictionsScheduledForDeletion !== null) {
                if (!$this->groupPolicyRestrictionsScheduledForDeletion->isEmpty()) {
                    \WildPHP\Core\Entities\GroupPolicyRestrictionQuery::create()
                        ->filterByPrimaryKeys($this->groupPolicyRestrictionsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->groupPolicyRestrictionsScheduledForDeletion = null;
                }
            }

            if ($this->collGroupPolicyRestrictions !== null) {
                foreach ($this->collGroupPolicyRestrictions as $referrerFK) {
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

        $this->modifiedColumns[IrcChannelTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . IrcChannelTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(IrcChannelTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(IrcChannelTableMap::COL_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'name';
        }
        if ($this->isColumnModified(IrcChannelTableMap::COL_TOPIC)) {
            $modifiedColumns[':p' . $index++]  = 'topic';
        }
        if ($this->isColumnModified(IrcChannelTableMap::COL_CREATED_TIME)) {
            $modifiedColumns[':p' . $index++]  = 'created_time';
        }
        if ($this->isColumnModified(IrcChannelTableMap::COL_CREATED_BY)) {
            $modifiedColumns[':p' . $index++]  = 'created_by';
        }
        if ($this->isColumnModified(IrcChannelTableMap::COL_MODES)) {
            $modifiedColumns[':p' . $index++]  = 'modes';
        }

        $sql = sprintf(
            'INSERT INTO channel (%s) VALUES (%s)',
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
                    case 'name':
                        $stmt->bindValue($identifier, $this->name, PDO::PARAM_STR);
                        break;
                    case 'topic':
                        $stmt->bindValue($identifier, $this->topic, PDO::PARAM_STR);
                        break;
                    case 'created_time':
                        $stmt->bindValue($identifier, $this->created_time ? $this->created_time->format("Y-m-d H:i:s.u") : null, PDO::PARAM_STR);
                        break;
                    case 'created_by':
                        $stmt->bindValue($identifier, $this->created_by, PDO::PARAM_STR);
                        break;
                    case 'modes':
                        $stmt->bindValue($identifier, $this->modes, PDO::PARAM_STR);
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
        $pos = IrcChannelTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getName();
                break;
            case 2:
                return $this->getTopic();
                break;
            case 3:
                return $this->getCreatedTime();
                break;
            case 4:
                return $this->getCreatedBy();
                break;
            case 5:
                return $this->getModes();
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

        if (isset($alreadyDumpedObjects['IrcChannel'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['IrcChannel'][$this->hashCode()] = true;
        $keys = IrcChannelTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getName(),
            $keys[2] => $this->getTopic(),
            $keys[3] => $this->getCreatedTime(),
            $keys[4] => $this->getCreatedBy(),
            $keys[5] => $this->getModes(),
        );
        if ($result[$keys[3]] instanceof \DateTimeInterface) {
            $result[$keys[3]] = $result[$keys[3]]->format('c');
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
            if (null !== $this->collUserPolicyRestrictions) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'userPolicyRestrictions';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'user_policy_channel_restrictions';
                        break;
                    default:
                        $key = 'UserPolicyRestrictions';
                }

                $result[$key] = $this->collUserPolicyRestrictions->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collGroupPolicyRestrictions) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'groupPolicyRestrictions';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'group_policy_channel_restrictions';
                        break;
                    default:
                        $key = 'GroupPolicyRestrictions';
                }

                $result[$key] = $this->collGroupPolicyRestrictions->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
     * @return $this|\WildPHP\Core\Entities\IrcChannel
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = IrcChannelTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\WildPHP\Core\Entities\IrcChannel
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setName($value);
                break;
            case 2:
                $this->setTopic($value);
                break;
            case 3:
                $this->setCreatedTime($value);
                break;
            case 4:
                $this->setCreatedBy($value);
                break;
            case 5:
                $this->setModes($value);
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
        $keys = IrcChannelTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setName($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setTopic($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setCreatedTime($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setCreatedBy($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setModes($arr[$keys[5]]);
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
     * @return $this|\WildPHP\Core\Entities\IrcChannel The current object, for fluid interface
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
        $criteria = new Criteria(IrcChannelTableMap::DATABASE_NAME);

        if ($this->isColumnModified(IrcChannelTableMap::COL_ID)) {
            $criteria->add(IrcChannelTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(IrcChannelTableMap::COL_NAME)) {
            $criteria->add(IrcChannelTableMap::COL_NAME, $this->name);
        }
        if ($this->isColumnModified(IrcChannelTableMap::COL_TOPIC)) {
            $criteria->add(IrcChannelTableMap::COL_TOPIC, $this->topic);
        }
        if ($this->isColumnModified(IrcChannelTableMap::COL_CREATED_TIME)) {
            $criteria->add(IrcChannelTableMap::COL_CREATED_TIME, $this->created_time);
        }
        if ($this->isColumnModified(IrcChannelTableMap::COL_CREATED_BY)) {
            $criteria->add(IrcChannelTableMap::COL_CREATED_BY, $this->created_by);
        }
        if ($this->isColumnModified(IrcChannelTableMap::COL_MODES)) {
            $criteria->add(IrcChannelTableMap::COL_MODES, $this->modes);
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
        $criteria = ChildIrcChannelQuery::create();
        $criteria->add(IrcChannelTableMap::COL_ID, $this->id);

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
     * @param      object $copyObj An object of \WildPHP\Core\Entities\IrcChannel (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setName($this->getName());
        $copyObj->setTopic($this->getTopic());
        $copyObj->setCreatedTime($this->getCreatedTime());
        $copyObj->setCreatedBy($this->getCreatedBy());
        $copyObj->setModes($this->getModes());

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

            foreach ($this->getUserPolicyRestrictions() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addUserPolicyRestriction($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getGroupPolicyRestrictions() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addGroupPolicyRestriction($relObj->copy($deepCopy));
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
     * @return \WildPHP\Core\Entities\IrcChannel Clone of current object.
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
        if ('UserPolicyRestriction' == $relationName) {
            $this->initUserPolicyRestrictions();
            return;
        }
        if ('GroupPolicyRestriction' == $relationName) {
            $this->initGroupPolicyRestrictions();
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
     * If this ChildIrcChannel is new, it will return
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
                    ->filterByIrcChannel($this)
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
     * @return $this|ChildIrcChannel The current object (for fluent API support)
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
            $userChannelRemoved->setIrcChannel(null);
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
                ->filterByIrcChannel($this)
                ->count($con);
        }

        return count($this->collUserChannels);
    }

    /**
     * Method called to associate a ChildUserChannel object to this object
     * through the ChildUserChannel foreign key attribute.
     *
     * @param  ChildUserChannel $l ChildUserChannel
     * @return $this|\WildPHP\Core\Entities\IrcChannel The current object (for fluent API support)
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
        $userChannel->setIrcChannel($this);
    }

    /**
     * @param  ChildUserChannel $userChannel The ChildUserChannel object to remove.
     * @return $this|ChildIrcChannel The current object (for fluent API support)
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
            $userChannel->setIrcChannel(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this IrcChannel is new, it will return
     * an empty collection; or if this IrcChannel has previously
     * been saved, it will retrieve related UserChannels from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in IrcChannel.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildUserChannel[] List of ChildUserChannel objects
     */
    public function getUserChannelsJoinIrcUser(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildUserChannelQuery::create(null, $criteria);
        $query->joinWith('IrcUser', $joinBehavior);

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
     * If this ChildIrcChannel is new, it will return
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
                    ->filterByIrcChannel($this)
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
     * @return $this|ChildIrcChannel The current object (for fluent API support)
     */
    public function setUserModeChannels(Collection $userModeChannels, ConnectionInterface $con = null)
    {
        /** @var ChildUserModeChannel[] $userModeChannelsToDelete */
        $userModeChannelsToDelete = $this->getUserModeChannels(new Criteria(), $con)->diff($userModeChannels);


        $this->userModeChannelsScheduledForDeletion = $userModeChannelsToDelete;

        foreach ($userModeChannelsToDelete as $userModeChannelRemoved) {
            $userModeChannelRemoved->setIrcChannel(null);
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
                ->filterByIrcChannel($this)
                ->count($con);
        }

        return count($this->collUserModeChannels);
    }

    /**
     * Method called to associate a ChildUserModeChannel object to this object
     * through the ChildUserModeChannel foreign key attribute.
     *
     * @param  ChildUserModeChannel $l ChildUserModeChannel
     * @return $this|\WildPHP\Core\Entities\IrcChannel The current object (for fluent API support)
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
        $userModeChannel->setIrcChannel($this);
    }

    /**
     * @param  ChildUserModeChannel $userModeChannel The ChildUserModeChannel object to remove.
     * @return $this|ChildIrcChannel The current object (for fluent API support)
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
            $userModeChannel->setIrcChannel(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this IrcChannel is new, it will return
     * an empty collection; or if this IrcChannel has previously
     * been saved, it will retrieve related UserModeChannels from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in IrcChannel.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildUserModeChannel[] List of ChildUserModeChannel objects
     */
    public function getUserModeChannelsJoinIrcUser(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildUserModeChannelQuery::create(null, $criteria);
        $query->joinWith('IrcUser', $joinBehavior);

        return $this->getUserModeChannels($query, $con);
    }

    /**
     * Clears out the collUserPolicyRestrictions collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addUserPolicyRestrictions()
     */
    public function clearUserPolicyRestrictions()
    {
        $this->collUserPolicyRestrictions = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collUserPolicyRestrictions collection loaded partially.
     */
    public function resetPartialUserPolicyRestrictions($v = true)
    {
        $this->collUserPolicyRestrictionsPartial = $v;
    }

    /**
     * Initializes the collUserPolicyRestrictions collection.
     *
     * By default this just sets the collUserPolicyRestrictions collection to an empty array (like clearcollUserPolicyRestrictions());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initUserPolicyRestrictions($overrideExisting = true)
    {
        if (null !== $this->collUserPolicyRestrictions && !$overrideExisting) {
            return;
        }

        $collectionClassName = UserPolicyRestrictionTableMap::getTableMap()->getCollectionClassName();

        $this->collUserPolicyRestrictions = new $collectionClassName;
        $this->collUserPolicyRestrictions->setModel('\WildPHP\Core\Entities\UserPolicyRestriction');
    }

    /**
     * Gets an array of ChildUserPolicyRestriction objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildIrcChannel is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildUserPolicyRestriction[] List of ChildUserPolicyRestriction objects
     * @throws PropelException
     */
    public function getUserPolicyRestrictions(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collUserPolicyRestrictionsPartial && !$this->isNew();
        if (null === $this->collUserPolicyRestrictions || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collUserPolicyRestrictions) {
                // return empty collection
                $this->initUserPolicyRestrictions();
            } else {
                $collUserPolicyRestrictions = ChildUserPolicyRestrictionQuery::create(null, $criteria)
                    ->filterByIrcChannel($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collUserPolicyRestrictionsPartial && count($collUserPolicyRestrictions)) {
                        $this->initUserPolicyRestrictions(false);

                        foreach ($collUserPolicyRestrictions as $obj) {
                            if (false == $this->collUserPolicyRestrictions->contains($obj)) {
                                $this->collUserPolicyRestrictions->append($obj);
                            }
                        }

                        $this->collUserPolicyRestrictionsPartial = true;
                    }

                    return $collUserPolicyRestrictions;
                }

                if ($partial && $this->collUserPolicyRestrictions) {
                    foreach ($this->collUserPolicyRestrictions as $obj) {
                        if ($obj->isNew()) {
                            $collUserPolicyRestrictions[] = $obj;
                        }
                    }
                }

                $this->collUserPolicyRestrictions = $collUserPolicyRestrictions;
                $this->collUserPolicyRestrictionsPartial = false;
            }
        }

        return $this->collUserPolicyRestrictions;
    }

    /**
     * Sets a collection of ChildUserPolicyRestriction objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $userPolicyRestrictions A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildIrcChannel The current object (for fluent API support)
     */
    public function setUserPolicyRestrictions(Collection $userPolicyRestrictions, ConnectionInterface $con = null)
    {
        /** @var ChildUserPolicyRestriction[] $userPolicyRestrictionsToDelete */
        $userPolicyRestrictionsToDelete = $this->getUserPolicyRestrictions(new Criteria(), $con)->diff($userPolicyRestrictions);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->userPolicyRestrictionsScheduledForDeletion = clone $userPolicyRestrictionsToDelete;

        foreach ($userPolicyRestrictionsToDelete as $userPolicyRestrictionRemoved) {
            $userPolicyRestrictionRemoved->setIrcChannel(null);
        }

        $this->collUserPolicyRestrictions = null;
        foreach ($userPolicyRestrictions as $userPolicyRestriction) {
            $this->addUserPolicyRestriction($userPolicyRestriction);
        }

        $this->collUserPolicyRestrictions = $userPolicyRestrictions;
        $this->collUserPolicyRestrictionsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related UserPolicyRestriction objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related UserPolicyRestriction objects.
     * @throws PropelException
     */
    public function countUserPolicyRestrictions(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collUserPolicyRestrictionsPartial && !$this->isNew();
        if (null === $this->collUserPolicyRestrictions || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collUserPolicyRestrictions) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getUserPolicyRestrictions());
            }

            $query = ChildUserPolicyRestrictionQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByIrcChannel($this)
                ->count($con);
        }

        return count($this->collUserPolicyRestrictions);
    }

    /**
     * Method called to associate a ChildUserPolicyRestriction object to this object
     * through the ChildUserPolicyRestriction foreign key attribute.
     *
     * @param  ChildUserPolicyRestriction $l ChildUserPolicyRestriction
     * @return $this|\WildPHP\Core\Entities\IrcChannel The current object (for fluent API support)
     */
    public function addUserPolicyRestriction(ChildUserPolicyRestriction $l)
    {
        if ($this->collUserPolicyRestrictions === null) {
            $this->initUserPolicyRestrictions();
            $this->collUserPolicyRestrictionsPartial = true;
        }

        if (!$this->collUserPolicyRestrictions->contains($l)) {
            $this->doAddUserPolicyRestriction($l);

            if ($this->userPolicyRestrictionsScheduledForDeletion and $this->userPolicyRestrictionsScheduledForDeletion->contains($l)) {
                $this->userPolicyRestrictionsScheduledForDeletion->remove($this->userPolicyRestrictionsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildUserPolicyRestriction $userPolicyRestriction The ChildUserPolicyRestriction object to add.
     */
    protected function doAddUserPolicyRestriction(ChildUserPolicyRestriction $userPolicyRestriction)
    {
        $this->collUserPolicyRestrictions[]= $userPolicyRestriction;
        $userPolicyRestriction->setIrcChannel($this);
    }

    /**
     * @param  ChildUserPolicyRestriction $userPolicyRestriction The ChildUserPolicyRestriction object to remove.
     * @return $this|ChildIrcChannel The current object (for fluent API support)
     */
    public function removeUserPolicyRestriction(ChildUserPolicyRestriction $userPolicyRestriction)
    {
        if ($this->getUserPolicyRestrictions()->contains($userPolicyRestriction)) {
            $pos = $this->collUserPolicyRestrictions->search($userPolicyRestriction);
            $this->collUserPolicyRestrictions->remove($pos);
            if (null === $this->userPolicyRestrictionsScheduledForDeletion) {
                $this->userPolicyRestrictionsScheduledForDeletion = clone $this->collUserPolicyRestrictions;
                $this->userPolicyRestrictionsScheduledForDeletion->clear();
            }
            $this->userPolicyRestrictionsScheduledForDeletion[]= clone $userPolicyRestriction;
            $userPolicyRestriction->setIrcChannel(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this IrcChannel is new, it will return
     * an empty collection; or if this IrcChannel has previously
     * been saved, it will retrieve related UserPolicyRestrictions from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in IrcChannel.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildUserPolicyRestriction[] List of ChildUserPolicyRestriction objects
     */
    public function getUserPolicyRestrictionsJoinUserPolicy(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildUserPolicyRestrictionQuery::create(null, $criteria);
        $query->joinWith('UserPolicy', $joinBehavior);

        return $this->getUserPolicyRestrictions($query, $con);
    }

    /**
     * Clears out the collGroupPolicyRestrictions collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addGroupPolicyRestrictions()
     */
    public function clearGroupPolicyRestrictions()
    {
        $this->collGroupPolicyRestrictions = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collGroupPolicyRestrictions collection loaded partially.
     */
    public function resetPartialGroupPolicyRestrictions($v = true)
    {
        $this->collGroupPolicyRestrictionsPartial = $v;
    }

    /**
     * Initializes the collGroupPolicyRestrictions collection.
     *
     * By default this just sets the collGroupPolicyRestrictions collection to an empty array (like clearcollGroupPolicyRestrictions());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initGroupPolicyRestrictions($overrideExisting = true)
    {
        if (null !== $this->collGroupPolicyRestrictions && !$overrideExisting) {
            return;
        }

        $collectionClassName = GroupPolicyRestrictionTableMap::getTableMap()->getCollectionClassName();

        $this->collGroupPolicyRestrictions = new $collectionClassName;
        $this->collGroupPolicyRestrictions->setModel('\WildPHP\Core\Entities\GroupPolicyRestriction');
    }

    /**
     * Gets an array of ChildGroupPolicyRestriction objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildIrcChannel is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildGroupPolicyRestriction[] List of ChildGroupPolicyRestriction objects
     * @throws PropelException
     */
    public function getGroupPolicyRestrictions(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collGroupPolicyRestrictionsPartial && !$this->isNew();
        if (null === $this->collGroupPolicyRestrictions || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collGroupPolicyRestrictions) {
                // return empty collection
                $this->initGroupPolicyRestrictions();
            } else {
                $collGroupPolicyRestrictions = ChildGroupPolicyRestrictionQuery::create(null, $criteria)
                    ->filterByIrcChannel($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collGroupPolicyRestrictionsPartial && count($collGroupPolicyRestrictions)) {
                        $this->initGroupPolicyRestrictions(false);

                        foreach ($collGroupPolicyRestrictions as $obj) {
                            if (false == $this->collGroupPolicyRestrictions->contains($obj)) {
                                $this->collGroupPolicyRestrictions->append($obj);
                            }
                        }

                        $this->collGroupPolicyRestrictionsPartial = true;
                    }

                    return $collGroupPolicyRestrictions;
                }

                if ($partial && $this->collGroupPolicyRestrictions) {
                    foreach ($this->collGroupPolicyRestrictions as $obj) {
                        if ($obj->isNew()) {
                            $collGroupPolicyRestrictions[] = $obj;
                        }
                    }
                }

                $this->collGroupPolicyRestrictions = $collGroupPolicyRestrictions;
                $this->collGroupPolicyRestrictionsPartial = false;
            }
        }

        return $this->collGroupPolicyRestrictions;
    }

    /**
     * Sets a collection of ChildGroupPolicyRestriction objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $groupPolicyRestrictions A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildIrcChannel The current object (for fluent API support)
     */
    public function setGroupPolicyRestrictions(Collection $groupPolicyRestrictions, ConnectionInterface $con = null)
    {
        /** @var ChildGroupPolicyRestriction[] $groupPolicyRestrictionsToDelete */
        $groupPolicyRestrictionsToDelete = $this->getGroupPolicyRestrictions(new Criteria(), $con)->diff($groupPolicyRestrictions);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->groupPolicyRestrictionsScheduledForDeletion = clone $groupPolicyRestrictionsToDelete;

        foreach ($groupPolicyRestrictionsToDelete as $groupPolicyRestrictionRemoved) {
            $groupPolicyRestrictionRemoved->setIrcChannel(null);
        }

        $this->collGroupPolicyRestrictions = null;
        foreach ($groupPolicyRestrictions as $groupPolicyRestriction) {
            $this->addGroupPolicyRestriction($groupPolicyRestriction);
        }

        $this->collGroupPolicyRestrictions = $groupPolicyRestrictions;
        $this->collGroupPolicyRestrictionsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related GroupPolicyRestriction objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related GroupPolicyRestriction objects.
     * @throws PropelException
     */
    public function countGroupPolicyRestrictions(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collGroupPolicyRestrictionsPartial && !$this->isNew();
        if (null === $this->collGroupPolicyRestrictions || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collGroupPolicyRestrictions) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getGroupPolicyRestrictions());
            }

            $query = ChildGroupPolicyRestrictionQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByIrcChannel($this)
                ->count($con);
        }

        return count($this->collGroupPolicyRestrictions);
    }

    /**
     * Method called to associate a ChildGroupPolicyRestriction object to this object
     * through the ChildGroupPolicyRestriction foreign key attribute.
     *
     * @param  ChildGroupPolicyRestriction $l ChildGroupPolicyRestriction
     * @return $this|\WildPHP\Core\Entities\IrcChannel The current object (for fluent API support)
     */
    public function addGroupPolicyRestriction(ChildGroupPolicyRestriction $l)
    {
        if ($this->collGroupPolicyRestrictions === null) {
            $this->initGroupPolicyRestrictions();
            $this->collGroupPolicyRestrictionsPartial = true;
        }

        if (!$this->collGroupPolicyRestrictions->contains($l)) {
            $this->doAddGroupPolicyRestriction($l);

            if ($this->groupPolicyRestrictionsScheduledForDeletion and $this->groupPolicyRestrictionsScheduledForDeletion->contains($l)) {
                $this->groupPolicyRestrictionsScheduledForDeletion->remove($this->groupPolicyRestrictionsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildGroupPolicyRestriction $groupPolicyRestriction The ChildGroupPolicyRestriction object to add.
     */
    protected function doAddGroupPolicyRestriction(ChildGroupPolicyRestriction $groupPolicyRestriction)
    {
        $this->collGroupPolicyRestrictions[]= $groupPolicyRestriction;
        $groupPolicyRestriction->setIrcChannel($this);
    }

    /**
     * @param  ChildGroupPolicyRestriction $groupPolicyRestriction The ChildGroupPolicyRestriction object to remove.
     * @return $this|ChildIrcChannel The current object (for fluent API support)
     */
    public function removeGroupPolicyRestriction(ChildGroupPolicyRestriction $groupPolicyRestriction)
    {
        if ($this->getGroupPolicyRestrictions()->contains($groupPolicyRestriction)) {
            $pos = $this->collGroupPolicyRestrictions->search($groupPolicyRestriction);
            $this->collGroupPolicyRestrictions->remove($pos);
            if (null === $this->groupPolicyRestrictionsScheduledForDeletion) {
                $this->groupPolicyRestrictionsScheduledForDeletion = clone $this->collGroupPolicyRestrictions;
                $this->groupPolicyRestrictionsScheduledForDeletion->clear();
            }
            $this->groupPolicyRestrictionsScheduledForDeletion[]= clone $groupPolicyRestriction;
            $groupPolicyRestriction->setIrcChannel(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this IrcChannel is new, it will return
     * an empty collection; or if this IrcChannel has previously
     * been saved, it will retrieve related GroupPolicyRestrictions from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in IrcChannel.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildGroupPolicyRestriction[] List of ChildGroupPolicyRestriction objects
     */
    public function getGroupPolicyRestrictionsJoinGroupPolicy(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildGroupPolicyRestrictionQuery::create(null, $criteria);
        $query->joinWith('GroupPolicy', $joinBehavior);

        return $this->getGroupPolicyRestrictions($query, $con);
    }

    /**
     * Clears out the collIrcUsers collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addIrcUsers()
     */
    public function clearIrcUsers()
    {
        $this->collIrcUsers = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Initializes the collIrcUsers crossRef collection.
     *
     * By default this just sets the collIrcUsers collection to an empty collection (like clearIrcUsers());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @return void
     */
    public function initIrcUsers()
    {
        $collectionClassName = UserChannelTableMap::getTableMap()->getCollectionClassName();

        $this->collIrcUsers = new $collectionClassName;
        $this->collIrcUsersPartial = true;
        $this->collIrcUsers->setModel('\WildPHP\Core\Entities\IrcUser');
    }

    /**
     * Checks if the collIrcUsers collection is loaded.
     *
     * @return bool
     */
    public function isIrcUsersLoaded()
    {
        return null !== $this->collIrcUsers;
    }

    /**
     * Gets a collection of ChildIrcUser objects related by a many-to-many relationship
     * to the current object by way of the user_channel cross-reference table.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildIrcChannel is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria Optional query object to filter the query
     * @param      ConnectionInterface $con Optional connection object
     *
     * @return ObjectCollection|ChildIrcUser[] List of ChildIrcUser objects
     */
    public function getIrcUsers(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collIrcUsersPartial && !$this->isNew();
        if (null === $this->collIrcUsers || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collIrcUsers) {
                    $this->initIrcUsers();
                }
            } else {

                $query = ChildIrcUserQuery::create(null, $criteria)
                    ->filterByIrcChannel($this);
                $collIrcUsers = $query->find($con);
                if (null !== $criteria) {
                    return $collIrcUsers;
                }

                if ($partial && $this->collIrcUsers) {
                    //make sure that already added objects gets added to the list of the database.
                    foreach ($this->collIrcUsers as $obj) {
                        if (!$collIrcUsers->contains($obj)) {
                            $collIrcUsers[] = $obj;
                        }
                    }
                }

                $this->collIrcUsers = $collIrcUsers;
                $this->collIrcUsersPartial = false;
            }
        }

        return $this->collIrcUsers;
    }

    /**
     * Sets a collection of IrcUser objects related by a many-to-many relationship
     * to the current object by way of the user_channel cross-reference table.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param  Collection $ircUsers A Propel collection.
     * @param  ConnectionInterface $con Optional connection object
     * @return $this|ChildIrcChannel The current object (for fluent API support)
     */
    public function setIrcUsers(Collection $ircUsers, ConnectionInterface $con = null)
    {
        $this->clearIrcUsers();
        $currentIrcUsers = $this->getIrcUsers();

        $ircUsersScheduledForDeletion = $currentIrcUsers->diff($ircUsers);

        foreach ($ircUsersScheduledForDeletion as $toDelete) {
            $this->removeIrcUser($toDelete);
        }

        foreach ($ircUsers as $ircUser) {
            if (!$currentIrcUsers->contains($ircUser)) {
                $this->doAddIrcUser($ircUser);
            }
        }

        $this->collIrcUsersPartial = false;
        $this->collIrcUsers = $ircUsers;

        return $this;
    }

    /**
     * Gets the number of IrcUser objects related by a many-to-many relationship
     * to the current object by way of the user_channel cross-reference table.
     *
     * @param      Criteria $criteria Optional query object to filter the query
     * @param      boolean $distinct Set to true to force count distinct
     * @param      ConnectionInterface $con Optional connection object
     *
     * @return int the number of related IrcUser objects
     */
    public function countIrcUsers(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collIrcUsersPartial && !$this->isNew();
        if (null === $this->collIrcUsers || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collIrcUsers) {
                return 0;
            } else {

                if ($partial && !$criteria) {
                    return count($this->getIrcUsers());
                }

                $query = ChildIrcUserQuery::create(null, $criteria);
                if ($distinct) {
                    $query->distinct();
                }

                return $query
                    ->filterByIrcChannel($this)
                    ->count($con);
            }
        } else {
            return count($this->collIrcUsers);
        }
    }

    /**
     * Associate a ChildIrcUser to this object
     * through the user_channel cross reference table.
     *
     * @param ChildIrcUser $ircUser
     * @return ChildIrcChannel The current object (for fluent API support)
     */
    public function addIrcUser(ChildIrcUser $ircUser)
    {
        if ($this->collIrcUsers === null) {
            $this->initIrcUsers();
        }

        if (!$this->getIrcUsers()->contains($ircUser)) {
            // only add it if the **same** object is not already associated
            $this->collIrcUsers->push($ircUser);
            $this->doAddIrcUser($ircUser);
        }

        return $this;
    }

    /**
     *
     * @param ChildIrcUser $ircUser
     */
    protected function doAddIrcUser(ChildIrcUser $ircUser)
    {
        $userChannel = new ChildUserChannel();

        $userChannel->setIrcUser($ircUser);

        $userChannel->setIrcChannel($this);

        $this->addUserChannel($userChannel);

        // set the back reference to this object directly as using provided method either results
        // in endless loop or in multiple relations
        if (!$ircUser->isIrcChannelsLoaded()) {
            $ircUser->initIrcChannels();
            $ircUser->getIrcChannels()->push($this);
        } elseif (!$ircUser->getIrcChannels()->contains($this)) {
            $ircUser->getIrcChannels()->push($this);
        }

    }

    /**
     * Remove ircUser of this object
     * through the user_channel cross reference table.
     *
     * @param ChildIrcUser $ircUser
     * @return ChildIrcChannel The current object (for fluent API support)
     */
    public function removeIrcUser(ChildIrcUser $ircUser)
    {
        if ($this->getIrcUsers()->contains($ircUser)) {
            $userChannel = new ChildUserChannel();
            $userChannel->setIrcUser($ircUser);
            if ($ircUser->isIrcChannelsLoaded()) {
                //remove the back reference if available
                $ircUser->getIrcChannels()->removeObject($this);
            }

            $userChannel->setIrcChannel($this);
            $this->removeUserChannel(clone $userChannel);
            $userChannel->clear();

            $this->collIrcUsers->remove($this->collIrcUsers->search($ircUser));

            if (null === $this->ircUsersScheduledForDeletion) {
                $this->ircUsersScheduledForDeletion = clone $this->collIrcUsers;
                $this->ircUsersScheduledForDeletion->clear();
            }

            $this->ircUsersScheduledForDeletion->push($ircUser);
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
        $this->name = null;
        $this->topic = null;
        $this->created_time = null;
        $this->created_by = null;
        $this->modes = null;
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
            if ($this->collUserPolicyRestrictions) {
                foreach ($this->collUserPolicyRestrictions as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collGroupPolicyRestrictions) {
                foreach ($this->collGroupPolicyRestrictions as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collIrcUsers) {
                foreach ($this->collIrcUsers as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collUserChannels = null;
        $this->collUserModeChannels = null;
        $this->collUserPolicyRestrictions = null;
        $this->collGroupPolicyRestrictions = null;
        $this->collIrcUsers = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(IrcChannelTableMap::DEFAULT_STRING_FORMAT);
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
