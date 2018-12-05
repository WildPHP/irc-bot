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
use WildPHP\Core\Entities\IrcChannel as ChildIrcChannel;
use WildPHP\Core\Entities\IrcChannelQuery as ChildIrcChannelQuery;
use WildPHP\Core\Entities\Map\IrcChannelTableMap;

/**
 * Base class that represents a query for the 'channel' table.
 *
 *
 *
 * @method     ChildIrcChannelQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildIrcChannelQuery orderByName($order = Criteria::ASC) Order by the name column
 * @method     ChildIrcChannelQuery orderByTopic($order = Criteria::ASC) Order by the topic column
 * @method     ChildIrcChannelQuery orderByCreatedTime($order = Criteria::ASC) Order by the created_time column
 * @method     ChildIrcChannelQuery orderByCreatedBy($order = Criteria::ASC) Order by the created_by column
 * @method     ChildIrcChannelQuery orderByModes($order = Criteria::ASC) Order by the modes column
 *
 * @method     ChildIrcChannelQuery groupById() Group by the id column
 * @method     ChildIrcChannelQuery groupByName() Group by the name column
 * @method     ChildIrcChannelQuery groupByTopic() Group by the topic column
 * @method     ChildIrcChannelQuery groupByCreatedTime() Group by the created_time column
 * @method     ChildIrcChannelQuery groupByCreatedBy() Group by the created_by column
 * @method     ChildIrcChannelQuery groupByModes() Group by the modes column
 *
 * @method     ChildIrcChannelQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildIrcChannelQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildIrcChannelQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildIrcChannelQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildIrcChannelQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildIrcChannelQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildIrcChannelQuery leftJoinUserChannel($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserChannel relation
 * @method     ChildIrcChannelQuery rightJoinUserChannel($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserChannel relation
 * @method     ChildIrcChannelQuery innerJoinUserChannel($relationAlias = null) Adds a INNER JOIN clause to the query using the UserChannel relation
 *
 * @method     ChildIrcChannelQuery joinWithUserChannel($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserChannel relation
 *
 * @method     ChildIrcChannelQuery leftJoinWithUserChannel() Adds a LEFT JOIN clause and with to the query using the UserChannel relation
 * @method     ChildIrcChannelQuery rightJoinWithUserChannel() Adds a RIGHT JOIN clause and with to the query using the UserChannel relation
 * @method     ChildIrcChannelQuery innerJoinWithUserChannel() Adds a INNER JOIN clause and with to the query using the UserChannel relation
 *
 * @method     ChildIrcChannelQuery leftJoinUserModeChannel($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserModeChannel relation
 * @method     ChildIrcChannelQuery rightJoinUserModeChannel($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserModeChannel relation
 * @method     ChildIrcChannelQuery innerJoinUserModeChannel($relationAlias = null) Adds a INNER JOIN clause to the query using the UserModeChannel relation
 *
 * @method     ChildIrcChannelQuery joinWithUserModeChannel($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserModeChannel relation
 *
 * @method     ChildIrcChannelQuery leftJoinWithUserModeChannel() Adds a LEFT JOIN clause and with to the query using the UserModeChannel relation
 * @method     ChildIrcChannelQuery rightJoinWithUserModeChannel() Adds a RIGHT JOIN clause and with to the query using the UserModeChannel relation
 * @method     ChildIrcChannelQuery innerJoinWithUserModeChannel() Adds a INNER JOIN clause and with to the query using the UserModeChannel relation
 *
 * @method     ChildIrcChannelQuery leftJoinUserPolicyRestriction($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserPolicyRestriction relation
 * @method     ChildIrcChannelQuery rightJoinUserPolicyRestriction($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserPolicyRestriction relation
 * @method     ChildIrcChannelQuery innerJoinUserPolicyRestriction($relationAlias = null) Adds a INNER JOIN clause to the query using the UserPolicyRestriction relation
 *
 * @method     ChildIrcChannelQuery joinWithUserPolicyRestriction($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserPolicyRestriction relation
 *
 * @method     ChildIrcChannelQuery leftJoinWithUserPolicyRestriction() Adds a LEFT JOIN clause and with to the query using the UserPolicyRestriction relation
 * @method     ChildIrcChannelQuery rightJoinWithUserPolicyRestriction() Adds a RIGHT JOIN clause and with to the query using the UserPolicyRestriction relation
 * @method     ChildIrcChannelQuery innerJoinWithUserPolicyRestriction() Adds a INNER JOIN clause and with to the query using the UserPolicyRestriction relation
 *
 * @method     ChildIrcChannelQuery leftJoinGroupPolicyRestriction($relationAlias = null) Adds a LEFT JOIN clause to the query using the GroupPolicyRestriction relation
 * @method     ChildIrcChannelQuery rightJoinGroupPolicyRestriction($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GroupPolicyRestriction relation
 * @method     ChildIrcChannelQuery innerJoinGroupPolicyRestriction($relationAlias = null) Adds a INNER JOIN clause to the query using the GroupPolicyRestriction relation
 *
 * @method     ChildIrcChannelQuery joinWithGroupPolicyRestriction($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the GroupPolicyRestriction relation
 *
 * @method     ChildIrcChannelQuery leftJoinWithGroupPolicyRestriction() Adds a LEFT JOIN clause and with to the query using the GroupPolicyRestriction relation
 * @method     ChildIrcChannelQuery rightJoinWithGroupPolicyRestriction() Adds a RIGHT JOIN clause and with to the query using the GroupPolicyRestriction relation
 * @method     ChildIrcChannelQuery innerJoinWithGroupPolicyRestriction() Adds a INNER JOIN clause and with to the query using the GroupPolicyRestriction relation
 *
 * @method     \WildPHP\Core\Entities\UserChannelQuery|\WildPHP\Core\Entities\UserModeChannelQuery|\WildPHP\Core\Entities\UserPolicyRestrictionQuery|\WildPHP\Core\Entities\GroupPolicyRestrictionQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildIrcChannel findOne(ConnectionInterface $con = null) Return the first ChildIrcChannel matching the query
 * @method     ChildIrcChannel findOneOrCreate(ConnectionInterface $con = null) Return the first ChildIrcChannel matching the query, or a new ChildIrcChannel object populated from the query conditions when no match is found
 *
 * @method     ChildIrcChannel findOneById(int $id) Return the first ChildIrcChannel filtered by the id column
 * @method     ChildIrcChannel findOneByName(string $name) Return the first ChildIrcChannel filtered by the name column
 * @method     ChildIrcChannel findOneByTopic(string $topic) Return the first ChildIrcChannel filtered by the topic column
 * @method     ChildIrcChannel findOneByCreatedTime(string $created_time) Return the first ChildIrcChannel filtered by the created_time column
 * @method     ChildIrcChannel findOneByCreatedBy(string $created_by) Return the first ChildIrcChannel filtered by the created_by column
 * @method     ChildIrcChannel findOneByModes(string $modes) Return the first ChildIrcChannel filtered by the modes column *

 * @method     ChildIrcChannel requirePk($key, ConnectionInterface $con = null) Return the ChildIrcChannel by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildIrcChannel requireOne(ConnectionInterface $con = null) Return the first ChildIrcChannel matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildIrcChannel requireOneById(int $id) Return the first ChildIrcChannel filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildIrcChannel requireOneByName(string $name) Return the first ChildIrcChannel filtered by the name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildIrcChannel requireOneByTopic(string $topic) Return the first ChildIrcChannel filtered by the topic column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildIrcChannel requireOneByCreatedTime(string $created_time) Return the first ChildIrcChannel filtered by the created_time column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildIrcChannel requireOneByCreatedBy(string $created_by) Return the first ChildIrcChannel filtered by the created_by column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildIrcChannel requireOneByModes(string $modes) Return the first ChildIrcChannel filtered by the modes column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildIrcChannel[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildIrcChannel objects based on current ModelCriteria
 * @method     ChildIrcChannel[]|ObjectCollection findById(int $id) Return ChildIrcChannel objects filtered by the id column
 * @method     ChildIrcChannel[]|ObjectCollection findByName(string $name) Return ChildIrcChannel objects filtered by the name column
 * @method     ChildIrcChannel[]|ObjectCollection findByTopic(string $topic) Return ChildIrcChannel objects filtered by the topic column
 * @method     ChildIrcChannel[]|ObjectCollection findByCreatedTime(string $created_time) Return ChildIrcChannel objects filtered by the created_time column
 * @method     ChildIrcChannel[]|ObjectCollection findByCreatedBy(string $created_by) Return ChildIrcChannel objects filtered by the created_by column
 * @method     ChildIrcChannel[]|ObjectCollection findByModes(string $modes) Return ChildIrcChannel objects filtered by the modes column
 * @method     ChildIrcChannel[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class IrcChannelQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \WildPHP\Core\Entities\Base\IrcChannelQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'persistent', $modelName = '\\WildPHP\\Core\\Entities\\IrcChannel', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildIrcChannelQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildIrcChannelQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildIrcChannelQuery) {
            return $criteria;
        }
        $query = new ChildIrcChannelQuery();
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
     * @return ChildIrcChannel|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(IrcChannelTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = IrcChannelTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildIrcChannel A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, name, topic, created_time, created_by, modes FROM channel WHERE id = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildIrcChannel $obj */
            $obj = new ChildIrcChannel();
            $obj->hydrate($row);
            IrcChannelTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildIrcChannel|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildIrcChannelQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(IrcChannelTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildIrcChannelQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(IrcChannelTableMap::COL_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildIrcChannelQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(IrcChannelTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(IrcChannelTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(IrcChannelTableMap::COL_ID, $id, $comparison);
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
     * @return $this|ChildIrcChannelQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(IrcChannelTableMap::COL_NAME, $name, $comparison);
    }

    /**
     * Filter the query on the topic column
     *
     * Example usage:
     * <code>
     * $query->filterByTopic('fooValue');   // WHERE topic = 'fooValue'
     * $query->filterByTopic('%fooValue%', Criteria::LIKE); // WHERE topic LIKE '%fooValue%'
     * </code>
     *
     * @param     string $topic The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildIrcChannelQuery The current query, for fluid interface
     */
    public function filterByTopic($topic = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($topic)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(IrcChannelTableMap::COL_TOPIC, $topic, $comparison);
    }

    /**
     * Filter the query on the created_time column
     *
     * Example usage:
     * <code>
     * $query->filterByCreatedTime('2011-03-14'); // WHERE created_time = '2011-03-14'
     * $query->filterByCreatedTime('now'); // WHERE created_time = '2011-03-14'
     * $query->filterByCreatedTime(array('max' => 'yesterday')); // WHERE created_time > '2011-03-13'
     * </code>
     *
     * @param     mixed $createdTime The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildIrcChannelQuery The current query, for fluid interface
     */
    public function filterByCreatedTime($createdTime = null, $comparison = null)
    {
        if (is_array($createdTime)) {
            $useMinMax = false;
            if (isset($createdTime['min'])) {
                $this->addUsingAlias(IrcChannelTableMap::COL_CREATED_TIME, $createdTime['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdTime['max'])) {
                $this->addUsingAlias(IrcChannelTableMap::COL_CREATED_TIME, $createdTime['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(IrcChannelTableMap::COL_CREATED_TIME, $createdTime, $comparison);
    }

    /**
     * Filter the query on the created_by column
     *
     * Example usage:
     * <code>
     * $query->filterByCreatedBy('fooValue');   // WHERE created_by = 'fooValue'
     * $query->filterByCreatedBy('%fooValue%', Criteria::LIKE); // WHERE created_by LIKE '%fooValue%'
     * </code>
     *
     * @param     string $createdBy The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildIrcChannelQuery The current query, for fluid interface
     */
    public function filterByCreatedBy($createdBy = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($createdBy)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(IrcChannelTableMap::COL_CREATED_BY, $createdBy, $comparison);
    }

    /**
     * Filter the query on the modes column
     *
     * Example usage:
     * <code>
     * $query->filterByModes('fooValue');   // WHERE modes = 'fooValue'
     * $query->filterByModes('%fooValue%', Criteria::LIKE); // WHERE modes LIKE '%fooValue%'
     * </code>
     *
     * @param     string $modes The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildIrcChannelQuery The current query, for fluid interface
     */
    public function filterByModes($modes = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($modes)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(IrcChannelTableMap::COL_MODES, $modes, $comparison);
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\UserChannel object
     *
     * @param \WildPHP\Core\Entities\UserChannel|ObjectCollection $userChannel the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildIrcChannelQuery The current query, for fluid interface
     */
    public function filterByUserChannel($userChannel, $comparison = null)
    {
        if ($userChannel instanceof \WildPHP\Core\Entities\UserChannel) {
            return $this
                ->addUsingAlias(IrcChannelTableMap::COL_ID, $userChannel->getChannelId(), $comparison);
        } elseif ($userChannel instanceof ObjectCollection) {
            return $this
                ->useUserChannelQuery()
                ->filterByPrimaryKeys($userChannel->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByUserChannel() only accepts arguments of type \WildPHP\Core\Entities\UserChannel or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserChannel relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildIrcChannelQuery The current query, for fluid interface
     */
    public function joinUserChannel($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserChannel');

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
            $this->addJoinObject($join, 'UserChannel');
        }

        return $this;
    }

    /**
     * Use the UserChannel relation UserChannel object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \WildPHP\Core\Entities\UserChannelQuery A secondary query class using the current class as primary query
     */
    public function useUserChannelQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinUserChannel($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserChannel', '\WildPHP\Core\Entities\UserChannelQuery');
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\UserModeChannel object
     *
     * @param \WildPHP\Core\Entities\UserModeChannel|ObjectCollection $userModeChannel the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildIrcChannelQuery The current query, for fluid interface
     */
    public function filterByUserModeChannel($userModeChannel, $comparison = null)
    {
        if ($userModeChannel instanceof \WildPHP\Core\Entities\UserModeChannel) {
            return $this
                ->addUsingAlias(IrcChannelTableMap::COL_ID, $userModeChannel->getChannelId(), $comparison);
        } elseif ($userModeChannel instanceof ObjectCollection) {
            return $this
                ->useUserModeChannelQuery()
                ->filterByPrimaryKeys($userModeChannel->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByUserModeChannel() only accepts arguments of type \WildPHP\Core\Entities\UserModeChannel or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserModeChannel relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildIrcChannelQuery The current query, for fluid interface
     */
    public function joinUserModeChannel($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserModeChannel');

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
            $this->addJoinObject($join, 'UserModeChannel');
        }

        return $this;
    }

    /**
     * Use the UserModeChannel relation UserModeChannel object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \WildPHP\Core\Entities\UserModeChannelQuery A secondary query class using the current class as primary query
     */
    public function useUserModeChannelQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinUserModeChannel($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserModeChannel', '\WildPHP\Core\Entities\UserModeChannelQuery');
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\UserPolicyRestriction object
     *
     * @param \WildPHP\Core\Entities\UserPolicyRestriction|ObjectCollection $userPolicyRestriction the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildIrcChannelQuery The current query, for fluid interface
     */
    public function filterByUserPolicyRestriction($userPolicyRestriction, $comparison = null)
    {
        if ($userPolicyRestriction instanceof \WildPHP\Core\Entities\UserPolicyRestriction) {
            return $this
                ->addUsingAlias(IrcChannelTableMap::COL_ID, $userPolicyRestriction->getChannelId(), $comparison);
        } elseif ($userPolicyRestriction instanceof ObjectCollection) {
            return $this
                ->useUserPolicyRestrictionQuery()
                ->filterByPrimaryKeys($userPolicyRestriction->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByUserPolicyRestriction() only accepts arguments of type \WildPHP\Core\Entities\UserPolicyRestriction or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserPolicyRestriction relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildIrcChannelQuery The current query, for fluid interface
     */
    public function joinUserPolicyRestriction($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserPolicyRestriction');

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
            $this->addJoinObject($join, 'UserPolicyRestriction');
        }

        return $this;
    }

    /**
     * Use the UserPolicyRestriction relation UserPolicyRestriction object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \WildPHP\Core\Entities\UserPolicyRestrictionQuery A secondary query class using the current class as primary query
     */
    public function useUserPolicyRestrictionQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinUserPolicyRestriction($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserPolicyRestriction', '\WildPHP\Core\Entities\UserPolicyRestrictionQuery');
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\GroupPolicyRestriction object
     *
     * @param \WildPHP\Core\Entities\GroupPolicyRestriction|ObjectCollection $groupPolicyRestriction the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildIrcChannelQuery The current query, for fluid interface
     */
    public function filterByGroupPolicyRestriction($groupPolicyRestriction, $comparison = null)
    {
        if ($groupPolicyRestriction instanceof \WildPHP\Core\Entities\GroupPolicyRestriction) {
            return $this
                ->addUsingAlias(IrcChannelTableMap::COL_ID, $groupPolicyRestriction->getChannelId(), $comparison);
        } elseif ($groupPolicyRestriction instanceof ObjectCollection) {
            return $this
                ->useGroupPolicyRestrictionQuery()
                ->filterByPrimaryKeys($groupPolicyRestriction->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByGroupPolicyRestriction() only accepts arguments of type \WildPHP\Core\Entities\GroupPolicyRestriction or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GroupPolicyRestriction relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildIrcChannelQuery The current query, for fluid interface
     */
    public function joinGroupPolicyRestriction($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GroupPolicyRestriction');

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
            $this->addJoinObject($join, 'GroupPolicyRestriction');
        }

        return $this;
    }

    /**
     * Use the GroupPolicyRestriction relation GroupPolicyRestriction object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \WildPHP\Core\Entities\GroupPolicyRestrictionQuery A secondary query class using the current class as primary query
     */
    public function useGroupPolicyRestrictionQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinGroupPolicyRestriction($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GroupPolicyRestriction', '\WildPHP\Core\Entities\GroupPolicyRestrictionQuery');
    }

    /**
     * Filter the query by a related IrcUser object
     * using the user_channel table as cross reference
     *
     * @param IrcUser $ircUser the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildIrcChannelQuery The current query, for fluid interface
     */
    public function filterByIrcUser($ircUser, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useUserChannelQuery()
            ->filterByIrcUser($ircUser, $comparison)
            ->endUse();
    }

    /**
     * Exclude object from result
     *
     * @param   ChildIrcChannel $ircChannel Object to remove from the list of results
     *
     * @return $this|ChildIrcChannelQuery The current query, for fluid interface
     */
    public function prune($ircChannel = null)
    {
        if ($ircChannel) {
            $this->addUsingAlias(IrcChannelTableMap::COL_ID, $ircChannel->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the channel table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(IrcChannelTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            IrcChannelTableMap::clearInstancePool();
            IrcChannelTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(IrcChannelTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(IrcChannelTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            IrcChannelTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            IrcChannelTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // IrcChannelQuery
