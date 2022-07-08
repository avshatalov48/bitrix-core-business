<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Vote;
use \Bitrix\Main\AccessDeniedException;
use Bitrix\Main\Context;
use \Bitrix\Main\Entity;
use Bitrix\Main\ErrorCollection;
use \Bitrix\Main\ORM\Data\AddResult;
use \Bitrix\Main\ORM\Data\Result;
use \Bitrix\Main\Application;
use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ArgumentException;
use \Bitrix\Main\ORM\Data\UpdateResult;
use \Bitrix\Main\ORM\Event;
use \Bitrix\Main\ORM\Fields\DatetimeField;
use \Bitrix\Main\ORM\Fields\ExpressionField;
use \Bitrix\Main\ORM\Fields\TextField;
use \Bitrix\Main\ORM\Fields\Validators\DateValidator;
use \Bitrix\Main\ORM\Fields\IntegerField;
use \Bitrix\Main\ORM\Fields\EnumField;
use \Bitrix\Main\ORM\Fields\StringField;
use \Bitrix\Main\ORM\Fields\BooleanField;
use \Bitrix\Main\ORM\Fields\Relations\Reference;
use \Bitrix\Main\ORM\Fields\FieldError;
use \Bitrix\Main\ORM\EntityError;
use \Bitrix\Main\ORM\Query\Join;
use \Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Web\MimeType;
use \Bitrix\Vote\Base\BaseObject;
use \Bitrix\Vote\Vote\Option;
use \Bitrix\Vote\Vote\EventLimits;
use \Bitrix\Vote\Vote\Anonymity;
use \Bitrix\Main\Type\DateTime;
use \Bitrix\Main\FileTable;


Loc::loadMessages(__FILE__);

/**
 * Class VoteTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CHANNEL_ID int,
 * <li> C_SORT int,
 * <li> ACTIVE bool mandatory default "Y",
 * <li> ANONYMITY int,
 * <li> NOTIFY bool mandatory default "N",
 * <li> AUTHOR_ID int,
 * <li> AUTHOR reference,
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
 * UNIQUE_TYPE = (UNIQUE_TYPE_SESSION | UNIQUE_TYPE_COOKIE | UNIQUE_TYPE_IP | UNIQUE_TYPE_USER_ID | UNIQUE_TYPE_USER_ID_NEW)
 * <li> KEEP_IP_SEC int,
 * <li> OPTIONS int,
 * </ul>
 *
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Vote_Query query()
 * @method static EO_Vote_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Vote_Result getById($id)
 * @method static EO_Vote_Result getList(array $parameters = array())
 * @method static EO_Vote_Entity getEntity()
 * @method static \Bitrix\Vote\EO_Vote createObject($setDefaultValues = true)
 * @method static \Bitrix\Vote\EO_Vote_Collection createCollection()
 * @method static \Bitrix\Vote\EO_Vote wakeUpObject($row)
 * @method static \Bitrix\Vote\EO_Vote_Collection wakeUpCollection($rows)
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
		return "b_vote";
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getMap()
	{
		$now = Application::getInstance()->getConnection()->getSqlHelper()->getCurrentDateTimeFunction();
		return array(
			(new IntegerField("ID", ["primary" => true, "autocomplete" => true])),
			(new IntegerField("CHANNEL_ID", ["required" => true])),
			(new IntegerField("C_SORT", ["default_value" => 100])),
			(new BooleanField("ACTIVE", ["values" => ["N", "Y"], "default_value" => "Y"])),
			(new IntegerField("ANONYMITY", ["default_value" => Anonymity::PUBLICLY])),
			(new EnumField("NOTIFY", ["values" => ["N", "Y", "I"], "default_value" => "N"])),
			(new IntegerField("AUTHOR_ID")),
			(new Reference("AUTHOR", \Bitrix\Main\UserTable::class, Join::on("this.AUTHOR_ID", "ref.ID"))),
			(new DatetimeField("TIMESTAMP_X", ["default_value" => function(){return new DateTime();}])),
			(new DatetimeField("DATE_START", ["default_value" => function(){return new DateTime();}, "required" => true, "validation" => function() {
				return [
					new DateValidator,
					[__CLASS__, "validateActivityDate"]
				];
			}])),
			(new DatetimeField("DATE_END", ["default_value" => function(){
				$time = new DateTime();
				$time->add("1D");
				return $time;
			}, "required" => true, "validation" => function() {
				return [
					new DateValidator,
					[__CLASS__, "validateActivityDate"]
				];
			}])),
			(new StringField("URL", ["size" => 255])),
			(new IntegerField("COUNTER")),
			(new StringField("TITLE", ["size" => 255])),
			(new TextField("DESCRIPTION")),
			(new EnumField("DESCRIPTION_TYPE", ["values" => ["text", "html"], "default_value" => "text"])),
			(new IntegerField("IMAGE_ID")),
			(new Reference("IMAGE", FileTable::class, Join::on("this.IMAGE_ID", "ref.ID"))),
			(new StringField("EVENT1", ["size" => 255])),
			(new StringField("EVENT2", ["size" => 255])),
			(new StringField("EVENT3", ["size" => 255])),
			(new IntegerField("UNIQUE_TYPE", ["default_value" => EventLimits::BY_IP|EventLimits::BY_USER_ID])),
			(new IntegerField("KEEP_IP_SEC", ["default_value" => 604800])), // one week
			(new IntegerField("OPTIONS", ["default_value" => Option::ALLOW_REVOTE])),
			(new ExpressionField("LAMP",
				"CASE ".
					"WHEN (%s='Y' AND %s='Y' AND %s <= {$now} AND {$now} <= %s AND %s='Y') THEN 'yellow' ".
					"WHEN (%s='Y' AND %s='Y' AND %s <= {$now} AND {$now} <= %s AND %s!='Y') THEN 'green' ".
					"ELSE 'red' ".
				"END",
				["CHANNEL.ACTIVE", "ACTIVE", "DATE_START", "DATE_END", "CHANNEL.VOTE_SINGLE",
				"CHANNEL.ACTIVE", "ACTIVE", "DATE_START", "DATE_END", "CHANNEL.VOTE_SINGLE"])),
			(new Reference("CHANNEL", ChannelTable::class, Join::on("this.CHANNEL_ID", "ref.ID"))),
			(new Reference("QUESTION", QuestionTable::class, Join::on("this.ID", "ref.VOTE_ID"))),
			(new Reference("USER", \Bitrix\Main\UserTable::class, Join::on("this.AUTHOR_ID", "ref.ID"))),
		);
	}
	/**
	 * @param mixed $value   Value to check.
	 * @param array $primary Has no use in this function.
	 * @param array $row     Has no use in this function.
	 * @param DateTimeField $field   Field metadata.
	 * @return FieldError|bool
	 */
	public static function validateActivityDate($value, $primary, $row, $field)
	{
		/**@var $field */
		if (empty($value))
			return new FieldError(
				$field, Loc::getMessage("VOTE_ERROR_DATE_VOTE_IS_EMPTY"), $field->getName()
			);

		return true;
	}

	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @throws \Bitrix\Main\ObjectException
	 * @return \Bitrix\Main\ORM\EventResult
	 */
	public static function onBeforeAdd(\Bitrix\Main\ORM\Event $event)
	{
		$result = new \Bitrix\Main\ORM\EventResult();
		if (($events = GetModuleEvents("vote", "onBeforeVoteAdd", true)) && !empty($events))
		{
			/** @var array $data */
			$data = $event->getParameter("fields");
			foreach ($events as $ev)
			{
				if (ExecuteModuleEventEx($ev, array(&$data)) === false)
				{
					$result->addError(new EntityError("Error: ".serialize($ev), "event"));
					return $result;
				}
			}
			if ($data != $event->getParameter("fields"))
			{
				$result->modifyFields($data);
			}
		}
		return self::modifyData($event, $result);
	}
	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @return void
	 */
	public static function onAfterAdd(\Bitrix\Main\ORM\Event $event)
	{
		$id = $event->getParameter("id");
		$id = is_array($id) && array_key_exists("ID", $id) ? $id["ID"] : $id;
		$fields = $event->getParameter("fields");
		/***************** Event onAfterVoteAdd ****************************/
		foreach (GetModuleEvents("vote", "onAfterVoteAdd", true) as $event)
			ExecuteModuleEventEx($event, [$id, $fields]);
		/***************** /Event ******************************************/
	}
	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @return \Bitrix\Main\ORM\EventResult|void
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function onBeforeUpdate(\Bitrix\Main\ORM\Event $event)
	{
		$result = new \Bitrix\Main\ORM\EventResult();
		if (($events = GetModuleEvents("vote", "onBeforeVoteUpdate", true)) && !empty($events))
		{
			/** @var array $data */
			$data = $event->getParameter("fields");
			$id = $event->getParameter("id");
			$id = is_array($id) && array_key_exists("ID", $id) ? $id["ID"] : $id;
			foreach ($events as $ev)
			{
				if (ExecuteModuleEventEx($ev, array($id, &$data)) === false)
				{
					$result->addError(new EntityError("Error: ".serialize($ev), "event"));
					return $result;
				}
			}
			if ($data != $event->getParameter("fields"))
			{
				$result->modifyFields($data);
			}
		}
		return self::modifyData($event, $result);
	}

	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @return void
	 */
	public static function onAfterUpdate(\Bitrix\Main\ORM\Event $event)
	{
		$id = $event->getParameter("id");
		$id = is_array($id) && array_key_exists("ID", $id) ? $id["ID"] : $id;
		$fields = $event->getParameter("fields");
		/***************** Event onAfterVoteAdd ****************************/
		foreach (GetModuleEvents("vote", "onAfterVoteUpdate", true) as $event)
			ExecuteModuleEventEx($event, [$id, $fields]);
		/***************** /Event ******************************************/
	}
	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @param \Bitrix\Main\ORM\EventResult $result
	 * @return \Bitrix\Main\ORM\EventResult
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function modifyData(\Bitrix\Main\ORM\Event $event, \Bitrix\Main\ORM\EventResult $result)
	{
		$data = array_merge($event->getParameter("fields"), $result->getModified());
		$fields = [];

		if (isset($data["UNIQUE_TYPE"]) && (
				!($data["UNIQUE_TYPE"] & \Bitrix\Vote\Vote\EventLimits::BY_USER_AUTH) &&
				($data["UNIQUE_TYPE"] & \Bitrix\Vote\Vote\EventLimits::BY_USER_DATE_REGISTER ||
					$data["UNIQUE_TYPE"] & \Bitrix\Vote\Vote\EventLimits::BY_USER_ID)
			))
			$fields["UNIQUE_TYPE"] = $data["UNIQUE_TYPE"] | \Bitrix\Vote\Vote\EventLimits::BY_USER_AUTH;

		foreach (["TIMESTAMP_X", "DATE_START", "DATE_END"] as $key)
		{
			if (isset($data[$key]) && !($data[$key] instanceof DateTime))
				$fields[$key] = DateTime::createFromUserTime($data[$key]);
		}

		//region check image
		if (array_key_exists("IMAGE_ID", $data))
		{
			if ($str = \CFile::CheckImageFile($data["IMAGE_ID"]))
			{
				$result->addError(new FieldError(static::getEntity()->getField("IMAGE_ID"), $str));
			}
			else
			{
				$fields["IMAGE_ID"] = $data["IMAGE_ID"];
				$fields["IMAGE_ID"]["MODULE_ID"] = "vote";
				if ($id = $event->getParameter("id"))
				{
					$id = is_integer($id) ? $id : $id["ID"];
					if ($id > 0 && ($vote = VoteTable::getById($id)->fetch()) && ($vote["IMAGE_ID"] > 0))
					{
						$fields["IMAGE_ID"]["old_file"] = $vote["IMAGE_ID"];
					}
				}
				if (\CFile::SaveForDB($fields, "IMAGE_ID", "") === false)
				{
					$result->unsetField("IMAGE_ID");
				}
			}
		}
		//endregion
		if (!empty($fields))
		{
			$result->modifyFields(array_merge($result->getModified(), $fields));
		}
		return $result;
	}
	/**
	 * @param Result $result
	 * @param mixed $primary
	 * @param array $data
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function checkFields(Result $result, $primary, array $data)
	{
		parent::checkFields($result, $primary, $data);
		if ($result->isSuccess())
		{
			try
			{
				$vote = null;
				//region check activity dates
				/**@var $date["start"] \Bitrix\Main\Type\DateTime */
				/**@var $date["end"] \Bitrix\Main\Type\DateTime */
				$params = null;
				if ($result instanceof AddResult)
				{
					$params = [
						"ID" => null,
						"ACTIVE" => (array_key_exists("ACTIVE", $data) ? $data["ACTIVE"] : "Y"),
						"CHANNEL_ID" => $data["CHANNEL_ID"],
						"DATE_START" => $data["DATE_START"],
						"DATE_END" => $data["DATE_END"]
					];
				}
				else if (array_key_exists("CHANNEL_ID", $data) ||
					array_key_exists("ACTIVE", $data) && $data["ACTIVE"] == "Y" ||
					array_key_exists("DATE_START", $data) ||
					array_key_exists("DATE_END", $data)
				)
				{
					// if it is need to move to other channel or activate or change the dates
					$vote = Vote::loadFromId($primary["ID"]);
					$params = [
						"ID" => $primary["ID"],
						"ACTIVE" => (array_key_exists("ACTIVE", $data) ? $data["ACTIVE"] : $vote["ACTIVE"]),
						"CHANNEL_ID" => (array_key_exists("CHANNEL_ID", $data) ? $data["CHANNEL_ID"] : $vote["CHANNEL_ID"]),
						"DATE_START" => (array_key_exists("DATE_START", $data) ? $data["DATE_START"] : $vote["DATE_START"]),
						"DATE_END" => (array_key_exists("DATE_END", $data) ? $data["DATE_END"] : $vote["DATE_END"])
					];
				}
				if (!is_null($params))
				{
					$params["DATE_START"] = static::getEntity()->getField("DATE_START")->cast($params["DATE_START"]);
					$params["DATE_END"] = static::getEntity()->getField("DATE_END")->cast($params["DATE_END"]);
					if (array_key_exists("DATE_END", $data))
						$data["DATE_END"] = $params["DATE_END"];
					if (!($params["DATE_START"] instanceof DateTime) || !($params["DATE_END"] instanceof DateTime))
						$result->addError(new FieldError(
							static::getEntity()->getField("DATE_START"),
							Loc::getMessage("VOTE_ERROR_DATE_VOTE_IS_WRONG")
						));
					else if ($params["DATE_START"]->getTimestamp() > $params["DATE_END"]->getTimeStamp())
					{
						$result->addError(new FieldError(
							static::getEntity()->getField("DATE_START"),
							Loc::getMessage("VOTE_ERROR_DATE_START_LATER_THAN_END")
						));
					}
					else if ($params["ACTIVE"] == "Y")
					{
						/**@var $channel Channel */
						$channel = Channel::loadFromId($params["CHANNEL_ID"]);
						if ($channel["VOTE_SINGLE"] == "Y")
						{
							$dbRes = VoteTable::getList([
								"select" => ["ID", "TITLE", "DATE_START", "DATE_END"],
								"filter" => (is_null($params["ID"]) ? [] : [
										"!ID" => $params["ID"]]) + [
										"CHANNEL_ID" => $channel["ID"],
										[
											"LOGIC" => "OR",
											"><DATE_START" =>  [$params["DATE_START"], $params["DATE_END"]],
											"><DATE_END" =>  [$params["DATE_START"], $params["DATE_END"]],
											[
												"<=DATE_START" =>  $params["DATE_START"],
												">=DATE_END" => $params["DATE_END"]
											]
										]
									]
							]);
							if ($res = $dbRes->fetch())
							{
								$field = static::getEntity()->getField("DATE_START");
								$result->addError(new FieldError(
									$field,
									Loc::getMessage("VOTE_ERROR_SAME_DATE_VOTE_IS_ALREADY_EXISTS", ["#VOTE#" => $res["TITLE"]." [".$res["ID"]."]"])
								));
							}
						}
					}
				}
				//endregion
			}
			catch (\Exception $e)
			{
				$result->addError(new Error(
					$e->getMessage()
				));
			}
		}
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
	/** @var Result[] */
	protected static $canVoteStorage = [];
	public static $storage = array();
	public static $statStorage = array();

	public function __construct($id)
	{
		if (!($id > 0))
			throw new \Bitrix\Main\ArgumentNullException("vote id");
		parent::__construct($id);
	}

	public function init()
	{
		$data = self::getData($this->id);
		if ($data === null)
			throw new ArgumentException("Wrong vote id!");
		$this->vote = array_diff_key($data, array("QUESTIONS" => ""));
		foreach ($data["QUESTIONS"] as $q)
		{
			$this->questions[$q["ID"]] = $q;
		}
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->addEventHandler("vote", "onAfterVoteQuestionAdd", array($this, "clearCache"));
		$eventManager->addEventHandler("vote", "onAfterVoteQuestionUpdate", array($this, "clearCache"));
		$eventManager->addEventHandler("vote", "onAfterVoteQuestionDelete", array($this, "clearCache"));
		$eventManager->addEventHandler("vote", "onVoteQuestionActivate", array($this, "clearCache"));
		$eventManager->addEventHandler("vote", "onVoteReset", array($this, "clearCache"));
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
			/**@var $dbRes \Bitrix\Main\ORM\Query\Result */
			$dbRes = VoteTable::getList(array(
				"select" => array(
					"V_" => "*",
					"V_LAMP" => "LAMP",
					"Q_" => "QUESTION.*",
					"A_" => "QUESTION.ANSWER",
				),
				"order" => array(
					"QUESTION.C_SORT" => "ASC",
					"QUESTION.ID" => "ASC",
					"QUESTION.ANSWER.C_SORT" => "ASC",
					"QUESTION.ANSWER.ID" => "ASC",
				),
				"filter" => array(
					"ID" => $id
				)
			));
			// TODO Remake to a \Bitrix\Main\ORM\Objectify\Collection and its method ->fill()
			if (($row = $dbRes->fetch()) && $row)
			{
				$images = array();
				$vote = array();
				foreach ($row as $key => $val)
					if (mb_strpos($key, "V_") === 0)
						$vote[mb_substr($key, 2)] = $val;
				$vote += array(
						"IMAGE" => null,
						"FIELD_NAME" => \Bitrix\Vote\Event::getExtrasFieldName($vote["ID"], "#ENTITY_ID#"),
						"QUESTIONS" => array());
				if ($vote["IMAGE_ID"] > 0)
					$images[$vote["IMAGE_ID"]] = &$vote["IMAGE"];
				$question = array("ID" => null);
				do
				{
					$answer = array();
					foreach ($row as $key => $val)
					{
						if (mb_strpos($key, "A_") === 0)
							$answer[mb_substr($key, 2)] = $val;
					}
					if ($answer["IMAGE_ID"] > 0)
						$images[$answer["IMAGE_ID"]] = &$answer["IMAGE"];
					if ($answer["QUESTION_ID"] != $question["ID"])
					{
						unset($question);
						$question = array();
						foreach ($row as $key => $val)
						{
							if (mb_strpos($key, "Q_") === 0)
								$question[mb_substr($key, 2)] = $val;
						}
						$question += array(
							"IMAGE" => null,
							"FIELD_NAME" => \Bitrix\Vote\Event::getFieldName($vote["ID"], $question["ID"]),
							"ANSWERS" => array()
						);
						if ($question["IMAGE_ID"] > 0)
							$images[$question["IMAGE_ID"]] = &$question["IMAGE"];
						$vote["QUESTIONS"][$question["ID"]] = &$question;
					}
					$answer["FIELD_NAME"] = $answer["~FIELD_NAME"] = \Bitrix\Vote\Event::getFieldName($vote["ID"], $question["ID"]);
					$answer["MESSAGE_FIELD_NAME"] = \Bitrix\Vote\Event::getMessageFieldName($vote["ID"], $question["ID"], $answer["ID"]);
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
					unset($answer);
				} while (($row = $dbRes->fetch()) && $row);
				unset($question);
				//region Getting images
				if (count($images) > 0)
				{
					$dbRes = \Bitrix\Main\FileTable::getList(array("select" => array("*"), "filter" => array("ID" => array_keys($images))));
					while ($res = $dbRes->fetch())
					{
						$images[$res["ID"]] = $res + array("SRC" => \CFile::GetFileSRC($res));
					}
				}
				//endregion
				//region Setting data into local storages
				foreach ($vote["QUESTIONS"] as $question)
				{
					$questionId = strval($question["ID"]);
					if (!array_key_exists($questionId, Question::$storage))
						Question::$storage[$questionId] = $question;
					foreach ($question["ANSWERS"] as $answer)
					{
						if (!array_key_exists($answer["ID"], Answer::$storage))
							Answer::$storage[$answer["ID"]] = $answer;
					}
				}
				self::$storage[$id] = $vote;
				//endregion
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
	public static function checkData(array &$data, $voteId = 0)
	{
		$result = new AddResult();
		$questionsToRevise = [];
		if ($voteId > 0)
		{
			$result = new UpdateResult();
			$vote = static::getData($voteId);
			if (is_null($vote))
				throw new ArgumentException(Loc::getMessage("VOTE_VOTE_NOT_FOUND", array("#ID#", $voteId)));
			$questionsToRevise = ($vote["QUESTIONS"] ?: []);
		}
		$questionsToSave = isset($data["QUESTIONS"]) && is_array($data["QUESTIONS"]) ? $data["QUESTIONS"] : [];
		unset($data["QUESTIONS"]);
		VoteTable::checkFields($result, ["ID" => $voteId], $data);
		if (!$result->isSuccess())
			throw new ArgumentException(implode("", $result->getErrorMessages()));
		/************** Check Data *****************************************/
		$questions = array();
		foreach ($questionsToSave as $key => $question)
		{
			if ($question["DEL"] == "Y")
				continue;

			$question["ID"] = intval($question["ID"]);
			$question = array(
				"ID" => (array_key_exists($question["ID"], $questionsToRevise) ? $question["ID"] : null),
				"QUESTION" => trim($question["QUESTION"]),
				"QUESTION_TYPE" => trim($question["QUESTION_TYPE"]),
				"FIELD_TYPE" => $question["FIELD_TYPE"],
				"ANSWERS" => (is_array($question["ANSWERS"]) ? $question["ANSWERS"] : array()));

			$savedAnswers = ($question["ID"] > 0 ? $questionsToRevise[$question["ID"]]["ANSWERS"] : array());
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
				$result->addError(new Error(Loc::getMessage("VOTE_QUESTION_EMPTY", array("#NUMBER#" => $key)), "QUESTION_".$key));
			}
			else if (empty($question["ANSWERS"]))
			{
				$result->addError(new Error(Loc::getMessage("VOTE_ANSWERS_EMPTY", array("#QUESTION#" => HtmlFilter::encode($question["QUESTION"]))), "QUESTION_".$key));
			}
			else
			{
				foreach ($savedAnswers as $answer)
				{
					$question["ANSWERS"][] = $answer + array("DEL" => "Y");
				}
				$questions[] = $question;
				unset($questionsToRevise[$question["ID"]]);
			}
		}
		if (!$result->isSuccess())
		{
			throw new ArgumentException(implode("", $result->getErrorMessages()));
		}
		foreach ($questionsToRevise as $question)
		{
			$questions[] = $question + array("DEL" => "Y");
		}
		$data += array("QUESTIONS" => $questions);
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
		if (!($voteId > 0) && empty($data["QUESTIONS"]))
		{
			return 0;
		}
		if ($voteId)
		{
			$result = VoteTable::update($voteId, $data);
		}
		else
		{
			$result = VoteTable::add($data);
			if ($result->isSuccess())
				$voteId = $result->getId();
		}
		if (!$result->isSuccess())
			throw new ArgumentException(implode("", $result->getErrorMessages()));
		else if ($result instanceof UpdateResult)
			$vote = static::getData($voteId);
		else
			$vote = \Bitrix\Vote\VoteTable::getById($voteId)->fetch();
		$vote += ["QUESTIONS" => []];
		/************** Check Data *****************************************/
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
		if ($type == "im" && \Bitrix\Main\Loader::includeModule("im"))
		{
			$url = "";
			if (!empty($vote["URL"]))
			{
				if (defined("SITE_SERVER_NAME"))
					$url = SITE_SERVER_NAME;
				$url = (!empty($url) ? $url : \COption::GetOptionString("main", "server_name"));
				if (!empty($url))
					$url = (\CMain::IsHTTPS() ? "https" : "http") . "://" . $url . $vote["URL"];
			}

			// send notification
			$gender = "";
			if ($event["VISIBLE"] == "Y" && $this->getUser()->getParam("PERSONAL_GENDER") == "F")
				$gender = "_F";
			$res = array(
				"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
				"TO_USER_ID" => $vote["AUTHOR_ID"],
				"FROM_USER_ID" => ( $event["VISIBLE"] == "Y" ? $this->getUser()->getId() : 0),
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
			);
			\CIMNotify::Add($res);
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
					"ID" => $event["EVENT_ID"],
					"TIME" => GetTime(time(), "FULL"),
					"VOTE_TITLE" => $vote["TITLE"],
					"VOTE_DESCRIPTION" => $vote["DESCRIPTION"],
					"VOTE_ID" => $vote["ID"],
					"VOTE_COUNTER" => $vote["COUNTER"],
					"URL" => $vote["URL"],
					"CHANNEL" => $channel["TITLE"],
					"CHANNEL_ID" => $channel["ID"],
					"VOTER_ID" => $event["VOTE_USER_ID"],
					"USER_NAME" => ($event["VISIBLE"] == "Y" ? $this->getUser()->getFullName() : "Hidden"),
					"LOGIN" => ($event["VISIBLE"] == "Y" ? $this->getUser()->getLogin() : "hidden"),
					"USER_ID" => ($event["VISIBLE"] == "Y" ? $this->getUser()->getID() : 0),
					"STAT_GUEST_ID" => intval($_SESSION["SESS_GUEST_ID"]),
					"SESSION_ID" => intval($_SESSION["SESS_SESSION_ID"]),
					"IP" => \Bitrix\Main\Context::getCurrent()->getServer()->get("REMOTE_ADDR")
				);
				$eventFields["USER_NAME"] = (!!$eventFields["USER_NAME"] && $event["VISIBLE"] == "Y" ? $eventFields["USER_NAME"] : $eventFields["LOGIN"]);
				// VOTE_STATISTIC
				$text = array();
				foreach ($this["QUESTIONS"] as $question)
				{
					if (array_key_exists($question["ID"], $event["BALLOT"]))
					{
						$text[$question["ID"]] = array();
						foreach ($question["ANSWERS"] as $answer)
						{
							if (array_key_exists($answer["ID"], $event["BALLOT"][$question["ID"]]["ANSWERS"]))
							{
								if (($answer["FIELD_TYPE"] == \Bitrix\Vote\AnswerTypes::TEXT || $answer["FIELD_TYPE"] == \Bitrix\Vote\AnswerTypes::TEXTAREA) &&
									$event["BALLOT"][$question["ID"]]["ANSWERS"][$answer["ID"]]["MESSAGE"] !== "")
								{
									$text[$question["ID"]][] = $event["BALLOT"][$question["ID"]]["ANSWERS"][$answer["ID"]]["MESSAGE"];
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
						else
						{
							$text[$question["ID"]] = " - " . $question["QUESTION"] . "\n - ...\n";
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
	 * Complete vote array by data from DB.
	 * @return void
	 */
	public function fillStatistic()
	{
		foreach ($this->questions as &$qs)
			foreach ($qs["ANSWERS"] as &$as)
				$as["STAT"] = array();

		$dbRes = \Bitrix\Vote\EventTable::getList(array(
			"select" => array(
				"V_" => "*",
				"Q_" => "QUESTION.*",
				"A_" => "QUESTION.ANSWER.*",
				"U_ID" => "USER.USER.ID",
				"U_NAME" => "USER.USER.NAME",
				"U_LAST_NAME" => "USER.USER.LAST_NAME",
				"U_SECOND_NAME" => "USER.USER.SECOND_NAME",
				"U_LOGIN" => "USER.USER.LOGIN",
				"U_PERSONAL_PHOTO" => "USER.USER.PERSONAL_PHOTO",
			),
			"filter" => array("VOTE_ID" => $this->id, "VALID" => "Y"),
			"order" => array(
				"USER.USER.LAST_NAME" => "ASC",
				"USER.USER.NAME" => "ASC",
				"USER.USER.LOGIN" => "ASC"
			)
		));
		while ($dbRes && ($res = $dbRes->fetch()))
		{
			if (array_key_exists($res["Q_QUESTION_ID"], $this->questions) &&
				array_key_exists($res["A_ANSWER_ID"], $this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"]))
			{
				$stat = &$this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"][$res["A_ANSWER_ID"]]["STAT"];
				$result = array(
					"USER" => array(
						"ID" => 0,
					),
					"MESSAGE" => $res["A_MESSAGE"]
				);
				if ($this["ANONYMITY"] !== \Bitrix\Vote\Vote\Anonymity::ANONYMOUSLY &&
					$res["V_VISIBLE"] == "Y" && $res["U_ID"] > 0)
				{
					$result["USER"] = array(
						"ID" => $res["U_ID"],
						"NAME" => $res["U_NAME"],
						"LAST_NAME" => $res["U_LAST_NAME"],
						"SECOND_NAME" => $res["U_SECOND_NAME"],
						"LOGIN" => $res["U_LOGIN"],
						"PERSONAL_PHOTO" => $res["U_PERSONAL_PHOTO"],
					);
				}
				$stat[$res["A_ID"]] = $result;
			}
		}
	}

	public function getStatistic() {
		$dbRes = \Bitrix\Vote\EventTable::getList(array(
			"select" => array(
				"V_" => "*",
				"Q_" => "QUESTION.*",
				"A_" => "QUESTION.ANSWER.*",
				"U_ID" => "USER.USER.ID",
				"U_NAME" => "USER.USER.NAME",
				"U_LAST_NAME" => "USER.USER.LAST_NAME",
				"U_SECOND_NAME" => "USER.USER.SECOND_NAME",
				"U_PERSONAL_PHOTO" => "USER.USER.PERSONAL_PHOTO",
			),
			"filter" => array("VOTE_ID" => $this->id, "VALID" => "Y"),
			"order" => array(
				"USER.USER.LAST_NAME" => "ASC",
				"USER.USER.NAME" => "ASC",
				"USER.USER.LOGIN" => "ASC"
			)
		));
		$result = [];
		while ($dbRes && ($res = $dbRes->fetch()))
		{
			if (!array_key_exists($res["V_ID"], $result))
			{
				$result[$res["V_ID"]] = [
					"ID" => $res["V_ID"],
					"DATE" => $res["V_DATE_VOTE"],
					"VISIBLE" => ($this["ANONYMITY"] !== \Bitrix\Vote\Vote\Anonymity::ANONYMOUSLY &&
						$res["V_VISIBLE"] == "Y" ? "Y" : "N"),
					"BALLOT" => [],
					"USER" => ["ID" => 0]
				];
				if ($result[$res["V_ID"]]["VISIBLE"] == "Y" && $res["U_ID"] > 0)
				{
					$result[$res["V_ID"]]["USER"] = array(
						"ID" => $res["U_ID"],
						"NAME" => $res["U_NAME"],
						"LAST_NAME" => $res["U_LAST_NAME"],
						"SECOND_NAME" => $res["U_SECOND_NAME"],
						"LOGIN" => $res["U_LOGIN"],
						"PERSONAL_PHOTO" => $res["U_PERSONAL_PHOTO"],
					);
				}
			}
			$ballot = &$result[$res["V_ID"]]["BALLOT"];
			if (!array_key_exists($res["Q_QUESTION_ID"], $ballot))
			{
				$ballot[$res["Q_QUESTION_ID"]] = [];
			}
			$ballot[$res["Q_QUESTION_ID"]][$res["A_ANSWER_ID"]] = trim($res["A_MESSAGE"]);
		}
		return $result;
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
	 * Returns question array.
	 * @param int $id
	 * @return array|null
	 */
	public function getQuestion(int $id)
	{
		if (array_key_exists($id, $this->questions))
			return $this->questions[$id];
		return null;
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
		VoteTable::update($this->id, ["DATE_END" => (new DateTime())->add("1Y")]);
		$this->clearCache();
	}

	/**
	 * Sets the finish time for voting by current moment
	 * @return void
	 */
	public function stop()
	{
		VoteTable::update($this->id, ["DATE_END" => new DateTime()]);
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
	public function clearCache()
	{
		global $VOTE_CACHE;
		unset($VOTE_CACHE["VOTE"][$this->id]);
		unset(self::$storage[$this->id]);
		unset(self::$canVoteStorage[$this->id]);
	}
	/**
	 * Clears vote events cache
	 * @return void
	 */
	private function clearVotingCache()
	{
		global $VOTE_CACHE;
		unset($VOTE_CACHE["VOTE_CACHE_VOTING"][$this->id]);
		unset(self::$canVoteStorage[$this->id]);
	}

	/**
	 * Exports data of voting into excel file
	 * @param string $type html|xls|csv
	 * @return void
	 */
	public function exportExcel($type = "html")
	{
		global $APPLICATION;
		$nameTemplate = Context::getCurrent()->getCulture()->getFormatName();
		$dateTemplate = \Bitrix\Main\Type\DateTime::getFormat();

		$APPLICATION->restartBuffer();
		while(ob_get_clean());
		header("Content-Transfer-Encoding: binary");

		$statistic = $this->getStatistic();
		$table1 = ["body" => []];
		$table2 = [
			"head" => [Loc::getMessage("V_EXPORT_DATE"), Loc::getMessage("V_EXPORT_NAME")],
			"body" => []
		];

		foreach ($statistic as $event)
		{
			$user = Loc::getMessage("VOTE_GUEST");
			if ($event["VISIBLE"] !== "Y")
			{
				$user = Loc::getMessage("VOTE_ANONYMOUSLY");
			}
			else if ($event["USER"]["ID"] > 0)
			{
				$user = \CUser::formatName($nameTemplate, $event["USER"], true, false);
			}
			/*@var \Bitrix\Main\Type\DateTime $event["DATE"] */
			$row = [
				"DATE" => $event["DATE"]->toUserTime()->format($dateTemplate),
				"USER" => $user
			];

			foreach ($this->questions as $questionId => $question)
			{
				$answerMessage = [];
				if (array_key_exists($questionId, $event["BALLOT"]))
				{
					foreach ($question["ANSWERS"] as $answerId => $answer)
					{
						if (array_key_exists($answerId, $event["BALLOT"][$questionId]))
						{
							if (!array_key_exists("STAT", $this->questions[$questionId]["ANSWERS"][$answerId]))
								$this->questions[$questionId]["ANSWERS"][$answerId]["STAT"] = [];
							$stat = &$this->questions[$questionId]["ANSWERS"][$answerId]["STAT"];
							if ($event["BALLOT"][$questionId][$answerId] <> '')
							{
								$stat[$event["ID"]] = $row["USER"]." (".$event["BALLOT"][$questionId][$answerId].")";
								$answerMessage[] = $event["BALLOT"][$questionId][$answerId];
							}
							else
							{
								$answerMessage[] = $answer["MESSAGE"];
								$stat[$event["ID"]] = $row["USER"];
							}
						}
					}
				}
				$row[] = implode(", ", $answerMessage);
			}
			$table2["body"][] = array_values($row);
		}
		foreach ($this->questions as $questionId => $question)
		{
			$table1["body"][] = [$question["QUESTION"], "", "", ""];
			foreach ($question["ANSWERS"] as $answerId => $answer)
			{
				$table1["body"][] = ["", $answer["MESSAGE"], $answer["COUNTER"], (array_key_exists("STAT", $answer) ? implode(", ", $answer["STAT"]) : "")];
			}
			$table2["head"][] = $question["QUESTION"];
		}

		if ($type === "csv")
		{
			Header("Content-Type: ". MimeType::getByFileExtension("csv"));
			header("Content-Disposition: attachment;filename=vote".$this->id.".csv");

			$f = fopen("php://output", "w");
			fputcsv($f, $table2["head"], ';');
			foreach ($table2["body"] as $row) {
				fputcsv($f, $row, ';');
			}
			fclose($f);
		}
		else
		{
			$mess = [
				"GENERAL_INFO" => Loc::getMessage("V_EXPORT_GENERAL_INFO"),
				"STATISTIC" => Loc::getMessage("V_EXPORT_STATISTIC")
			];
			if ($type === "xls")
			{
				$bodyRows = [];
				foreach ($table1["body"] as $row)
				{
					$bodyRows[] = implode("</Data></Cell><Cell ss:StyleID=\"bold\"><Data ss:Type=\"String\">", $row);
				}
				$table1["body"] = implode("</Data></Cell></Row><Row><Cell><Data ss:Type=\"String\">", $bodyRows);

				$table2["head"] = implode("</Data></Cell><Cell ss:StyleID=\"bold\"><Data ss:Type=\"String\">", $table2["head"]);
				$bodyRows = [];
				foreach ($table2["body"] as $row)
				{
					$bodyRows[] = implode("</Data></Cell><Cell ss:StyleID=\"bold\"><Data ss:Type=\"String\">", $row);
				}
				$table2["body"] = implode("</Data></Cell></Row><Row><Cell><Data ss:Type=\"String\">", $bodyRows);
				$LANG_CHARSET = LANG_CHARSET;

				$res = <<<XML
<?xml version="1.0" charset="{$LANG_CHARSET}"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">
	<Styles>
		<Style ss:ID="bold">
			<Font ss:Bold="1"/>
		</Style>
	</Styles>
	<Worksheet ss:Name="{$mess["GENERAL_INFO"]}">
		<Table>
			<Row>
				<Cell><Data ss:Type="String">{$table1["body"]}</Data></Cell>
			</Row>
		</Table>
	</Worksheet>
	<Worksheet ss:Name="{$mess["STATISTIC"]}">
		<Table>
			<Row>
				<Cell ss:StyleID="bold"><Data ss:Type="String">{$table2["head"]}</Data></Cell>
			</Row>
			<Row>
				<Cell><Data ss:Type="String">{$table2["body"]}</Data></Cell>
			</Row>
		</Table>
	</Worksheet>
</Workbook>
XML;
			}
			else
			{
				$LANG_CHARSET = LANG_CHARSET;

				$bodyRows = [];
				foreach ($table1["body"] as $row)
				{
					$bodyRows[] = implode("</td><td>", $row);
				}
				$table1["body"] = implode("</td></tr><tr><td>", $bodyRows);

				$table2["head"] = implode("</th><th>", $table2["head"]);
				$bodyRows = [];
				foreach ($table2["body"] as $row)
				{
					$bodyRows[] = implode("</td><td>", $row);
				}
				$table2["body"] = implode("</td></tr><tr><td>", $bodyRows);

				$res = <<<HTML
<meta http-equiv="Content-type" content="text/html;charset={$LANG_CHARSET}" />
<p>
<table border="1">
	<tbody>
		<tr><td>{$table1["body"]}</td></tr>
	</tbody>
</table>
</p>
<p>
<table border="1">
	<thead><tr><th>{$table2["head"]}</th></tr></thead>
	<tbody><tr><td>{$table2["body"]}</td></tr></tbody>
</table>
</p>
HTML;
			}
			header("Content-Type: ". MimeType::getByFileExtension("xls"));
			header("Content-Disposition: attachment;filename=vote".$this->id.".xls");
			echo $res;
		}
		\CMain::finalActions();
		die();
	}

	private function getDataFromRequest(array $request)
	{
		$res = \Bitrix\Vote\Event::getDataFromRequest($this->getId(), $request);
		if ($res !== null)
		{
			$data = $res;
		}
		else
		{
			$questions = $this->getQuestions();
			$data = ["EXTRAS" => [], "BALLOT" => [], "MESSAGE" => []];

			foreach ($questions as $question)
			{
				$data["BALLOT"][$question["ID"]] = array();
				foreach ($question["ANSWERS"] as $answer)
				{
					$fieldType = (
							$question["FIELD_TYPE"] == \Bitrix\Vote\QuestionTypes::COMPATIBILITY ||
							$answer["FIELD_TYPE"] == AnswerTypes::TEXTAREA || $answer["FIELD_TYPE"] == AnswerTypes::TEXT ?
								$answer["FIELD_TYPE"] : $question["FIELD_TYPE"]);

					switch ($fieldType)
					{
						case AnswerTypes::RADIO :
						case AnswerTypes::DROPDOWN :
							$fieldName = ($fieldType == AnswerTypes::RADIO ? "vote_radio_" : "vote_dropdown_").$question["ID"];
							if ($request[$fieldName] == $answer["ID"])
								$data["BALLOT"][$question["ID"]][$answer["ID"]] = true;
							break;
						case AnswerTypes::CHECKBOX :
						case AnswerTypes::MULTISELECT :
							$fieldName = ($fieldType == AnswerTypes::CHECKBOX ? "vote_checkbox_" : "vote_multiselect_").$question["ID"];
							if (array_key_exists($fieldName, $request) && is_array($request[$fieldName]) && in_array($answer["ID"], $request[$fieldName]))
								$data["BALLOT"][$question["ID"]][$answer["ID"]] = true;
							break;
						default :
							$fieldName = ($answer["FIELD_TYPE"] == AnswerTypes::TEXT ? "vote_field_" : "vote_memo_") . $answer["ID"];
							$value = trim($request[$fieldName]);
							if ($value <> '')
							{
								if (!array_key_exists($question["ID"], $data["MESSAGE"]))
									$data["MESSAGE"][$question["ID"]] = [];
								$data["MESSAGE"][$question["ID"]][$answer["ID"]] = $value;
								$data["BALLOT"][$question["ID"]][$answer["ID"]] = true;
							}
							break;
					}
				}
			}
		}
		return $data;
	}
	/**
	 * Voting for vote  from current user $USER.
	 * @param array $request Old variant Array("
	 * 							vote_checkbox_".$questionId => array(1,2,3,...),
	 * 							"vote_multiselect_".$questionId => array(1,2,3,...),
	 * 							"vote_dropdown_".$questionId => 12 || "12",
	 * 							"vote_radio_".$questionId => 12 || "12",
	 * 							"vote_field_".$answerId => "12").
	 * 	New variant is
	 * [
	 *  EXTRA => [HIDDEN => N],
	 *  871 => [345 => (Does not matter || text from field)],
	 *  QUESTION_ID => [ANSWER_ID => (Y || text from field)],
	 * ]
	 * @param array $params
	 * @return bool
	 * @throws AccessDeniedException
	 */
	public function voteFor(array $request, $params = [])
	{
		return $this->registerEvent($this->getDataFromRequest($request), $params, User::getCurrent());
	}

	public function registerEvent(array $data, array $params, \Bitrix\Vote\User $user)
	{
		if ($this["LAMP"] == "red")
			throw new AccessDeniedException(Loc::getMessage("VOTE_IS_NOT_ACTIVE"));

		$voteId = $this->getId();
		$userId = $user->getId();
		/** @var \Bitrix\Main\Result $result */
		if ($params["revote"] != true)
		{
			$result = $this->canVote($user);
		}
		//region Delete event If It is possible
		else
		{
			$result = $this->canRevote($user);

			if ($result->isSuccess() && !empty($result->getData()))
			{
				$ids = [];
				foreach ($result->getData() as $res)
					$ids[] = $res["ID"];
				if (!empty($ids))
				{
					$dbRes = \Bitrix\Vote\EventTable::getList([
						"select" => [
							"V_" => "*",
							"Q_" => "QUESTION.*",
							"A_" => "QUESTION.ANSWER.*"],
						"filter" => [
							"VOTE_ID" => $voteId,
							"ID" => $ids],
						"order" => [
							"ID" => "ASC",
							"QUESTION.ID" => "ASC",
							"QUESTION.ANSWER.ID" => "ASC"]
					]);
					if ($dbRes && ($res = $dbRes->fetch()))
					{
						if (\Bitrix\Main\Loader::includeModule("im"))
							\CIMNotify::DeleteByTag("VOTING|".$voteId, $userId);
						$vEId = 0;
						$qEId = 0;
						do
						{
							if ($vEId < $res["V_ID"])
							{
								$vEId = $res["V_ID"];
								\Bitrix\Vote\Event::deleteEvent(intval($res["V_ID"]));
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

								$this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"][$res["A_ANSWER_ID"]]["COUNTER"] = max(
									$this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"][$res["A_ANSWER_ID"]]["COUNTER"] - 1,
									0);
								if ($this->questions[$res["Q_QUESTION_ID"]]["COUNTER"] > 0)
								{
									$this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"][$res["A_ANSWER_ID"]]["~PERCENT"] =
										$this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"][$res["A_ANSWER_ID"]]["COUNTER"] * 100 /
										$this->questions[$res["Q_QUESTION_ID"]]["COUNTER"];
									$this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"][$res["A_ANSWER_ID"]]["PERCENT"] = round($this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"][$res["A_ANSWER_ID"]]["~PERCENT"], 2);
								}
								else
								{
									$this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"][$res["A_ANSWER_ID"]]["~PERCENT"] = 0;
									$this->questions[$res["Q_QUESTION_ID"]]["ANSWERS"][$res["A_ANSWER_ID"]]["PERCENT"] = 0;
								}
							}
						} while ($dbRes && ($res = $dbRes->fetch()));
						$this->clearCache();
						$this->clearVotingCache();
					}
				}
				$result = $this->canVote($user);
			}
		}
		//endregion
		if (!$result->isSuccess())
			throw new AccessDeniedException(implode(" ", $result->getErrorMessages()));

		$event = new \Bitrix\Vote\Event($this);
		if ($event->check($data))
		{
			/**
			 * @var \Bitrix\Main\Type\Dictionary $eventResult
			 */
			$eventFields = array(
				"VOTE_USER_ID"		=> \Bitrix\Vote\User::getCurrent()->setVotedUserId(true),
				"DATE_VOTE"			=> (new DateTime()),
				"STAT_SESSION_ID"	=> $_SESSION["SESS_SESSION_ID"],
				"IP"				=> \Bitrix\Main\Context::getCurrent()->getServer()->get("REMOTE_ADDR"),
				"VALID"				=> "Y",
				"VISIBLE" 			=> ($this["ANONYMITY"] == \Bitrix\Vote\Vote\Anonymity::ANONYMOUSLY ? "N" : "Y") // can be replaced from $data array ["EXTRAS"]["HIDDEN"] = "Y"
			);
			if (($eventResult = $event->add($eventFields, $data)) && $eventResult)
			{
				$this->vote["COUNTER"]++;
				foreach ($eventResult->get("BALLOT") as $questionId => $question)
				{
					$this->questions[$questionId]["COUNTER"]++;
					foreach ($question["ANSWERS"] as $answerId => $answerEventParams)
					{
						$this->questions[$questionId]["ANSWERS"][$answerId]["COUNTER"]++;
					}
				}
				foreach ($this->questions as $questionId => $question)
				{
					foreach ($question["ANSWERS"] as $answerId => $answerEventParams)
					{
						if ($this->questions[$questionId]["ANSWERS"][$answerId]["COUNTER"] > 0)
						{
							$this->questions[$questionId]["ANSWERS"][$answerId]["~PERCENT"] =
								$this->questions[$questionId]["ANSWERS"][$answerId]["COUNTER"] * 100 /
								$this->questions[$questionId]["COUNTER"];
							$this->questions[$questionId]["ANSWERS"][$answerId]["PERCENT"] = round($this->questions[$questionId]["ANSWERS"][$answerId]["~PERCENT"], 2);
						}
						else
						{
							$this->questions[$questionId]["ANSWERS"][$answerId]["~PERCENT"] = 0;
							$this->questions[$questionId]["ANSWERS"][$answerId]["PERCENT"] = 0;
						}
					}
				}
				self::$statStorage[] = $voteId;
				$_SESSION["VOTE"]["VOTES"][$voteId] = $eventResult->get("EVENT_ID");
				// statistic module
				if (\Bitrix\Main\Loader::includeModule("statistic"))
				{
					$event3 = $this["EVENT3"];
					if (empty($event3))
					{
						$event3 = (\Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https://" : "http://") .
							\Bitrix\Main\Context::getCurrent()->getServer()->getHttpHost() .
							"/bitrix/admin/vote_user_results.php?EVENT_ID=" . $eventResult->get("EVENT_ID") . "&lang=" . LANGUAGE_ID;
					}
					\CStatEvent::AddCurrent($this["EVENT1"], $this["EVENT2"], $event3);
				}
				// notification TODO replace this functional into other function
				if ($this["NOTIFY"] !== "N" && $this["AUTHOR_ID"] > 0 && $this["AUTHOR_ID"] != $userId)
					self::sendVotingMessage($eventResult->toArray(), $this, ($this["NOTIFY"] == "I" ? "im" : "mail"));

				/***************** Event onAfterVoting *****************************/
				foreach (GetModuleEvents("vote", "onAfterVoting", true) as $ev)
					ExecuteModuleEventEx($ev, array($voteId, $eventResult->get("EVENT_ID"), $userId));
				/***************** /Event ******************************************/
				return true;
			}
		}
		$this->errorCollection->add($event->getErrors());
		return false;
	}

	/**
	 * Checks if current user voted for this vote.
	 * @param int|User $userId User ID.
	 * @return bool|int
	 */
	public function isVotedFor($userId)
	{
		$result = false;
		$user = ($userId instanceof User ? $userId : ($userId == $this->getUser()->getId() ? User::getCurrent() : User::loadFromId($userId)));
		$canVoteResult = $this->canVote($user);
		if (!$canVoteResult->isSuccess())
		{
			$result = 0;
			for (
				$canVoteResult->getErrorCollection()->rewind();
				$canVoteResult->getErrorCollection()->valid();
				$canVoteResult->getErrorCollection()->next()
			)
			{
				/** @var Error $error */
				$error = $canVoteResult->getErrorCollection()->current();
				$result |= $error->getCode();
			}
		}
		return $result;
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
			$dbRes = Channel::getList(array(
				"select" => array("*"),
				"filter" => array(
					"ACTIVE" => "Y",
					"HIDDEN" => "N",
					">=PERMISSION.PERMISSION" => 1,
					"PERMISSION.GROUP_ID" => $groups
				),
				"order" => array(
					"TITLE" => "ASC"
				),
				"group" => array("ID")
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
			$dbRes = Channel::getList(array(
				"select" => array("*"),
				"filter" => array(
					"ACTIVE" => "Y",
					"HIDDEN" => "N",
					">=PERMISSION.PERMISSION" => 4,
					"PERMISSION.GROUP_ID" => $groups
				),
				"order" => array(
					"TITLE" => "ASC"
				),
				"group" => array("ID")
			));
			while ($res = $dbRes->fetch())
			{
				if ($res["ID"] == $this->get("CHANNEL_ID"))
					return true;
			}
		}
		return false;
	}

	public function canParticipate($userId)
	{
		return $this->canRead($userId) && $this->vote["LAMP"] == "green";
	}

	/**
	 * @param \Bitrix\Vote\User|integer $user
	 * @return \Bitrix\Main\Result
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function canVote($user)
	{
		$vote = $this;
		$voteId = intval($vote["ID"]);
		if (!($user instanceof \Bitrix\Vote\User))
		{
			$user = \Bitrix\Vote\User::loadFromId($user);
		}
		if (!array_key_exists($voteId, self::$canVoteStorage))
		{
			self::$canVoteStorage[$voteId] = [];
		}
		if (array_key_exists($user->getId(), self::$canVoteStorage[$voteId]))
		{
			return self::$canVoteStorage[$voteId][$user->getId()];
		}

		$uniqueType = intval($vote["UNIQUE_TYPE"]);
		$filterCard = 0;

		$filter = ["LOGIC" => "OR"];

		$result = new \Bitrix\Main\Result();

		if ($uniqueType & \Bitrix\Vote\Vote\EventLimits::BY_SESSION && is_array($_SESSION["VOTE"]["VOTES"]) && array_key_exists($voteId, $_SESSION["VOTE"]["VOTES"]))
		{
			$filter["ID"] = $_SESSION["VOTE"]["VOTES"][$voteId];
			$filterCard |= \Bitrix\Vote\Vote\EventLimits::BY_SESSION;
			$result->addError(new \Bitrix\Main\Error(Loc::getMessage("VOTE_ERROR_BY_SESSION"), \Bitrix\Vote\Vote\EventLimits::BY_SESSION));
		}
		if (($uniqueType & \Bitrix\Vote\Vote\EventLimits::BY_COOKIE) && $user->getCookieId() > 0)
		{
			$filter["USER.COOKIE_ID"] = $user->getCookieId();
			$filterCard |= \Bitrix\Vote\Vote\EventLimits::BY_COOKIE;
		}
		if ($uniqueType & \Bitrix\Vote\Vote\EventLimits::BY_IP)
		{
			$delay = intval($vote["KEEP_IP_SEC"]);
			$filter[] = ([
					"IP" => \Bitrix\Main\Context::getCurrent()->getRequest()->getRemoteAddress()] +
				($delay > 0 ? [
					">=DATE_VOTE" => (new \Bitrix\Main\Type\DateTime())->add("-T".$delay."S")] : []));
			$filterCard |= \Bitrix\Vote\Vote\EventLimits::BY_IP;
		}
		if (
				$uniqueType & \Bitrix\Vote\Vote\EventLimits::BY_USER_AUTH ||
				$uniqueType & \Bitrix\Vote\Vote\EventLimits::BY_USER_DATE_REGISTER ||
				$uniqueType & \Bitrix\Vote\Vote\EventLimits::BY_USER_ID)
		{
			if (!$user->getUser()->IsAuthorized())
			{
				$filterCard |= \Bitrix\Vote\Vote\EventLimits::BY_USER_AUTH;
				$result->addError(new \Bitrix\Main\Error(Loc::getMessage("VOTE_ERROR_BY_USER_AUTH"), \Bitrix\Vote\Vote\EventLimits::BY_USER_AUTH));
			}
			else
			{
				if ($uniqueType & \Bitrix\Vote\Vote\EventLimits::BY_USER_DATE_REGISTER)
				{
					$us = \CUser::GetByID($user->getId())->fetch();
					if (MakeTimeStamp($vote["DATE_START"]) < MakeTimeStamp($us["DATE_REGISTER"]))
					{
						$result->addError(new \Bitrix\Main\Error(Loc::getMessage("VOTE_ERROR_BY_USER_DATE_REGISTER"), \Bitrix\Vote\Vote\EventLimits::BY_USER_DATE_REGISTER));
					}
				}
				if ($uniqueType & \Bitrix\Vote\Vote\EventLimits::BY_USER_ID)
				{
					$filter["USER.AUTH_USER_ID"] = $user->getId();
					$filterCard |= \Bitrix\Vote\Vote\EventLimits::BY_USER_ID;
				}
			}
		}

		if ($filterCard > 0)
		{
			$dbRes = \Bitrix\Vote\EventTable::getList([
				"select" => [
					"*",
					"USER_COOKIE_ID" => "USER.COOKIE_ID",
					"USER_AUTH_USER_ID" => "USER.AUTH_USER_ID",
				],
				"filter" => [
					"VOTE_ID" => $voteId,
					$filter
				]
			]);
			$data = $dbRes->fetchAll();
			$result->setData($data);
			foreach ($data as $res)
			{
				if (($filterCard & \Bitrix\Vote\Vote\EventLimits::BY_COOKIE) && $res["USER_COOKIE_ID"] == $user->getCookieId())
				{
					$result->addError(new \Bitrix\Main\Error(Loc::getMessage("VOTE_ERROR_BY_COOKIE"), \Bitrix\Vote\Vote\EventLimits::BY_COOKIE));
					$filterCard &= ~\Bitrix\Vote\Vote\EventLimits::BY_COOKIE;
				}
				if (($filterCard & \Bitrix\Vote\Vote\EventLimits::BY_IP) && ($res["IP"] == \Bitrix\Main\Context::getCurrent()->getRequest()->getRemoteAddress()))
				{
					if ($vote["KEEP_IP_SEC"] > 0)
					{
						/**@var DateTime $res["DATE_VOTE"] */
						$res["DATE_VOTE"]->add("T".$vote["KEEP_IP_SEC"]."S");
						$result->addError(new \Bitrix\Main\Error(Loc::getMessage("VOTE_ERROR_BY_IP_2", ["#DATE#" => $res["DATE_VOTE"]->toString()]), \Bitrix\Vote\Vote\EventLimits::BY_IP));
					}
					else
					{
						$result->addError(new \Bitrix\Main\Error(Loc::getMessage("VOTE_ERROR_BY_IP"), \Bitrix\Vote\Vote\EventLimits::BY_IP));
					}
					$filterCard &= ~\Bitrix\Vote\Vote\EventLimits::BY_IP;
				}
				if (($filterCard & \Bitrix\Vote\Vote\EventLimits::BY_USER_ID) && ($res["USER_AUTH_USER_ID"] == $user->getId()))
				{
					$result->addError(new \Bitrix\Main\Error(Loc::getMessage("VOTE_ERROR_BY_USER_ID"), \Bitrix\Vote\Vote\EventLimits::BY_USER_ID));
					$filterCard &= ~\Bitrix\Vote\Vote\EventLimits::BY_USER_ID;
				}
				if ($filterCard <= 0)
					break;
			}
		}
		self::$canVoteStorage[$voteId][$user->getId()] = $result;
		return $result;
	}

	public function canRevote($user)
	{
		$canVoteResult = $this->canVote($user);
		$result = new \Bitrix\Main\Result();
		if ($canVoteResult->isSuccess() || (
				($this["OPTIONS"] & Vote\Option::ALLOW_REVOTE) &&
				$canVoteResult->getErrorCollection()->getErrorByCode(\Bitrix\Vote\Vote\EventLimits::BY_USER_ID) &&
				$canVoteResult->getErrorCollection()->count() == 1
			))
		{
			$result->setData($canVoteResult->getData());
			return $result;
		}
		return $canVoteResult;
	}

	public function canReadResult($user)
	{
		$result = new \Bitrix\Main\Result();

		if (!($user instanceof \Bitrix\Vote\User))
		{
			$user = \Bitrix\Vote\User::loadFromId($user);
		}

		if ($this["AUTHOR_ID"] != $user->getId())
		{
			if ($this["OPTIONS"] & Vote\Option::HIDE_RESULT)
			{
				$result->addError(new Error("Access denied.", "Hidden results"));
			}
			else if ($this["LAMP"] == "green")
			{
				$canVoteResult = $this->canVote($user);
				if ($canVoteResult->isSuccess())
					$result->addError(new Error("Access denied.", "Hidden results"));
			}
		}
		return $result;
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
		throw new \Bitrix\Main\NotSupportedException("Model provide ArrayAccess only for reading");
	}
	/**
	 * Is not supported.
	 * @param string $offset Key for vote or attach data array.
	 * @return void
	 * @throws \Bitrix\Main\NotSupportedException
	 */
	public function offsetUnset($offset)
	{
		throw new \Bitrix\Main\NotSupportedException("Model provide ArrayAccess only for reading");
	}
}
