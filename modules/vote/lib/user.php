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
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use \Bitrix\Vote\Base\BaseObject;
use Bitrix\Main\ErrorCollection;
Loc::loadMessages(__FILE__);

/**
 * Class VoteEventTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> STAT_GUEST_ID int,
 * <li> AUTH_USER_ID int,
 * <li> COUNTER int,
 * <li> DATE_FIRST datetime,
 * <li> DATE_LAST datetime,
 * <li> LAST_IP string(15),
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
			'STAT_GUEST_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_STAT_GUEST_ID'),
			),
			'AUTH_USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
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

/**
 * Class VoteEventQuestionTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> EVENT_ID int,
 * <li> QUESTION_ID int,
 * </ul>
 **/
class VoteEventQuestionTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_vote_event_question';
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
			'EVENT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_EVENT_ID'),
			),
			'QUESTION_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_QUESTION_ID'),
			),
			'VOTE' => array(
				'data_type' => '\Bitrix\Vote\EventTable',
				'reference' => array(
					'=this.EVENT_ID' => 'ref.ID',
				),
				'join_type' => 'RIGHT',
			),
			'ANSWER' => array(
				'data_type' => '\Bitrix\Vote\EventAnswerTable',
				'reference' => array(
					'=this.ID' => 'ref.EVENT_QUESTION_ID',
				),
				'join_type' => 'LEFT',
			)
		);
	}
}/**
 * Class VoteEventAnswerTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> EVENT_QUESTION_ID int,
 * <li> ANSWER_ID int,
 * <li> MESSAGE text,
 * </ul>
 *
 */
class VoteEventAnswer extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_vote_event_answer';
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
			'EVENT_QUESTION_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_EVENT_ID'),
			),
			'ANSWER_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_ANSWER_ID'),
			),
			'MESSAGE' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('V_TABLE_FIELD_MESSAGE'),
			),
			'VOTE_USER' => array(

			),
			'USER' => array(
				'data_type' => '\Bitrix\Main\UserTable',
				'reference' => array(
					'=this.ID' => 'ref.EVENT_QUESTION_ID',
				),
				'join_type' => 'LEFT',
			)
		);
	}
}

class User extends BaseObject
{
	const SYSTEM_USER_ID = 0;

	static $instance = null;

	/**
	 * User constructor.
	 * @param $id
	 */
	public function __construct($id)
	{
		$this->id = $id;
		$this->errorCollection = new ErrorCollection;
		$this->init();
	}

	/**
	 * @return void
	 * @throws ArgumentException
	 */
	public function init()
	{
		if ($this->id != $this->getUser()->getId())
			throw new ArgumentException("User id is wrong.");
	}

	/**
	 * @return int
	 */
	public function getVotedUserId()
	{
		global $APPLICATION;
		return intval($APPLICATION->get_cookie("VOTE_USER_ID"));
	}

	/**
	 * @param null $incrementCount If true - increment, in false - decrement, null - no changes.
	 * @return int
	 */
	public function setVotedUserId($incrementCount = null)
	{
		global $APPLICATION;
		$cookieUserId = intval($APPLICATION->get_cookie("VOTE_USER_ID"));

		$dbRes = false;
		$fields = array(
			"STAT_GUEST_ID"	=> intval($_SESSION["SESS_GUEST_ID"]),
			"DATE_LAST"		=> new DateTime(),
			"LAST_IP"		=> $_SERVER["REMOTE_ADDR"]
		);
		if ($incrementCount == true)
			$fields["COUNTER"] = new SqlExpression('?# + 1', 'COUNTER');
		else if ($incrementCount == false)
			$fields["COUNTER"] = new SqlExpression('?# - 1', 'COUNTER');

		if ($cookieUserId > 0)
		{
			$dbRes = UserTable::update(
				array(
					"ID" => $cookieUserId,
					"AUTH_USER_ID"	=> intval($this->getUser()->getId())
				),
				$fields
			);
		}
		if (!$dbRes || !$dbRes->getAffectedRowsCount())
		{
			try
			{
				$fields["COUNTER"] = ($incrementCount == true ? 1 : 0);

				$dbRes = UserTable::add(
					array(
						"AUTH_USER_ID"	=> intval($this->getUser()->getId()),
						"DATE_FIRST"	=> new DateTime(),
					) + $fields
				);
			}
			catch (\Bitrix\Main\DB\SqlException $e)
			{
			}
		}
		$cookieUserId = ($dbRes ? $dbRes->getId() : 0);
		if (is_array($cookieUserId))
			$cookieUserId = $cookieUserId["ID"];

		$APPLICATION->set_cookie("VOTE_USER_ID", $cookieUserId."");

		return intval($cookieUserId);
	}

	/**
	 * @param integer $voteId Vote ID.
	 * @return bool|int
	 */
	public function isVotedFor($voteId)
	{
		if ($voteId <= 0)
			return false;
		$vote = Vote::loadFromId($voteId);
		return \CVote::UserAlreadyVote($voteId, self::getVotedUserId(), $vote["UNIQUE_TYPE"], $vote["KEEP_IP_SEC"], $this->getUser()->getId());
	}

	/**
	 * @param integer $voteId Vote ID.
	 * @param integer $userId User ID.
	 * @return bool|int
	 */
	public static function isUserVotedFor($voteId, $userId)
	{
		if ($voteId <= 0)
			return false;
		$vote = Vote::loadFromId($voteId);
		return \CVote::UserAlreadyVote($voteId, 0, $vote["UNIQUE_TYPE"], $vote["KEEP_IP_SEC"], $userId);
	}

	public static function getVotingMask(array $vote, array $user)
	{
		global $DB, $USER;
		$voteId = intval($vote["ID"]);
		$delay = intval($vote["DELAY"]);
		$uniqueType = $voteId > 0 ? intval($vote["UNIQUE_TYPE"]) : 0;
		$voteUserId = intval($user["COOKIE_ID"]);

		$userId = intval($user["ID"]);
		$result = array();
		$checkedMask = 0;

		$arSqlSearch = array();
		$arSqlSelect = array("VE.ID");

		while (($uniqueType & $checkedMask) != $uniqueType)
		{
			//One session
			if (($uniqueType & 1) && ($checkedMask < 1) && ($checkedMask |= 1))
			{
				if (IsModuleInstalled('statistic') && array_key_exists($voteId, $_SESSION["VOTE"]["VOTES"]))
					$result[] = 1;
			}
			//Same cookie
			if (($uniqueType & 2) && ($checkedMask < 2) && ($checkedMask |= 2))
			{
				$arSqlSelect[] = "VE.VOTE_USER_ID";
				$arSqlSearch[] = "VE.VOTE_USER_ID='".$voteUserId."'";
			}
			// Same IP
			if (($uniqueType & 4) && ($checkedMask < 4) && ($checkedMask |= 4))
			{
				$arSqlSelect[] = "TIMESTAMPDIFF(SECOND, VE.DATE_VOTE, NOW()) AS KEEP_IP_SEC";
				$arSqlSearch[] = "VE.IP='".$DB->ForSql($_SERVER["REMOTE_ADDR"], 15)."'".($delay > 0 ?
						" AND (FROM_UNIXTIME(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - ".$delay.") <= VE.DATE_VOTE)" : "");
			}
			// Same User ID
			if (($uniqueType & 8) && ($checkedMask < 8) && ($checkedMask |= 8))
			{
				if ($userId <= 0 || $userId == $USER->GetID() && isset($_SESSION["VOTE"]["VOTES"][$voteId]))
				{
					$result |= 8;
				}
				else
				{
					$arSqlSelect[] = "VU.AUTH_USER_ID";
					$arSqlSearch[] = "VU.AUTH_USER_ID=".$userId;
				}
			}
			// Check Users from due register date
			if (($uniqueType & 16) && ($checkedMask < 16) && ($checkedMask |= 16))
			{
				$userRegister = 0;
				if ($userId > 0)
				{
					$us = (isset($user['DATE_REGISTER']) ? $user : \CUser::GetByID($userId)->fetch());
					$userRegister = MakeTimeStamp($us['DATE_REGISTER']);
				}
				if (!(0 < $userRegister && $userRegister < MakeTimeStamp($vote['DATE_START'])))
				{
					$result |= 16;
				}
			}
		}

		if (!empty($arSqlSearch))
		{
			$dbRes = $DB->Query("SELECT ".implode(",", $arSqlSelect)."
				FROM b_vote_event VE
				LEFT JOIN b_vote_user VU ON (VE.VOTE_USER_ID = VU.ID)
				WHERE VE.VOTE_ID=".$voteId." AND ((".implode(") OR (", $arSqlSearch)."))", false, 'File: '.__FILE__.' Line:'.__LINE__);
			while ($res = $dbRes->Fetch())
			{
				$_SESSION["VOTE"]["VOTES"][$voteId] = $res["ID"];
				if (($uniqueType & 2) && $res["VOTE_USER_ID"] == $voteUserId)
				{
					$result |= 2;
				}
				if (($uniqueType & 4) && $res["IP"] == $_SERVER["REMOTE_ADDR"] &&
					($delay <= 0 || $delay > $res["KEEP_IP_SEC"]))
				{
					$result |= 4;
				}
				if (($uniqueType & 8) && $res["AUTH_USER_ID"] == $userId)
				{
					$result |= 8;
				}
			}
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
}