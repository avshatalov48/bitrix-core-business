<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Vote;
use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\DB\MssqlConnection;
use Bitrix\Main\DB\MysqlCommonConnection;
use Bitrix\Main\DB\OracleConnection;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity;
use Bitrix\Main\Error;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ArgumentException;
use Bitrix\Vote\Base\BaseObject;
Loc::loadMessages(__FILE__);


/**
 * Class VoteTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CHANNEL_ID int,
 * <li> C_SORT int,
 * <li> ACTIVE bool mandatory default 'Y',
 * <li> NOTIFY bool mandatory default 'N',
 * <li> AUTHOR_ID int,
 * <li> TIMESTAMP_X datetime,
 * <li> DATE_START datetime,
 * <li> DATE_END datetime,
 * <li> URL string(255) NULL,
 * <li> COUNTER int,
 * <li> TITLE string(255),
 * <li> DESCRIPTION text,
 * <li> DESCRIPTION_TYPE string(4),
 * <li> IMAGE_ID int,
 * <li> EVENT1 string(255),
 * <li> EVENT2 string(255),
 * <li> EVENT3 string(255),
 * <li> UNIQUE_TYPE int (coded in binary system:
 * UNIQUE_TYPE_SESSION - 1 (00001)
 * UNIQUE_TYPE_COOKIE - 2 (00010)
 * UNIQUE_TYPE_IP - 4 (00100)
 * UNIQUE_TYPE_USER_ID - 8 (01000)
 * UNIQUE_TYPE_USER_ID_NEW - 16 (10000)
 * UNIQUE_TYPE = (UNIQUE_TYPE_SESSION & UNIQUE_TYPE_COOKIE & UNIQUE_TYPE_IP & UNIQUE_TYPE_USER_ID & UNIQUE_TYPE_USER_ID_NEW)
 * <li> KEEP_IP_SEC int,
 * </ul>
 *
 */
class VoteTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_vote';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		$now = Application::getInstance()->getConnection()->getSqlHelper()->getCurrentDateTimeFunction();
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('V_TABLE_FIELD_ID'),
			),
			'CHANNEL_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_CHANNEL_ID'),
			),
			'C_SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_C_SORT'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'Y',
				'title' => Loc::getMessage('V_TABLE_FIELD_ACTIVE')
			),
			'NOTIFY' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('V_TABLE_FIELD_NOTIFY')
			),
			'AUTHOR_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_AUTHOR_ID'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('V_TABLE_FIELD_TIMESTAMP_X'),
			),
			'DATE_START' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('V_TABLE_FIELD_DATE_START'),
			),
			'DATE_END' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('V_TABLE_FIELD_DATE_END'),
			),
			'URL' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('V_TABLE_FIELD_URL')
			),
			'COUNTER' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_COUNTER'),
			),
			'TITLE' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('V_TABLE_FIELD_TITLE')
			),
			'DESCRIPTION' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('V_TABLE_FIELD_DESCRIPTION')
			),
			'DESCRIPTION_TYPE' => array(
				'data_type' => 'enum',
				'values' => array("text", "html"),
				'default_value' => "text",
				'title' => Loc::getMessage('V_TABLE_FIELD_DESCRIPTION_TYPE'),
			),
			'IMAGE_ID' =>  array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_IMAGE_ID'),
			),
			'EVENT1' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('V_TABLE_FIELD_EVENT1'),
			),
			'EVENT2' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('V_TABLE_FIELD_EVENT2'),
			),
			'EVENT3' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('V_TABLE_FIELD_EVENT3'),
			),
			'UNIQUE_TYPE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_UNIQUE_TYPE'),
			),
			'KEEP_IP_SEC' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_KEEP_IP_SEC'),
			),
			'LAMP' => array(
				'data_type' => 'string',
				'expression' => array(
					"CASE ".
						"WHEN (%s='Y' AND %s='Y' AND %s <= {$now} AND {$now} <= %s AND %s='Y') THEN 'yellow' ".
						"WHEN (%s='Y' AND %s='Y' AND %s <= {$now} AND {$now} <= %s AND %s!='Y') THEN 'green' ".
						"ELSE 'red' ".
					"END",
					"CHANNEL.ACTIVE", "ACTIVE", "DATE_START", "DATE_END", "CHANNEL.VOTE_SINGLE",
					"CHANNEL.ACTIVE", "ACTIVE", "DATE_START", "DATE_END", "CHANNEL.VOTE_SINGLE"
				),
			),
			'CHANNEL' => array(
				'data_type' => '\Bitrix\Vote\ChannelTable',
				'reference' => array(
					'=this.CHANNEL_ID' => 'ref.ID',
				),
				'join_type' => 'LEFT',
			),
			'QUESTION' => array(
				'data_type' => '\Bitrix\Vote\QuestionTable',
				'reference' => array(
					'=this.ID' => 'ref.VOTE_ID',
				),
				'join_type' => 'LEFT',
			),
		);
	}

	/**
	 * @param array $parameters Filter in terms ORM.
	 * @return DBResult
	 */
	public static function getList(array $parameters = array())
	{
		return new DBResult(parent::getList($parameters));
	}

	/**
	 * @param array $id Vote IDs.
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

class Vote extends BaseObject implements \ArrayAccess
{
	protected $vote = array();
	protected $questions = array();
	protected $channel = null;
	public static $users = array();
	public static $storage = array();
	public static $statStorage = array();

	public function init()
	{
		$data = self::getData($this->id);
		if ($data === null)
			throw new ArgumentException('Wrong vote id!');
		$this->vote = array_diff_key($data, array("QUESTIONS" => ""));
		foreach ($data["QUESTIONS"] as $q)
		{
			$this->questions[$q["ID"]] = $q;
		}
	}

	/**
	 * @param integer $id Vote ID.
	 * @return array|null
	 */
	public static function getData($id)
	{
		if (!array_key_exists($id, self::$storage))
		{
			self::$storage[$id] = null;

			$dbRes = VoteTable::getList(array(
				'select' => array(
					'V_' => '*',
					'V_LAMP' => 'LAMP',
					'Q_' => 'QUESTION.*',
					'A_' => 'QUESTION.ANSWER',
				),
				'order' => array(
					'QUESTION.C_SORT' => 'ASC',
					'QUESTION.ID' => 'ASC',
					'QUESTION.ANSWER.C_SORT' => 'ASC',
					'QUESTION.ANSWER.ID' => 'ASC',
				),
				'filter' => array(
					'ID' => $id
				)
			));
			if (($res = $dbRes->fetch()) && $res)
			{
				$vote = array();
				foreach ($res as $key => $val)
					if (strpos($key, "V_") === 0)
						$vote[substr($key, 2)] = $val;
				$vote["QUESTIONS"] = array();
				$questions = &$vote["QUESTIONS"];
				do
				{
					$question = array(); $answer = array();
					foreach ($res as $key => $val)
					{
						if (strpos($key, "Q_") === 0)
							$question[substr($key, 2)] = $val;
						else if (strpos($key, "A_") === 0)
							$answer[substr($key, 2)] = $val;
					}
					$questionId = "".$question["ID"];
					if (!array_key_exists($questionId, $questions))
						$questions[$questionId] = array_merge($question, array("ANSWERS" => array()));
					if (!array_key_exists($questionId, Question::$storage))
						Question::$storage[$questionId] = $question;
					$answers = &$questions[$questionId]["ANSWERS"];
					if (!empty($answer))
					{
						switch ($answer["FIELD_TYPE"])
						{
							case 1://checkbox
								$answer["FIELD_NAME"] = 'vote_checkbox_' . $questionId;
								break;
							case 2://select
								$answer["FIELD_NAME"] = 'vote_dropdown_' . $questionId;
								break;
							case 3://multiselect
								$answer["FIELD_NAME"] = 'vote_multiselect_' . $questionId;
								break;
							case 4://text field
								$answer["FIELD_NAME"] = 'vote_field_' . $answer["ID"];
								break;
							case 5 :
								$answer["FIELD_NAME"] = 'vote_memo_' . $answer["ID"];
								break;
							default: //radio
								$answer["FIELD_NAME"] = 'vote_radio_' . $questionId;
								break;
						}
						$answer["~PERCENT"] = ($question["COUNTER"] > 0 ? $answer["COUNTER"] * 100 / $question["COUNTER"] : 0);
						$answer["PERCENT"] = round($answer["~PERCENT"], 2);
						if (!array_key_exists($answer["ID"], $answers))
							$answers[$answer["ID"]] = $answer;
						if (!array_key_exists($answer["ID"], Answer::$storage))
							Answer::$storage[$answer["ID"]] = $answer;
					}
				} while (($res = $dbRes->fetch()) && $res);
				self::$storage[$id] = $vote;
			}
		}
		return self::$storage[$id];
	}

	/**
	 * @param int $voteId Vote ID.
	 * @param array &$data Array(
			"CHANNEL_ID" => 5,
			"AUTHOR_ID" => 1,
			"DATE_START" => ...,
			"DATE_END" => ...,
			"TITLE" => "ABC...",
			"ACTIVE" => "Y",
			"URL" => "http://",
			"NOTIFY" => "Y" || "N" || "I",
			"UNIQUE_TYPE" => security context,
			"DELAY" => 150, //seconds
			"QUESTIONS" => array(
				1 => array(
					"ID" => 0,
					"QUESTION" => "Question text",
					"QUESTION_TYPE" => "text"||"html",
					"ANSWERS" => array(
						array(
							"ID" => 0,
							"MESSAGE" => "Answer text",
							"MESSAGE_TYPE" => "text"||"html",
							"FIELD_TYPE" => 0||1||2||3||4||
						)
					)
				)
			);.
	 * @return bool
	 * @throws ArgumentException
	 */
	public static function checkData($voteId = 0, array &$data)
	{
		$msg = array();
		$vote = array();

		$fieldsVote = array_intersect_key($data, array_flip(array(
			"CHANNEL_ID",
			"AUTHOR_ID",
			"DATE_START",
			"DATE_END",
			"TITLE",
			"ACTIVE",
			"URL",
			"NOTIFY",
			"UNIQUE_TYPE",
			"DELAY",
			"DELAY_TYPE")));
		global $APPLICATION;
		if (!\CVote::CheckFields("UPDATE", $fieldsVote))
			throw new ArgumentException($APPLICATION->GetException());
		if ($voteId > 0 && ($vote = static::getData($voteId)) && is_null($vote))
			throw new ArgumentException(Loc::getMessage("VOTE_VOTE_NOT_FOUND", array("#ID#", $voteId)));
		/************** Check Data *****************************************/
		if (!is_array($vote["QUESTIONS"]))
			$vote["QUESTIONS"] = array();
		if (!is_array($data["QUESTIONS"]))
			$data["QUESTIONS"] = array();
		$questions = array();
		foreach ($data["QUESTIONS"] as $key => $question)
		{
			if ($question["DEL"] == "Y")
				continue;

			$question["ID"] = intval($question["ID"]);
			$question = array(
				"ID" => (array_key_exists($question["ID"], $vote["QUESTIONS"]) ? $question["ID"] : false),
				"QUESTION" => trim($question["QUESTION"]),
				"QUESTION_TYPE" => trim($question["QUESTION_TYPE"]),
				"ANSWERS" => (is_array($question["ANSWERS"]) ? $question["ANSWERS"] : array()));

			$savedAnswers = ($question["ID"] > 0 ? $vote["QUESTIONS"][$question["ID"]]["ANSWERS"] : array());
			$newAnswers = array();
			foreach ($question["ANSWERS"] as $keya => $answer)
			{
				$answer["ID"] = intval($answer["ID"]);
				$answer["MESSAGE"] = trim($answer["MESSAGE"]);
				if ($answer["DEL"] != "Y" && $answer["MESSAGE"] !== "")
				{
					$answer = array(
						"ID" => $answer["ID"],
						"MESSAGE" => $answer["MESSAGE"],
						"MESSAGE_TYPE" => $answer["MESSAGE_TYPE"],
						"FIELD_TYPE" => $answer["FIELD_TYPE"]);
					if (!array_key_exists($answer["ID"], $savedAnswers))
						unset($answer["ID"]);
					else
						unset($savedAnswers[$answer["ID"]]);
					$newAnswers[] = $answer;
				}
			}
			$question["ANSWERS"] = $newAnswers;

			if ($question["QUESTION"] == "" && empty($question["ANSWERS"]))
				continue;
			else if ($question["QUESTION"] == "")
			{
				$msg[] = array(
					"id" => "QUESTION_".$key,
					"text" => Loc::getMessage("VOTE_QUESTION_EMPTY", array("#NUMBER#" => $key))
				);
			}
			else if (empty($question["ANSWERS"]))
			{
				$msg[] = array(
					"id" => "QUESTION_".$key,
					"text" => Loc::getMessage("VOTE_ANSWERS_EMPTY", array("#QUESTION#" => $question["QUESTION"]))
				);
			}
			else
			{
				foreach ($savedAnswers as $answer)
				{
					$question["ANSWERS"][] = $answer + array("DEL" => "Y");
				}
				$questions[] = $question;
				unset($vote["QUESTIONS"][$question["ID"]]);
			}
		}
		if (!empty($msg))
		{
			$e = new \CAdminException(array_reverse($msg));
			$APPLICATION->ThrowException($e);
			throw new ArgumentException($APPLICATION->GetException()->GetString());
		}
		foreach ($vote["QUESTIONS"] as $question)
		{
			$questions[] = $question + array("DEL" => "Y");
		}
		$data = $fieldsVote + array("QUESTIONS" => $questions);
		return true;
	}

	/**
	 * @param integer $voteId Vote ID, can be 0.
	 * @param array $data Look at checkData.
	 * @return int
	 * @throws ArgumentException
	 */
	public static function saveData($voteId, array $data)
	{
		$fieldsVote = array_intersect_key($data, array_flip(array(
			"CHANNEL_ID",
			"AUTHOR_ID",
			"DATE_START",
			"DATE_END",
			"TITLE",
			"ACTIVE",
			"URL",
			"UNIQUE_TYPE",
			"KEEP_IP_SEC",
			"NOTIFY",
			"DELAY",
			"DELAY_TYPE")));
		if ($voteId)
		{
			$vote = static::getData($voteId);
			if (is_null($vote))
				throw new ArgumentException(Loc::getMessage("VOTE_VOTE_NOT_FOUND", array("#ID#", $voteId)));
			\CVote::Update($voteId, $fieldsVote);
		}
		else
		{
			$voteId = intval(\CVote::Add($fieldsVote));
			if ($voteId <= 0)
				throw new ArgumentException(Loc::getMessage("VOTE_VOTE_IS_NOT_CREATED", array("#ID#", $voteId)));
			$vote = $fieldsVote + array("ID" => $voteId, "QUESTIONS" => array());
		}
		/************** Check Data *****************************************/
		$data["QUESTIONS"] = (is_array($data["QUESTIONS"]) ? $data["QUESTIONS"] : array());
		$iQuestions = 0;
		foreach ($data["QUESTIONS"] as $key => $question)
		{
			$savedAnswers = array();
			if ($question["ID"] > 0 && array_key_exists($question["ID"], $vote["QUESTIONS"]))
			{
				$savedAnswers = $vote["QUESTIONS"][$question["ID"]]["ANSWERS"];
				unset($vote["QUESTIONS"][$question["ID"]]);
				if ($question["DEL"] == "Y")
				{
					\CVoteQuestion::Delete($question["ID"]);
					continue;
				}
				$question["C_SORT"] = (++$iQuestions) * 10;
				\CVoteQuestion::Update($question["ID"], $question);
			}
			else
			{
				$question["C_SORT"] = (++$iQuestions) * 10;
				$question["VOTE_ID"] = $vote["ID"];
				$question["ID"] = \CVoteQuestion::Add($question);
				if ($question["ID"] <= 0)
					continue;
			}
			$iAnswers = 0;
			foreach ($question["ANSWERS"] as $answer)
			{
				if (array_key_exists($answer["ID"], $savedAnswers))
				{
					unset($savedAnswers[$answer["ID"]]);
					if ($answer["DEL"] == "Y")
					{
						\CVoteAnswer::Delete($answer["ID"]);
						continue;
					}
					$answer["C_SORT"] = (++$iAnswers)* 10;
					\CVoteAnswer::Update($answer["ID"], $answer);
				}
				else
				{
					$answer["QUESTION_ID"] = $question["ID"];
					$answer["C_SORT"] = (++$iAnswers) * 10;
					$answer["ID"] = intval(\CVoteAnswer::Add($answer));
					if ($answer["ID"] <= 0)
						continue;
				}
			}
			if ($iAnswers <= 0)
			{
				\CVoteQuestion::Delete($question["ID"]);
				$iQuestions--;
			}
			else if (!empty($savedAnswers))
			{
				while ($answer = array_pop($savedAnswers))
					\CVoteAnswer::Delete($answer["ID"]);
			}
		}
		if ($iQuestions <= 0)
		{
			Vote::delete($vote["ID"]);
			$vote["ID"] = 0;
		}
		return $vote["ID"];
	}

	/**
	 * Sends notifications to users.
	 * @param array $event Array("ID" => 1, "VOTE_USER_ID" => 45);.
	 * @param array $vote Array(ID => 1, QUESTIONS => array("ID" => 2, ANSWERS => array()));.
	 * @param string $type Can be "im" || "mail".
	 * @return bool
	 */
	public function sendVotingMessage(array $event, $vote, $type = "im")
	{
		if ($type == "im" && \CModule::IncludeModule("im"))
		{
			$url = "";
			if (!empty($vote["URL"]))
			{
				if (defined('SITE_SERVER_NAME'))
					$url = SITE_SERVER_NAME;
				$url = (!empty($url) ? $url : \COption::GetOptionString("main", "server_name"));
				if (!empty($url))
					$url = (\CMain::IsHTTPS() ? "https" : "http") . "://" . $url . $vote["URL"];
			}

			// send notification
			$gender = "";
			if ($this->getUser()->getParam("PERSONAL_GENDER") == "F")
				$gender = "_F";
			\CIMNotify::Add(array(
				"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
				"TO_USER_ID" => $vote["AUTHOR_ID"],
				"FROM_USER_ID" => $this->getUser()->getId(),
				"NOTIFY_TYPE" => IM_NOTIFY_FROM,
				"NOTIFY_MODULE" => "vote",
				"NOTIFY_EVENT" => "voting",
				"NOTIFY_TAG" => "VOTING|" . $vote["ID"],
				"NOTIFY_MESSAGE" => (!empty($vote["URL"]) ?
					Loc::getMessage("V_NOTIFY_MESSAGE_HREF" . $gender, array("#VOTE_TITLE#" => $vote["TITLE"], "#VOTE_URL#" => $vote["URL"])) :
					Loc::getMessage("V_NOTIFY_MESSAGE" . $gender, array("#VOTE_TITLE#" => $vote["TITLE"]))),
				"NOTIFY_MESSAGE_OUT" => (!empty($url) ?
					Loc::getMessage("V_NOTIFY_MESSAGE_OUT_HREF" . $gender, array("#VOTE_TITLE#" => $vote["TITLE"], "#VOTE_URL#" => $url)) :
					Loc::getMessage("V_NOTIFY_MESSAGE" . $gender, array("#VOTE_TITLE#" => $vote["TITLE"])))
			));
		}
		else
		{
			$channel = $this->getChannel();
			// send e-mail
			$dbUser = \CUser::getById($vote["AUTHOR_ID"]);
			if ($dbUser && ($u = $dbUser->Fetch()) && !empty($u["EMAIL"]))
			{
				$eventFields = array(
					"EMAIL_TO" => $u["EMAIL"],
					"VOTE_STATISTIC" => "",
					"ID" => $event["ID"],
					"TIME" => GetTime(time(), "FULL"),
					"VOTE_TITLE" => $vote["TITLE"],
					"VOTE_DESCRIPTION" => $vote["DESCRIPTION"],
					"VOTE_ID" => $vote["ID"],
					"VOTE_COUNTER" => $vote["COUNTER"],
					"URL" => $vote["URL"],
					"CHANNEL" => $channel["TITLE"],
					"CHANNEL_ID" => $channel["ID"],
					"VOTER_ID" => $event["VOTE_USER_ID"],
					"USER_NAME" => $this->getUser()->getFullName(),
					"LOGIN" => $this->getUser()->getLogin(),
					"USER_ID" => $this->getUser()->getID(),
					"STAT_GUEST_ID" => intval($_SESSION["SESS_GUEST_ID"]),
					"SESSION_ID" => intval($_SESSION["SESS_SESSION_ID"]),
					"IP" => $_SERVER["REMOTE_ADDR"]);
				$eventFields["USER_NAME"] = (!!$eventFields["USER_NAME"] ? $eventFields["USER_NAME"] : $eventFields["LOGIN"]);
				// VOTE_STATISTIC
				$text = array();
				foreach ($this["QUESTIONS"] as $question)
				{
					if (array_key_exists($question["ID"], $event["BALLOT"]))
					{
						$text[$question["ID"]] = array();
						foreach ($question["ANSWERS"] as $answer)
						{
							if (array_key_exists($answer["ID"], $event["BALLOT"][$question["ID"]]))
							{
								if ($answer["FIELD_TYPE"] == 4 || $answer["FIELD_TYPE"] == 5)
								{
									if ($event["BALLOT"][$question["ID"]][$answer["ID"]]["MESSAGE"] !== "")
									{
										$text[$question["ID"]][] = $event["BALLOT"][$question["ID"]][$answer["ID"]]["MESSAGE"];
									}
								}
								else
								{
									$text[$question["ID"]][] = $answer["MESSAGE"];
								}
							}
						}
						if (!empty($text[$question["ID"]]))
						{
							$text[$question["ID"]] = " - " . $question["QUESTION"] . "\n - " . implode(", ", $text[$question["ID"]]);
						}
					}
				}
				$eventFields["VOTE_STATISTIC"] = "\n" . implode("\n\n", $text);
				$arrSites = \CVoteChannel::GetSiteArray($channel["ID"]);
				\CEvent::Send("VOTE_FOR", $arrSites, $eventFields, "N");
			}
		}

		return true;
	}

	/**
	 * Gets statistic from DB.
	 * @return void
	 */
	public function getStatistic()
	{
		foreach ($this->questions as &$qs)
			foreach ($qs["ANSWERS"] as &$as)
				$as["STAT"] = array();


		$dbRes = \Bitrix\Vote\EventTable::getList(array(
			'select' => array(
				'V_' => '*',
				'Q_' => 'QUESTION.*',
				'A_' => 'QUESTION.ANSWER.*',
				'U_' => 'USER.USER.*',
			),
			'filter' => array('VOTE_ID' => $this->id),
			'order' => array(
				'USER.USER.LAST_NAME' => 'ASC',
				'USER.USER.NAME' => 'ASC',
				'USER.USER.LOGIN' => 'ASC'
			)
		));
		while ($dbRes && ($res = $dbRes->fetch()))
		{
			if (array_key_exists($res["Q_QUESTION_ID"], $this->questions) &&
				array_key_exists($res["A_ANSWER_ID"], $this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"]))
			{
				$stat = &$this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"][$res["A_ANSWER_ID"]]["STAT"];
				$stat[$res["A_ID"]] = array(
					"USER" => array(
						"ID" => $res["U_ID"],
						"NAME" => $res["U_NAME"],
						"LAST_NAME" => $res["U_LAST_NAME"],
						"SECOND_NAME" => $res["U_SECOND_NAME"],
						"LOGIN" => $res["U_LOGIN"],
						"PERSONAL_PHOTO" => $res["U_PERSONAL_PHOTO"],
					),
					"MESSAGE" => $res["A_MESSAGE"]
				);
			}
		}
	}

	/**
	 * @return array|null
	 */
	public function getChannel()
	{
		if ($this->channel === null)
		{
			$this->channel = array();
			$db = Channel::getList(array());
			while (($res = $db->fetch()) && $res)
			{
				if ($this->vote["CHANNEL_ID"] == $res["ID"])
				{
					$this->channel = $res;
					break;
				}
			}
		}
		return $this->channel;
	}

	/**
	 * @param string $key The name if characteristic that you want to know.
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->vote[$key];
	}

	/**
	 * @return array
	 */
	public function getQuestions()
	{
		return $this->questions;
	}

	/**
	 * Prolongs the time of voting for a year
	 * @return void
	 */
	public function resume()
	{
		$format = 'Y-m-d H:i:s';
		VoteTable::update($this->id, array("DATE_END" => new \Bitrix\Main\Type\DateTime(date($format, (time() + (365 * 86400))), $format)));
		$this->clearCache();
	}

	/**
	 * Sets the finish time for voting by current moment
	 * @return void
	 */
	public function stop()
	{
		$format = 'Y-m-d H:i:s';
		VoteTable::update($this->id, array("DATE_END" => new \Bitrix\Main\Type\DateTime(date($format, (time() - 3600)), $format)));
		$this->clearCache();
	}

	/**
	 * Deletes Vote by its id.
	 * @param integer $id Vote ID.
	 * @return bool
	 */
	public static function delete($id)
	{
		// @todo delete all attaches
		return \CVote::Delete($id);
	}

	/**
	 * Clears cache
	 * @return void
	 */
	private function clearCache()
	{
		global $VOTE_CACHE;
		unset($VOTE_CACHE["VOTE"][$this->id]);
		unset(self::$storage[$this->id]);
	}
	/**
	 * Clears vote events cache
	 * @return void
	 */
	private function clearVotingCache()
	{
		global $VOTE_CACHE;
		unset($VOTE_CACHE["VOTE_CACHE_VOTING"][$this->id]);
	}

	/**
	 * Exports data of voting into excel file
	 * @return void
	 */
	public function exportExcel()
	{
		global $APPLICATION;
		$this->getStatistic();
		$nameTemplate = \CSite::getNameFormat();

		$APPLICATION->restartBuffer();
		while(ob_get_clean());
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment;filename=vote".$this->id.".xls");
		header("Content-Transfer-Encoding: binary");
		?>
		<meta http-equiv="Content-type" content="text/html;charset=<?=LANG_CHARSET?>" />
		<table border="1">
			<tbody>
			<?
			$q = 0;
			foreach ($this->questions as $questionId => $question)
			{
				?><tr><th align="left" colspan="3"><?=(++$q)?>. <?=$question["QUESTION"]?></th></tr><?

				foreach ($question["ANSWERS"] as $answer)
				{
					?><tr><td colspan="3"><?=$answer["MESSAGE"]?> (<?=$answer["COUNTER"]?>)</td></tr><?
					$guests = 0;
					foreach ($answer["STAT"] as $event)
					{
						if ($event["USER"]["ID"] > 0)
						{
							$user = \CUser::formatName($nameTemplate, $event["USER"], true, false);
							?><tr><td></td><td><?=$user?></td><td><?=$event["MESSAGE"]?></td></tr><?
						}
						else if ($event["MESSAGE"] !== "")
						{
							?><tr><td></td><td><?=Loc::getMessage("VOTE_GUEST")?></td><td><?=$event["MESSAGE"]?></td></tr><?
						}
						else
						{
							$guests++;
						}
					}
					if ($guests > 0)
					{
						?><tr><td></td><td><?=Loc::getMessage("VOTE_GUESTS")?>: <?=$guests?></td><td></td></tr><?
					}
				}
			}?>
			</tbody>
		</table>
		<?

		\CMain::finalActions();
		die();
	}
	/**
	 * Voting for vote  from current user $USER.
	 * @param array $request Array("
	 * 							vote_checkbox_".$questionId => array(1,2,3,...),
	 * 							"vote_multiselect_".$questionId => array(1,2,3,...),
	 * 							"vote_dropdown_".$questionId => 12 || "12",
	 * 							"vote_radio_".$questionId => 12 || "12",
	 * 							"vote_field_".$answerId => "12").
	 * @return bool
	 * @throws AccessDeniedException
	 */
	public function voteFor(array $request)
	{
		if ($this["LAMP"] == "red")
			throw new AccessDeniedException(Loc::getMessage("VOTE_IS_NOT_ACTIVE"));

		$voteId = $this->getId();
		$userId = $this->getUser()->getId();

		$statusVote = $this->isVotedFor($userId);
		if (!($statusVote === false || $statusVote == 8 && $this->getUser()->isAuthorized()))
			throw new AccessDeniedException(Loc::getMessage("VOTE_ALREADY_VOTED"));

		$sqlAnswers = array();
		$questions = $this->getQuestions();
		// check answers
		foreach ($questions as $questionId => $question)
		{
			$sqlAnswers[$question["ID"]] = array();
			foreach ($question["ANSWERS"] as $answer)
			{
				$value = $request[$answer["FIELD_NAME"]];
				$answerId = $answer["ID"];
				if ($answer["FIELD_TYPE"] == 4 || $answer["FIELD_TYPE"] == 5)
				{
					if (($value = trim($value)) && $value != "")
					{
						$sqlAnswers[$questionId][$answerId] = array(
							"ANSWER_ID" => $answerId,
							"MESSAGE" => substr($value, 0, 2000));
					}
				}
				else
				{
					$value = array_intersect(is_array($value) ? $value : array($value), array_keys($question["ANSWERS"]));
					$found = false;
					foreach($value as $v)
					{
						$sqlAnswers[$questionId][$v] = array("ANSWER_ID" => $v);
						$found = true;
					}
					if ($found && ($answer["FIELD_TYPE"] == 0 || $answer["FIELD_TYPE"] == 2))
						break;
				}
			}
			if (empty($sqlAnswers[$questionId]))
			{
				unset($sqlAnswers[$questionId]);
				if ($question['REQUIRED'] == 'Y')
				{
					$this->errorCollection->add(array(new Error(Loc::getMessage("VOTE_REQUIRED_MISSING"), "QUESTION_".$questionId)));
				}
			}
		}
		if (!empty($sqlAnswers) && $this->errorCollection->isEmpty())
		{
			// vote event
			$eventFields = array(
				"VOTE_ID"			=> $voteId,
				"VOTE_USER_ID"		=> \Bitrix\Vote\User::getCurrent()->setVotedUserId(true),
				"DATE_VOTE"			=> new \Bitrix\Main\Type\DateTime(),
				"STAT_SESSION_ID"	=> $_SESSION["SESS_SESSION_ID"],
				"IP"				=> substr($_SERVER["REMOTE_ADDR"], 0, 15),
				"VALID"				=> "Y");

			/***************** Event onBeforeVoting ****************************/
			foreach (GetModuleEvents("vote", "onBeforeVoting", true) as $event)
			{
				if (ExecuteModuleEventEx($event, array(&$eventFields, &$sqlAnswers)) === false)
				{
					$this->errorCollection->add(array(new Error("onBeforeVoting error", "VOTE_".$voteId)));
					return false;
				}
			}
			/***************** /Event ******************************************/
			if (($this["UNIQUE_TYPE"] & 8) && $userId > 0)
			{
				$dbRes = \Bitrix\Vote\EventTable::getList(array(
					'select' => array(
						'V_' => '*',
						'Q_' => 'QUESTION.*',
						'A_' => 'QUESTION.ANSWER.*'
					),
					'filter' => array('VOTE_ID' => $voteId, 'USER.AUTH_USER_ID' => $userId),
					'order' => array(
						'ID' => 'ASC',
						'QUESTION.ID' => 'ASC',
						'QUESTION.ANSWER.ID' => 'ASC'
					)
				));

				if ($dbRes && ($res = $dbRes->fetch()))
				{
					if (\CModule::IncludeModule("im"))
						\CIMNotify::DeleteByTag("VOTING|".$voteId, $userId);
					$qEId = 0;
					$vEId = 0;

					do
					{
						if ($vEId < $res["V_ID"])
						{
							$vEId = $res["V_ID"];
							Event::deleteEvent(intval($res["V_ID"]));
							$this->vote["COUNTER"] = max($this->vote["COUNTER"] - 1, 0);
						}
						if (array_key_exists($res["Q_QUESTION_ID"], $this->questions) &&
							array_key_exists($res["A_ANSWER_ID"], $this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"]))
						{
							if ($qEId < $res["Q_ID"])
							{
								$qEId = $res["Q_ID"];
								$this->questions[$res["Q_QUESTION_ID"]]["COUNTER"] = max($this->questions[$res["Q_QUESTION_ID"]]["COUNTER"] - 1, 0);
							}

							$this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"][$res["A_ANSWER_ID"]]["COUNTER"]--;
						}
					} while ($dbRes && ($res = $dbRes->fetch()));
				}
			}

			$this->clearCache();
			$this->clearVotingCache();

			if (($eventId = EventTable::add($eventFields)->getId()) && $eventId > 0)
			{
				$this->vote["COUNTER"]++;
				$ids = array();
				$idAs = array();
				foreach ($sqlAnswers as $questionId => $sqlAnswer)
				{
					if (($eventQId = EventQuestionTable::add(array("EVENT_ID" => $eventId, "QUESTION_ID" => $questionId))->getId()) && $eventQId > 0)
					{
						$this->questions[$questionId]["COUNTER"]++;

						$ids[$questionId] = array();
						foreach ($sqlAnswer as $answerId => $res)
						{
							$res["EVENT_QUESTION_ID"] = $eventQId;
							if (($eventAId = EventAnswerTable::add($res)->getId()) && $eventAId > 0)
							{
								$this->questions[$questionId]["ANSWERS"][$answerId]["COUNTER"]++;
								$ids[$questionId][] = $answerId;
								$idAs[] = $answerId;
							}
						}
						if (empty($ids[$questionId]))
							unset($ids[$questionId]);
					}
				}

				if (empty($ids))
				{
					EventTable::delete($eventId);
					$this->vote["COUNTER"]--;
				}

				foreach ($this->questions as $questionId => $question)
				{
					foreach ($question["ANSWERS"] as $answerId => $answer)
					{
						if ($question["COUNTER"] > 0 && $this->questions[$questionId]["ANSWERS"][$answerId]["COUNTER"] > 0)
						{
							$this->questions[$questionId]["ANSWERS"][$answerId]["~PERCENT"] = $answer["COUNTER"] * 100 / $question["COUNTER"];
							$this->questions[$questionId]["ANSWERS"][$answerId]["PERCENT"] = round($this->questions[$questionId]["ANSWERS"][$answerId]["~PERCENT"], 2);
						}
						else
						{
							$this->questions[$questionId]["ANSWERS"][$answerId]["COUNTER"] = 0;
							$this->questions[$questionId]["ANSWERS"][$answerId]["~PERCENT"] = 0;
							$this->questions[$questionId]["ANSWERS"][$answerId]["PERCENT"] = 0;
						}
					}
				}

				if (!empty($ids))
				{
					VoteTable::setCounter(array($voteId), true);
					QuestionTable::setCounter(array_keys($ids), true);
					AnswerTable::setCounter($idAs, true);

					self::$statStorage[] = $voteId;
					// TODO this is bad to use super globals
					$_SESSION["VOTE"]["VOTES"][$voteId] = $eventId;
					// statistic module
					if (\CModule::IncludeModule("statistic"))
					{
						$event3 = $this["EVENT3"];
						if (!empty($event3)):
							$event3 = "http://" . $_SERVER["HTTP_HOST"] . "/bitrix/admin/vote_user_results.php?EVENT_ID=" . $eventId . "&lang=" . LANGUAGE_ID;
						endif;
						\CStatEvent::AddCurrent($this["EVENT1"], $this["EVENT2"], $event3);
					}
					// notification TODO replace this functional into other function
					if ($this["NOTIFY"] !== "N" && $this["AUTHOR_ID"] > 0 && $this["AUTHOR_ID"] != $userId)
						self::sendVotingMessage(array_merge($eventFields, array("ID" => $eventId, "BALLOT" => $sqlAnswers)), $this, ($this["NOTIFY"] == "I" ? "im" : "mail"));
				}
				/***************** Event onAfterVoting *****************************/
				foreach (GetModuleEvents("vote", "onAfterVoting", true) as $event)
					ExecuteModuleEventEx($event, array($voteId, $eventId, $userId));
				/***************** /Event ******************************************/
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks if current user voted for this vote.
	 * @param int $userId User ID.
	 * @return bool|int
	 */
	public function isVotedFor($userId)
	{
		if ($userId == $this->getUser()->getId())
			return User::getCurrent()->isVotedFor($this["ID"]);
		return User::isUserVotedFor($this["ID"], $userId);
	}
	/**
	 * Checks rights to read current attached object.
	 * @param int $userId Id of user.
	 * @return bool
	 */
	public function canRead($userId)
	{
		if (parent::canEdit($userId))
			return true;
		else if (parent::canRead($userId))
		{
			$groups = parent::loadUserGroups($userId);
			$dbRes = \Bitrix\Vote\Channel::getList(array(
				'select' => array("*"),
				'filter' => array(
					"ACTIVE" => "Y",
					"HIDDEN" => "N",
					">=PERMISSION.PERMISSION" => 1,
					"PERMISSION.GROUP_ID" => $groups
				),
				'order' => array(
					'TITLE' => 'ASC'
				),
				'group' => array("ID")
			));
			while ($res = $dbRes->fetch())
			{
				if ($res["ID"] == $this->get("CHANNEL_ID"))
					return true;
			}
		}
		return false;
	}

	/**
	 * Checks rights to update current attached object.
	 * @param int $userId Id of user.
	 * @return bool
	 */
	public function canEdit($userId)
	{
		if (parent::canEdit($userId))
			return true;
		else if (parent::canRead($userId))
		{
			$groups = parent::loadUserGroups($userId);
			$dbRes = \Bitrix\Vote\Channel::getList(array(
				'select' => array("*"),
				'filter' => array(
					"ACTIVE" => "Y",
					"HIDDEN" => "N",
					">=PERMISSION.PERMISSION" => 4,
					"PERMISSION.GROUP_ID" => $groups
				),
				'order' => array(
					'TITLE' => 'ASC'
				),
				'group' => array("ID")
			));
			while ($res = $dbRes->fetch())
			{
				if ($res["ID"] == $this->get("CHANNEL_ID"))
					return true;
			}
		}
		return false;
	}

	/**
	 * @param string $offset Key for vote or attach data array.
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		if ($offset == "QUESTIONS")
			return true;
		return array_key_exists($offset, $this->vote);
	}
	/**
	 * @param string $offset Key for vote or attach data array.
	 * @return array|mixed|null
	 */
	public function offsetGet($offset)
	{
		if (array_key_exists($offset, $this->vote))
			return $this->vote[$offset];
		else if ($offset == "QUESTIONS")
			return $this->questions;
		return null;
	}
	/**
	 * Is not supported.
	 * @param string $offset Key for vote or attach data array.
	 * @param mixed $value Value for vote or attach data array.
	 * @return void
	 * @throws \Bitrix\Main\NotSupportedException
	 */
	public function offsetSet($offset, $value)
	{
		throw new \Bitrix\Main\NotSupportedException('Model provide ArrayAccess only for reading');
	}
	/**
	 * Is not supported.
	 * @param string $offset Key for vote or attach data array.
	 * @return void
	 * @throws \Bitrix\Main\NotSupportedException
	 */
	public function offsetUnset($offset)
	{
		throw new \Bitrix\Main\NotSupportedException('Model provide ArrayAccess only for reading');
	}

}
