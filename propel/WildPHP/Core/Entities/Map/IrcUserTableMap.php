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
use WildPHP\Core\Entities\IrcUser;
use WildPHP\Core\Entities\IrcUserQuery;


/**
 * This class defines the structure of the 'user' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class IrcUserTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'WildPHP.Core.Entities.Map.IrcUserTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'persistent';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'user';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\WildPHP\\Core\\Entities\\IrcUser';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'WildPHP.Core.Entities.IrcUser';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 7;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 7;

    /**
     * the column name for the id field
     */
    const COL_ID = 'user.id';

    /**
     * the column name for the nickname field
     */
    const COL_NICKNAME = 'user.nickname';

    /**
     * the column name for the username field
     */
    const COL_USERNAME = 'user.username';

    /**
     * the column name for the realname field
     */
    const COL_REALNAME = 'user.realname';

    /**
     * the column name for the hostname field
     */
    const COL_HOSTNAME = 'user.hostname';

    /**
     * the column name for the irc_account field
     */
    const COL_IRC_ACCOUNT = 'user.irc_account';

    /**
     * the column name for the last_seen field
     */
    const COL_LAST_SEEN = 'user.last_seen';

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
        self::TYPE_PHPNAME       => array('Id', 'Nickname', 'Username', 'Realname', 'Hostname', 'IrcAccount', 'LastSeen', ),
        self::TYPE_CAMELNAME     => array('id', 'nickname', 'username', 'realname', 'hostname', 'ircAccount', 'lastSeen', ),
        self::TYPE_COLNAME       => array(IrcUserTableMap::COL_ID, IrcUserTableMap::COL_NICKNAME, IrcUserTableMap::COL_USERNAME, IrcUserTableMap::COL_REALNAME, IrcUserTableMap::COL_HOSTNAME, IrcUserTableMap::COL_IRC_ACCOUNT, IrcUserTableMap::COL_LAST_SEEN, ),
        self::TYPE_FIELDNAME     => array('id', 'nickname', 'username', 'realname', 'hostname', 'irc_account', 'last_seen', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'Nickname' => 1, 'Username' => 2, 'Realname' => 3, 'Hostname' => 4, 'IrcAccount' => 5, 'LastSeen' => 6, ),
        self::TYPE_CAMELNAME     => array('id' => 0, 'nickname' => 1, 'username' => 2, 'realname' => 3, 'hostname' => 4, 'ircAccount' => 5, 'lastSeen' => 6, ),
        self::TYPE_COLNAME       => array(IrcUserTableMap::COL_ID => 0, IrcUserTableMap::COL_NICKNAME => 1, IrcUserTableMap::COL_USERNAME => 2, IrcUserTableMap::COL_REALNAME => 3, IrcUserTableMap::COL_HOSTNAME => 4, IrcUserTableMap::COL_IRC_ACCOUNT => 5, IrcUserTableMap::COL_LAST_SEEN => 6, ),
        self::TYPE_FIELDNAME     => array('id' => 0, 'nickname' => 1, 'username' => 2, 'realname' => 3, 'hostname' => 4, 'irc_account' => 5, 'last_seen' => 6, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, )
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
        $this->setName('user');
        $this->setPhpName('IrcUser');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\WildPHP\\Core\\Entities\\IrcUser');
        $this->setPackage('WildPHP.Core.Entities');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('nickname', 'Nickname', 'VARCHAR', true, 128, null);
        $this->addColumn('username', 'Username', 'VARCHAR', false, 128, null);
        $this->addColumn('realname', 'Realname', 'VARCHAR', false, 128, null);
        $this->addColumn('hostname', 'Hostname', 'VARCHAR', false, 128, null);
        $this->addColumn('irc_account', 'IrcAccount', 'VARCHAR', false, 128, null);
        $this->addColumn('last_seen', 'LastSeen', 'TIMESTAMP', false, null, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('UserChannel', '\\WildPHP\\Core\\Entities\\UserChannel', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':user_id',
    1 => ':id',
  ),
), null, null, 'UserChannels', false);
        $this->addRelation('UserModeChannel', '\\WildPHP\\Core\\Entities\\UserModeChannel', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':user_id',
    1 => ':id',
  ),
), null, null, 'UserModeChannels', false);
        $this->addRelation('IrcChannel', '\\WildPHP\\Core\\Entities\\IrcChannel', RelationMap::MANY_TO_MANY, array(), null, null, 'IrcChannels');
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
        return $withPrefix ? IrcUserTableMap::CLASS_DEFAULT : IrcUserTableMap::OM_CLASS;
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
     * @return array           (IrcUser object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = IrcUserTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = IrcUserTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + IrcUserTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = IrcUserTableMap::OM_CLASS;
            /** @var IrcUser $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            IrcUserTableMap::addInstanceToPool($obj, $key);
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
            $key = IrcUserTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = IrcUserTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var IrcUser $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                IrcUserTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(IrcUserTableMap::COL_ID);
            $criteria->addSelectColumn(IrcUserTableMap::COL_NICKNAME);
            $criteria->addSelectColumn(IrcUserTableMap::COL_USERNAME);
            $criteria->addSelectColumn(IrcUserTableMap::COL_REALNAME);
            $criteria->addSelectColumn(IrcUserTableMap::COL_HOSTNAME);
            $criteria->addSelectColumn(IrcUserTableMap::COL_IRC_ACCOUNT);
            $criteria->addSelectColumn(IrcUserTableMap::COL_LAST_SEEN);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.nickname');
            $criteria->addSelectColumn($alias . '.username');
            $criteria->addSelectColumn($alias . '.realname');
            $criteria->addSelectColumn($alias . '.hostname');
            $criteria->addSelectColumn($alias . '.irc_account');
            $criteria->addSelectColumn($alias . '.last_seen');
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
        return Propel::getServiceContainer()->getDatabaseMap(IrcUserTableMap::DATABASE_NAME)->getTable(IrcUserTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(IrcUserTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(IrcUserTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new IrcUserTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a IrcUser or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or IrcUser object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(IrcUserTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \WildPHP\Core\Entities\IrcUser) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(IrcUserTableMap::DATABASE_NAME);
            $criteria->add(IrcUserTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = IrcUserQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            IrcUserTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                IrcUserTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the user table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return IrcUserQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a IrcUser or Criteria object.
     *
     * @param mixed               $criteria Criteria or IrcUser object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(IrcUserTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from IrcUser object
        }

        if ($criteria->containsKey(IrcUserTableMap::COL_ID) && $criteria->keyContainsValue(IrcUserTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.IrcUserTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = IrcUserQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // IrcUserTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
IrcUserTableMap::buildTableMap();
