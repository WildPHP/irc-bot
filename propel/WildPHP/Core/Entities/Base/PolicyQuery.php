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
use WildPHP\Core\Entities\Policy as ChildPolicy;
use WildPHP\Core\Entities\PolicyQuery as ChildPolicyQuery;
use WildPHP\Core\Entities\Map\PolicyTableMap;

/**
 * Base class that represents a query for the 'policy' table.
 *
 *
 *
 * @method     ChildPolicyQuery orderByName($order = Criteria::ASC) Order by the name column
 * @method     ChildPolicyQuery orderByDescription($order = Criteria::ASC) Order by the description column
 *
 * @method     ChildPolicyQuery groupByName() Group by the name column
 * @method     ChildPolicyQuery groupByDescription() Group by the description column
 *
 * @method     ChildPolicyQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildPolicyQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildPolicyQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildPolicyQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildPolicyQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildPolicyQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildPolicyQuery leftJoinUserPolicy($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserPolicy relation
 * @method     ChildPolicyQuery rightJoinUserPolicy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserPolicy relation
 * @method     ChildPolicyQuery innerJoinUserPolicy($relationAlias = null) Adds a INNER JOIN clause to the query using the UserPolicy relation
 *
 * @method     ChildPolicyQuery joinWithUserPolicy($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserPolicy relation
 *
 * @method     ChildPolicyQuery leftJoinWithUserPolicy() Adds a LEFT JOIN clause and with to the query using the UserPolicy relation
 * @method     ChildPolicyQuery rightJoinWithUserPolicy() Adds a RIGHT JOIN clause and with to the query using the UserPolicy relation
 * @method     ChildPolicyQuery innerJoinWithUserPolicy() Adds a INNER JOIN clause and with to the query using the UserPolicy relation
 *
 * @method     ChildPolicyQuery leftJoinGroupPolicy($relationAlias = null) Adds a LEFT JOIN clause to the query using the GroupPolicy relation
 * @method     ChildPolicyQuery rightJoinGroupPolicy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GroupPolicy relation
 * @method     ChildPolicyQuery innerJoinGroupPolicy($relationAlias = null) Adds a INNER JOIN clause to the query using the GroupPolicy relation
 *
 * @method     ChildPolicyQuery joinWithGroupPolicy($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the GroupPolicy relation
 *
 * @method     ChildPolicyQuery leftJoinWithGroupPolicy() Adds a LEFT JOIN clause and with to the query using the GroupPolicy relation
 * @method     ChildPolicyQuery rightJoinWithGroupPolicy() Adds a RIGHT JOIN clause and with to the query using the GroupPolicy relation
 * @method     ChildPolicyQuery innerJoinWithGroupPolicy() Adds a INNER JOIN clause and with to the query using the GroupPolicy relation
 *
 * @method     ChildPolicyQuery leftJoinModeGroupPolicy($relationAlias = null) Adds a LEFT JOIN clause to the query using the ModeGroupPolicy relation
 * @method     ChildPolicyQuery rightJoinModeGroupPolicy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ModeGroupPolicy relation
 * @method     ChildPolicyQuery innerJoinModeGroupPolicy($relationAlias = null) Adds a INNER JOIN clause to the query using the ModeGroupPolicy relation
 *
 * @method     ChildPolicyQuery joinWithModeGroupPolicy($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ModeGroupPolicy relation
 *
 * @method     ChildPolicyQuery leftJoinWithModeGroupPolicy() Adds a LEFT JOIN clause and with to the query using the ModeGroupPolicy relation
 * @method     ChildPolicyQuery rightJoinWithModeGroupPolicy() Adds a RIGHT JOIN clause and with to the query using the ModeGroupPolicy relation
 * @method     ChildPolicyQuery innerJoinWithModeGroupPolicy() Adds a INNER JOIN clause and with to the query using the ModeGroupPolicy relation
 *
 * @method     \WildPHP\Core\Entities\UserPolicyQuery|\WildPHP\Core\Entities\GroupPolicyQuery|\WildPHP\Core\Entities\ModeGroupPolicyQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildPolicy findOne(ConnectionInterface $con = null) Return the first ChildPolicy matching the query
 * @method     ChildPolicy findOneOrCreate(ConnectionInterface $con = null) Return the first ChildPolicy matching the query, or a new ChildPolicy object populated from the query conditions when no match is found
 *
 * @method     ChildPolicy findOneByName(string $name) Return the first ChildPolicy filtered by the name column
 * @method     ChildPolicy findOneByDescription(string $description) Return the first ChildPolicy filtered by the description column *

 * @method     ChildPolicy requirePk($key, ConnectionInterface $con = null) Return the ChildPolicy by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPolicy requireOne(ConnectionInterface $con = null) Return the first ChildPolicy matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildPolicy requireOneByName(string $name) Return the first ChildPolicy filtered by the name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPolicy requireOneByDescription(string $description) Return the first ChildPolicy filtered by the description column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildPolicy[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildPolicy objects based on current ModelCriteria
 * @method     ChildPolicy[]|ObjectCollection findByName(string $name) Return ChildPolicy objects filtered by the name column
 * @method     ChildPolicy[]|ObjectCollection findByDescription(string $description) Return ChildPolicy objects filtered by the description column
 * @method     ChildPolicy[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class PolicyQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \WildPHP\Core\Entities\Base\PolicyQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'persistent', $modelName = '\\WildPHP\\Core\\Entities\\Policy', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildPolicyQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildPolicyQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildPolicyQuery) {
            return $criteria;
        }
        $query = new ChildPolicyQuery();
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
     * @return ChildPolicy|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(PolicyTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = PolicyTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildPolicy A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT name, description FROM policy WHERE name = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildPolicy $obj */
            $obj = new ChildPolicy();
            $obj->hydrate($row);
            PolicyTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildPolicy|array|mixed the result, formatted by the current formatter
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
     * $objs = $c->findPks(array(12, 56, 832), $con);
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
     * @return $this|ChildPolicyQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(PolicyTableMap::COL_NAME, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildPolicyQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(PolicyTableMap::COL_NAME, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
     * $query->filterByName('%fooValue%', Criteria::LIKE); // WHERE name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $name The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPolicyQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PolicyTableMap::COL_NAME, $name, $comparison);
    }

    /**
     * Filter the query on the description column
     *
     * Example usage:
     * <code>
     * $query->filterByDescription('fooValue');   // WHERE description = 'fooValue'
     * $query->filterByDescription('%fooValue%', Criteria::LIKE); // WHERE description LIKE '%fooValue%'
     * </code>
     *
     * @param     string $description The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPolicyQuery The current query, for fluid interface
     */
    public function filterByDescription($description = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($description)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PolicyTableMap::COL_DESCRIPTION, $description, $comparison);
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\UserPolicy object
     *
     * @param \WildPHP\Core\Entities\UserPolicy|ObjectCollection $userPolicy the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPolicyQuery The current query, for fluid interface
     */
    public function filterByUserPolicy($userPolicy, $comparison = null)
    {
        if ($userPolicy instanceof \WildPHP\Core\Entities\UserPolicy) {
            return $this
                ->addUsingAlias(PolicyTableMap::COL_NAME, $userPolicy->getPolicyName(), $comparison);
        } elseif ($userPolicy instanceof ObjectCollection) {
            return $this
                ->useUserPolicyQuery()
                ->filterByPrimaryKeys($userPolicy->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByUserPolicy() only accepts arguments of type \WildPHP\Core\Entities\UserPolicy or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserPolicy relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildPolicyQuery The current query, for fluid interface
     */
    public function joinUserPolicy($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserPolicy');

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
            $this->addJoinObject($join, 'UserPolicy');
        }

        return $this;
    }

    /**
     * Use the UserPolicy relation UserPolicy object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \WildPHP\Core\Entities\UserPolicyQuery A secondary query class using the current class as primary query
     */
    public function useUserPolicyQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinUserPolicy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserPolicy', '\WildPHP\Core\Entities\UserPolicyQuery');
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\GroupPolicy object
     *
     * @param \WildPHP\Core\Entities\GroupPolicy|ObjectCollection $groupPolicy the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPolicyQuery The current query, for fluid interface
     */
    public function filterByGroupPolicy($groupPolicy, $comparison = null)
    {
        if ($groupPolicy instanceof \WildPHP\Core\Entities\GroupPolicy) {
            return $this
                ->addUsingAlias(PolicyTableMap::COL_NAME, $groupPolicy->getPolicyName(), $comparison);
        } elseif ($groupPolicy instanceof ObjectCollection) {
            return $this
                ->useGroupPolicyQuery()
                ->filterByPrimaryKeys($groupPolicy->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByGroupPolicy() only accepts arguments of type \WildPHP\Core\Entities\GroupPolicy or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GroupPolicy relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildPolicyQuery The current query, for fluid interface
     */
    public function joinGroupPolicy($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GroupPolicy');

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
            $this->addJoinObject($join, 'GroupPolicy');
        }

        return $this;
    }

    /**
     * Use the GroupPolicy relation GroupPolicy object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \WildPHP\Core\Entities\GroupPolicyQuery A secondary query class using the current class as primary query
     */
    public function useGroupPolicyQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinGroupPolicy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GroupPolicy', '\WildPHP\Core\Entities\GroupPolicyQuery');
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\ModeGroupPolicy object
     *
     * @param \WildPHP\Core\Entities\ModeGroupPolicy|ObjectCollection $modeGroupPolicy the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPolicyQuery The current query, for fluid interface
     */
    public function filterByModeGroupPolicy($modeGroupPolicy, $comparison = null)
    {
        if ($modeGroupPolicy instanceof \WildPHP\Core\Entities\ModeGroupPolicy) {
            return $this
                ->addUsingAlias(PolicyTableMap::COL_NAME, $modeGroupPolicy->getPolicyName(), $comparison);
        } elseif ($modeGroupPolicy instanceof ObjectCollection) {
            return $this
                ->useModeGroupPolicyQuery()
                ->filterByPrimaryKeys($modeGroupPolicy->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByModeGroupPolicy() only accepts arguments of type \WildPHP\Core\Entities\ModeGroupPolicy or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ModeGroupPolicy relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildPolicyQuery The current query, for fluid interface
     */
    public function joinModeGroupPolicy($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ModeGroupPolicy');

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
            $this->addJoinObject($join, 'ModeGroupPolicy');
        }

        return $this;
    }

    /**
     * Use the ModeGroupPolicy relation ModeGroupPolicy object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \WildPHP\Core\Entities\ModeGroupPolicyQuery A secondary query class using the current class as primary query
     */
    public function useModeGroupPolicyQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinModeGroupPolicy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ModeGroupPolicy', '\WildPHP\Core\Entities\ModeGroupPolicyQuery');
    }

    /**
     * Filter the query by a related Group object
     * using the group_policy table as cross reference
     *
     * @param Group $group the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPolicyQuery The current query, for fluid interface
     */
    public function filterByGroup($group, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useGroupPolicyQuery()
            ->filterByGroup($group, $comparison)
            ->endUse();
    }

    /**
     * Filter the query by a related ModeGroup object
     * using the mode_group_policy table as cross reference
     *
     * @param ModeGroup $modeGroup the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPolicyQuery The current query, for fluid interface
     */
    public function filterByModeGroup($modeGroup, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useModeGroupPolicyQuery()
            ->filterByModeGroup($modeGroup, $comparison)
            ->endUse();
    }

    /**
     * Exclude object from result
     *
     * @param   ChildPolicy $policy Object to remove from the list of results
     *
     * @return $this|ChildPolicyQuery The current query, for fluid interface
     */
    public function prune($policy = null)
    {
        if ($policy) {
            $this->addUsingAlias(PolicyTableMap::COL_NAME, $policy->getName(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the policy table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PolicyTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            PolicyTableMap::clearInstancePool();
            PolicyTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(PolicyTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(PolicyTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            PolicyTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            PolicyTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // PolicyQuery
