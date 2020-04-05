<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Vote;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlExpression;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Event;
use \Bitrix\Main\Result;
use \Bitrix\Main\Type\DateTime;
use \Bitrix\Vote\Base\BaseObject;
use \Bitrix\Vote\Vote;

Loc::loadMessages(__FILE__);

/**
 * Class VoteEventTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> COOKIE_ID int,
 * <li> AUTH_USER_ID int,
 * <li> COUNTER int,
 * <li> DATE_FIRST datetime,
 * <li> DATE_LAST datetime,
 * <li> LAST_IP string(15),
 * <li> STAT_GUEST_ID int,
 * </ul>
 *
 */
class UserTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_vote_user';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('V_TABLE_FIELD_ID'),
			),
			'COOKIE_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_AUTH_USER_ID'),
			),
			'AUTH_USER_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_AUTH_USER_ID'),
			),
			'COUNTER' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_COUNTER'),
			),
			'DATE_FIRST' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('V_TABLE_FIELD_DATE_FIRST'),
			),
			'DATE_LAST' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('V_TABLE_FIELD_DATE_LAST'),
			),
			'LAST_IP' => array(
				'data_type' => 'string',
				'size' => 15,
				'title' => Loc::getMessage('V_TABLE_FIELD_STAT_SESSION_ID')
			),
			'STAT_GUEST_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_STAT_GUEST_ID'),
			),
			'USER' => array(
				'data_type' => '\Bitrix\Main\UserTable',
				'reference' => array(
					'=this.AUTH_USER_ID' => 'ref.ID',
				),
				'join_type' => 'LEFT',
			),
		);
	}
	/**
	 * @param array $id User IDs.
	 * @param mixed $increment True - increment, false - decrement, integer - exact value.
	 * @return void
	 */
	public static function setCounter(array $id, $increment = true)
	{
		if (empty($id))
			return;

		$connection = \Bitrix\Main\Application::getInstance()->getConnection();

		$sql = intval($increment);
		if ($increment === true)
			$sql = "COUNTER+1";
		else if ($increment === false)
			$sql = "COUNTER-1";
		$connection->queryExecute("UPDATE ".self::getTableName()." SET COUNTER=".$sql." WHERE ID IN (".implode(", ", $id).")");
	}
}

class User extends BaseObject
{
	const SYSTEM_USER_ID = 0;
	static $usersIds = [];

	static $instance = null;

	/**
	 * @return void
	 * @throws ArgumentException
	 */
	public function init()
	{
/*		if ($this->id != $this->getUser()->getId())
			throw new ArgumentException("User id is wrong.");*/
	}

	/**
	 * @return int
	 */
	public function getCookieId()
	{
		global $APPLICATION;
		return intval($APPLICATION->get_cookie("VOTE_USER_ID"));
	}
	/**
	 * @return int
	 */
	public function getVotedUserId()
	{
		$cookieId = self::getCookieId();
		$filter = [
			"COOKIE_ID" => $cookieId,
			"AUTH_USER_ID"	=> intval($this->getId())
		];
		$id = implode($filter, "_");

		if ($cookieId > 0 && !array_key_exists($id, self::$usersIds) && ($res = UserTable::getList([
				"select" => ["ID"],
				"filter" => [
					"COOKIE_ID" => $cookieId,
					"AUTH_USER_ID"	=> intval($this->getId())
				]
			])->fetch()))
		{
			self::$usersIds[$id] = intval($res["ID"]);
		}
		return isset(self::$usersIds[$id]) ? self::$usersIds[$id] : 0;
	}

	/**
	 * @return void
	 */
	public function setCookieId($id)
	{
		$cookie = new \Bitrix\Main\Web\Cookie("VOTE_USER_ID", strval($id));
		\Bitrix\Main\Context::getCurrent()->getResponse()->addCookie($cookie);
	}
	/**
	 * @param null $incrementCount If true - increment, in false - decrement, null - no changes.
	 * @return int
	 */
	public function setVotedUserId($incrementCount = null)
	{
		$id = $this->getVotedUserId();
		$fields = array(
			"STAT_GUEST_ID"	=> intval($_SESSION["SESS_GUEST_ID"]),
			"DATE_LAST"		=> new DateTime(),
			"LAST_IP"		=> $_SERVER["REMOTE_ADDR"]
		);
		if ($incrementCount === true)
			$fields["COUNTER"] = new SqlExpression('?# + 1', 'COUNTER');
		else if ($incrementCount === false)
			$fields["COUNTER"] = new SqlExpression('?# - 1', 'COUNTER');

		if ($id > 0)
		{
			$dbRes = UserTable::update($id, $fields);
			$dbRes->setData(["COOKIE_ID" => $this->getCookieId()]);
		}
		else
		{
			$add = true;
			$fields = [
					"AUTH_USER_ID"	=> intval($this->getId()),
					"DATE_FIRST"	=> new DateTime(),
					"COUNTER" => ($incrementCount === true ? 1 : 0)
				] + $fields;
			if ($this->getCookieId() > 0)
			{
				$dbRes = UserTable::add(["COOKIE_ID" => $this->getCookieId()] + $fields);
				$add = !$dbRes->isSuccess();
			}
			if ($add)
			{
				$connection = \Bitrix\Main\Application::getInstance()->getConnection();
				$insert = $connection->getSqlHelper()->prepareInsert(UserTable::getTableName(), $fields);
				$connection->queryExecute(
					"INSERT INTO ".UserTable::getTableName()."(COOKIE_ID, ".$insert[0].") ".
					"SELECT MAX(COOKIE_ID) + 1, ".$insert[1] . " FROM ".UserTable::getTableName());
				$dbRes = new AddResult();
				$dbRes->setId($connection->getInsertedId());
				$dbRes->setData(UserTable::getById($dbRes->getId())->fetch());
			}
		}
		$id = $dbRes->getId();
		$fields = $dbRes->getData();
		self::$usersIds[implode([
			"COOKIE_ID" => $fields["COOKIE_ID"],
			"AUTH_USER_ID"	=> $fields["AUTH_USER_ID"]
		], "_")] = $id;
		self::setCookieId($fields["COOKIE_ID"]);
		return $id;
	}

	/**
	 * @param integer $voteId Vote ID.
	 * @return bool|int
	 */
	public function isVotedFor($voteId)
	{
		$result = false;
		if ($voteId > 0)
		{
			/** @var Vote $vote */
			$vote = Vote::loadFromId($voteId);
			$result = $vote->isVotedFor($this);
		}
		return $result;
	}
	/**
	 * @param integer $voteId Vote ID.
	 * @param integer $userId User ID.
	 * @return bool|int
	 */
	public static function isUserVotedFor($voteId, $userId)
	{
		$result = false;
		if ($voteId > 0)
		{
			/** @var Vote $vote */
			$vote = Vote::loadFromId($voteId);
			$result = $vote->isVotedFor($userId);
		}
		return $result;
	}

	/**
	 * @return User
	 */
	public static function getCurrent()
	{
		global $USER;
		if (is_null(self::$instance))
			self::$instance = self::loadFromId($USER->getId());
		return self::$instance;
	}

	public static function onUserLogin()
	{
		$_SESSION["VOTE"] = ["VOTES" => []];
	}
}