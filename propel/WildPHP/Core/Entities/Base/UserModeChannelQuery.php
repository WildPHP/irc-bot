<?php

namespace WildPHP\Core\Entities\Base;

use \Exception;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use WildPHP\Core\Entities\UserModeChannel as ChildUserModeChannel;
use WildPHP\Core\Entities\UserModeChannelQuery as ChildUserModeChannelQuery;
use WildPHP\Core\Entities\Map\UserModeChannelTableMap;

/**
 * Base class that represents a query for the 'user_mode_channel' table.
 *
 *
 *
 * @method     ChildUserModeChannelQuery orderByUserId($order = Criteria::ASC) Order by the user_id column
 * @method     ChildUserModeChannelQuery orderByChannelId($order = Criteria::ASC) Order by the channel_id column
 * @method     ChildUserModeChannelQuery orderByMode($order = Criteria::ASC) Order by the mode column
 *
 * @method     ChildUserModeChannelQuery groupByUserId() Group by the user_id column
 * @method     ChildUserModeChannelQuery groupByChannelId() Group by the channel_id column
 * @method     ChildUserModeChannelQuery groupByMode() Group by the mode column
 *
 * @method     ChildUserModeChannelQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildUserModeChannelQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildUserModeChannelQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildUserModeChannelQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildUserModeChannelQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildUserModeChannelQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildUserModeChannelQuery leftJoinIrcUser($relationAlias = null) Adds a LEFT JOIN clause to the query using the IrcUser relation
 * @method     ChildUserModeChannelQuery rightJoinIrcUser($relationAlias = null) Adds a RIGHT JOIN clause to the query using the IrcUser relation
 * @method     ChildUserModeChannelQuery innerJoinIrcUser($relationAlias = null) Adds a INNER JOIN clause to the query using the IrcUser relation
 *
 * @method     ChildUserModeChannelQuery joinWithIrcUser($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the IrcUser relation
 *
 * @method     ChildUserModeChannelQuery leftJoinWithIrcUser() Adds a LEFT JOIN clause and with to the query using the IrcUser relation
 * @method     ChildUserModeChannelQuery rightJoinWithIrcUser() Adds a RIGHT JOIN clause and with to the query using the IrcUser relation
 * @method     ChildUserModeChannelQuery innerJoinWithIrcUser() Adds a INNER JOIN clause and with to the query using the IrcUser relation
 *
 * @method     ChildUserModeChannelQuery leftJoinIrcChannel($relationAlias = null) Adds a LEFT JOIN clause to the query using the IrcChannel relation
 * @method     ChildUserModeChannelQuery rightJoinIrcChannel($relationAlias = null) Adds a RIGHT JOIN clause to the query using the IrcChannel relation
 * @method     ChildUserModeChannelQuery innerJoinIrcChannel($relationAlias = null) Adds a INNER JOIN clause to the query using the IrcChannel relation
 *
 * @method     ChildUserModeChannelQuery joinWithIrcChannel($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the IrcChannel relation
 *
 * @method     ChildUserModeChannelQuery leftJoinWithIrcChannel() Adds a LEFT JOIN clause and with to the query using the IrcChannel relation
 * @method     ChildUserModeChannelQuery rightJoinWithIrcChannel() Adds a RIGHT JOIN clause and with to the query using the IrcChannel relation
 * @method     ChildUserModeChannelQuery innerJoinWithIrcChannel() Adds a INNER JOIN clause and with to the query using the IrcChannel relation
 *
 * @method     \WildPHP\Core\Entities\IrcUserQuery|\WildPHP\Core\Entities\IrcChannelQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildUserModeChannel findOne(ConnectionInterface $con = null) Return the first ChildUserModeChannel matching the query
 * @method     ChildUserModeChannel findOneOrCreate(ConnectionInterface $con = null) Return the first ChildUserModeChannel matching the query, or a new ChildUserModeChannel object populated from the query conditions when no match is found
 *
 * @method     ChildUserModeChannel findOneByUserId(int $user_id) Return the first ChildUserModeChannel filtered by the user_id column
 * @method     ChildUserModeChannel findOneByChannelId(int $channel_id) Return the first ChildUserModeChannel filtered by the channel_id column
 * @method     ChildUserModeChannel findOneByMode(string $mode) Return the first ChildUserModeChannel filtered by the mode column *

 * @method     ChildUserModeChannel requirePk($key, ConnectionInterface $con = null) Return the ChildUserModeChannel by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserModeChannel requireOne(ConnectionInterface $con = null) Return the first ChildUserModeChannel matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserModeChannel requireOneByUserId(int $user_id) Return the first ChildUserModeChannel filtered by the user_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserModeChannel requireOneByChannelId(int $channel_id) Return the first ChildUserModeChannel filtered by the channel_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserModeChannel requireOneByMode(string $mode) Return the first ChildUserModeChannel filtered by the mode column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserModeChannel[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildUserModeChannel objects based on current ModelCriteria
 * @method     ChildUserModeChannel[]|ObjectCollection findByUserId(int $user_id) Return ChildUserModeChannel objects filtered by the user_id column
 * @method     ChildUserModeChannel[]|ObjectCollection findByChannelId(int $channel_id) Return ChildUserModeChannel objects filtered by the channel_id column
 * @method     ChildUserModeChannel[]|ObjectCollection findByMode(string $mode) Return ChildUserModeChannel objects filtered by the mode column
 * @method     ChildUserModeChannel[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class UserModeChannelQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \WildPHP\Core\Entities\Base\UserModeChannelQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'persistent', $modelName = '\\WildPHP\\Core\\Entities\\UserModeChannel', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildUserModeChannelQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildUserModeChannelQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildUserModeChannelQuery) {
            return $criteria;
        }
        $query = new ChildUserModeChannelQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildUserModeChannel|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        throw new LogicException('The UserModeChannel object has no primary key');
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(array(12, 56), array(832, 123), array(123, 456)), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ConnectionInterface $con = null)
    {
        throw new LogicException('The UserModeChannel object has no primary key');
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return $this|ChildUserModeChannelQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        throw new LogicException('The UserModeChannel object has no primary key');
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildUserModeChannelQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        throw new LogicException('The UserModeChannel object has no primary key');
    }

    /**
     * Filter the query on the user_id column
     *
     * Example usage:
     * <code>
     * $query->filterByUserId(1234); // WHERE user_id = 1234
     * $query->filterByUserId(array(12, 34)); // WHERE user_id IN (12, 34)
     * $query->filterByUserId(array('min' => 12)); // WHERE user_id > 12
     * </code>
     *
     * @see       filterByIrcUser()
     *
     * @param     mixed $userId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserModeChannelQuery The current query, for fluid interface
     */
    public function filterByUserId($userId = null, $comparison = null)
    {
        if (is_array($userId)) {
            $useMinMax = false;
            if (isset($userId['min'])) {
                $this->addUsingAlias(UserModeChannelTableMap::COL_USER_ID, $userId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userId['max'])) {
                $this->addUsingAlias(UserModeChannelTableMap::COL_USER_ID, $userId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserModeChannelTableMap::COL_USER_ID, $userId, $comparison);
    }

    /**
     * Filter the query on the channel_id column
     *
     * Example usage:
     * <code>
     * $query->filterByChannelId(1234); // WHERE channel_id = 1234
     * $query->filterByChannelId(array(12, 34)); // WHERE channel_id IN (12, 34)
     * $query->filterByChannelId(array('min' => 12)); // WHERE channel_id > 12
     * </code>
     *
     * @see       filterByIrcChannel()
     *
     * @param     mixed $channelId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserModeChannelQuery The current query, for fluid interface
     */
    public function filterByChannelId($channelId = null, $comparison = null)
    {
        if (is_array($channelId)) {
            $useMinMax = false;
            if (isset($channelId['min'])) {
                $this->addUsingAlias(UserModeChannelTableMap::COL_CHANNEL_ID, $channelId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($channelId['max'])) {
                $this->addUsingAlias(UserModeChannelTableMap::COL_CHANNEL_ID, $channelId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserModeChannelTableMap::COL_CHANNEL_ID, $channelId, $comparison);
    }

    /**
     * Filter the query on the mode column
     *
     * Example usage:
     * <code>
     * $query->filterByMode('fooValue');   // WHERE mode = 'fooValue'
     * $query->filterByMode('%fooValue%', Criteria::LIKE); // WHERE mode LIKE '%fooValue%'
     * </code>
     *
     * @param     string $mode The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserModeChannelQuery The current query, for fluid interface
     */
    public function filterByMode($mode = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($mode)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserModeChannelTableMap::COL_MODE, $mode, $comparison);
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\IrcUser object
     *
     * @param \WildPHP\Core\Entities\IrcUser|ObjectCollection $ircUser The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildUserModeChannelQuery The current query, for fluid interface
     */
    public function filterByIrcUser($ircUser, $comparison = null)
    {
        if ($ircUser instanceof \WildPHP\Core\Entities\IrcUser) {
            return $this
                ->addUsingAlias(UserModeChannelTableMap::COL_USER_ID, $ircUser->getId(), $comparison);
        } elseif ($ircUser instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserModeChannelTableMap::COL_USER_ID, $ircUser->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByIrcUser() only accepts arguments of type \WildPHP\Core\Entities\IrcUser or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the IrcUser relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildUserModeChannelQuery The current query, for fluid interface
     */
    public function joinIrcUser($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('IrcUser');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'IrcUser');
        }

        return $this;
    }

    /**
     * Use the IrcUser relation IrcUser object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \WildPHP\Core\Entities\IrcUserQuery A secondary query class using the current class as primary query
     */
    public function useIrcUserQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinIrcUser($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'IrcUser', '\WildPHP\Core\Entities\IrcUserQuery');
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\IrcChannel object
     *
     * @param \WildPHP\Core\Entities\IrcChannel|ObjectCollection $ircChannel The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildUserModeChannelQuery The current query, for fluid interface
     */
    public function filterByIrcChannel($ircChannel, $comparison = null)
    {
        if ($ircChannel instanceof \WildPHP\Core\Entities\IrcChannel) {
            return $this
                ->addUsingAlias(UserModeChannelTableMap::COL_CHANNEL_ID, $ircChannel->getId(), $comparison);
        } elseif ($ircChannel instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserModeChannelTableMap::COL_CHANNEL_ID, $ircChannel->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByIrcChannel() only accepts arguments of type \WildPHP\Core\Entities\IrcChannel or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the IrcChannel relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildUserModeChannelQuery The current query, for fluid interface
     */
    public function joinIrcChannel($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('IrcChannel');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'IrcChannel');
        }

        return $this;
    }

    /**
     * Use the IrcChannel relation IrcChannel object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \WildPHP\Core\Entities\IrcChannelQuery A secondary query class using the current class as primary query
     */
    public function useIrcChannelQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinIrcChannel($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'IrcChannel', '\WildPHP\Core\Entities\IrcChannelQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildUserModeChannel $userModeChannel Object to remove from the list of results
     *
     * @return $this|ChildUserModeChannelQuery The current query, for fluid interface
     */
    public function prune($userModeChannel = null)
    {
        if ($userModeChannel) {
            throw new LogicException('UserModeChannel object has no primary key');

        }

        return $this;
    }

    /**
     * Deletes all rows from the user_mode_channel table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserModeChannelTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            UserModeChannelTableMap::clearInstancePool();
            UserModeChannelTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserModeChannelTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(UserModeChannelTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            UserModeChannelTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            UserModeChannelTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // UserModeChannelQuery
