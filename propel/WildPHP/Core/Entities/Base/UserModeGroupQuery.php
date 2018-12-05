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
use WildPHP\Core\Entities\UserModeGroup as ChildUserModeGroup;
use WildPHP\Core\Entities\UserModeGroupQuery as ChildUserModeGroupQuery;
use WildPHP\Core\Entities\Map\UserModeGroupTableMap;

/**
 * Base class that represents a query for the 'user_mode_group' table.
 *
 *
 *
 * @method     ChildUserModeGroupQuery orderByUserIrcAccount($order = Criteria::ASC) Order by the user_irc_account column
 * @method     ChildUserModeGroupQuery orderByModeGroupId($order = Criteria::ASC) Order by the mode_group_id column
 *
 * @method     ChildUserModeGroupQuery groupByUserIrcAccount() Group by the user_irc_account column
 * @method     ChildUserModeGroupQuery groupByModeGroupId() Group by the mode_group_id column
 *
 * @method     ChildUserModeGroupQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildUserModeGroupQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildUserModeGroupQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildUserModeGroupQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildUserModeGroupQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildUserModeGroupQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildUserModeGroupQuery leftJoinModeGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the ModeGroup relation
 * @method     ChildUserModeGroupQuery rightJoinModeGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ModeGroup relation
 * @method     ChildUserModeGroupQuery innerJoinModeGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the ModeGroup relation
 *
 * @method     ChildUserModeGroupQuery joinWithModeGroup($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ModeGroup relation
 *
 * @method     ChildUserModeGroupQuery leftJoinWithModeGroup() Adds a LEFT JOIN clause and with to the query using the ModeGroup relation
 * @method     ChildUserModeGroupQuery rightJoinWithModeGroup() Adds a RIGHT JOIN clause and with to the query using the ModeGroup relation
 * @method     ChildUserModeGroupQuery innerJoinWithModeGroup() Adds a INNER JOIN clause and with to the query using the ModeGroup relation
 *
 * @method     \WildPHP\Core\Entities\ModeGroupQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildUserModeGroup findOne(ConnectionInterface $con = null) Return the first ChildUserModeGroup matching the query
 * @method     ChildUserModeGroup findOneOrCreate(ConnectionInterface $con = null) Return the first ChildUserModeGroup matching the query, or a new ChildUserModeGroup object populated from the query conditions when no match is found
 *
 * @method     ChildUserModeGroup findOneByUserIrcAccount(string $user_irc_account) Return the first ChildUserModeGroup filtered by the user_irc_account column
 * @method     ChildUserModeGroup findOneByModeGroupId(int $mode_group_id) Return the first ChildUserModeGroup filtered by the mode_group_id column *

 * @method     ChildUserModeGroup requirePk($key, ConnectionInterface $con = null) Return the ChildUserModeGroup by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserModeGroup requireOne(ConnectionInterface $con = null) Return the first ChildUserModeGroup matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserModeGroup requireOneByUserIrcAccount(string $user_irc_account) Return the first ChildUserModeGroup filtered by the user_irc_account column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserModeGroup requireOneByModeGroupId(int $mode_group_id) Return the first ChildUserModeGroup filtered by the mode_group_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserModeGroup[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildUserModeGroup objects based on current ModelCriteria
 * @method     ChildUserModeGroup[]|ObjectCollection findByUserIrcAccount(string $user_irc_account) Return ChildUserModeGroup objects filtered by the user_irc_account column
 * @method     ChildUserModeGroup[]|ObjectCollection findByModeGroupId(int $mode_group_id) Return ChildUserModeGroup objects filtered by the mode_group_id column
 * @method     ChildUserModeGroup[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class UserModeGroupQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \WildPHP\Core\Entities\Base\UserModeGroupQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'persistent', $modelName = '\\WildPHP\\Core\\Entities\\UserModeGroup', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildUserModeGroupQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildUserModeGroupQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildUserModeGroupQuery) {
            return $criteria;
        }
        $query = new ChildUserModeGroupQuery();
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
     * @param array[$user_irc_account, $mode_group_id] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildUserModeGroup|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(UserModeGroupTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = UserModeGroupTableMap::getInstanceFromPool(serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]))))) {
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
     * @return ChildUserModeGroup A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT user_irc_account, mode_group_id FROM user_mode_group WHERE user_irc_account = :p0 AND mode_group_id = :p1';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_STR);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildUserModeGroup $obj */
            $obj = new ChildUserModeGroup();
            $obj->hydrate($row);
            UserModeGroupTableMap::addInstanceToPool($obj, serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]));
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
     * @return ChildUserModeGroup|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildUserModeGroupQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(UserModeGroupTableMap::COL_USER_IRC_ACCOUNT, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(UserModeGroupTableMap::COL_MODE_GROUP_ID, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildUserModeGroupQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(UserModeGroupTableMap::COL_USER_IRC_ACCOUNT, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(UserModeGroupTableMap::COL_MODE_GROUP_ID, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
    }

    /**
     * Filter the query on the user_irc_account column
     *
     * Example usage:
     * <code>
     * $query->filterByUserIrcAccount('fooValue');   // WHERE user_irc_account = 'fooValue'
     * $query->filterByUserIrcAccount('%fooValue%', Criteria::LIKE); // WHERE user_irc_account LIKE '%fooValue%'
     * </code>
     *
     * @param     string $userIrcAccount The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserModeGroupQuery The current query, for fluid interface
     */
    public function filterByUserIrcAccount($userIrcAccount = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($userIrcAccount)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserModeGroupTableMap::COL_USER_IRC_ACCOUNT, $userIrcAccount, $comparison);
    }

    /**
     * Filter the query on the mode_group_id column
     *
     * Example usage:
     * <code>
     * $query->filterByModeGroupId(1234); // WHERE mode_group_id = 1234
     * $query->filterByModeGroupId(array(12, 34)); // WHERE mode_group_id IN (12, 34)
     * $query->filterByModeGroupId(array('min' => 12)); // WHERE mode_group_id > 12
     * </code>
     *
     * @see       filterByModeGroup()
     *
     * @param     mixed $modeGroupId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserModeGroupQuery The current query, for fluid interface
     */
    public function filterByModeGroupId($modeGroupId = null, $comparison = null)
    {
        if (is_array($modeGroupId)) {
            $useMinMax = false;
            if (isset($modeGroupId['min'])) {
                $this->addUsingAlias(UserModeGroupTableMap::COL_MODE_GROUP_ID, $modeGroupId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($modeGroupId['max'])) {
                $this->addUsingAlias(UserModeGroupTableMap::COL_MODE_GROUP_ID, $modeGroupId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserModeGroupTableMap::COL_MODE_GROUP_ID, $modeGroupId, $comparison);
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\ModeGroup object
     *
     * @param \WildPHP\Core\Entities\ModeGroup|ObjectCollection $modeGroup The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildUserModeGroupQuery The current query, for fluid interface
     */
    public function filterByModeGroup($modeGroup, $comparison = null)
    {
        if ($modeGroup instanceof \WildPHP\Core\Entities\ModeGroup) {
            return $this
                ->addUsingAlias(UserModeGroupTableMap::COL_MODE_GROUP_ID, $modeGroup->getId(), $comparison);
        } elseif ($modeGroup instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserModeGroupTableMap::COL_MODE_GROUP_ID, $modeGroup->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByModeGroup() only accepts arguments of type \WildPHP\Core\Entities\ModeGroup or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ModeGroup relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildUserModeGroupQuery The current query, for fluid interface
     */
    public function joinModeGroup($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ModeGroup');

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
            $this->addJoinObject($join, 'ModeGroup');
        }

        return $this;
    }

    /**
     * Use the ModeGroup relation ModeGroup object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \WildPHP\Core\Entities\ModeGroupQuery A secondary query class using the current class as primary query
     */
    public function useModeGroupQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinModeGroup($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ModeGroup', '\WildPHP\Core\Entities\ModeGroupQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildUserModeGroup $userModeGroup Object to remove from the list of results
     *
     * @return $this|ChildUserModeGroupQuery The current query, for fluid interface
     */
    public function prune($userModeGroup = null)
    {
        if ($userModeGroup) {
            $this->addCond('pruneCond0', $this->getAliasedColName(UserModeGroupTableMap::COL_USER_IRC_ACCOUNT), $userModeGroup->getUserIrcAccount(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(UserModeGroupTableMap::COL_MODE_GROUP_ID), $userModeGroup->getModeGroupId(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the user_mode_group table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserModeGroupTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            UserModeGroupTableMap::clearInstancePool();
            UserModeGroupTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(UserModeGroupTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(UserModeGroupTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            UserModeGroupTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            UserModeGroupTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // UserModeGroupQuery
