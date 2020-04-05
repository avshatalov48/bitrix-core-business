<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Vote;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ArgumentException;
use \Bitrix\Vote\Base\BaseObject;
use \Bitrix\Vote\DBResult;
use \Bitrix\Main\SystemException;
Loc::loadMessages(__FILE__);

/**
 * Class AttachTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> OBJECT_ID int,
 * <li> MODULE_ID string(32),
 * <li> ENTITY_TYPE string(100),
 * <li> ENTITY_ID int,
 * <li> CREATE_TIME datetime,
 * <li> CREATED_BY int
 * </ul>
 *
 */
class AttachTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_vote_attached_object';
	}

	/**
	 * Returns entity map definition.
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
			'OBJECT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_OBJECT_ID'),
			),
			'MODULE_ID' => array(
				'data_type' => 'string',
				'size' => 32,
				'title' => Loc::getMessage('V_TABLE_FIELD_MODULE_ID')
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'string',
				'size' => 100,
				'title' => Loc::getMessage('V_TABLE_FIELD_ENTITY_TYPE')
			),
			'ENTITY_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_OBJECT_ID'),
			),
			'CREATE_TIME' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('V_TABLE_FIELD_TIMESTAMP_X'),
			),
			'CREATED_BY' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_AUTHOR_ID'),
			),
			'VOTE' => array(
				'data_type' => '\Bitrix\Vote\VoteTable',
				'reference' => array(
					'=this.OBJECT_ID' => 'ref.ID',
				),
				'join_type' => 'INNER',
			),
		);
	}

	/**
	 * @param array $parameters Array in terms ORM.
	 * @return \Bitrix\Vote\DBResult
	 */
	public static function getList(array $parameters = array())
	{
		return new DBResult(parent::getList($parameters));
	}

	/**
	 * Removes group of attaches
	 * @param array $filter Array in terms ORM.
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function deleteByFilter(array $filter)
	{
		if (!$filter)
		{
			throw new \Bitrix\Main\ArgumentNullException('filter');
		}

		$result = static::getList(array(
			'select' => array('ID'),
			'filter' => $filter,
		));
		while($row = $result->fetch())
		{
			if(!empty($row['ID']))
			{
				$resultDelete = static::delete($row['ID']);
				if(!$resultDelete->isSuccess())
				{
					return false;
				}
			}
		}
		return true;
	}
}

class Attach extends BaseObject
{
	public static $storage = array();

	/**
	 * Return array where first key is attach array, second - vote array
	 * @param integer $id Attach ID.
	 * @return array|null
	 */
	public static function getData($id)
	{
		$filter = array();

		if (is_array($id))
		{
			$filter = array_change_key_case($id, CASE_UPPER);
			$id = md5(serialize($filter));
		}
		else if (($id = intval($id)) && $id > 0)
			$filter["ID"] = $id;
		else
			return null;

		if (!array_key_exists($id, self::$storage))
		{
			self::$storage[$id] = null;
			$dbRes = AttachTable::getList(array(
				'select' => array(
					'O_' => "*",
					'V_' => 'VOTE.*',
					'V_LAMP' => 'VOTE.LAMP',
					'Q_' => 'VOTE.QUESTION.*',
					'A_' => 'VOTE.QUESTION.ANSWER',
				),
				'order' => array(
					'VOTE.QUESTION.C_SORT' => 'ASC',
					'VOTE.QUESTION.ID' => 'ASC',
					'VOTE.QUESTION.ANSWER.C_SORT' => 'ASC',
					'VOTE.QUESTION.ANSWER.ID' => 'ASC',
				),
				'filter' => $filter
			));
			$attaches = array();
			$votes = array();
			while (($res = $dbRes->fetch()) && $res)
			{
				$attach = array();
				$vote = array();
				$question = array();
				$answer = array();

				foreach ($res as $key => $val)
				{
					if (strpos($key, "O_") === 0)
						$attach[substr($key, 2)] = $val;
					else if (strpos($key, "V_") === 0)
						$vote[substr($key, 2)] = $val;
					else if (strpos($key, "Q_") === 0)
						$question[substr($key, 2)] = $val;
					else if (strpos($key, "A_") === 0)
						$answer[substr($key, 2)] = $val;
				}
				if (!array_key_exists($attach["ID"], $attaches))
					$attaches[$attach["ID"]] = $attach;
				if (!array_key_exists($vote["ID"], $votes))
					$votes[$vote["ID"]] = array_merge($vote, array("QUESTIONS" => array()));
				$vote = $votes[$vote["ID"]];

				$questions = &$vote["QUESTIONS"];

				$qid = "".$question["ID"];

				if (!array_key_exists($qid, $questions))
					$questions[$qid] = array_merge($question, array("ANSWERS" => array()));
				if (!array_key_exists($qid, Question::$storage))
					Question::$storage[$qid] = $question;
				$answers = &$questions[$qid]["ANSWERS"];
				if (!empty($answer))
				{
					switch ($answer["FIELD_TYPE"])
					{
						case 1://checkbox
							$answer["FIELD_NAME"] = 'vote_checkbox_' . $qid;
							break;
						case 2://select
							$answer["FIELD_NAME"] = 'vote_dropdown_' . $qid;
							break;
						case 3://multiselect
							$answer["FIELD_NAME"] = 'vote_multiselect_' . $qid;
							break;
						case 4://text field
							$answer["FIELD_NAME"] = 'vote_field_' . $answer["ID"];
							break;
						case 5 :
							$answer["FIELD_NAME"] = 'vote_field_' . $answer["ID"];
							break;
						default: //radio
							$answer["FIELD_NAME"] = 'vote_radio_' . $qid;
							break;
					}
					$answer["~PERCENT"] = ($question["COUNTER"] > 0 ? $answer["COUNTER"] * 100 / $question["COUNTER"] : 0);
					$answer["PERCENT"] = round($answer["~PERCENT"], 2);
					if (!array_key_exists($answer["ID"], $answers))
						$answers[$answer["ID"]] = $answer;
					if (!array_key_exists($answer["ID"], Answer::$storage))
						Answer::$storage[$answer["ID"]] = $answer;
				}
				$votes[$vote["ID"]] = $vote;
			}

			foreach ($votes as $vote)
				Vote::$storage[$vote["ID"]] = $vote;
			foreach ($attaches as $attach)
			{
				self::$storage[$attach["ID"]] = array($attach, $votes[$attach["OBJECT_ID"]]);
				if (is_string($id))
				{
					self::$storage[$id] = (is_array(self::$storage[$id]) ? self::$storage[$id] : array());
					self::$storage[$id][$attach["ID"]] = array($attach, $votes[$attach["OBJECT_ID"]]);
				}
			}
		}
		return self::$storage[$id];
	}

	/**
	 * Returns array of attaches linked to special entity
	 * @param array $id Array("ENTITY_TYPE" => "blog", "ENTITY_ID" => 89);.
	 * @return mixed
	 */
	public static function getDataByEntity(array $id)
	{
		$id1 = md5(serialize($id));
		if (!array_key_exists($id1, self::$storage))
		{
			self::$storage[$id1] = array();

			$dbRes = AttachTable::getList(array(
				'select' => array(
					'O_' => "*",
					'V_' => 'VOTE.*',
					'V_LAMP' => 'VOTE.LAMP',
					'Q_' => 'VOTE.QUESTION.*',
					'A_' => 'VOTE.QUESTION.ANSWER',
				),
				'order' => array(
					'VOTE.QUESTION.C_SORT' => 'ASC',
					'VOTE.QUESTION.ID' => 'ASC',
					'VOTE.QUESTION.ANSWER.C_SORT' => 'ASC',
					'VOTE.QUESTION.ANSWER.ID' => 'ASC',
				),
				'filter' => array(
					'ENTITY_TYPE' => $id['ENTITY_TYPE'],
					'ENTITY_ID' => $id['ENTITY_ID']
				)
			));
			if (($res = $dbRes->fetch()) && $res)
			{
				$attach = array();
				$vote = array();
				foreach ($res as $key => $val)
					if (strpos($key, "O_") === 0)
						$attach[substr($key, 2)] = $val;
					else if (strpos($key, "V_") === 0)
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
					$qid = "".$question["ID"];
					if (!array_key_exists($qid, $questions))
						$questions[$qid] = array_merge($question, array("ANSWERS" => array()));
					if (!array_key_exists($qid, Question::$storage))
						Question::$storage[$qid] = $question;
					$answers = &$questions[$qid]["ANSWERS"];
					if (!empty($answer))
					{
						if (!array_key_exists($answer["ID"], $answers))
							$answers[$answer["ID"]] = $answer;
						if (!array_key_exists($answer["ID"], Answer::$storage))
							Answer::$storage[$answer["ID"]] = $answer;
					}

				} while (($res = $dbRes->fetch()) && $res);
				Vote::$storage[$vote["ID"]] = $vote;
				self::$storage[$id1] = array($attach, $vote);
			}
		}
		return self::$storage[$id1];
	}
}
