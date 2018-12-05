<?php

namespace WildPHP\Core\Entities\Base;

use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use WildPHP\Core\Entities\UserChannel as ChildUserChannel;
use WildPHP\Core\Entities\UserChannelQuery as ChildUserChannelQuery;
use WildPHP\Core\Entities\Map\UserChannelTableMap;

/**
 * Base class that represents a query for the 'user_channel' table.
 *
 *
 *
 * @method     ChildUserChannelQuery orderByUserId($order = Criteria::ASC) Order by the user_id column
 * @method     ChildUserChannelQuery orderByChannelId($order = Criteria::ASC) Order by the channel_id column
 *
 * @method     ChildUserChannelQuery groupByUserId() Group by the user_id column
 * @method     ChildUserChannelQuery groupByChannelId() Group by the channel_id column
 *
 * @method     ChildUserChannelQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildUserChannelQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildUserChannelQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildUserChannelQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildUserChannelQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildUserChannelQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildUserChannelQuery leftJoinIrcUser($relationAlias = null) Adds a LEFT JOIN clause to the query using the IrcUser relation
 * @method     ChildUserChannelQuery rightJoinIrcUser($relationAlias = null) Adds a RIGHT JOIN clause to the query using the IrcUser relation
 * @method     ChildUserChannelQuery innerJoinIrcUser($relationAlias = null) Adds a INNER JOIN clause to the query using the IrcUser relation
 *
 * @method     ChildUserChannelQuery joinWithIrcUser($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the IrcUser relation
 *
 * @method     ChildUserChannelQuery leftJoinWithIrcUser() Adds a LEFT JOIN clause and with to the query using the IrcUser relation
 * @method     ChildUserChannelQuery rightJoinWithIrcUser() Adds a RIGHT JOIN clause and with to the query using the IrcUser relation
 * @method     ChildUserChannelQuery innerJoinWithIrcUser() Adds a INNER JOIN clause and with to the query using the IrcUser relation
 *
 * @method     ChildUserChannelQuery leftJoinIrcChannel($relationAlias = null) Adds a LEFT JOIN clause to the query using the IrcChannel relation
 * @method     ChildUserChannelQuery rightJoinIrcChannel($relationAlias = null) Adds a RIGHT JOIN clause to the query using the IrcChannel relation
 * @method     ChildUserChannelQuery innerJoinIrcChannel($relationAlias = null) Adds a INNER JOIN clause to the query using the IrcChannel relation
 *
 * @method     ChildUserChannelQuery joinWithIrcChannel($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the IrcChannel relation
 *
 * @method     ChildUserChannelQuery leftJoinWithIrcChannel() Adds a LEFT JOIN clause and with to the query using the IrcChannel relation
 * @method     ChildUserChannelQuery rightJoinWithIrcChannel() Adds a RIGHT JOIN clause and with to the query using the IrcChannel relation
 * @method     ChildUserChannelQuery innerJoinWithIrcChannel() Adds a INNER JOIN clause and with to the query using the IrcChannel relation
 *
 * @method     \WildPHP\Core\Entities\IrcUserQuery|\WildPHP\Core\Entities\IrcChannelQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildUserChannel findOne(ConnectionInterface $con = null) Return the first ChildUserChannel matching the query
 * @method     ChildUserChannel findOneOrCreate(ConnectionInterface $con = null) Return the first ChildUserChannel matching the query, or a new ChildUserChannel object populated from the query conditions when no match is found
 *
 * @method     ChildUserChannel findOneByUserId(int $user_id) Return the first ChildUserChannel filtered by the user_id column
 * @method     ChildUserChannel findOneByChannelId(int $channel_id) Return the first ChildUserChannel filtered by the channel_id column *

 * @method     ChildUserChannel requirePk($key, ConnectionInterface $con = null) Return the ChildUserChannel by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserChannel requireOne(ConnectionInterface $con = null) Return the first ChildUserChannel matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserChannel requireOneByUserId(int $user_id) Return the first ChildUserChannel filtered by the user_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserChannel requireOneByChannelId(int $channel_id) Return the first ChildUserChannel filtered by the channel_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserChannel[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildUserChannel objects based on current ModelCriteria
 * @method     ChildUserChannel[]|ObjectCollection findByUserId(int $user_id) Return ChildUserChannel objects filtered by the user_id column
 * @method     ChildUserChannel[]|ObjectCollection findByChannelId(int $channel_id) Return ChildUserChannel objects filtered by the channel_id column
 * @method     ChildUserChannel[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class UserChannelQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \WildPHP\Core\Entities\Base\UserChannelQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'persistent', $modelName = '\\WildPHP\\Core\\Entities\\UserChannel', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildUserChannelQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildUserChannelQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildUserChannelQuery) {
            return $criteria;
        }
        $query = new ChildUserChannelQuery();
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
     * $obj = $c->findPk(array(12, 34), $con);
     * </code>
     *
     * @param array[$user_id, $channel_id] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildUserChannel|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(UserChannelTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = UserChannelTableMap::getInstanceFromPool(serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]))))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildUserChannel A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT user_id, channel_id FROM user_channel WHERE user_id = :p0 AND channel_id = :p1';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_INT);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildUserChannel $obj */
            $obj = new ChildUserChannel();
            $obj->hydrate($row);
            UserChannelTableMap::addInstanceToPool($obj, serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]));
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildUserChannel|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
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
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return $this|ChildUserChannelQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(UserChannelTableMap::COL_USER_ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(UserChannelTableMap::COL_CHANNEL_ID, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildUserChannelQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(UserChannelTableMap::COL_USER_ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(UserChannelTableMap::COL_CHANNEL_ID, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
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
     * @return $this|ChildUserChannelQuery The current query, for fluid interface
     */
    public function filterByUserId($userId = null, $comparison = null)
    {
        if (is_array($userId)) {
            $useMinMax = false;
            if (isset($userId['min'])) {
                $this->addUsingAlias(UserChannelTableMap::COL_USER_ID, $userId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userId['max'])) {
                $this->addUsingAlias(UserChannelTableMap::COL_USER_ID, $userId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserChannelTableMap::COL_USER_ID, $userId, $comparison);
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
     * @return $this|ChildUserChannelQuery The current query, for fluid interface
     */
    public function filterByChannelId($channelId = null, $comparison = null)
    {
        if (is_array($channelId)) {
            $useMinMax = false;
            if (isset($channelId['min'])) {
                $this->addUsingAlias(UserChannelTableMap::COL_CHANNEL_ID, $channelId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($channelId['max'])) {
                $this->addUsingAlias(UserChannelTableMap::COL_CHANNEL_ID, $channelId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserChannelTableMap::COL_CHANNEL_ID, $channelId, $comparison);
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\IrcUser object
     *
     * @param \WildPHP\Core\Entities\IrcUser|ObjectCollection $ircUser The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildUserChannelQuery The current query, for fluid interface
     */
    public function filterByIrcUser($ircUser, $comparison = null)
    {
        if ($ircUser instanceof \WildPHP\Core\Entities\IrcUser) {
            return $this
                ->addUsingAlias(UserChannelTableMap::COL_USER_ID, $ircUser->getId(), $comparison);
        } elseif ($ircUser instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserChannelTableMap::COL_USER_ID, $ircUser->toKeyValue('PrimaryKey', 'Id'), $comparison);
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
     * @return $this|ChildUserChannelQuery The current query, for fluid interface
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
     * @return ChildUserChannelQuery The current query, for fluid interface
     */
    public function filterByIrcChannel($ircChannel, $comparison = null)
    {
        if ($ircChannel instanceof \WildPHP\Core\Entities\IrcChannel) {
            return $this
                ->addUsingAlias(UserChannelTableMap::COL_CHANNEL_ID, $ircChannel->getId(), $comparison);
        } elseif ($ircChannel instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserChannelTableMap::COL_CHANNEL_ID, $ircChannel->toKeyValue('PrimaryKey', 'Id'), $comparison);
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
     * @return $this|ChildUserChannelQuery The current query, for fluid interface
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
     * @param   ChildUserChannel $userChannel Object to remove from the list of results
     *
     * @return $this|ChildUserChannelQuery The current query, for fluid interface
     */
    public function prune($userChannel = null)
    {
        if ($userChannel) {
            $this->addCond('pruneCond0', $this->getAliasedColName(UserChannelTableMap::COL_USER_ID), $userChannel->getUserId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(UserChannelTableMap::COL_CHANNEL_ID), $userChannel->getChannelId(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the user_channel table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserChannelTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            UserChannelTableMap::clearInstancePool();
            UserChannelTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(UserChannelTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(UserChannelTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            UserChannelTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            UserChannelTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // UserChannelQuery
