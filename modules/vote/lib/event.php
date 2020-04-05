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
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\DB\MssqlConnection;
use Bitrix\Main\DB\MysqlCommonConnection;
use Bitrix\Main\DB\OracleConnection;
use Bitrix\Main\Application;

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
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('V_TABLE_FIELD_ID'),
			),
			'VOTE_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_VOTE_ID'),
			),
			'VOTE_USER_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_VOTE_USER_ID'),
			),
			'DATE_VOTE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('V_TABLE_FIELD_DATE_VOTE'),
			),
			'STAT_SESSION_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_STAT_SESSION_ID')
			),
			'IP' => array(
				'data_type' => 'string',
				'size' => 15,
				'title' => Loc::getMessage('V_TABLE_FIELD_IP')
			),
			'VALID' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'Y',
				'title' => Loc::getMessage('V_TABLE_FIELD_VALID')
			),
			'QUESTION' => array(
				'data_type' => '\Bitrix\Vote\EventQuestionTable',
				'reference' => array(
					'=this.ID' => 'ref.EVENT_ID',
				),
				'join_type' => 'LEFT',
			),
			'USER' => array(
				'data_type' => '\Bitrix\Vote\UserTable',
				'reference' => array(
					'=this.VOTE_USER_ID' => 'ref.ID'
				),
				'join_type' => 'LEFT'
			)
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
 * Class EventAnswerTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> EVENT_QUESTION_ID int,
 * <li> ANSWER_ID int,
 * <li> MESSAGE text,
 * </ul>
 *
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
			)
		);
	}
}

class Event {
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
				'A_' => 'QUESTION.ANSWER.*'
			),
			'filter' => array('ID' => $eventId, '!=VALID' => $valid),
			'order' => array(
				'ID' => 'ASC',
				'QUESTION.ID' => 'ASC',
				'QUESTION.ANSWER.ID' => 'ASC'
			)
		));
		if (($res = $dbRes->fetch()) && $res)
		{
			$questions = array();
			$answers = array();
			EventTable::update($eventId, array("VALID" => $valid));
			VoteTable::setCounter(array($res["V_VOTE_ID"]), ($valid == "Y"));
			UserTable::setCounter(array($res["V_VOTE_USER_ID"]), ($valid == "Y"));
			do {
				$questions[] = $res["Q_QUESTION_ID"];
				$answers[] = $res["A_ANSWER_ID"];
			} while ($res = $dbRes->fetch());

			QuestionTable::setCounter(array_unique($questions), ($valid == "Y"));
			AnswerTable::setCounter($answers, ($valid == "Y"));
			return true;
		}
		return false;
	}
}