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
use WildPHP\Core\Entities\UserPolicyRestriction as ChildUserPolicyRestriction;
use WildPHP\Core\Entities\UserPolicyRestrictionQuery as ChildUserPolicyRestrictionQuery;
use WildPHP\Core\Entities\Map\UserPolicyRestrictionTableMap;

/**
 * Base class that represents a query for the 'user_policy_channel_restriction' table.
 *
 *
 *
 * @method     ChildUserPolicyRestrictionQuery orderByUserIrcAccount($order = Criteria::ASC) Order by the user_irc_account column
 * @method     ChildUserPolicyRestrictionQuery orderByPolicyName($order = Criteria::ASC) Order by the policy_name column
 * @method     ChildUserPolicyRestrictionQuery orderByChannelId($order = Criteria::ASC) Order by the channel_id column
 *
 * @method     ChildUserPolicyRestrictionQuery groupByUserIrcAccount() Group by the user_irc_account column
 * @method     ChildUserPolicyRestrictionQuery groupByPolicyName() Group by the policy_name column
 * @method     ChildUserPolicyRestrictionQuery groupByChannelId() Group by the channel_id column
 *
 * @method     ChildUserPolicyRestrictionQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildUserPolicyRestrictionQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildUserPolicyRestrictionQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildUserPolicyRestrictionQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildUserPolicyRestrictionQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildUserPolicyRestrictionQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildUserPolicyRestrictionQuery leftJoinUserPolicy($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserPolicy relation
 * @method     ChildUserPolicyRestrictionQuery rightJoinUserPolicy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserPolicy relation
 * @method     ChildUserPolicyRestrictionQuery innerJoinUserPolicy($relationAlias = null) Adds a INNER JOIN clause to the query using the UserPolicy relation
 *
 * @method     ChildUserPolicyRestrictionQuery joinWithUserPolicy($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserPolicy relation
 *
 * @method     ChildUserPolicyRestrictionQuery leftJoinWithUserPolicy() Adds a LEFT JOIN clause and with to the query using the UserPolicy relation
 * @method     ChildUserPolicyRestrictionQuery rightJoinWithUserPolicy() Adds a RIGHT JOIN clause and with to the query using the UserPolicy relation
 * @method     ChildUserPolicyRestrictionQuery innerJoinWithUserPolicy() Adds a INNER JOIN clause and with to the query using the UserPolicy relation
 *
 * @method     ChildUserPolicyRestrictionQuery leftJoinIrcChannel($relationAlias = null) Adds a LEFT JOIN clause to the query using the IrcChannel relation
 * @method     ChildUserPolicyRestrictionQuery rightJoinIrcChannel($relationAlias = null) Adds a RIGHT JOIN clause to the query using the IrcChannel relation
 * @method     ChildUserPolicyRestrictionQuery innerJoinIrcChannel($relationAlias = null) Adds a INNER JOIN clause to the query using the IrcChannel relation
 *
 * @method     ChildUserPolicyRestrictionQuery joinWithIrcChannel($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the IrcChannel relation
 *
 * @method     ChildUserPolicyRestrictionQuery leftJoinWithIrcChannel() Adds a LEFT JOIN clause and with to the query using the IrcChannel relation
 * @method     ChildUserPolicyRestrictionQuery rightJoinWithIrcChannel() Adds a RIGHT JOIN clause and with to the query using the IrcChannel relation
 * @method     ChildUserPolicyRestrictionQuery innerJoinWithIrcChannel() Adds a INNER JOIN clause and with to the query using the IrcChannel relation
 *
 * @method     \WildPHP\Core\Entities\UserPolicyQuery|\WildPHP\Core\Entities\IrcChannelQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildUserPolicyRestriction findOne(ConnectionInterface $con = null) Return the first ChildUserPolicyRestriction matching the query
 * @method     ChildUserPolicyRestriction findOneOrCreate(ConnectionInterface $con = null) Return the first ChildUserPolicyRestriction matching the query, or a new ChildUserPolicyRestriction object populated from the query conditions when no match is found
 *
 * @method     ChildUserPolicyRestriction findOneByUserIrcAccount(string $user_irc_account) Return the first ChildUserPolicyRestriction filtered by the user_irc_account column
 * @method     ChildUserPolicyRestriction findOneByPolicyName(string $policy_name) Return the first ChildUserPolicyRestriction filtered by the policy_name column
 * @method     ChildUserPolicyRestriction findOneByChannelId(int $channel_id) Return the first ChildUserPolicyRestriction filtered by the channel_id column *

 * @method     ChildUserPolicyRestriction requirePk($key, ConnectionInterface $con = null) Return the ChildUserPolicyRestriction by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserPolicyRestriction requireOne(ConnectionInterface $con = null) Return the first ChildUserPolicyRestriction matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserPolicyRestriction requireOneByUserIrcAccount(string $user_irc_account) Return the first ChildUserPolicyRestriction filtered by the user_irc_account column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserPolicyRestriction requireOneByPolicyName(string $policy_name) Return the first ChildUserPolicyRestriction filtered by the policy_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserPolicyRestriction requireOneByChannelId(int $channel_id) Return the first ChildUserPolicyRestriction filtered by the channel_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserPolicyRestriction[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildUserPolicyRestriction objects based on current ModelCriteria
 * @method     ChildUserPolicyRestriction[]|ObjectCollection findByUserIrcAccount(string $user_irc_account) Return ChildUserPolicyRestriction objects filtered by the user_irc_account column
 * @method     ChildUserPolicyRestriction[]|ObjectCollection findByPolicyName(string $policy_name) Return ChildUserPolicyRestriction objects filtered by the policy_name column
 * @method     ChildUserPolicyRestriction[]|ObjectCollection findByChannelId(int $channel_id) Return ChildUserPolicyRestriction objects filtered by the channel_id column
 * @method     ChildUserPolicyRestriction[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class UserPolicyRestrictionQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \WildPHP\Core\Entities\Base\UserPolicyRestrictionQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'persistent', $modelName = '\\WildPHP\\Core\\Entities\\UserPolicyRestriction', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildUserPolicyRestrictionQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildUserPolicyRestrictionQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildUserPolicyRestrictionQuery) {
            return $criteria;
        }
        $query = new ChildUserPolicyRestrictionQuery();
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
     * $obj = $c->findPk(array(12, 34, 56), $con);
     * </code>
     *
     * @param array[$user_irc_account, $policy_name, $channel_id] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildUserPolicyRestriction|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(UserPolicyRestrictionTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = UserPolicyRestrictionTableMap::getInstanceFromPool(serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1]), (null === $key[2] || is_scalar($key[2]) || is_callable([$key[2], '__toString']) ? (string) $key[2] : $key[2])]))))) {
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
     * @return ChildUserPolicyRestriction A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT user_irc_account, policy_name, channel_id FROM user_policy_channel_restriction WHERE user_irc_account = :p0 AND policy_name = :p1 AND channel_id = :p2';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_STR);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_STR);
            $stmt->bindValue(':p2', $key[2], PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildUserPolicyRestriction $obj */
            $obj = new ChildUserPolicyRestriction();
            $obj->hydrate($row);
            UserPolicyRestrictionTableMap::addInstanceToPool($obj, serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1]), (null === $key[2] || is_scalar($key[2]) || is_callable([$key[2], '__toString']) ? (string) $key[2] : $key[2])]));
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
     * @return ChildUserPolicyRestriction|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildUserPolicyRestrictionQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(UserPolicyRestrictionTableMap::COL_USER_IRC_ACCOUNT, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(UserPolicyRestrictionTableMap::COL_POLICY_NAME, $key[1], Criteria::EQUAL);
        $this->addUsingAlias(UserPolicyRestrictionTableMap::COL_CHANNEL_ID, $key[2], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildUserPolicyRestrictionQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(UserPolicyRestrictionTableMap::COL_USER_IRC_ACCOUNT, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(UserPolicyRestrictionTableMap::COL_POLICY_NAME, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $cton2 = $this->getNewCriterion(UserPolicyRestrictionTableMap::COL_CHANNEL_ID, $key[2], Criteria::EQUAL);
            $cton0->addAnd($cton2);
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
     * @return $this|ChildUserPolicyRestrictionQuery The current query, for fluid interface
     */
    public function filterByUserIrcAccount($userIrcAccount = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($userIrcAccount)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserPolicyRestrictionTableMap::COL_USER_IRC_ACCOUNT, $userIrcAccount, $comparison);
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
     * @return $this|ChildUserPolicyRestrictionQuery The current query, for fluid interface
     */
    public function filterByPolicyName($policyName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($policyName)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserPolicyRestrictionTableMap::COL_POLICY_NAME, $policyName, $comparison);
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
     * @return $this|ChildUserPolicyRestrictionQuery The current query, for fluid interface
     */
    public function filterByChannelId($channelId = null, $comparison = null)
    {
        if (is_array($channelId)) {
            $useMinMax = false;
            if (isset($channelId['min'])) {
                $this->addUsingAlias(UserPolicyRestrictionTableMap::COL_CHANNEL_ID, $channelId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($channelId['max'])) {
                $this->addUsingAlias(UserPolicyRestrictionTableMap::COL_CHANNEL_ID, $channelId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserPolicyRestrictionTableMap::COL_CHANNEL_ID, $channelId, $comparison);
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\UserPolicy object
     *
     * @param \WildPHP\Core\Entities\UserPolicy $userPolicy The related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildUserPolicyRestrictionQuery The current query, for fluid interface
     */
    public function filterByUserPolicy($userPolicy, $comparison = null)
    {
        if ($userPolicy instanceof \WildPHP\Core\Entities\UserPolicy) {
            return $this
                ->addUsingAlias(UserPolicyRestrictionTableMap::COL_USER_IRC_ACCOUNT, $userPolicy->getUserIrcAccount(), $comparison)
                ->addUsingAlias(UserPolicyRestrictionTableMap::COL_POLICY_NAME, $userPolicy->getPolicyName(), $comparison);
        } else {
            throw new PropelException('filterByUserPolicy() only accepts arguments of type \WildPHP\Core\Entities\UserPolicy');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserPolicy relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildUserPolicyRestrictionQuery The current query, for fluid interface
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
     * Filter the query by a related \WildPHP\Core\Entities\IrcChannel object
     *
     * @param \WildPHP\Core\Entities\IrcChannel|ObjectCollection $ircChannel The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildUserPolicyRestrictionQuery The current query, for fluid interface
     */
    public function filterByIrcChannel($ircChannel, $comparison = null)
    {
        if ($ircChannel instanceof \WildPHP\Core\Entities\IrcChannel) {
            return $this
                ->addUsingAlias(UserPolicyRestrictionTableMap::COL_CHANNEL_ID, $ircChannel->getId(), $comparison);
        } elseif ($ircChannel instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserPolicyRestrictionTableMap::COL_CHANNEL_ID, $ircChannel->toKeyValue('PrimaryKey', 'Id'), $comparison);
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
     * @return $this|ChildUserPolicyRestrictionQuery The current query, for fluid interface
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
     * @param   ChildUserPolicyRestriction $userPolicyRestriction Object to remove from the list of results
     *
     * @return $this|ChildUserPolicyRestrictionQuery The current query, for fluid interface
     */
    public function prune($userPolicyRestriction = null)
    {
        if ($userPolicyRestriction) {
            $this->addCond('pruneCond0', $this->getAliasedColName(UserPolicyRestrictionTableMap::COL_USER_IRC_ACCOUNT), $userPolicyRestriction->getUserIrcAccount(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(UserPolicyRestrictionTableMap::COL_POLICY_NAME), $userPolicyRestriction->getPolicyName(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond2', $this->getAliasedColName(UserPolicyRestrictionTableMap::COL_CHANNEL_ID), $userPolicyRestriction->getChannelId(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1', 'pruneCond2'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the user_policy_channel_restriction table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserPolicyRestrictionTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            UserPolicyRestrictionTableMap::clearInstancePool();
            UserPolicyRestrictionTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(UserPolicyRestrictionTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(UserPolicyRestrictionTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            UserPolicyRestrictionTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            UserPolicyRestrictionTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // UserPolicyRestrictionQuery
