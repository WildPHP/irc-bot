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
use WildPHP\Core\Entities\ModeGroupPolicy as ChildModeGroupPolicy;
use WildPHP\Core\Entities\ModeGroupPolicyQuery as ChildModeGroupPolicyQuery;
use WildPHP\Core\Entities\Map\ModeGroupPolicyTableMap;

/**
 * Base class that represents a query for the 'mode_group_policy' table.
 *
 *
 *
 * @method     ChildModeGroupPolicyQuery orderByModeGroupId($order = Criteria::ASC) Order by the mode_group_id column
 * @method     ChildModeGroupPolicyQuery orderByPolicyName($order = Criteria::ASC) Order by the policy_name column
 *
 * @method     ChildModeGroupPolicyQuery groupByModeGroupId() Group by the mode_group_id column
 * @method     ChildModeGroupPolicyQuery groupByPolicyName() Group by the policy_name column
 *
 * @method     ChildModeGroupPolicyQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildModeGroupPolicyQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildModeGroupPolicyQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildModeGroupPolicyQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildModeGroupPolicyQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildModeGroupPolicyQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildModeGroupPolicyQuery leftJoinModeGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the ModeGroup relation
 * @method     ChildModeGroupPolicyQuery rightJoinModeGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ModeGroup relation
 * @method     ChildModeGroupPolicyQuery innerJoinModeGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the ModeGroup relation
 *
 * @method     ChildModeGroupPolicyQuery joinWithModeGroup($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ModeGroup relation
 *
 * @method     ChildModeGroupPolicyQuery leftJoinWithModeGroup() Adds a LEFT JOIN clause and with to the query using the ModeGroup relation
 * @method     ChildModeGroupPolicyQuery rightJoinWithModeGroup() Adds a RIGHT JOIN clause and with to the query using the ModeGroup relation
 * @method     ChildModeGroupPolicyQuery innerJoinWithModeGroup() Adds a INNER JOIN clause and with to the query using the ModeGroup relation
 *
 * @method     ChildModeGroupPolicyQuery leftJoinPolicy($relationAlias = null) Adds a LEFT JOIN clause to the query using the Policy relation
 * @method     ChildModeGroupPolicyQuery rightJoinPolicy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Policy relation
 * @method     ChildModeGroupPolicyQuery innerJoinPolicy($relationAlias = null) Adds a INNER JOIN clause to the query using the Policy relation
 *
 * @method     ChildModeGroupPolicyQuery joinWithPolicy($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Policy relation
 *
 * @method     ChildModeGroupPolicyQuery leftJoinWithPolicy() Adds a LEFT JOIN clause and with to the query using the Policy relation
 * @method     ChildModeGroupPolicyQuery rightJoinWithPolicy() Adds a RIGHT JOIN clause and with to the query using the Policy relation
 * @method     ChildModeGroupPolicyQuery innerJoinWithPolicy() Adds a INNER JOIN clause and with to the query using the Policy relation
 *
 * @method     \WildPHP\Core\Entities\ModeGroupQuery|\WildPHP\Core\Entities\PolicyQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildModeGroupPolicy findOne(ConnectionInterface $con = null) Return the first ChildModeGroupPolicy matching the query
 * @method     ChildModeGroupPolicy findOneOrCreate(ConnectionInterface $con = null) Return the first ChildModeGroupPolicy matching the query, or a new ChildModeGroupPolicy object populated from the query conditions when no match is found
 *
 * @method     ChildModeGroupPolicy findOneByModeGroupId(int $mode_group_id) Return the first ChildModeGroupPolicy filtered by the mode_group_id column
 * @method     ChildModeGroupPolicy findOneByPolicyName(string $policy_name) Return the first ChildModeGroupPolicy filtered by the policy_name column *

 * @method     ChildModeGroupPolicy requirePk($key, ConnectionInterface $con = null) Return the ChildModeGroupPolicy by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildModeGroupPolicy requireOne(ConnectionInterface $con = null) Return the first ChildModeGroupPolicy matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildModeGroupPolicy requireOneByModeGroupId(int $mode_group_id) Return the first ChildModeGroupPolicy filtered by the mode_group_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildModeGroupPolicy requireOneByPolicyName(string $policy_name) Return the first ChildModeGroupPolicy filtered by the policy_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildModeGroupPolicy[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildModeGroupPolicy objects based on current ModelCriteria
 * @method     ChildModeGroupPolicy[]|ObjectCollection findByModeGroupId(int $mode_group_id) Return ChildModeGroupPolicy objects filtered by the mode_group_id column
 * @method     ChildModeGroupPolicy[]|ObjectCollection findByPolicyName(string $policy_name) Return ChildModeGroupPolicy objects filtered by the policy_name column
 * @method     ChildModeGroupPolicy[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class ModeGroupPolicyQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \WildPHP\Core\Entities\Base\ModeGroupPolicyQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'persistent', $modelName = '\\WildPHP\\Core\\Entities\\ModeGroupPolicy', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildModeGroupPolicyQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildModeGroupPolicyQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildModeGroupPolicyQuery) {
            return $criteria;
        }
        $query = new ChildModeGroupPolicyQuery();
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
     * @param array[$mode_group_id, $policy_name] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildModeGroupPolicy|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ModeGroupPolicyTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ModeGroupPolicyTableMap::getInstanceFromPool(serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]))))) {
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
     * @return ChildModeGroupPolicy A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT mode_group_id, policy_name FROM mode_group_policy WHERE mode_group_id = :p0 AND policy_name = :p1';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_INT);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildModeGroupPolicy $obj */
            $obj = new ChildModeGroupPolicy();
            $obj->hydrate($row);
            ModeGroupPolicyTableMap::addInstanceToPool($obj, serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]));
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
     * @return ChildModeGroupPolicy|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildModeGroupPolicyQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(ModeGroupPolicyTableMap::COL_MODE_GROUP_ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(ModeGroupPolicyTableMap::COL_POLICY_NAME, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildModeGroupPolicyQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(ModeGroupPolicyTableMap::COL_MODE_GROUP_ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(ModeGroupPolicyTableMap::COL_POLICY_NAME, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
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
     * @return $this|ChildModeGroupPolicyQuery The current query, for fluid interface
     */
    public function filterByModeGroupId($modeGroupId = null, $comparison = null)
    {
        if (is_array($modeGroupId)) {
            $useMinMax = false;
            if (isset($modeGroupId['min'])) {
                $this->addUsingAlias(ModeGroupPolicyTableMap::COL_MODE_GROUP_ID, $modeGroupId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($modeGroupId['max'])) {
                $this->addUsingAlias(ModeGroupPolicyTableMap::COL_MODE_GROUP_ID, $modeGroupId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ModeGroupPolicyTableMap::COL_MODE_GROUP_ID, $modeGroupId, $comparison);
    }

    /**
     * Filter the query on the policy_name column
     *
     * Example usage:
     * <code>
     * $query->filterByPolicyName('fooValue');   // WHERE policy_name = 'fooValue'
     * $query->filterByPolicyName('%fooValue%', Criteria::LIKE); // WHERE policy_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $policyName The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildModeGroupPolicyQuery The current query, for fluid interface
     */
    public function filterByPolicyName($policyName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($policyName)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ModeGroupPolicyTableMap::COL_POLICY_NAME, $policyName, $comparison);
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\ModeGroup object
     *
     * @param \WildPHP\Core\Entities\ModeGroup|ObjectCollection $modeGroup The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildModeGroupPolicyQuery The current query, for fluid interface
     */
    public function filterByModeGroup($modeGroup, $comparison = null)
    {
        if ($modeGroup instanceof \WildPHP\Core\Entities\ModeGroup) {
            return $this
                ->addUsingAlias(ModeGroupPolicyTableMap::COL_MODE_GROUP_ID, $modeGroup->getId(), $comparison);
        } elseif ($modeGroup instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ModeGroupPolicyTableMap::COL_MODE_GROUP_ID, $modeGroup->toKeyValue('PrimaryKey', 'Id'), $comparison);
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
     * @return $this|ChildModeGroupPolicyQuery The current query, for fluid interface
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
     * Filter the query by a related \WildPHP\Core\Entities\Policy object
     *
     * @param \WildPHP\Core\Entities\Policy|ObjectCollection $policy The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildModeGroupPolicyQuery The current query, for fluid interface
     */
    public function filterByPolicy($policy, $comparison = null)
    {
        if ($policy instanceof \WildPHP\Core\Entities\Policy) {
            return $this
                ->addUsingAlias(ModeGroupPolicyTableMap::COL_POLICY_NAME, $policy->getName(), $comparison);
        } elseif ($policy instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ModeGroupPolicyTableMap::COL_POLICY_NAME, $policy->toKeyValue('PrimaryKey', 'Name'), $comparison);
        } else {
            throw new PropelException('filterByPolicy() only accepts arguments of type \WildPHP\Core\Entities\Policy or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Policy relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildModeGroupPolicyQuery The current query, for fluid interface
     */
    public function joinPolicy($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Policy');

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
            $this->addJoinObject($join, 'Policy');
        }

        return $this;
    }

    /**
     * Use the Policy relation Policy object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \WildPHP\Core\Entities\PolicyQuery A secondary query class using the current class as primary query
     */
    public function usePolicyQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinPolicy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Policy', '\WildPHP\Core\Entities\PolicyQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildModeGroupPolicy $modeGroupPolicy Object to remove from the list of results
     *
     * @return $this|ChildModeGroupPolicyQuery The current query, for fluid interface
     */
    public function prune($modeGroupPolicy = null)
    {
        if ($modeGroupPolicy) {
            $this->addCond('pruneCond0', $this->getAliasedColName(ModeGroupPolicyTableMap::COL_MODE_GROUP_ID), $modeGroupPolicy->getModeGroupId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(ModeGroupPolicyTableMap::COL_POLICY_NAME), $modeGroupPolicy->getPolicyName(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the mode_group_policy table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ModeGroupPolicyTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ModeGroupPolicyTableMap::clearInstancePool();
            ModeGroupPolicyTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(ModeGroupPolicyTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ModeGroupPolicyTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ModeGroupPolicyTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ModeGroupPolicyTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // ModeGroupPolicyQuery
