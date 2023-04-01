<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Vote;
use \Bitrix\Main\AccessDeniedException;
use \Bitrix\Main\ArgumentNullException;
use \Bitrix\Main\ArgumentTypeException;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Error;
use \Bitrix\Main\ErrorCollection;
use \Bitrix\Main\InvalidOperationException;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ArgumentException;
use \Bitrix\Main\NotSupportedException;
use \Bitrix\Main\Type\DateTime;
use \Bitrix\Vote\Attachment\Connector;
use \Bitrix\Vote\Base\BaseObject;
use \Bitrix\Vote\DBResult;
use \Bitrix\Main\SystemException;
use \Bitrix\Vote\Event;
use \Bitrix\Main\ObjectNotFoundException;

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
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Attach_Query query()
 * @method static EO_Attach_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Attach_Result getById($id)
 * @method static EO_Attach_Result getList(array $parameters = array())
 * @method static EO_Attach_Entity getEntity()
 * @method static \Bitrix\Vote\EO_Attach createObject($setDefaultValues = true)
 * @method static \Bitrix\Vote\EO_Attach_Collection createCollection()
 * @method static \Bitrix\Vote\EO_Attach wakeUpObject($row)
 * @method static \Bitrix\Vote\EO_Attach_Collection wakeUpCollection($rows)
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

class Attach extends BaseObject implements \ArrayAccess
{
	/** @var array */
	protected $attach;
	/** @var Vote */
	protected $vote;
	/** @var array */
	protected $connector;
	/** @var Channel */
	protected $channel;

	public static $storage = array();
	protected static $loaded = array(
		"attachIds" => array(),
		"voteIds" => array(),
		"entities" => array()
	);

	/**
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 */
	function init()
	{
		$attach = null;
		$vote = null;
		if (is_array($this->id))
		{
			$attach = $this->id;
		}
		else
		{
			$data = self::getData($this->id);
			if (is_null($data))
			{
				throw new ObjectNotFoundException("Attach");
			}
			[$attach, $vote] = $data;
		}
		if (!is_array($attach) || empty($attach))
		{
			throw new ObjectNotFoundException("Wrong attach id!");
		}

		if (!array_key_exists("MODULE_ID", $attach) || $attach["MODULE_ID"] == '')
			throw new ArgumentNullException("module ID");
		if (!array_key_exists("ENTITY_TYPE", $attach) || $attach["ENTITY_TYPE"] == '')
			throw new ArgumentNullException("entity type");
		if (array_key_exists("ID", $attach))
			$this->id = intval($attach["ID"]);
		else
		{
			$this->id = null;
			unset($attach["ID"]);
		}

		$this->attach = $attach;

		if (is_array($vote))
		{
			$this->setVote($vote["ID"]);
			$this->setStorage($this->vote["CHANNEL_ID"]);
		}
	}

	/**
	 * @param int|Vote $vote
	 */
	public function setVote($vote)
	{
		if ($vote instanceof Vote)
			$this->vote = $vote;
		else
			$this->vote = Vote::loadFromId($vote);
	}

	/**
	 * @param integer $id
	 * @throws ArgumentNullException
	 */
	public function setStorage($id)
	{
		$this->channel = new Channel($id);
	}
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
					'VOTE.ID' => 'ASC',
					'VOTE.QUESTION.C_SORT' => 'ASC',
					'VOTE.QUESTION.ID' => 'ASC',
					'VOTE.QUESTION.ANSWER.C_SORT' => 'ASC',
					'VOTE.QUESTION.ANSWER.ID' => 'ASC',
				),
				'filter' => $filter
			));
			$attaches = [];
			$images = [];
			$attach = ["ID" => null];
			$vote = ["ID" => null];
			$question = ["ID" => null];

			while (($res = $dbRes->fetch()) && $res)
			{
				$buffer = ["attach" => [], "vote" => [], "question" => []];
				unset($answer);
				$answer = [];
				foreach ($res as $key => $val)
				{
					if (mb_strpos($key, "O_") === 0)
						$buffer["attach"][mb_substr($key, 2)] = $val;
					else if (mb_strpos($key, "V_") === 0)
						$buffer["vote"][mb_substr($key, 2)] = $val;
					else if (mb_strpos($key, "Q_") === 0)
						$buffer["question"][mb_substr($key, 2)] = $val;
					else if (mb_strpos($key, "A_") === 0)
						$answer[mb_substr($key, 2)] = $val;
				}
				if ($buffer["attach"]["ID"] != $attach["ID"])
				{
					unset($attach);
					$attach = $buffer["attach"];
					$attaches[$attach["ID"]] = $attach;
				}
				if ($buffer["vote"]["ID"] != $vote["ID"])
				{
					unset($vote);
					$vote = $buffer["vote"] + array(
						"FIELD_NAME" => \Bitrix\Vote\Event::getExtrasFieldName($attach["ID"], "#ENTITY_ID#"),
						"IMAGE" => null,
						"QUESTIONS" => array());
					if ($vote["IMAGE_ID"] > 0)
						$images[$vote["IMAGE_ID"]] = &$vote["IMAGE"];
					if (!array_key_exists($vote["ID"], Vote::$storage))
						Vote::$storage[$vote["ID"]] = &$vote;
				}
				if ($buffer["question"]["ID"] != $question["ID"])
				{
					unset($question);
					$question = $buffer["question"] + array(
						"FIELD_NAME" => \Bitrix\Vote\Event::getFieldName($attach["ID"], $buffer["question"]["ID"]),
						"IMAGE" => null,
						"ANSWERS" => array()
					);
					if ($question["IMAGE_ID"] > 0)
						$images[$question["IMAGE_ID"]] = &$question["IMAGE"];
					if (!array_key_exists($question["ID"], Question::$storage))
						Question::$storage[$question["ID"]] = &$question;
					$vote["QUESTIONS"][$question["ID"]] = &$question;
				}
				$answer["FIELD_NAME"] = $answer["~FIELD_NAME"] = \Bitrix\Vote\Event::getFieldName($attach["ID"], $question["ID"]);
				$answer["MESSAGE_FIELD_NAME"] = \Bitrix\Vote\Event::getMessageFieldName($attach["ID"], $question["ID"], $answer["ID"]);
				if (
					$answer["FIELD_TYPE"] == \Bitrix\Vote\AnswerTypes::TEXT ||
					$answer["FIELD_TYPE"] == \Bitrix\Vote\AnswerTypes::TEXTAREA
				)
				{
					if ($question["FIELD_TYPE"] == \Bitrix\Vote\QuestionTypes::COMPATIBILITY)
						$answer["FIELD_NAME"] = $answer["MESSAGE_FIELD_NAME"];
				}
				else if ($question["FIELD_TYPE"] != \Bitrix\Vote\QuestionTypes::COMPATIBILITY)
				{
					$answer["FIELD_TYPE"] = $question["FIELD_TYPE"];
				}
				$answer["~PERCENT"] = ($question["COUNTER"] > 0 ? $answer["COUNTER"] * 100 / $question["COUNTER"] : 0);
				$answer["PERCENT"] = round($answer["~PERCENT"], 2);
				$question["ANSWERS"][$answer["ID"]] = &$answer;
				Answer::$storage[$answer["ID"]] = &$answer;
				unset($answer);
			}
			unset($vote); unset($question);
			//region Getting images
			if (count($images) > 0)
			{
				$dbRes = \Bitrix\Main\FileTable::getList(array('select' => array('*'), 'filter' => array('ID' => array_keys($images))));
				while ($res = $dbRes->fetch())
				{
					$images[$res["ID"]] = $res + array("SRC" => \CFile::GetFileSRC($res));
				}
			}
			//endregion
			//region Setting data into local storages
			foreach ($attaches as $attach)
			{
				self::$storage[$attach["ID"]] = array($attach, Vote::$storage[$attach["OBJECT_ID"]]);
				if (is_string($id))
				{
					self::$storage[$id] = (is_array(self::$storage[$id]) ? self::$storage[$id] : array());
					self::$storage[$id][$attach["ID"]] = array($attach, Vote::$storage[$attach["OBJECT_ID"]]);
				}
			}
			//endregion
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
					if (mb_strpos($key, "O_") === 0)
						$attach[mb_substr($key, 2)] = $val;
					else if (mb_strpos($key, "V_") === 0)
						$vote[mb_substr($key, 2)] = $val;
				$vote["QUESTIONS"] = array();
				$questions = &$vote["QUESTIONS"];
				do
				{
					$question = array(); $answer = array();
					foreach ($res as $key => $val)
					{
						if (mb_strpos($key, "Q_") === 0)
							$question[mb_substr($key, 2)] = $val;
						else if (mb_strpos($key, "A_") === 0)
							$answer[mb_substr($key, 2)] = $val;
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

	/**
	 * Checks rights to read current attached object.
	 * @param int $userId Id of user.
	 * @return bool
	 * @throws SystemException
	 */
	public function canRead($userId)
	{
		return $this->getConnector()->canRead($userId);
	}

	/**
	 * Checks rights for voting.
	 * @param int $userId Id of user.
	 * @return bool
	 */
	public function canParticipate($userId)
	{
		return $this->getConnector()->canRead($userId) && is_object($this->vote) && $this->vote["LAMP"] == "green";
	}

	public function canVote($userId)
	{
		return $this->vote->canVote($userId);
	}

	public function canRevote($userId)
	{
		return $this->vote->canRevote($userId);
	}

	public function canReadResult($userId)
	{
		return $this->vote->canReadResult($userId);
	}
	/**
	 * Checks rights to update current attached object.
	 * @param int $userId Id of user.
	 * @return bool
	 * @throws SystemException
	 */
	public function canEdit($userId)
	{
		return $this->getConnector()->canEdit($userId);
	}

	/**
	 * Returns connector instance for attached object.
	 * @return Connector|null
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getConnector()
	{
		if ($this->connector === null)
		{
			$this->connector = Connector::buildFromAttachedObject($this);
		}
		return $this->connector;
	}

	/**
	 * @return Channel
	 */
	public function getStorage()
	{
		if (!($this->channel instanceof Channel))
		{
			$this->setStorage($this->vote instanceof Vote ? $this->vote["CHANNEL_ID"] : null);
		}
		return $this->channel;
	}
	/**
	 * Returns attachment id.
	 * @return int|null
	 */
	public function getAttachId()
	{
		return array_key_exists("ID", $this->attach) ? $this->attach["ID"] : null;
	}
	/**
	 * Returns vote id.
	 * @return int|null
	 */
	public function getVoteId()
	{
		return is_object($this->vote) ? $this->vote["ID"] : null;
	}
	/**
	 * Returns module id.
	 * @return string
	 */
	public function getModuleId()
	{
		return $this->attach["MODULE_ID"];
	}
	/**
	 * Returns entity type.
	 * @return string
	 */
	public function getEntityType()
	{
		return $this->attach["ENTITY_TYPE"];
	}
	/**
	 * Returns entity id.
	 * @return string
	 */
	public function getEntityId()
	{
		return $this->attach["ENTITY_ID"];
	}

	/**
	 * @return void
	 */
	public function fillStatistic()
	{
		if (is_object($this->vote))
			$this->vote->fillStatistic();
	}

	/**
	 * Deletes attach and vote in some cases.
	 * @return boolean;
	 */
	public function delete()
	{
		if (empty($this->vote))
			return true;

		if ($this->attach["ID"] > 0)
			AttachTable::delete($this->attach["ID"]);

		$othersAttaches = AttachTable::getList(array(
			"select" => array("ID", "OBJECT_ID"),
			"filter" => array("OBJECT_ID" => $this->vote["ID"]),
			'order' => array(
				'ID' => 'ASC'
			)
		))->fetch();

		if (empty($othersAttaches) && ($channel = $this->getStorage()) && $channel["HIDDEN"] == "Y")
			Vote::delete($this->vote["ID"]);

		return true;
	}

	/**
	 * Checks array for correct data/
	 * @param array &$data Array(
	"TITLE" => "ABC...",
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
	);.
	 * @return void
	 * @throws AccessDeniedException
	 */
	public function checkData(array &$data)
	{
		$channel = $this->getStorage();
		if ($channel["ACTIVE"] !== "Y")
			throw new AccessDeniedException(Loc::getMessage("VOTE_CHANNEL_IS_NOT_ACTIVE"));
		$data = array_merge($data, (is_null($this->vote) ? [
			"ACTIVE" => "Y",
			"DATE_START" => new DateTime(),
		] : []), [
			"CHANNEL_ID" => $channel["ID"],
			"DATE_END" => (isset($data["DATE_END"]) ? new DateTime($data["DATE_END"]) : (new DateTime())->add("1Y"))
		]);
		$this->getConnector()->checkFields($data);
		Vote::checkData($data, $data["ID"]);
		if (($data["TITLE"] ?? null) == '' && is_array($data["QUESTIONS"]))
		{
			$q = reset($data["QUESTIONS"]);
			if (is_array($q) && $q["QUESTION"] <> '')
			{
				$data["TITLE"] = $q["QUESTION"];
			}
		}
	}

	/**
	 * Update vote data.
	 * @param array $data Array(
	"TITLE" => "ABC...",
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
	);.
	 * @param int $createdBy User ID.
	 * @return bool
	 */
	public function save($data, $createdBy = 0)
	{
		if (!isset($data["AUTHOR_ID"]))
			$data["AUTHOR_ID"] = $createdBy;

		$this->checkData($data);

		$voteId = Vote::saveData(is_null($this->vote) ? 0 : $this->vote["ID"], $data);
		if ($voteId > 0)
		{
			if (!array_key_exists("ID", $this->attach))
			{
				$id = AttachTable::add(array(
					'MODULE_ID' => $this->getModuleId(),
					'OBJECT_ID' => $voteId,
					'ENTITY_ID' => $this->getEntityId(),
					'ENTITY_TYPE' => $this->getEntityType(),
					'CREATED_BY' => $createdBy,
					'CREATE_TIME' => new DateTime()
				))->getId();
			}
			else
			{
				$id = $this->attach["ID"];
			}
			[$attach, $vote] = \Bitrix\Vote\Attach::getData($id);
			$this->attach = $attach;
			$this->vote = $vote;
		}
		else if ($this->attach["ID"] ?? null > 0)
		{
			$this->attach = null;
			$this->vote = null;
		}
		return true;
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
	 * @throws InvalidOperationException
	 */
	public function voteFor(array $request)
	{
		if (!is_object($this->vote))
			throw new InvalidOperationException("Poll is not found.");
		$res = \Bitrix\Vote\Event::getDataFromRequest($this->getAttachId(), $request);
		if (empty($res)) // for custom templates
			$result = $this->vote->voteFor($request, ["revote" => true]);
		else
			$result = $this->vote->registerEvent($res, ["revote" => true], User::getCurrent());
		if (!$result)
			$this->errorCollection->add($this->vote->getErrors());
		return $result;
	}
	/**
	 * Exports data of voting into excel file
	 * @return void
	 * @throws InvalidOperationException
	 */
	public function exportExcel()
	{
		if (!is_object($this->vote))
			throw new InvalidOperationException("Poll is not found.");
		$this->vote->exportExcel();
	}
	/**
	 * Checks if current user voted for this vote.
	 * @param int $userId User ID.
	 * @return bool|int
	 */
	public function isVotedFor($userId)
	{
		if ($this->vote)
			return $this->vote->isVotedFor($userId);
		return false;
	}

	/**
	 * Prolongs voting period.
	 * @return bool
	 * @throws InvalidOperationException
	 */
	public function resume()
	{
		if (!is_object($this->vote))
			throw new InvalidOperationException("Poll is not found.");
		return $this->vote->resume();
	}

	/**
	 * Finishes voting period.
	 * @return bool
	 * @throws InvalidOperationException
	 */
	public function stop()
	{
		if (!is_object($this->vote))
			throw new InvalidOperationException("Poll is not found.");
		return $this->vote->stop();
	}

	/**
	 * @param string $offset Key for vote or attach data array.
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		if (is_array($this->attach) && array_key_exists($offset, $this->attach) || is_object($this->vote) && isset($this->vote[$offset]))
			return true;
		if ($offset == "VOTE_ID" && is_object($this->vote))
			return true;
		return false;
	}

	/**
	 * @param string $offset Key for vote or attach data array.
	 * @return array|mixed|null
	 */
	public function offsetGet($offset)
	{
		if (is_array($this->attach) && array_key_exists($offset, $this->attach))
			return $this->attach[$offset];
		if (is_object($this->vote))
		{
			if (isset($this->vote[$offset]))
				return $this->vote[$offset];
			if ($offset == "VOTE_ID")
				return $this->vote["ID"];
		}
		return null;
	}

	/**
	 * Is not supported.
	 * @param string $offset Key for vote or attach data array.
	 * @param mixed $value Value for vote or attach data array.
	 * @return void
	 * @throws NotSupportedException
	 */
	public function offsetSet($offset, $value)
	{
		throw new NotSupportedException('Model provide ArrayAccess only for reading');
	}

	/**
	 * Is not supported.
	 * @param string $offset Key for vote or attach data array.
	 * @return void
	 * @throws NotSupportedException
	 */
	public function offsetUnset($offset)
	{
		throw new NotSupportedException('Model provide ArrayAccess only for reading');
	}
	/**
	 * @param integer $id Entity ID.
	 * @param bool $shouldBeNewIfIdIsNull
	 * @return BaseObject
	 */
	public static function loadFromId($id, $shouldBeNewIfIdIsNull = false)
	{
		return parent::loadFromId($id, true);
	}
}
