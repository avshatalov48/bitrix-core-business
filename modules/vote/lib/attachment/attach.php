<?php
namespace Bitrix\Vote\Attachment;

use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\InvalidOperationException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Vote\Vote;
use Bitrix\Vote\Channel;
use Bitrix\Vote\AttachTable;

Loc::loadMessages(__FILE__);

class Attach implements \ArrayAccess
{
	protected $attach = null;
	/** @var Vote */
	protected $vote = null;

	protected $connector = null;
	protected $storage;

	protected $errorCollection;

	protected static $loaded = array(
		"attachIds" => array(),
		"voteIds" => array(),
		"entities" => array()
	);

	/**
	 * Attach constructor.
	 * @param array $attach Array("ID" => 1, "ENTITY_TYPE" => "blog", "ENTITY_ID" => 98, ...);.
	 * @param null $vote Array("ID" => 3, ...);.
	 * @throws ArgumentNullException
	 */
	function __construct(array $attach, $vote = null)
	{
		if (!array_key_exists("MODULE_ID", $attach) || strlen($attach["MODULE_ID"]) <= 0)
			throw new ArgumentNullException("module ID");
		if (!array_key_exists("ENTITY_TYPE", $attach) || strlen($attach["ENTITY_TYPE"]) <= 0)
			throw new ArgumentNullException("entity type");
		if (array_key_exists("ID", $attach) && $attach["ID"] <= 0)
			unset($attach["ID"]);

		$this->attach = $attach;

		if (is_array($vote) && $vote["ID"] > 0)
			$this->vote = Vote::loadFromId($vote["ID"]);
		else if (is_object($vote))
			$this->vote = $vote;

		$this->storage = new Channel($vote["CHANNEL_ID"]);
		$this->errorCollection = new ErrorCollection;
		$this->init();
	}
	/**
	 * exists only for child class
	 * @return $this
	 */
	public function init()
	{
		return $this;
	}

	/**
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * @inheritdoc
	 * Returns an error with the necessary code.
	 * @param string|int $code The code of the error.
	 * @return Error|null
	 */
	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
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
		return $this->storage;
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
	 * @return array|null
	 */
	public function getStatistic()
	{
		return (is_object($this->vote) ? $this->vote->getStatistic() : null);
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

		if (empty($othersAttaches) && ($channel = $this->vote->getChannel()) && $channel["HIDDEN"] == "Y")
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
		if ($this->storage["ACTIVE"] !== "Y")
			throw new AccessDeniedException(Loc::getMessage("VOTE_CHANNEL_IS_NOT_ACTIVE"));
		$data = array_merge($data, (is_null($this->vote) ? array(
			"DATE_START" => GetTime(\CVote::GetNowTime(), "FULL"),
		) : array()), array(
			"CHANNEL_ID" => $this->storage["ID"],
			"DATE_END" => GetTime((isset($data["DATE_END"]) ? MakeTimeStamp($data["DATE_END"]) : 1924984799), "FULL")
		));
		$this->getConnector()->checkFields($data);
		Vote::checkData($data["ID"], $data);
		if (strlen($data["TITLE"]) <= 0 && is_array($data["QUESTIONS"]))
		{
			$q = reset($data["QUESTIONS"]);
			if (is_array($q) && strlen($q["QUESTION"]) > 0)
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
			list($attach, $vote) = \Bitrix\Vote\Attach::getData($id);
			$this->attach = $attach;
			$this->vote = $vote;
		}
		else if ($this->attach["ID"] > 0)
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
		return $this->vote->voteFor($request);
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
	 * @param int $id Attachment ID.
	 * @return Attach
	 * @throws ArgumentNullException
	 * @throws ArgumentTypeException
	 */
	public static function loadFromAttachId($id)
	{
		if ($id <= 0)
		{
			throw new ArgumentNullException("id");
		}
		$res = \Bitrix\Vote\Attach::getData($id);
		if (is_null($res))
		{
			throw new ArgumentNullException("Attach");
		}
		list($attach, $vote) = $res;

		return new static ($attach, $vote);
	}

	/**
	 * @param array $attach Data array from DB.
	 * @param integer $id Vote ID.
	 * @return static
	 * @throws ArgumentNullException
	 * @throws ArgumentTypeException
	 */
	public static function loadFromVoteId(array $attach, $id)
	{
		if ($id <= 0)
		{
			throw new ArgumentNullException("id");
		}
		$vote = Vote::getData($id);
		if (is_null($vote))
		{
			throw new ArgumentTypeException("Object", "array");
		}
		return new static($attach, $vote);

	}

	/**
	 * @param array $attach Data array from DB.
	 * @param array $voteParams Array(
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
	 * @return Attach
	 */
	public static function loadEmptyAttach(array $attach, array $voteParams)
	{
		return new static ($attach, $voteParams);
	}

	/**
	 * @param array $filter Array in terms ORM.
	 * @return Attach[]
	 * @throws ArgumentNullException
	 * @throws ArgumentTypeException
	 */
	public static function loadFromEntity(array $filter)
	{
		$filter = array_change_key_case($filter, CASE_UPPER);
		if (empty($filter))
			throw new ArgumentNullException("filter");

		$return = array();
		$res = \Bitrix\Vote\Attach::getData($filter);
		if (is_array($res))
		{
			foreach ($res as $attach)
			{
				$return[$attach[0]["ID"]] = new static ($attach[0], $attach[1]);
			}
		}
		return $return;
	}

	/**
	 * Deletes attach by Filter.
	 * @param array $filter Array in terms of ORM.
	 * @return void
	 */
	public static function detachByFilter(array $filter)
	{
		$votes = self::loadFromEntity($filter);
		foreach ($votes as $v)
			$v->delete();
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
}

