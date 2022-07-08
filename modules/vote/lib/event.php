<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Vote;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use \Bitrix\Main\Entity;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\DB\MssqlConnection;
use Bitrix\Main\DB\MysqlCommonConnection;
use Bitrix\Main\DB\OracleConnection;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\Dictionary as EventResult;
use Bitrix\Vote\Base\BaseObject;

Loc::loadMessages(__FILE__);

/**
 * Class VoteEventTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> VOTE_ID int,
 * <li> VOTE_USER_ID int,
 * <li> DATE_VOTE datetime,
 * <li> STAT_SESSION_ID int,
 * <li> IP string(15),
 * <li> VALID string(1)
 * </ul>
 *
 */
class EventTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_vote_event';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			(new IntegerField('ID', ['primary' => true, 'autocomplete' => true])),
			(new IntegerField('VOTE_ID')),
			(new IntegerField('VOTE_USER_ID', ["required" => true])),
			(new DatetimeField('DATE_VOTE')),
			(new IntegerField('STAT_SESSION_ID')),
			(new StringField('IP', ['size' => 15])),
			(new BooleanField('VALID', ['values' => ['N', 'Y'], 'default_value' => 'Y'])),
			(new BooleanField('VISIBLE', ['values' => ['N', 'Y'], 'default_value' => 'Y'])),
			(new Reference('QUESTION', \Bitrix\Vote\EventQuestionTable::class, Join::on('this.ID', 'ref.EVENT_ID'))),
			(new Reference('USER', \Bitrix\Vote\UserTable::class, Join::on('this.VOTE_USER_ID', 'ref.ID'))),
		);
	}
}
/**
 * Class EventQuestionTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> EVENT_ID int,
 * <li> QUESTION_ID int,
 * </ul>
 *
 */
class EventQuestionTable extends Entity\DataManager
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
			),
			'EVENT_ID' => array(
				'data_type' => 'integer',
			),
			'QUESTION_ID' => array(
				'data_type' => 'integer',
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
 * Class EventAnswerTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> EVENT_QUESTION_ID int,
 * <li> ANSWER_ID int,
 * <li> MESSAGE text,
 * </ul>
 *
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EventAnswer_Query query()
 * @method static EO_EventAnswer_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_EventAnswer_Result getById($id)
 * @method static EO_EventAnswer_Result getList(array $parameters = array())
 * @method static EO_EventAnswer_Entity getEntity()
 * @method static \Bitrix\Vote\EO_EventAnswer createObject($setDefaultValues = true)
 * @method static \Bitrix\Vote\EO_EventAnswer_Collection createCollection()
 * @method static \Bitrix\Vote\EO_EventAnswer wakeUpObject($row)
 * @method static \Bitrix\Vote\EO_EventAnswer_Collection wakeUpCollection($rows)
 */
class EventAnswerTable extends Entity\DataManager
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
			),
			'EVENT_QUESTION_ID' => array(
				'data_type' => 'integer',
			),
			'ANSWER_ID' => array(
				'data_type' => 'integer',
			),
			'MESSAGE' => array(
				'data_type' => 'text',
			)
		);
	}
}

class Event extends BaseObject
{
	private $vote;
	/**
	 * EVENT_FIELD_BALLOT_TEMPLATE - is a template to catch voting
	 * [#ID#][BALLOT][#QUESTION_ID#][#ANSWER_ID#][MESSAGE] - template for text
	 */
	const EVENT_FIELD_NAME = "bx_vote_event"; //
	const EVENT_FIELD_BALLOT_TEMPLATE = self::EVENT_FIELD_NAME."[#ID#][BALLOT][#QUESTION_ID#]"; // this is template for voting
	const EVENT_FIELD_MESSAGE_TEMPLATE = self::EVENT_FIELD_NAME."[#ID#][MESSAGE][#QUESTION_ID#][#ANSWER_ID#]"; // this is template for voting
	const EVENT_FIELD_EXTRAS_TEMPLATE = self::EVENT_FIELD_NAME."[#ID#][EXTRAS][#ENTITY_ID#]";

	/** @var  ErrorCollection */
	protected $errorCollection;
	/**
	 * Event constructor.
	 * @param Vote $vote
	 */
	function __construct(\Bitrix\Vote\Vote $vote)
	{
		$this->vote = $vote;
		$this->errorCollection = new ErrorCollection;
	}

	/**
	 * @param int $voteId Vote Id.
	 * @return void
	 */
	public static function calculateStatistic($voteId)
	{
		$connection = Application::getInstance()->getConnection();
		if ($connection instanceof MysqlCommonConnection)
		{
			$connection->executeSqlBatch(<<<SQL
UPDATE b_vote V SET V.COUNTER=(
	SELECT COUNT(VE.ID) 
	FROM b_vote_event VE 
	WHERE VE.VOTE_ID=V.ID) 
WHERE V.ID={$voteId};
UPDATE b_vote_question VQ SET VQ.COUNTER=(
	SELECT COUNT(VEQ.ID) 
	FROM b_vote_event_question VEQ 
	WHERE VEQ.QUESTION_ID=VQ.ID)
WHERE VQ.VOTE_ID={$voteId};
UPDATE b_vote_answer VA, b_vote_question VQ SET VA.COUNTER=(
	SELECT COUNT(VEA.ID)
	FROM b_vote_event_answer VEA 
	WHERE VEA.ANSWER_ID=VA.ID)
WHERE VQ.ID = VA.QUESTION_ID AND VQ.VOTE_ID={$voteId};
UPDATE b_vote_user VU, b_vote_event VE SET VU.COUNTER=(
	SELECT COUNT(VE.ID)
	FROM b_vote_event VE 
	WHERE VU.ID=VE.VOTE_USER_ID AND VE.VALID='Y')
WHERE VU.ID IN (SELECT VOTE_USER_ID FROM b_vote_event WHERE VOTE_ID={$voteId});
SQL
			);
		}
		else if ($connection instanceof MssqlConnection)
		{
			$connection->executeSqlBatch(<<<SQL
UPDATE b_vote SET b_vote.COUNTER=E.COUNTER
FROM (
	SELECT COUNT(ID) COUNTER, VOTE_ID
	FROM b_vote_event  
	WHERE VOTE_ID={$voteId}
	GROUP BY VOTE_ID) E 
WHERE b_vote.ID=E.VOTE_ID AND b_vote.ID={$voteId}
GO
UPDATE b_vote_question SET COUNTER=E.COUNTER
FROM (
	SELECT COUNT(EQ.ID) COUNTER, EQ.QUESTION_ID
	FROM b_vote_event_question EQ
	JOIN b_vote_question Q ON (Q.ID = EQ.QUESTION_ID) 
	WHERE Q.VOTE_ID={$voteId}
	GROUP BY EQ.QUESTION_ID) E
WHERE b_vote_question.ID=E.QUESTION_ID AND b_vote_question.VOTE_ID={$voteId}
GO
UPDATE b_vote_answer SET b_vote_answer.COUNTER=E.COUNTER
FROM (
	SELECT COUNT(VEA.ID) COUNTER, VEA.ANSWER_ID
	FROM b_vote_event_answer VEA 
	INNER JOIN b_vote_answer VA ON (VA.ID=VEA.ANSWER_ID)
	INNER JOIN b_vote_question VQ ON (VQ.ID=VA.QUESTION_ID)
	WHERE VQ.VOTE_ID={$voteId}
	GROUP BY VEA.ANSWER_ID
) E
WHERE b_vote_answer.ID=E.ANSWER_ID
GO
UPDATE b_vote_user SET b_vote_user.COUNTER=E.COUNTER
FROM (
	SELECT COUNT(ID) COUNTER, VOTE_USER_ID 
	FROM b_vote_event 
	WHERE VALID='Y'
	GROUP BY VOTE_USER_ID
) E 
WHERE b_vote_user.ID=E.VOTE_USER_ID AND b_vote_user.ID IN (SELECT VOTE_USER_ID FROM b_vote_event WHERE VOTE_ID={$voteId})
GO
SQL
			);
		}
		elseif ($connection instanceof OracleConnection)
		{
			$connection->executeSqlBatch(<<<SQL
UPDATE b_vote V SET V.COUNTER=(
	SELECT COUNT(VE.ID) 
	FROM b_vote_event VE 
	WHERE VE.VOTE_ID=V.ID) 
WHERE V.ID={$voteId}
/
UPDATE b_vote_question VQ SET VQ.COUNTER=(
	SELECT COUNT(VEQ.ID) 
	FROM b_vote_event_question VEQ 
	WHERE VEQ.QUESTION_ID=VQ.ID)
WHERE VQ.VOTE_ID={$voteId}
/
UPDATE b_vote_answer VA SET VA.COUNTER=(
	SELECT COUNT(ID)
	FROM b_vote_event_answer
	WHERE ANSWER_ID=VA.ID)
WHERE VA.QUESTION_ID IN (
	SELECT ID 
	FROM b_vote_question 
	WHERE VOTE_ID={$voteId}
)
/
UPDATE b_vote_user VU SET VU.COUNTER=(
	SELECT COUNT(ID)
	FROM b_vote_event 
	WHERE VU.ID=VOTE_USER_ID AND VALID='Y')
WHERE VU.ID IN (SELECT VOTE_USER_ID FROM b_vote_event WHERE VOTE_ID={$voteId})
/
SQL
			);
		}
	}

	/**
	 * @param int $voteId Vote Id.
	 * @return void
	 */
	public static function resetStatistic($voteId)
	{
		$connection = Application::getInstance()->getConnection();
		if ($connection instanceof MysqlCommonConnection)
		{
			$connection->executeSqlBatch(<<<SQL
UPDATE b_vote_user U 
	INNER JOIN (
		SELECT count(ID) as COUNTER, VOTE_USER_ID
		FROM b_vote_event 
		WHERE VOTE_ID={$voteId}
		GROUP BY VOTE_USER_ID
	) E ON (E.VOTE_USER_ID=U.ID) 
	SET U.COUNTER = (CASE WHEN U.COUNTER - E.COUNTER > 0 THEN U.COUNTER - E.COUNTER ELSE 0 END);
UPDATE b_vote set COUNTER=0 WHERE ID={$voteId};
UPDATE b_vote_question SET COUNTER=0 WHERE VOTE_ID={$voteId};
UPDATE b_vote_answer A 
	INNER JOIN b_vote_question Q ON (Q.ID=A.QUESTION_ID AND Q.VOTE_ID={$voteId}) 
	SET A.COUNTER = 0;
DELETE FROM b_vote_event WHERE VOTE_ID={$voteId};
DELETE EQ FROM b_vote_event_question EQ
	JOIN b_vote_question Q ON Q.ID = EQ.QUESTION_ID
WHERE Q.VOTE_ID = {$voteId};
DELETE EA FROM b_vote_event_answer EA
	JOIN b_vote_answer A ON A.ID = EA.ANSWER_ID
	JOIN b_vote_question Q ON Q.ID = A.QUESTION_ID
WHERE Q.VOTE_ID = {$voteId};

SQL
			);
		}
		else if ($connection instanceof MssqlConnection)
		{
			$connection->executeSqlBatch(<<<SQL
UPDATE b_vote_user SET b_vote_user.COUNTER = (CASE WHEN b_vote_user.COUNTER - E.COUNTER > 0 THEN b_vote_user.COUNTER - E.COUNTER ELSE 0 END) 
FROM (
	SELECT count(ID) as COUNTER, VOTE_USER_ID
	FROM b_vote_event 
	WHERE VOTE_ID={$voteId}
	GROUP BY VOTE_USER_ID
) E
WHERE (E.VOTE_USER_ID=b_vote_user.ID)
GO
UPDATE b_vote set COUNTER=0 WHERE ID={$voteId}
GO
UPDATE b_vote_question SET COUNTER=0 WHERE VOTE_ID={$voteId}
GO
UPDATE b_vote_answer SET COUNTER=0 
FROM (
	SELECT ID FROM b_vote_question WHERE VOTE_ID={$voteId}
) Q
WHERE b_vote_answer.QUESTION_ID=Q.ID
GO
DELETE FROM b_vote_event WHERE VOTE_ID={$voteId}
GO
DELETE EQ FROM b_vote_event_question EQ
	JOIN b_vote_question Q ON Q.ID = EQ.QUESTION_ID
WHERE Q.VOTE_ID = {$voteId}
GO
DELETE EA FROM b_vote_event_answer EA
	JOIN b_vote_answer A ON A.ID = EA.ANSWER_ID
	JOIN b_vote_question Q ON Q.ID = A.QUESTION_ID
WHERE Q.VOTE_ID = {$voteId}
GO
SQL
			);
		}
		elseif ($connection instanceof OracleConnection)
		{
			$connection->executeSqlBatch(<<<SQL
UPDATE b_vote_user U SET U.COUNTER = (
	SELECT (CASE WHEN U.COUNTER - E.COUNTER > 0 THEN U.COUNTER - E.COUNTER ELSE 0 END)
	FROM (
		SELECT count(ID) as COUNTER, VOTE_USER_ID
			FROM b_vote_event 
			WHERE VOTE_ID={$voteId}
			GROUP BY VOTE_USER_ID
	) E
	WHERE E.VOTE_USER_ID = U.ID
) 
WHERE U.ID IN (
	SELECT VOTE_USER_ID
	FROM b_vote_event 
	WHERE VOTE_ID={$voteId}
	GROUP BY VOTE_USER_ID
)
/
UPDATE b_vote set COUNTER=0 WHERE ID={$voteId}
/
UPDATE b_vote_question SET COUNTER=0 WHERE VOTE_ID={$voteId}
/
UPDATE b_vote_answer SET COUNTER=0 
WHERE QUESTION_ID IN (
	SELECT ID FROM b_vote_question WHERE VOTE_ID={$voteId}
)
/
DELETE FROM b_vote_event WHERE VOTE_ID={$voteId}
/
DELETE FROM b_vote_event_question 
WHERE QUESTION_ID IN ( 
	SELECT ID from b_vote_question WHERE VOTE_ID = {$voteId}
)
/
DELETE FROM b_vote_event_answer 
WHERE ANSWER_ID IN ( 
	SELECT A.ID 
		FROM b_vote_answer A
		JOIN b_vote_question Q ON (Q.ID = A.QUESTION_ID)
	WHERE Q.VOTE_ID = {$voteId}
)
/
SQL
			);
			/***************** Event OnVoteReset *******************************/
			foreach (GetModuleEvents("vote", "onVoteReset", true) as $event)
				ExecuteModuleEventEx($event, array($voteId));
			/***************** /Event ******************************************/
		}
	}

	/**
	 * @param int $eventId Event ID.
	 * @return boolean
	 */
	public static function deleteEvent($eventId)
	{
		if (!is_integer($eventId))
			throw new ArgumentTypeException("event ID");
		else if ($eventId <= 0)
			throw new ArgumentNullException("event ID");

		self::setValid($eventId, "N");
		$connection = Application::getInstance()->getConnection();
		$connection->queryExecute("DELETE FROM b_vote_event_answer WHERE EVENT_QUESTION_ID IN (SELECT VEQ.ID FROM b_vote_event_question VEQ WHERE VEQ.EVENT_ID={$eventId})");
		$connection->queryExecute("DELETE FROM b_vote_event_question WHERE EVENT_ID={$eventId}");
		$connection->queryExecute("DELETE FROM b_vote_event WHERE ID={$eventId}");
		return $connection->getAffectedRowsCount() > 0;
	}
	/**
	 * @param int $eventId Event ID.
	 * @param string $valid Validation ("Y" || "N").
	 * @return boolean
	 */
	public static function setValid($eventId, $valid)
	{
		$valid = ($valid == "Y" ? "Y" : "N");
		$eventId = intval($eventId);
		if ($eventId <= 0)
			return false;

		$dbRes = EventTable::getList(array(
			'select' => array(
				'V_' => '*',
				'Q_' => 'QUESTION.*',
				'A_' => 'QUESTION.ANSWER.*'),
			'filter' => array(
				'ID' => $eventId,
				'!=VALID' => $valid),
			'order' => array(
				'ID' => 'ASC',
				'QUESTION.ID' => 'ASC',
				'QUESTION.ANSWER.ID' => 'ASC')));
		if (($res = $dbRes->fetch()) && $res)
		{
			$questions = array();
			$answers = array();
			EventTable::update($eventId, array("VALID" => $valid));
			VoteTable::setCounter(array($res["V_VOTE_ID"]), ($valid == "Y"));
			UserTable::setCounter(array($res["V_VOTE_USER_ID"]), ($valid == "Y"));
			do
			{
				$questions[] = $res["Q_QUESTION_ID"];
				$answers[] = $res["A_ANSWER_ID"];
			} while ($res = $dbRes->fetch());

			QuestionTable::setCounter(array_unique($questions), ($valid == "Y"));
			AnswerTable::setCounter($answers, ($valid == "Y"));
			return true;
		}
		return false;
	}

	public static function getFieldName($id, $questionId)
	{
		return str_replace(array("#ID#", "#QUESTION_ID#"), array($id, $questionId), self::EVENT_FIELD_BALLOT_TEMPLATE);
	}
	public static function getMessageFieldName($id, $questionId, $answerId)
	{
		return str_replace(array("#ID#", "#QUESTION_ID#", "#ANSWER_ID#"), array($id, $questionId, $answerId), self::EVENT_FIELD_MESSAGE_TEMPLATE);
	}
	public static function getExtrasFieldName($id, $name)
	{
		return str_replace(array("#ID#", "#ENTITY_ID#"), array($id, $name), self::EVENT_FIELD_EXTRAS_TEMPLATE);
	}

	public static function getDataFromRequest($id, array $request)
	{
		if (
			array_key_exists(self::EVENT_FIELD_NAME, $request) &&
			is_array($request[self::EVENT_FIELD_NAME]) &&
			array_key_exists($id, $request[self::EVENT_FIELD_NAME]) &&
			is_array($request[self::EVENT_FIELD_NAME][$id])
		)
		{
			$data = [];
			if (array_key_exists("BALLOT", $request[self::EVENT_FIELD_NAME][$id]))
			{
				foreach ($request[self::EVENT_FIELD_NAME][$id]["BALLOT"] as $qId => $answerIds)
				{
					$answerIds = is_array($answerIds) ? $answerIds : array($answerIds);
					foreach ($answerIds as $answerId)
					{
						$data["BALLOT"] = is_array($data["BALLOT"]) ? $data["BALLOT"] : [];
						$data["BALLOT"][$qId] = is_array($data["BALLOT"][$qId]) ? $data["BALLOT"][$qId] : [];
						$data["BALLOT"][$qId][$answerId] = true;
					}
				}
			}
			if (array_key_exists("MESSAGE", $request[self::EVENT_FIELD_NAME][$id]))
			{
				foreach ($request[self::EVENT_FIELD_NAME][$id]["MESSAGE"] as $qId => $answerIds)
				{

					foreach ($answerIds as $answerId => $message)
					{
						$message = trim($message);
						if ($message <> '')
						{
							$data["MESSAGE"][$qId] = is_array($data["MESSAGE"][$qId]) ? $data["MESSAGE"][$qId] : [];
							$data["MESSAGE"][$qId][$answerId] = $message;
						}
					}
				}
			}
			if (array_key_exists("EXTRAS", $request[self::EVENT_FIELD_NAME][$id]))
			{
				$data["EXTRAS"] = $request[self::EVENT_FIELD_NAME][$id]["EXTRAS"];
			}
			if (!empty($data))
				return $data;
		}
		return null;
	}
	/**
	 * @param $data
	 * @return array
	 */
	public function check(array $ballot)
	{
		$questions = $this->vote->getQuestions();
		$fields = array();
		$data = (array_key_exists("BALLOT", $ballot) ? $ballot["BALLOT"] : []);
		$message = (array_key_exists("MESSAGE", $ballot) ? $ballot["MESSAGE"] : []);
		foreach ($questions as $questionId => $question)
		{
			if (array_key_exists($question["ID"], $data) && is_array($data[$question["ID"]]))
			{
				$answers = array_intersect_key($data[$question["ID"]], $question["ANSWERS"]);
				if ($question["FIELD_TYPE"] === QuestionTypes::COMPATIBILITY && array_key_exists($question["ID"], $message))
				{
					foreach($message[$question["ID"]] as $id => $value)
					{
						$value = trim($value);
						if ($value <> '')
						{
							$answers[$id] = true;
						}
					}
				}
				if (!empty($answers))
				{
					//region  this code should not exists
					if ($question["FIELD_TYPE"] == QuestionTypes::COMPATIBILITY)
					{
						$singleVal = array(AnswerTypes::RADIO => false, AnswerTypes::DROPDOWN => false);
						$res = [];
						foreach ($answers as $id => $value)
						{
							$answer = $question["ANSWERS"][$id];
							switch ($answer["FIELD_TYPE"])
							{
								case AnswerTypes::RADIO :
								case AnswerTypes::DROPDOWN :
									if (!$singleVal[$answer["FIELD_TYPE"]])
									{
										$singleVal[$answer["FIELD_TYPE"]] = true;
										$res[$id] = $value;
									}
									break;
								default :
									$res[$id] = $value;
									break;
							}
						}
						if (!empty($res))
						{
							$fields[$question["ID"]] = $res;
						}
					}
					//endregion
					else if ($question["FIELD_TYPE"] == QuestionTypes::RADIO ||
						$question["FIELD_TYPE"] == QuestionTypes::DROPDOWN)
					{
						$val = reset($answers);
						$fields[$question["ID"]] = array(
							key($answers) => $val
						);
					}
					else
					{
						$fields[$question["ID"]] = $answers;
					}
					//region Check for message text from form
					$res = $fields[$question["ID"]];
					if (array_key_exists($question["ID"], $message))
					{
						$message[$question["ID"]] = is_array($message[$question["ID"]]) ? $message[$question["ID"]] : [];
						foreach ($fields[$question["ID"]] as $id => $value)
						{
							if (array_key_exists($id, $message[$question["ID"]]))
								$fields[$question["ID"]][$id] = trim($message[$question["ID"]][$id]);
						}
					}
					if (empty($fields[$question["ID"]]))
					{
						unset($fields[$question["ID"]]);
					}
					//endregion
				}
			}
			if ($question['REQUIRED'] === 'Y' && $question['ACTIVE'] === 'Y' && !array_key_exists($question["ID"], $fields))
			{
				$this->errorCollection->add(array(new Error(Loc::getMessage("VOTE_REQUIRED_MISSING"), "QUESTION_".$questionId)));
			}
		}
		if (empty($fields))
			$this->errorCollection->add(array(new Error(Loc::getMessage("USER_VOTE_EMPTY"), "VOTE_".$this->vote->getId())));

		return $fields;
	}

	public function add(array $eventFields, array $ballot, $setCounter = true)
	{
		$this->errorCollection->clear();
		$fields = $this->check($ballot);
		if (!$this->errorCollection->isEmpty())
			return false;
		$eventFields = array(
			"VOTE_ID"			=> $this->vote->getId(),
			"VOTE_USER_ID"		=> $eventFields["VOTE_USER_ID"],
			"DATE_VOTE"			=> (array_key_exists("DATE_VOTE", $eventFields) ? $eventFields["DATE_VOTE"] : new \Bitrix\Main\Type\DateTime()),
			"STAT_SESSION_ID"	=> $eventFields["STAT_SESSION_ID"],
			"IP"				=> $eventFields["IP"],
			"VALID"				=> $eventFields["VALID"] ?: "Y",
			"VISIBLE" 			=> ($eventFields["VISIBLE"] ?: "Y")
		);
		if (array_key_exists("EXTRAS", $ballot) && is_array($ballot["EXTRAS"]) && array_key_exists("VISIBLE", $ballot["EXTRAS"]))
			$eventFields["VISIBLE"] = ($ballot["EXTRAS"]["VISIBLE"] === "N" ? "N" : "Y");

		// Compatibility
		$sqlAnswers = array();
		foreach ($fields as $questionId => $fieldsAnswer)
		{
			foreach ($fieldsAnswer as $answerId => $value)
			{
				$sqlAnswers[$questionId][$answerId] = array(
					"ANSWER_ID" => $answerId,
					"MESSAGE" => is_string($value)? mb_substr($value, 0, 2000) : "");
			}
		}

		/***************** Event onBeforeVoting ****************************/
		foreach (GetModuleEvents("vote", "onBeforeVoting", true) as $event)
		{
			if (ExecuteModuleEventEx($event, array(&$eventFields, &$sqlAnswers)) === false)
			{
				$this->errorCollection->add(array(new Error("onBeforeVoting error", "VOTE_".$eventFields["VOTE_ID"])));
				return false;
			}
		}
		/***************** /Event ******************************************/
		if (!empty($sqlAnswers) && ($eventId = EventTable::add($eventFields)->getId()) && $eventId > 0)
		{
			$ids = array();
			$answerIdsForCounter = array();
			foreach ($sqlAnswers as $questionId => $fieldsAnswer)
			{
				if (($eventQId = EventQuestionTable::add(array("EVENT_ID" => $eventId, "QUESTION_ID" => $questionId))->getId()) && $eventQId > 0)
				{
					$ids[$questionId] = [
						"EVENT_ID" => $eventQId,
						"ANSWERS" => []
					];
					foreach ($fieldsAnswer as $answerId => $res)
					{
						if (($eventAId = EventAnswerTable::add(array(
								"EVENT_QUESTION_ID" => $eventQId,
								"ANSWER_ID" => $res["ANSWER_ID"],
								"MESSAGE" => $res["MESSAGE"]))->getId()
							) && $eventAId > 0)
						{
							$ids[$questionId]["ANSWERS"][$answerId] = [
								"EVENT_ID" => $eventAId,
								"EVENT_QUESTION_ID" => $eventQId,
								"ANSWER_ID" => $res["ANSWER_ID"],
								"MESSAGE" => $res["MESSAGE"]
							];
							$answerIdsForCounter[] = $answerId;
						}
					}
					if (empty($ids[$questionId]))
					{
						EventQuestionTable::delete($eventQId);
						unset($ids[$questionId]);
					}
				}
			}

			if (!empty($ids))
			{
				if ($setCounter)
				{
					VoteTable::setCounter(array($this->vote->getId()), true);
					QuestionTable::setCounter(array_keys($ids), true);
					AnswerTable::setCounter($answerIdsForCounter, true);
				}

				return new EventResult(array(
					"EVENT_ID" => $eventId,
					"VOTE_ID"			=> $eventFields["VOTE_ID"],
					"VOTE_USER_ID"		=> $eventFields["VOTE_USER_ID"],
					"DATE_VOTE"			=> $eventFields["DATE_VOTE"],
					"STAT_SESSION_ID"	=> $eventFields["SESS_SESSION_ID"],
					"IP"				=> $eventFields["IP"],
					"VISIBLE"			=> $eventFields["VISIBLE"],
					"VALID"				=> $eventFields["VALID"],
					"BALLOT" => $ids
				));
			}
			EventTable::delete($eventId);
		}
		return false;
	}
}