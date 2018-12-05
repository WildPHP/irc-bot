<?php

namespace WildPHP\Core\Entities\Base;

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
use WildPHP\Core\Entities\Group as ChildGroup;
use WildPHP\Core\Entities\GroupPolicy as ChildGroupPolicy;
use WildPHP\Core\Entities\GroupPolicyQuery as ChildGroupPolicyQuery;
use WildPHP\Core\Entities\GroupQuery as ChildGroupQuery;
use WildPHP\Core\Entities\ModeGroup as ChildModeGroup;
use WildPHP\Core\Entities\ModeGroupPolicy as ChildModeGroupPolicy;
use WildPHP\Core\Entities\ModeGroupPolicyQuery as ChildModeGroupPolicyQuery;
use WildPHP\Core\Entities\ModeGroupQuery as ChildModeGroupQuery;
use WildPHP\Core\Entities\Policy as ChildPolicy;
use WildPHP\Core\Entities\PolicyQuery as ChildPolicyQuery;
use WildPHP\Core\Entities\UserPolicy as ChildUserPolicy;
use WildPHP\Core\Entities\UserPolicyQuery as ChildUserPolicyQuery;
use WildPHP\Core\Entities\Map\GroupPolicyTableMap;
use WildPHP\Core\Entities\Map\ModeGroupPolicyTableMap;
use WildPHP\Core\Entities\Map\PolicyTableMap;
use WildPHP\Core\Entities\Map\UserPolicyTableMap;

/**
 * Base class that represents a row from the 'policy' table.
 *
 *
 *
 * @package    propel.generator.WildPHP.Core.Entities.Base
 */
abstract class Policy implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\WildPHP\\Core\\Entities\\Map\\PolicyTableMap';


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
     * The value for the name field.
     *
     * @var        string
     */
    protected $name;

    /**
     * The value for the description field.
     *
     * @var        string
     */
    protected $description;

    /**
     * @var        ObjectCollection|ChildUserPolicy[] Collection to store aggregation of ChildUserPolicy objects.
     */
    protected $collUserPolicies;
    protected $collUserPoliciesPartial;

    /**
     * @var        ObjectCollection|ChildGroupPolicy[] Collection to store aggregation of ChildGroupPolicy objects.
     */
    protected $collGroupPolicies;
    protected $collGroupPoliciesPartial;

    /**
     * @var        ObjectCollection|ChildModeGroupPolicy[] Collection to store aggregation of ChildModeGroupPolicy objects.
     */
    protected $collModeGroupPolicies;
    protected $collModeGroupPoliciesPartial;

    /**
     * @var        ObjectCollection|ChildGroup[] Cross Collection to store aggregation of ChildGroup objects.
     */
    protected $collGroups;

    /**
     * @var bool
     */
    protected $collGroupsPartial;

    /**
     * @var        ObjectCollection|ChildModeGroup[] Cross Collection to store aggregation of ChildModeGroup objects.
     */
    protected $collModeGroups;

    /**
     * @var bool
     */
    protected $collModeGroupsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildGroup[]
     */
    protected $groupsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildModeGroup[]
     */
    protected $modeGroupsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildUserPolicy[]
     */
    protected $userPoliciesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildGroupPolicy[]
     */
    protected $groupPoliciesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildModeGroupPolicy[]
     */
    protected $modeGroupPoliciesScheduledForDeletion = null;

    /**
     * Initializes internal state of WildPHP\Core\Entities\Base\Policy object.
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
     * Compares this with another <code>Policy</code> instance.  If
     * <code>obj</code> is an instance of <code>Policy</code>, delegates to
     * <code>equals(Policy)</code>.  Otherwise, returns <code>false</code>.
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
     * @return $this|Policy The current object, for fluid interface
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
     * Get the [name] column value.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the [description] column value.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of [name] column.
     *
     * @param string $v new value
     * @return $this|\WildPHP\Core\Entities\Policy The current object (for fluent API support)
     */
    public function setName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->name !== $v) {
            $this->name = $v;
            $this->modifiedColumns[PolicyTableMap::COL_NAME] = true;
        }

        return $this;
    } // setName()

    /**
     * Set the value of [description] column.
     *
     * @param string $v new value
     * @return $this|\WildPHP\Core\Entities\Policy The current object (for fluent API support)
     */
    public function setDescription($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->description !== $v) {
            $this->description = $v;
            $this->modifiedColumns[PolicyTableMap::COL_DESCRIPTION] = true;
        }

        return $this;
    } // setDescription()

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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : PolicyTableMap::translateFieldName('Name', TableMap::TYPE_PHPNAME, $indexType)];
            $this->name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : PolicyTableMap::translateFieldName('Description', TableMap::TYPE_PHPNAME, $indexType)];
            $this->description = (null !== $col) ? (string) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 2; // 2 = PolicyTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\WildPHP\\Core\\Entities\\Policy'), 0, $e);
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
            $con = Propel::getServiceContainer()->getReadConnection(PolicyTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildPolicyQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collUserPolicies = null;

            $this->collGroupPolicies = null;

            $this->collModeGroupPolicies = null;

            $this->collGroups = null;
            $this->collModeGroups = null;
        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see Policy::setDeleted()
     * @see Policy::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(PolicyTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildPolicyQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(PolicyTableMap::DATABASE_NAME);
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
                PolicyTableMap::addInstanceToPool($this);
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

            if ($this->groupsScheduledForDeletion !== null) {
                if (!$this->groupsScheduledForDeletion->isEmpty()) {
                    $pks = array();
                    foreach ($this->groupsScheduledForDeletion as $entry) {
                        $entryPk = [];

                        $entryPk[1] = $this->getName();
                        $entryPk[0] = $entry->getId();
                        $pks[] = $entryPk;
                    }

                    \WildPHP\Core\Entities\GroupPolicyQuery::create()
                        ->filterByPrimaryKeys($pks)
                        ->delete($con);

                    $this->groupsScheduledForDeletion = null;
                }

            }

            if ($this->collGroups) {
                foreach ($this->collGroups as $group) {
                    if (!$group->isDeleted() && ($group->isNew() || $group->isModified())) {
                        $group->save($con);
                    }
                }
            }


            if ($this->modeGroupsScheduledForDeletion !== null) {
                if (!$this->modeGroupsScheduledForDeletion->isEmpty()) {
                    $pks = array();
                    foreach ($this->modeGroupsScheduledForDeletion as $entry) {
                        $entryPk = [];

                        $entryPk[1] = $this->getName();
                        $entryPk[0] = $entry->getId();
                        $pks[] = $entryPk;
                    }

                    \WildPHP\Core\Entities\ModeGroupPolicyQuery::create()
                        ->filterByPrimaryKeys($pks)
                        ->delete($con);

                    $this->modeGroupsScheduledForDeletion = null;
                }

            }

            if ($this->collModeGroups) {
                foreach ($this->collModeGroups as $modeGroup) {
                    if (!$modeGroup->isDeleted() && ($modeGroup->isNew() || $modeGroup->isModified())) {
                        $modeGroup->save($con);
                    }
                }
            }


            if ($this->userPoliciesScheduledForDeletion !== null) {
                if (!$this->userPoliciesScheduledForDeletion->isEmpty()) {
                    \WildPHP\Core\Entities\UserPolicyQuery::create()
                        ->filterByPrimaryKeys($this->userPoliciesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->userPoliciesScheduledForDeletion = null;
                }
            }

            if ($this->collUserPolicies !== null) {
                foreach ($this->collUserPolicies as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->groupPoliciesScheduledForDeletion !== null) {
                if (!$this->groupPoliciesScheduledForDeletion->isEmpty()) {
                    \WildPHP\Core\Entities\GroupPolicyQuery::create()
                        ->filterByPrimaryKeys($this->groupPoliciesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->groupPoliciesScheduledForDeletion = null;
                }
            }

            if ($this->collGroupPolicies !== null) {
                foreach ($this->collGroupPolicies as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->modeGroupPoliciesScheduledForDeletion !== null) {
                if (!$this->modeGroupPoliciesScheduledForDeletion->isEmpty()) {
                    \WildPHP\Core\Entities\ModeGroupPolicyQuery::create()
                        ->filterByPrimaryKeys($this->modeGroupPoliciesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->modeGroupPoliciesScheduledForDeletion = null;
                }
            }

            if ($this->collModeGroupPolicies !== null) {
                foreach ($this->collModeGroupPolicies as $referrerFK) {
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


         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(PolicyTableMap::COL_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'name';
        }
        if ($this->isColumnModified(PolicyTableMap::COL_DESCRIPTION)) {
            $modifiedColumns[':p' . $index++]  = 'description';
        }

        $sql = sprintf(
            'INSERT INTO policy (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'name':
                        $stmt->bindValue($identifier, $this->name, PDO::PARAM_STR);
                        break;
                    case 'description':
                        $stmt->bindValue($identifier, $this->description, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

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
        $pos = PolicyTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getName();
                break;
            case 1:
                return $this->getDescription();
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

        if (isset($alreadyDumpedObjects['Policy'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Policy'][$this->hashCode()] = true;
        $keys = PolicyTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getName(),
            $keys[1] => $this->getDescription(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collUserPolicies) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'userPolicies';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'user_policies';
                        break;
                    default:
                        $key = 'UserPolicies';
                }

                $result[$key] = $this->collUserPolicies->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collGroupPolicies) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'groupPolicies';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'group_policies';
                        break;
                    default:
                        $key = 'GroupPolicies';
                }

                $result[$key] = $this->collGroupPolicies->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collModeGroupPolicies) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'modeGroupPolicies';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'mode_group_policies';
                        break;
                    default:
                        $key = 'ModeGroupPolicies';
                }

                $result[$key] = $this->collModeGroupPolicies->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
     * @return $this|\WildPHP\Core\Entities\Policy
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = PolicyTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\WildPHP\Core\Entities\Policy
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setName($value);
                break;
            case 1:
                $this->setDescription($value);
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
        $keys = PolicyTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setName($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setDescription($arr[$keys[1]]);
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
     * @return $this|\WildPHP\Core\Entities\Policy The current object, for fluid interface
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
        $criteria = new Criteria(PolicyTableMap::DATABASE_NAME);

        if ($this->isColumnModified(PolicyTableMap::COL_NAME)) {
            $criteria->add(PolicyTableMap::COL_NAME, $this->name);
        }
        if ($this->isColumnModified(PolicyTableMap::COL_DESCRIPTION)) {
            $criteria->add(PolicyTableMap::COL_DESCRIPTION, $this->description);
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
        $criteria = ChildPolicyQuery::create();
        $criteria->add(PolicyTableMap::COL_NAME, $this->name);

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
        $validPk = null !== $this->getName();

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
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->getName();
    }

    /**
     * Generic method to set the primary key (name column).
     *
     * @param       string $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setName($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {
        return null === $this->getName();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \WildPHP\Core\Entities\Policy (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setName($this->getName());
        $copyObj->setDescription($this->getDescription());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getUserPolicies() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addUserPolicy($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getGroupPolicies() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addGroupPolicy($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getModeGroupPolicies() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addModeGroupPolicy($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
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
     * @return \WildPHP\Core\Entities\Policy Clone of current object.
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
        if ('UserPolicy' == $relationName) {
            $this->initUserPolicies();
            return;
        }
        if ('GroupPolicy' == $relationName) {
            $this->initGroupPolicies();
            return;
        }
        if ('ModeGroupPolicy' == $relationName) {
            $this->initModeGroupPolicies();
            return;
        }
    }

    /**
     * Clears out the collUserPolicies collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addUserPolicies()
     */
    public function clearUserPolicies()
    {
        $this->collUserPolicies = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collUserPolicies collection loaded partially.
     */
    public function resetPartialUserPolicies($v = true)
    {
        $this->collUserPoliciesPartial = $v;
    }

    /**
     * Initializes the collUserPolicies collection.
     *
     * By default this just sets the collUserPolicies collection to an empty array (like clearcollUserPolicies());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initUserPolicies($overrideExisting = true)
    {
        if (null !== $this->collUserPolicies && !$overrideExisting) {
            return;
        }

        $collectionClassName = UserPolicyTableMap::getTableMap()->getCollectionClassName();

        $this->collUserPolicies = new $collectionClassName;
        $this->collUserPolicies->setModel('\WildPHP\Core\Entities\UserPolicy');
    }

    /**
     * Gets an array of ChildUserPolicy objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildPolicy is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildUserPolicy[] List of ChildUserPolicy objects
     * @throws PropelException
     */
    public function getUserPolicies(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collUserPoliciesPartial && !$this->isNew();
        if (null === $this->collUserPolicies || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collUserPolicies) {
                // return empty collection
                $this->initUserPolicies();
            } else {
                $collUserPolicies = ChildUserPolicyQuery::create(null, $criteria)
                    ->filterByPolicy($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collUserPoliciesPartial && count($collUserPolicies)) {
                        $this->initUserPolicies(false);

                        foreach ($collUserPolicies as $obj) {
                            if (false == $this->collUserPolicies->contains($obj)) {
                                $this->collUserPolicies->append($obj);
                            }
                        }

                        $this->collUserPoliciesPartial = true;
                    }

                    return $collUserPolicies;
                }

                if ($partial && $this->collUserPolicies) {
                    foreach ($this->collUserPolicies as $obj) {
                        if ($obj->isNew()) {
                            $collUserPolicies[] = $obj;
                        }
                    }
                }

                $this->collUserPolicies = $collUserPolicies;
                $this->collUserPoliciesPartial = false;
            }
        }

        return $this->collUserPolicies;
    }

    /**
     * Sets a collection of ChildUserPolicy objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $userPolicies A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildPolicy The current object (for fluent API support)
     */
    public function setUserPolicies(Collection $userPolicies, ConnectionInterface $con = null)
    {
        /** @var ChildUserPolicy[] $userPoliciesToDelete */
        $userPoliciesToDelete = $this->getUserPolicies(new Criteria(), $con)->diff($userPolicies);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->userPoliciesScheduledForDeletion = clone $userPoliciesToDelete;

        foreach ($userPoliciesToDelete as $userPolicyRemoved) {
            $userPolicyRemoved->setPolicy(null);
        }

        $this->collUserPolicies = null;
        foreach ($userPolicies as $userPolicy) {
            $this->addUserPolicy($userPolicy);
        }

        $this->collUserPolicies = $userPolicies;
        $this->collUserPoliciesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related UserPolicy objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related UserPolicy objects.
     * @throws PropelException
     */
    public function countUserPolicies(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collUserPoliciesPartial && !$this->isNew();
        if (null === $this->collUserPolicies || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collUserPolicies) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getUserPolicies());
            }

            $query = ChildUserPolicyQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByPolicy($this)
                ->count($con);
        }

        return count($this->collUserPolicies);
    }

    /**
     * Method called to associate a ChildUserPolicy object to this object
     * through the ChildUserPolicy foreign key attribute.
     *
     * @param  ChildUserPolicy $l ChildUserPolicy
     * @return $this|\WildPHP\Core\Entities\Policy The current object (for fluent API support)
     */
    public function addUserPolicy(ChildUserPolicy $l)
    {
        if ($this->collUserPolicies === null) {
            $this->initUserPolicies();
            $this->collUserPoliciesPartial = true;
        }

        if (!$this->collUserPolicies->contains($l)) {
            $this->doAddUserPolicy($l);

            if ($this->userPoliciesScheduledForDeletion and $this->userPoliciesScheduledForDeletion->contains($l)) {
                $this->userPoliciesScheduledForDeletion->remove($this->userPoliciesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildUserPolicy $userPolicy The ChildUserPolicy object to add.
     */
    protected function doAddUserPolicy(ChildUserPolicy $userPolicy)
    {
        $this->collUserPolicies[]= $userPolicy;
        $userPolicy->setPolicy($this);
    }

    /**
     * @param  ChildUserPolicy $userPolicy The ChildUserPolicy object to remove.
     * @return $this|ChildPolicy The current object (for fluent API support)
     */
    public function removeUserPolicy(ChildUserPolicy $userPolicy)
    {
        if ($this->getUserPolicies()->contains($userPolicy)) {
            $pos = $this->collUserPolicies->search($userPolicy);
            $this->collUserPolicies->remove($pos);
            if (null === $this->userPoliciesScheduledForDeletion) {
                $this->userPoliciesScheduledForDeletion = clone $this->collUserPolicies;
                $this->userPoliciesScheduledForDeletion->clear();
            }
            $this->userPoliciesScheduledForDeletion[]= clone $userPolicy;
            $userPolicy->setPolicy(null);
        }

        return $this;
    }

    /**
     * Clears out the collGroupPolicies collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addGroupPolicies()
     */
    public function clearGroupPolicies()
    {
        $this->collGroupPolicies = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collGroupPolicies collection loaded partially.
     */
    public function resetPartialGroupPolicies($v = true)
    {
        $this->collGroupPoliciesPartial = $v;
    }

    /**
     * Initializes the collGroupPolicies collection.
     *
     * By default this just sets the collGroupPolicies collection to an empty array (like clearcollGroupPolicies());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initGroupPolicies($overrideExisting = true)
    {
        if (null !== $this->collGroupPolicies && !$overrideExisting) {
            return;
        }

        $collectionClassName = GroupPolicyTableMap::getTableMap()->getCollectionClassName();

        $this->collGroupPolicies = new $collectionClassName;
        $this->collGroupPolicies->setModel('\WildPHP\Core\Entities\GroupPolicy');
    }

    /**
     * Gets an array of ChildGroupPolicy objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildPolicy is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildGroupPolicy[] List of ChildGroupPolicy objects
     * @throws PropelException
     */
    public function getGroupPolicies(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collGroupPoliciesPartial && !$this->isNew();
        if (null === $this->collGroupPolicies || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collGroupPolicies) {
                // return empty collection
                $this->initGroupPolicies();
            } else {
                $collGroupPolicies = ChildGroupPolicyQuery::create(null, $criteria)
                    ->filterByPolicy($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collGroupPoliciesPartial && count($collGroupPolicies)) {
                        $this->initGroupPolicies(false);

                        foreach ($collGroupPolicies as $obj) {
                            if (false == $this->collGroupPolicies->contains($obj)) {
                                $this->collGroupPolicies->append($obj);
                            }
                        }

                        $this->collGroupPoliciesPartial = true;
                    }

                    return $collGroupPolicies;
                }

                if ($partial && $this->collGroupPolicies) {
                    foreach ($this->collGroupPolicies as $obj) {
                        if ($obj->isNew()) {
                            $collGroupPolicies[] = $obj;
                        }
                    }
                }

                $this->collGroupPolicies = $collGroupPolicies;
                $this->collGroupPoliciesPartial = false;
            }
        }

        return $this->collGroupPolicies;
    }

    /**
     * Sets a collection of ChildGroupPolicy objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $groupPolicies A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildPolicy The current object (for fluent API support)
     */
    public function setGroupPolicies(Collection $groupPolicies, ConnectionInterface $con = null)
    {
        /** @var ChildGroupPolicy[] $groupPoliciesToDelete */
        $groupPoliciesToDelete = $this->getGroupPolicies(new Criteria(), $con)->diff($groupPolicies);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->groupPoliciesScheduledForDeletion = clone $groupPoliciesToDelete;

        foreach ($groupPoliciesToDelete as $groupPolicyRemoved) {
            $groupPolicyRemoved->setPolicy(null);
        }

        $this->collGroupPolicies = null;
        foreach ($groupPolicies as $groupPolicy) {
            $this->addGroupPolicy($groupPolicy);
        }

        $this->collGroupPolicies = $groupPolicies;
        $this->collGroupPoliciesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related GroupPolicy objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related GroupPolicy objects.
     * @throws PropelException
     */
    public function countGroupPolicies(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collGroupPoliciesPartial && !$this->isNew();
        if (null === $this->collGroupPolicies || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collGroupPolicies) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getGroupPolicies());
            }

            $query = ChildGroupPolicyQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByPolicy($this)
                ->count($con);
        }

        return count($this->collGroupPolicies);
    }

    /**
     * Method called to associate a ChildGroupPolicy object to this object
     * through the ChildGroupPolicy foreign key attribute.
     *
     * @param  ChildGroupPolicy $l ChildGroupPolicy
     * @return $this|\WildPHP\Core\Entities\Policy The current object (for fluent API support)
     */
    public function addGroupPolicy(ChildGroupPolicy $l)
    {
        if ($this->collGroupPolicies === null) {
            $this->initGroupPolicies();
            $this->collGroupPoliciesPartial = true;
        }

        if (!$this->collGroupPolicies->contains($l)) {
            $this->doAddGroupPolicy($l);

            if ($this->groupPoliciesScheduledForDeletion and $this->groupPoliciesScheduledForDeletion->contains($l)) {
                $this->groupPoliciesScheduledForDeletion->remove($this->groupPoliciesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildGroupPolicy $groupPolicy The ChildGroupPolicy object to add.
     */
    protected function doAddGroupPolicy(ChildGroupPolicy $groupPolicy)
    {
        $this->collGroupPolicies[]= $groupPolicy;
        $groupPolicy->setPolicy($this);
    }

    /**
     * @param  ChildGroupPolicy $groupPolicy The ChildGroupPolicy object to remove.
     * @return $this|ChildPolicy The current object (for fluent API support)
     */
    public function removeGroupPolicy(ChildGroupPolicy $groupPolicy)
    {
        if ($this->getGroupPolicies()->contains($groupPolicy)) {
            $pos = $this->collGroupPolicies->search($groupPolicy);
            $this->collGroupPolicies->remove($pos);
            if (null === $this->groupPoliciesScheduledForDeletion) {
                $this->groupPoliciesScheduledForDeletion = clone $this->collGroupPolicies;
                $this->groupPoliciesScheduledForDeletion->clear();
            }
            $this->groupPoliciesScheduledForDeletion[]= clone $groupPolicy;
            $groupPolicy->setPolicy(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Policy is new, it will return
     * an empty collection; or if this Policy has previously
     * been saved, it will retrieve related GroupPolicies from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Policy.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildGroupPolicy[] List of ChildGroupPolicy objects
     */
    public function getGroupPoliciesJoinGroup(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildGroupPolicyQuery::create(null, $criteria);
        $query->joinWith('Group', $joinBehavior);

        return $this->getGroupPolicies($query, $con);
    }

    /**
     * Clears out the collModeGroupPolicies collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addModeGroupPolicies()
     */
    public function clearModeGroupPolicies()
    {
        $this->collModeGroupPolicies = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collModeGroupPolicies collection loaded partially.
     */
    public function resetPartialModeGroupPolicies($v = true)
    {
        $this->collModeGroupPoliciesPartial = $v;
    }

    /**
     * Initializes the collModeGroupPolicies collection.
     *
     * By default this just sets the collModeGroupPolicies collection to an empty array (like clearcollModeGroupPolicies());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initModeGroupPolicies($overrideExisting = true)
    {
        if (null !== $this->collModeGroupPolicies && !$overrideExisting) {
            return;
        }

        $collectionClassName = ModeGroupPolicyTableMap::getTableMap()->getCollectionClassName();

        $this->collModeGroupPolicies = new $collectionClassName;
        $this->collModeGroupPolicies->setModel('\WildPHP\Core\Entities\ModeGroupPolicy');
    }

    /**
     * Gets an array of ChildModeGroupPolicy objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildPolicy is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildModeGroupPolicy[] List of ChildModeGroupPolicy objects
     * @throws PropelException
     */
    public function getModeGroupPolicies(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collModeGroupPoliciesPartial && !$this->isNew();
        if (null === $this->collModeGroupPolicies || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collModeGroupPolicies) {
                // return empty collection
                $this->initModeGroupPolicies();
            } else {
                $collModeGroupPolicies = ChildModeGroupPolicyQuery::create(null, $criteria)
                    ->filterByPolicy($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collModeGroupPoliciesPartial && count($collModeGroupPolicies)) {
                        $this->initModeGroupPolicies(false);

                        foreach ($collModeGroupPolicies as $obj) {
                            if (false == $this->collModeGroupPolicies->contains($obj)) {
                                $this->collModeGroupPolicies->append($obj);
                            }
                        }

                        $this->collModeGroupPoliciesPartial = true;
                    }

                    return $collModeGroupPolicies;
                }

                if ($partial && $this->collModeGroupPolicies) {
                    foreach ($this->collModeGroupPolicies as $obj) {
                        if ($obj->isNew()) {
                            $collModeGroupPolicies[] = $obj;
                        }
                    }
                }

                $this->collModeGroupPolicies = $collModeGroupPolicies;
                $this->collModeGroupPoliciesPartial = false;
            }
        }

        return $this->collModeGroupPolicies;
    }

    /**
     * Sets a collection of ChildModeGroupPolicy objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $modeGroupPolicies A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildPolicy The current object (for fluent API support)
     */
    public function setModeGroupPolicies(Collection $modeGroupPolicies, ConnectionInterface $con = null)
    {
        /** @var ChildModeGroupPolicy[] $modeGroupPoliciesToDelete */
        $modeGroupPoliciesToDelete = $this->getModeGroupPolicies(new Criteria(), $con)->diff($modeGroupPolicies);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->modeGroupPoliciesScheduledForDeletion = clone $modeGroupPoliciesToDelete;

        foreach ($modeGroupPoliciesToDelete as $modeGroupPolicyRemoved) {
            $modeGroupPolicyRemoved->setPolicy(null);
        }

        $this->collModeGroupPolicies = null;
        foreach ($modeGroupPolicies as $modeGroupPolicy) {
            $this->addModeGroupPolicy($modeGroupPolicy);
        }

        $this->collModeGroupPolicies = $modeGroupPolicies;
        $this->collModeGroupPoliciesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ModeGroupPolicy objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related ModeGroupPolicy objects.
     * @throws PropelException
     */
    public function countModeGroupPolicies(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collModeGroupPoliciesPartial && !$this->isNew();
        if (null === $this->collModeGroupPolicies || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collModeGroupPolicies) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getModeGroupPolicies());
            }

            $query = ChildModeGroupPolicyQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByPolicy($this)
                ->count($con);
        }

        return count($this->collModeGroupPolicies);
    }

    /**
     * Method called to associate a ChildModeGroupPolicy object to this object
     * through the ChildModeGroupPolicy foreign key attribute.
     *
     * @param  ChildModeGroupPolicy $l ChildModeGroupPolicy
     * @return $this|\WildPHP\Core\Entities\Policy The current object (for fluent API support)
     */
    public function addModeGroupPolicy(ChildModeGroupPolicy $l)
    {
        if ($this->collModeGroupPolicies === null) {
            $this->initModeGroupPolicies();
            $this->collModeGroupPoliciesPartial = true;
        }

        if (!$this->collModeGroupPolicies->contains($l)) {
            $this->doAddModeGroupPolicy($l);

            if ($this->modeGroupPoliciesScheduledForDeletion and $this->modeGroupPoliciesScheduledForDeletion->contains($l)) {
                $this->modeGroupPoliciesScheduledForDeletion->remove($this->modeGroupPoliciesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildModeGroupPolicy $modeGroupPolicy The ChildModeGroupPolicy object to add.
     */
    protected function doAddModeGroupPolicy(ChildModeGroupPolicy $modeGroupPolicy)
    {
        $this->collModeGroupPolicies[]= $modeGroupPolicy;
        $modeGroupPolicy->setPolicy($this);
    }

    /**
     * @param  ChildModeGroupPolicy $modeGroupPolicy The ChildModeGroupPolicy object to remove.
     * @return $this|ChildPolicy The current object (for fluent API support)
     */
    public function removeModeGroupPolicy(ChildModeGroupPolicy $modeGroupPolicy)
    {
        if ($this->getModeGroupPolicies()->contains($modeGroupPolicy)) {
            $pos = $this->collModeGroupPolicies->search($modeGroupPolicy);
            $this->collModeGroupPolicies->remove($pos);
            if (null === $this->modeGroupPoliciesScheduledForDeletion) {
                $this->modeGroupPoliciesScheduledForDeletion = clone $this->collModeGroupPolicies;
                $this->modeGroupPoliciesScheduledForDeletion->clear();
            }
            $this->modeGroupPoliciesScheduledForDeletion[]= clone $modeGroupPolicy;
            $modeGroupPolicy->setPolicy(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Policy is new, it will return
     * an empty collection; or if this Policy has previously
     * been saved, it will retrieve related ModeGroupPolicies from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Policy.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildModeGroupPolicy[] List of ChildModeGroupPolicy objects
     */
    public function getModeGroupPoliciesJoinModeGroup(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildModeGroupPolicyQuery::create(null, $criteria);
        $query->joinWith('ModeGroup', $joinBehavior);

        return $this->getModeGroupPolicies($query, $con);
    }

    /**
     * Clears out the collGroups collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addGroups()
     */
    public function clearGroups()
    {
        $this->collGroups = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Initializes the collGroups crossRef collection.
     *
     * By default this just sets the collGroups collection to an empty collection (like clearGroups());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @return void
     */
    public function initGroups()
    {
        $collectionClassName = GroupPolicyTableMap::getTableMap()->getCollectionClassName();

        $this->collGroups = new $collectionClassName;
        $this->collGroupsPartial = true;
        $this->collGroups->setModel('\WildPHP\Core\Entities\Group');
    }

    /**
     * Checks if the collGroups collection is loaded.
     *
     * @return bool
     */
    public function isGroupsLoaded()
    {
        return null !== $this->collGroups;
    }

    /**
     * Gets a collection of ChildGroup objects related by a many-to-many relationship
     * to the current object by way of the group_policy cross-reference table.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildPolicy is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria Optional query object to filter the query
     * @param      ConnectionInterface $con Optional connection object
     *
     * @return ObjectCollection|ChildGroup[] List of ChildGroup objects
     */
    public function getGroups(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collGroupsPartial && !$this->isNew();
        if (null === $this->collGroups || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collGroups) {
                    $this->initGroups();
                }
            } else {

                $query = ChildGroupQuery::create(null, $criteria)
                    ->filterByPolicy($this);
                $collGroups = $query->find($con);
                if (null !== $criteria) {
                    return $collGroups;
                }

                if ($partial && $this->collGroups) {
                    //make sure that already added objects gets added to the list of the database.
                    foreach ($this->collGroups as $obj) {
                        if (!$collGroups->contains($obj)) {
                            $collGroups[] = $obj;
                        }
                    }
                }

                $this->collGroups = $collGroups;
                $this->collGroupsPartial = false;
            }
        }

        return $this->collGroups;
    }

    /**
     * Sets a collection of Group objects related by a many-to-many relationship
     * to the current object by way of the group_policy cross-reference table.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param  Collection $groups A Propel collection.
     * @param  ConnectionInterface $con Optional connection object
     * @return $this|ChildPolicy The current object (for fluent API support)
     */
    public function setGroups(Collection $groups, ConnectionInterface $con = null)
    {
        $this->clearGroups();
        $currentGroups = $this->getGroups();

        $groupsScheduledForDeletion = $currentGroups->diff($groups);

        foreach ($groupsScheduledForDeletion as $toDelete) {
            $this->removeGroup($toDelete);
        }

        foreach ($groups as $group) {
            if (!$currentGroups->contains($group)) {
                $this->doAddGroup($group);
            }
        }

        $this->collGroupsPartial = false;
        $this->collGroups = $groups;

        return $this;
    }

    /**
     * Gets the number of Group objects related by a many-to-many relationship
     * to the current object by way of the group_policy cross-reference table.
     *
     * @param      Criteria $criteria Optional query object to filter the query
     * @param      boolean $distinct Set to true to force count distinct
     * @param      ConnectionInterface $con Optional connection object
     *
     * @return int the number of related Group objects
     */
    public function countGroups(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collGroupsPartial && !$this->isNew();
        if (null === $this->collGroups || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collGroups) {
                return 0;
            } else {

                if ($partial && !$criteria) {
                    return count($this->getGroups());
                }

                $query = ChildGroupQuery::create(null, $criteria);
                if ($distinct) {
                    $query->distinct();
                }

                return $query
                    ->filterByPolicy($this)
                    ->count($con);
            }
        } else {
            return count($this->collGroups);
        }
    }

    /**
     * Associate a ChildGroup to this object
     * through the group_policy cross reference table.
     *
     * @param ChildGroup $group
     * @return ChildPolicy The current object (for fluent API support)
     */
    public function addGroup(ChildGroup $group)
    {
        if ($this->collGroups === null) {
            $this->initGroups();
        }

        if (!$this->getGroups()->contains($group)) {
            // only add it if the **same** object is not already associated
            $this->collGroups->push($group);
            $this->doAddGroup($group);
        }

        return $this;
    }

    /**
     *
     * @param ChildGroup $group
     */
    protected function doAddGroup(ChildGroup $group)
    {
        $groupPolicy = new ChildGroupPolicy();

        $groupPolicy->setGroup($group);

        $groupPolicy->setPolicy($this);

        $this->addGroupPolicy($groupPolicy);

        // set the back reference to this object directly as using provided method either results
        // in endless loop or in multiple relations
        if (!$group->isPoliciesLoaded()) {
            $group->initPolicies();
            $group->getPolicies()->push($this);
        } elseif (!$group->getPolicies()->contains($this)) {
            $group->getPolicies()->push($this);
        }

    }

    /**
     * Remove group of this object
     * through the group_policy cross reference table.
     *
     * @param ChildGroup $group
     * @return ChildPolicy The current object (for fluent API support)
     */
    public function removeGroup(ChildGroup $group)
    {
        if ($this->getGroups()->contains($group)) {
            $groupPolicy = new ChildGroupPolicy();
            $groupPolicy->setGroup($group);
            if ($group->isPoliciesLoaded()) {
                //remove the back reference if available
                $group->getPolicies()->removeObject($this);
            }

            $groupPolicy->setPolicy($this);
            $this->removeGroupPolicy(clone $groupPolicy);
            $groupPolicy->clear();

            $this->collGroups->remove($this->collGroups->search($group));

            if (null === $this->groupsScheduledForDeletion) {
                $this->groupsScheduledForDeletion = clone $this->collGroups;
                $this->groupsScheduledForDeletion->clear();
            }

            $this->groupsScheduledForDeletion->push($group);
        }


        return $this;
    }

    /**
     * Clears out the collModeGroups collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addModeGroups()
     */
    public function clearModeGroups()
    {
        $this->collModeGroups = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Initializes the collModeGroups crossRef collection.
     *
     * By default this just sets the collModeGroups collection to an empty collection (like clearModeGroups());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @return void
     */
    public function initModeGroups()
    {
        $collectionClassName = ModeGroupPolicyTableMap::getTableMap()->getCollectionClassName();

        $this->collModeGroups = new $collectionClassName;
        $this->collModeGroupsPartial = true;
        $this->collModeGroups->setModel('\WildPHP\Core\Entities\ModeGroup');
    }

    /**
     * Checks if the collModeGroups collection is loaded.
     *
     * @return bool
     */
    public function isModeGroupsLoaded()
    {
        return null !== $this->collModeGroups;
    }

    /**
     * Gets a collection of ChildModeGroup objects related by a many-to-many relationship
     * to the current object by way of the mode_group_policy cross-reference table.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildPolicy is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria Optional query object to filter the query
     * @param      ConnectionInterface $con Optional connection object
     *
     * @return ObjectCollection|ChildModeGroup[] List of ChildModeGroup objects
     */
    public function getModeGroups(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collModeGroupsPartial && !$this->isNew();
        if (null === $this->collModeGroups || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collModeGroups) {
                    $this->initModeGroups();
                }
            } else {

                $query = ChildModeGroupQuery::create(null, $criteria)
                    ->filterByPolicy($this);
                $collModeGroups = $query->find($con);
                if (null !== $criteria) {
                    return $collModeGroups;
                }

                if ($partial && $this->collModeGroups) {
                    //make sure that already added objects gets added to the list of the database.
                    foreach ($this->collModeGroups as $obj) {
                        if (!$collModeGroups->contains($obj)) {
                            $collModeGroups[] = $obj;
                        }
                    }
                }

                $this->collModeGroups = $collModeGroups;
                $this->collModeGroupsPartial = false;
            }
        }

        return $this->collModeGroups;
    }

    /**
     * Sets a collection of ModeGroup objects related by a many-to-many relationship
     * to the current object by way of the mode_group_policy cross-reference table.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param  Collection $modeGroups A Propel collection.
     * @param  ConnectionInterface $con Optional connection object
     * @return $this|ChildPolicy The current object (for fluent API support)
     */
    public function setModeGroups(Collection $modeGroups, ConnectionInterface $con = null)
    {
        $this->clearModeGroups();
        $currentModeGroups = $this->getModeGroups();

        $modeGroupsScheduledForDeletion = $currentModeGroups->diff($modeGroups);

        foreach ($modeGroupsScheduledForDeletion as $toDelete) {
            $this->removeModeGroup($toDelete);
        }

        foreach ($modeGroups as $modeGroup) {
            if (!$currentModeGroups->contains($modeGroup)) {
                $this->doAddModeGroup($modeGroup);
            }
        }

        $this->collModeGroupsPartial = false;
        $this->collModeGroups = $modeGroups;

        return $this;
    }

    /**
     * Gets the number of ModeGroup objects related by a many-to-many relationship
     * to the current object by way of the mode_group_policy cross-reference table.
     *
     * @param      Criteria $criteria Optional query object to filter the query
     * @param      boolean $distinct Set to true to force count distinct
     * @param      ConnectionInterface $con Optional connection object
     *
     * @return int the number of related ModeGroup objects
     */
    public function countModeGroups(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collModeGroupsPartial && !$this->isNew();
        if (null === $this->collModeGroups || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collModeGroups) {
                return 0;
            } else {

                if ($partial && !$criteria) {
                    return count($this->getModeGroups());
                }

                $query = ChildModeGroupQuery::create(null, $criteria);
                if ($distinct) {
                    $query->distinct();
                }

                return $query
                    ->filterByPolicy($this)
                    ->count($con);
            }
        } else {
            return count($this->collModeGroups);
        }
    }

    /**
     * Associate a ChildModeGroup to this object
     * through the mode_group_policy cross reference table.
     *
     * @param ChildModeGroup $modeGroup
     * @return ChildPolicy The current object (for fluent API support)
     */
    public function addModeGroup(ChildModeGroup $modeGroup)
    {
        if ($this->collModeGroups === null) {
            $this->initModeGroups();
        }

        if (!$this->getModeGroups()->contains($modeGroup)) {
            // only add it if the **same** object is not already associated
            $this->collModeGroups->push($modeGroup);
            $this->doAddModeGroup($modeGroup);
        }

        return $this;
    }

    /**
     *
     * @param ChildModeGroup $modeGroup
     */
    protected function doAddModeGroup(ChildModeGroup $modeGroup)
    {
        $modeGroupPolicy = new ChildModeGroupPolicy();

        $modeGroupPolicy->setModeGroup($modeGroup);

        $modeGroupPolicy->setPolicy($this);

        $this->addModeGroupPolicy($modeGroupPolicy);

        // set the back reference to this object directly as using provided method either results
        // in endless loop or in multiple relations
        if (!$modeGroup->isPoliciesLoaded()) {
            $modeGroup->initPolicies();
            $modeGroup->getPolicies()->push($this);
        } elseif (!$modeGroup->getPolicies()->contains($this)) {
            $modeGroup->getPolicies()->push($this);
        }

    }

    /**
     * Remove modeGroup of this object
     * through the mode_group_policy cross reference table.
     *
     * @param ChildModeGroup $modeGroup
     * @return ChildPolicy The current object (for fluent API support)
     */
    public function removeModeGroup(ChildModeGroup $modeGroup)
    {
        if ($this->getModeGroups()->contains($modeGroup)) {
            $modeGroupPolicy = new ChildModeGroupPolicy();
            $modeGroupPolicy->setModeGroup($modeGroup);
            if ($modeGroup->isPoliciesLoaded()) {
                //remove the back reference if available
                $modeGroup->getPolicies()->removeObject($this);
            }

            $modeGroupPolicy->setPolicy($this);
            $this->removeModeGroupPolicy(clone $modeGroupPolicy);
            $modeGroupPolicy->clear();

            $this->collModeGroups->remove($this->collModeGroups->search($modeGroup));

            if (null === $this->modeGroupsScheduledForDeletion) {
                $this->modeGroupsScheduledForDeletion = clone $this->collModeGroups;
                $this->modeGroupsScheduledForDeletion->clear();
            }

            $this->modeGroupsScheduledForDeletion->push($modeGroup);
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
        $this->name = null;
        $this->description = null;
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
            if ($this->collUserPolicies) {
                foreach ($this->collUserPolicies as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collGroupPolicies) {
                foreach ($this->collGroupPolicies as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collModeGroupPolicies) {
                foreach ($this->collModeGroupPolicies as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collGroups) {
                foreach ($this->collGroups as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collModeGroups) {
                foreach ($this->collModeGroups as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collUserPolicies = null;
        $this->collGroupPolicies = null;
        $this->collModeGroupPolicies = null;
        $this->collGroups = null;
        $this->collModeGroups = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(PolicyTableMap::DEFAULT_STRING_FORMAT);
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
