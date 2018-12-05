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
use WildPHP\Core\Entities\IrcUser as ChildIrcUser;
use WildPHP\Core\Entities\IrcUserQuery as ChildIrcUserQuery;
use WildPHP\Core\Entities\Map\IrcUserTableMap;

/**
 * Base class that represents a query for the 'user' table.
 *
 *
 *
 * @method     ChildIrcUserQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildIrcUserQuery orderByNickname($order = Criteria::ASC) Order by the nickname column
 * @method     ChildIrcUserQuery orderByUsername($order = Criteria::ASC) Order by the username column
 * @method     ChildIrcUserQuery orderByRealname($order = Criteria::ASC) Order by the realname column
 * @method     ChildIrcUserQuery orderByHostname($order = Criteria::ASC) Order by the hostname column
 * @method     ChildIrcUserQuery orderByIrcAccount($order = Criteria::ASC) Order by the irc_account column
 * @method     ChildIrcUserQuery orderByLastSeen($order = Criteria::ASC) Order by the last_seen column
 *
 * @method     ChildIrcUserQuery groupById() Group by the id column
 * @method     ChildIrcUserQuery groupByNickname() Group by the nickname column
 * @method     ChildIrcUserQuery groupByUsername() Group by the username column
 * @method     ChildIrcUserQuery groupByRealname() Group by the realname column
 * @method     ChildIrcUserQuery groupByHostname() Group by the hostname column
 * @method     ChildIrcUserQuery groupByIrcAccount() Group by the irc_account column
 * @method     ChildIrcUserQuery groupByLastSeen() Group by the last_seen column
 *
 * @method     ChildIrcUserQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildIrcUserQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildIrcUserQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildIrcUserQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildIrcUserQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildIrcUserQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildIrcUserQuery leftJoinUserChannel($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserChannel relation
 * @method     ChildIrcUserQuery rightJoinUserChannel($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserChannel relation
 * @method     ChildIrcUserQuery innerJoinUserChannel($relationAlias = null) Adds a INNER JOIN clause to the query using the UserChannel relation
 *
 * @method     ChildIrcUserQuery joinWithUserChannel($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserChannel relation
 *
 * @method     ChildIrcUserQuery leftJoinWithUserChannel() Adds a LEFT JOIN clause and with to the query using the UserChannel relation
 * @method     ChildIrcUserQuery rightJoinWithUserChannel() Adds a RIGHT JOIN clause and with to the query using the UserChannel relation
 * @method     ChildIrcUserQuery innerJoinWithUserChannel() Adds a INNER JOIN clause and with to the query using the UserChannel relation
 *
 * @method     ChildIrcUserQuery leftJoinUserModeChannel($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserModeChannel relation
 * @method     ChildIrcUserQuery rightJoinUserModeChannel($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserModeChannel relation
 * @method     ChildIrcUserQuery innerJoinUserModeChannel($relationAlias = null) Adds a INNER JOIN clause to the query using the UserModeChannel relation
 *
 * @method     ChildIrcUserQuery joinWithUserModeChannel($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserModeChannel relation
 *
 * @method     ChildIrcUserQuery leftJoinWithUserModeChannel() Adds a LEFT JOIN clause and with to the query using the UserModeChannel relation
 * @method     ChildIrcUserQuery rightJoinWithUserModeChannel() Adds a RIGHT JOIN clause and with to the query using the UserModeChannel relation
 * @method     ChildIrcUserQuery innerJoinWithUserModeChannel() Adds a INNER JOIN clause and with to the query using the UserModeChannel relation
 *
 * @method     \WildPHP\Core\Entities\UserChannelQuery|\WildPHP\Core\Entities\UserModeChannelQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildIrcUser findOne(ConnectionInterface $con = null) Return the first ChildIrcUser matching the query
 * @method     ChildIrcUser findOneOrCreate(ConnectionInterface $con = null) Return the first ChildIrcUser matching the query, or a new ChildIrcUser object populated from the query conditions when no match is found
 *
 * @method     ChildIrcUser findOneById(int $id) Return the first ChildIrcUser filtered by the id column
 * @method     ChildIrcUser findOneByNickname(string $nickname) Return the first ChildIrcUser filtered by the nickname column
 * @method     ChildIrcUser findOneByUsername(string $username) Return the first ChildIrcUser filtered by the username column
 * @method     ChildIrcUser findOneByRealname(string $realname) Return the first ChildIrcUser filtered by the realname column
 * @method     ChildIrcUser findOneByHostname(string $hostname) Return the first ChildIrcUser filtered by the hostname column
 * @method     ChildIrcUser findOneByIrcAccount(string $irc_account) Return the first ChildIrcUser filtered by the irc_account column
 * @method     ChildIrcUser findOneByLastSeen(string $last_seen) Return the first ChildIrcUser filtered by the last_seen column *

 * @method     ChildIrcUser requirePk($key, ConnectionInterface $con = null) Return the ChildIrcUser by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildIrcUser requireOne(ConnectionInterface $con = null) Return the first ChildIrcUser matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildIrcUser requireOneById(int $id) Return the first ChildIrcUser filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildIrcUser requireOneByNickname(string $nickname) Return the first ChildIrcUser filtered by the nickname column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildIrcUser requireOneByUsername(string $username) Return the first ChildIrcUser filtered by the username column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildIrcUser requireOneByRealname(string $realname) Return the first ChildIrcUser filtered by the realname column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildIrcUser requireOneByHostname(string $hostname) Return the first ChildIrcUser filtered by the hostname column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildIrcUser requireOneByIrcAccount(string $irc_account) Return the first ChildIrcUser filtered by the irc_account column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildIrcUser requireOneByLastSeen(string $last_seen) Return the first ChildIrcUser filtered by the last_seen column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildIrcUser[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildIrcUser objects based on current ModelCriteria
 * @method     ChildIrcUser[]|ObjectCollection findById(int $id) Return ChildIrcUser objects filtered by the id column
 * @method     ChildIrcUser[]|ObjectCollection findByNickname(string $nickname) Return ChildIrcUser objects filtered by the nickname column
 * @method     ChildIrcUser[]|ObjectCollection findByUsername(string $username) Return ChildIrcUser objects filtered by the username column
 * @method     ChildIrcUser[]|ObjectCollection findByRealname(string $realname) Return ChildIrcUser objects filtered by the realname column
 * @method     ChildIrcUser[]|ObjectCollection findByHostname(string $hostname) Return ChildIrcUser objects filtered by the hostname column
 * @method     ChildIrcUser[]|ObjectCollection findByIrcAccount(string $irc_account) Return ChildIrcUser objects filtered by the irc_account column
 * @method     ChildIrcUser[]|ObjectCollection findByLastSeen(string $last_seen) Return ChildIrcUser objects filtered by the last_seen column
 * @method     ChildIrcUser[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class IrcUserQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \WildPHP\Core\Entities\Base\IrcUserQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'persistent', $modelName = '\\WildPHP\\Core\\Entities\\IrcUser', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildIrcUserQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildIrcUserQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildIrcUserQuery) {
            return $criteria;
        }
        $query = new ChildIrcUserQuery();
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
     * @return ChildIrcUser|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(IrcUserTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = IrcUserTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildIrcUser A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, nickname, username, realname, hostname, irc_account, last_seen FROM user WHERE id = :p0';
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
            /** @var ChildIrcUser $obj */
            $obj = new ChildIrcUser();
            $obj->hydrate($row);
            IrcUserTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildIrcUser|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildIrcUserQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(IrcUserTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildIrcUserQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(IrcUserTableMap::COL_ID, $keys, Criteria::IN);
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
     * @return $this|ChildIrcUserQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(IrcUserTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(IrcUserTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(IrcUserTableMap::COL_ID, $id, $comparison);
    }

    /**
     * Filter the query on the nickname column
     *
     * Example usage:
     * <code>
     * $query->filterByNickname('fooValue');   // WHERE nickname = 'fooValue'
     * $query->filterByNickname('%fooValue%', Criteria::LIKE); // WHERE nickname LIKE '%fooValue%'
     * </code>
     *
     * @param     string $nickname The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildIrcUserQuery The current query, for fluid interface
     */
    public function filterByNickname($nickname = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($nickname)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(IrcUserTableMap::COL_NICKNAME, $nickname, $comparison);
    }

    /**
     * Filter the query on the username column
     *
     * Example usage:
     * <code>
     * $query->filterByUsername('fooValue');   // WHERE username = 'fooValue'
     * $query->filterByUsername('%fooValue%', Criteria::LIKE); // WHERE username LIKE '%fooValue%'
     * </code>
     *
     * @param     string $username The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildIrcUserQuery The current query, for fluid interface
     */
    public function filterByUsername($username = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($username)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(IrcUserTableMap::COL_USERNAME, $username, $comparison);
    }

    /**
     * Filter the query on the realname column
     *
     * Example usage:
     * <code>
     * $query->filterByRealname('fooValue');   // WHERE realname = 'fooValue'
     * $query->filterByRealname('%fooValue%', Criteria::LIKE); // WHERE realname LIKE '%fooValue%'
     * </code>
     *
     * @param     string $realname The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildIrcUserQuery The current query, for fluid interface
     */
    public function filterByRealname($realname = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($realname)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(IrcUserTableMap::COL_REALNAME, $realname, $comparison);
    }

    /**
     * Filter the query on the hostname column
     *
     * Example usage:
     * <code>
     * $query->filterByHostname('fooValue');   // WHERE hostname = 'fooValue'
     * $query->filterByHostname('%fooValue%', Criteria::LIKE); // WHERE hostname LIKE '%fooValue%'
     * </code>
     *
     * @param     string $hostname The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildIrcUserQuery The current query, for fluid interface
     */
    public function filterByHostname($hostname = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($hostname)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(IrcUserTableMap::COL_HOSTNAME, $hostname, $comparison);
    }

    /**
     * Filter the query on the irc_account column
     *
     * Example usage:
     * <code>
     * $query->filterByIrcAccount('fooValue');   // WHERE irc_account = 'fooValue'
     * $query->filterByIrcAccount('%fooValue%', Criteria::LIKE); // WHERE irc_account LIKE '%fooValue%'
     * </code>
     *
     * @param     string $ircAccount The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildIrcUserQuery The current query, for fluid interface
     */
    public function filterByIrcAccount($ircAccount = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($ircAccount)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(IrcUserTableMap::COL_IRC_ACCOUNT, $ircAccount, $comparison);
    }

    /**
     * Filter the query on the last_seen column
     *
     * Example usage:
     * <code>
     * $query->filterByLastSeen('2011-03-14'); // WHERE last_seen = '2011-03-14'
     * $query->filterByLastSeen('now'); // WHERE last_seen = '2011-03-14'
     * $query->filterByLastSeen(array('max' => 'yesterday')); // WHERE last_seen > '2011-03-13'
     * </code>
     *
     * @param     mixed $lastSeen The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildIrcUserQuery The current query, for fluid interface
     */
    public function filterByLastSeen($lastSeen = null, $comparison = null)
    {
        if (is_array($lastSeen)) {
            $useMinMax = false;
            if (isset($lastSeen['min'])) {
                $this->addUsingAlias(IrcUserTableMap::COL_LAST_SEEN, $lastSeen['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($lastSeen['max'])) {
                $this->addUsingAlias(IrcUserTableMap::COL_LAST_SEEN, $lastSeen['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(IrcUserTableMap::COL_LAST_SEEN, $lastSeen, $comparison);
    }

    /**
     * Filter the query by a related \WildPHP\Core\Entities\UserChannel object
     *
     * @param \WildPHP\Core\Entities\UserChannel|ObjectCollection $userChannel the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildIrcUserQuery The current query, for fluid interface
     */
    public function filterByUserChannel($userChannel, $comparison = null)
    {
        if ($userChannel instanceof \WildPHP\Core\Entities\UserChannel) {
            return $this
                ->addUsingAlias(IrcUserTableMap::COL_ID, $userChannel->getUserId(), $comparison);
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
     * @return $this|ChildIrcUserQuery The current query, for fluid interface
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
     * @return ChildIrcUserQuery The current query, for fluid interface
     */
    public function filterByUserModeChannel($userModeChannel, $comparison = null)
    {
        if ($userModeChannel instanceof \WildPHP\Core\Entities\UserModeChannel) {
            return $this
                ->addUsingAlias(IrcUserTableMap::COL_ID, $userModeChannel->getUserId(), $comparison);
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
     * @return $this|ChildIrcUserQuery The current query, for fluid interface
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
     * Filter the query by a related IrcChannel object
     * using the user_channel table as cross reference
     *
     * @param IrcChannel $ircChannel the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildIrcUserQuery The current query, for fluid interface
     */
    public function filterByIrcChannel($ircChannel, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useUserChannelQuery()
            ->filterByIrcChannel($ircChannel, $comparison)
            ->endUse();
    }

    /**
     * Exclude object from result
     *
     * @param   ChildIrcUser $ircUser Object to remove from the list of results
     *
     * @return $this|ChildIrcUserQuery The current query, for fluid interface
     */
    public function prune($ircUser = null)
    {
        if ($ircUser) {
            $this->addUsingAlias(IrcUserTableMap::COL_ID, $ircUser->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the user table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(IrcUserTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            IrcUserTableMap::clearInstancePool();
            IrcUserTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(IrcUserTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(IrcUserTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            IrcUserTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            IrcUserTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // IrcUserQuery
