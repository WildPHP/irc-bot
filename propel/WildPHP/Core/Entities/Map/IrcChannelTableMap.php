<?php

namespace WildPHP\Core\Entities\Map;

use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;
use WildPHP\Core\Entities\IrcChannel;
use WildPHP\Core\Entities\IrcChannelQuery;


/**
 * This class defines the structure of the 'channel' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class IrcChannelTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'WildPHP.Core.Entities.Map.IrcChannelTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'persistent';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'channel';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\WildPHP\\Core\\Entities\\IrcChannel';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'WildPHP.Core.Entities.IrcChannel';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 6;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 6;

    /**
     * the column name for the id field
     */
    const COL_ID = 'channel.id';

    /**
     * the column name for the name field
     */
    const COL_NAME = 'channel.name';

    /**
     * the column name for the topic field
     */
    const COL_TOPIC = 'channel.topic';

    /**
     * the column name for the created_time field
     */
    const COL_CREATED_TIME = 'channel.created_time';

    /**
     * the column name for the created_by field
     */
    const COL_CREATED_BY = 'channel.created_by';

    /**
     * the column name for the modes field
     */
    const COL_MODES = 'channel.modes';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('Id', 'Name', 'Topic', 'CreatedTime', 'CreatedBy', 'Modes', ),
        self::TYPE_CAMELNAME     => array('id', 'name', 'topic', 'createdTime', 'createdBy', 'modes', ),
        self::TYPE_COLNAME       => array(IrcChannelTableMap::COL_ID, IrcChannelTableMap::COL_NAME, IrcChannelTableMap::COL_TOPIC, IrcChannelTableMap::COL_CREATED_TIME, IrcChannelTableMap::COL_CREATED_BY, IrcChannelTableMap::COL_MODES, ),
        self::TYPE_FIELDNAME     => array('id', 'name', 'topic', 'created_time', 'created_by', 'modes', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'Name' => 1, 'Topic' => 2, 'CreatedTime' => 3, 'CreatedBy' => 4, 'Modes' => 5, ),
        self::TYPE_CAMELNAME     => array('id' => 0, 'name' => 1, 'topic' => 2, 'createdTime' => 3, 'createdBy' => 4, 'modes' => 5, ),
        self::TYPE_COLNAME       => array(IrcChannelTableMap::COL_ID => 0, IrcChannelTableMap::COL_NAME => 1, IrcChannelTableMap::COL_TOPIC => 2, IrcChannelTableMap::COL_CREATED_TIME => 3, IrcChannelTableMap::COL_CREATED_BY => 4, IrcChannelTableMap::COL_MODES => 5, ),
        self::TYPE_FIELDNAME     => array('id' => 0, 'name' => 1, 'topic' => 2, 'created_time' => 3, 'created_by' => 4, 'modes' => 5, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, )
    );

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('channel');
        $this->setPhpName('IrcChannel');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\WildPHP\\Core\\Entities\\IrcChannel');
        $this->setPackage('WildPHP.Core.Entities');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('name', 'Name', 'VARCHAR', true, 128, null);
        $this->addColumn('topic', 'Topic', 'VARCHAR', false, 128, null);
        $this->addColumn('created_time', 'CreatedTime', 'TIMESTAMP', false, null, null);
        $this->addColumn('created_by', 'CreatedBy', 'VARCHAR', false, 128, null);
        $this->addColumn('modes', 'Modes', 'VARCHAR', false, 128, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('UserChannel', '\\WildPHP\\Core\\Entities\\UserChannel', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':channel_id',
    1 => ':id',
  ),
), null, null, 'UserChannels', false);
        $this->addRelation('UserModeChannel', '\\WildPHP\\Core\\Entities\\UserModeChannel', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':channel_id',
    1 => ':id',
  ),
), null, null, 'UserModeChannels', false);
        $this->addRelation('UserPolicyRestriction', '\\WildPHP\\Core\\Entities\\UserPolicyRestriction', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':channel_id',
    1 => ':id',
  ),
), null, null, 'UserPolicyRestrictions', false);
        $this->addRelation('GroupPolicyRestriction', '\\WildPHP\\Core\\Entities\\GroupPolicyRestriction', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':channel_id',
    1 => ':id',
  ),
), null, null, 'GroupPolicyRestrictions', false);
        $this->addRelation('IrcUser', '\\WildPHP\\Core\\Entities\\IrcUser', RelationMap::MANY_TO_MANY, array(), null, null, 'IrcUsers');
    } // buildRelations()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        return (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 0 + $offset
                : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param boolean $withPrefix Whether or not to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass($withPrefix = true)
    {
        return $withPrefix ? IrcChannelTableMap::CLASS_DEFAULT : IrcChannelTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array  $row       row returned by DataFetcher->fetch().
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array           (IrcChannel object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = IrcChannelTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = IrcChannelTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + IrcChannelTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = IrcChannelTableMap::OM_CLASS;
            /** @var IrcChannel $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            IrcChannelTableMap::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = IrcChannelTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = IrcChannelTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var IrcChannel $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                IrcChannelTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria object containing the columns to add.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(IrcChannelTableMap::COL_ID);
            $criteria->addSelectColumn(IrcChannelTableMap::COL_NAME);
            $criteria->addSelectColumn(IrcChannelTableMap::COL_TOPIC);
            $criteria->addSelectColumn(IrcChannelTableMap::COL_CREATED_TIME);
            $criteria->addSelectColumn(IrcChannelTableMap::COL_CREATED_BY);
            $criteria->addSelectColumn(IrcChannelTableMap::COL_MODES);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.name');
            $criteria->addSelectColumn($alias . '.topic');
            $criteria->addSelectColumn($alias . '.created_time');
            $criteria->addSelectColumn($alias . '.created_by');
            $criteria->addSelectColumn($alias . '.modes');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getServiceContainer()->getDatabaseMap(IrcChannelTableMap::DATABASE_NAME)->getTable(IrcChannelTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(IrcChannelTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(IrcChannelTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new IrcChannelTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a IrcChannel or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or IrcChannel object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param  ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(IrcChannelTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \WildPHP\Core\Entities\IrcChannel) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(IrcChannelTableMap::DATABASE_NAME);
            $criteria->add(IrcChannelTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = IrcChannelQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            IrcChannelTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                IrcChannelTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the channel table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return IrcChannelQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a IrcChannel or Criteria object.
     *
     * @param mixed               $criteria Criteria or IrcChannel object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(IrcChannelTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from IrcChannel object
        }

        if ($criteria->containsKey(IrcChannelTableMap::COL_ID) && $criteria->keyContainsValue(IrcChannelTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.IrcChannelTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = IrcChannelQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // IrcChannelTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
IrcChannelTableMap::buildTableMap();
