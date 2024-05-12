<?php
namespace Bitrix\Forum;

use Bitrix\Forum;
use Bitrix\Main;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\EntityError;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class MessageTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FORUM_ID int mandatory
 * <li> TOPIC_ID int mandatory
 * <li> USE_SMILES bool optional default 'Y'
 * <li> NEW_TOPIC bool optional default 'N'
 * <li> APPROVED bool optional default 'Y'
 * <li> SOURCE_ID string(255) mandatory default 'WEB'
 * <li> POST_DATE datetime mandatory
 * <li> POST_MESSAGE string optional
 * <li> POST_MESSAGE_HTML string optional
 * <li> POST_MESSAGE_FILTER string optional
 * <li> POST_MESSAGE_CHECK string(32) optional
 * <li> ATTACH_IMG int optional
 * <li> PARAM1 string(2) optional
 * <li> PARAM2 int optional
 * <li> AUTHOR_ID int optional
 * <li> AUTHOR_NAME string(255) optional
 * <li> AUTHOR_EMAIL string(255) optional
 * <li> AUTHOR_IP string(255) optional
 * <li> AUTHOR_REAL_IP string(128) optional
 * <li> GUEST_ID int optional
 * <li> EDITOR_ID int optional
 * <li> EDITOR_NAME string(255) optional
 * <li> EDITOR_EMAIL string(255) optional
 * <li> EDIT_REASON string optional
 * <li> EDIT_DATE datetime optional
 * <li> XML_ID string(255) optional
 * <li> HTML string optional
 * <li> MAIL_HEADER string optional
 * </ul>
 *
 * @package Bitrix\Forum
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Message_Query query()
 * @method static EO_Message_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Message_Result getById($id)
 * @method static EO_Message_Result getList(array $parameters = [])
 * @method static EO_Message_Entity getEntity()
 * @method static \Bitrix\Forum\EO_Message createObject($setDefaultValues = true)
 * @method static \Bitrix\Forum\EO_Message_Collection createCollection()
 * @method static \Bitrix\Forum\EO_Message wakeUpObject($row)
 * @method static \Bitrix\Forum\EO_Message_Collection wakeUpCollection($rows)
 */
class MessageTable extends Main\Entity\DataManager
{
	const SOURCE_ID_EMAIL = "EMAIL";
	const SOURCE_ID_WEB = "WEB";
	const SOURCE_ID_MOBILE = "MOBILE";

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_forum_message';
	}

	public static function getUfId()
	{
		return 'FORUM_MESSAGE';
	}

	private static $post_message_hash = [];
	private static $messageById = [];

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			(new IntegerField("ID", ["primary" => true, "autocomplete" => true])),
			(new IntegerField("FORUM_ID", ["required" => true])),
			(new IntegerField("TOPIC_ID", ["required" => true])),
			(new BooleanField("USE_SMILES", ["values" => ["N", "Y"], "default_value" => "Y"])),
			(new BooleanField("NEW_TOPIC", ["values" => ["N", "Y"], "default_value" => "N"])),
			(new BooleanField("APPROVED", ["values" => ["N", "Y"], "default_value" => "Y"])),
			(new BooleanField("SOURCE_ID", ["values" => [self::SOURCE_ID_EMAIL, self::SOURCE_ID_WEB, self::SOURCE_ID_MOBILE], "default_value" => self::SOURCE_ID_WEB])),
			(new DatetimeField("POST_DATE", ["required" => true, "default_value" => function(){ return new DateTime();}])),
			(new TextField("POST_MESSAGE", ["required" => true])),
			(new TextField("POST_MESSAGE_HTML")),
			(new TextField("POST_MESSAGE_FILTER")),
			(new StringField("POST_MESSAGE_CHECK", ["size" => 32])),
			(new IntegerField("ATTACH_IMG")),
			(new StringField("PARAM1", ["size" => 2])),
			(new IntegerField("PARAM2")),

			(new IntegerField("AUTHOR_ID")),
			(new StringField("AUTHOR_NAME", ["required" => true, "size" => 255])),
			(new StringField("AUTHOR_EMAIL", ["size" => 255])),
			(new StringField("AUTHOR_IP", ["size" => 255])),
			(new StringField("AUTHOR_REAL_IP", ["size" => 255])),
			(new IntegerField("GUEST_ID")),

			(new IntegerField("EDITOR_ID")),
			(new StringField("EDITOR_NAME", ["size" => 255])),
			(new StringField("EDITOR_EMAIL", ["size" => 255])),
			(new TextField("EDIT_REASON")),
			(new DatetimeField("EDIT_DATE")),

			(new StringField("XML_ID", ["size" => 255])),

			(new TextField("HTML")),
			(new TextField("MAIL_HEADER")),
			(new IntegerField("SERVICE_TYPE")),
			(new TextField("SERVICE_DATA")),

			(new Reference("TOPIC", TopicTable::class, Join::on("this.TOPIC_ID", "ref.ID"))),
			(new Reference("FORUM_USER", UserTable::class, Join::on("this.AUTHOR_ID", "ref.USER_ID"))),
			(new Reference("FORUM_USER_TOPIC", UserTopicTable::class, Join::on("this.TOPIC_ID", "ref.TOPIC_ID"))),
			(new Reference("USER", Main\UserTable::class, Join::on("this.AUTHOR_ID", "ref.ID")))
		);
	}

	public static function getFilteredFields()
	{
		return [
			"AUTHOR_NAME",
			"AUTHOR_EMAIL",
			"EDITOR_NAME",
			"EDITOR_EMAIL",
			"EDIT_REASON"
		];
	}

	private static function modifyMessageFields(array &$data)
	{
		unset($data["UPLOAD_DIR"]);
		if (array_key_exists("USE_SMILES", $data))
		{
			$data["USE_SMILES"] = $data["USE_SMILES"] === "N" ? "N" : "Y";
		}
		if (array_key_exists("NEW_TOPIC", $data))
		{
			$data["NEW_TOPIC"] = $data["NEW_TOPIC"] === "Y" ? "Y" : "N";
		}
		if (array_key_exists("APPROVED", $data))
		{
			$data["APPROVED"] = $data["APPROVED"] === Message::APPROVED_DISAPPROVED ? Message::APPROVED_DISAPPROVED : Message::APPROVED_APPROVED;
		}
		if (array_key_exists("SOURCE_ID", $data))
		{
			$data["SOURCE_ID"] = self::filterSourceIdParam($data['SOURCE_ID']);
		}
	}

	public static function filterSourceIdParam(string $sourceId): string
	{
		if (in_array($sourceId, [self::SOURCE_ID_WEB, self::SOURCE_ID_MOBILE, self::SOURCE_ID_EMAIL], true))
		{
			return $sourceId;
		}
		return self::SOURCE_ID_WEB;
	}

	public static function onBeforeAdd(Event $event)
	{
		$result = new Main\ORM\EventResult();
		/** @var array $data */
		$data = $event->getParameter("fields");
		$strUploadDir = array_key_exists("UPLOAD_DIR", $data) ? $data["UPLOAD_DIR"] : "forum";
		self::modifyMessageFields($data);
		//region Files
		if (array_key_exists("ATTACH_IMG", $data) && !empty($data["ATTACH_IMG"]))
		{
			if (!array_key_exists("FILES", $data))
			{
				$data["FILES"] = [];
			}
			$data["FILES"][] = $data["ATTACH_IMG"];
			unset($data["ATTACH_IMG"]);
		}
		if (array_key_exists("FILES", $data))
		{
			$data["FILES"] = is_array($data["FILES"]) ? $data["FILES"] : [$data["FILES"]];
			if (!empty($data["FILES"]))
			{
				$res = File::checkFiles(
					Forum\Forum::getById($data["FORUM_ID"]),
					$data["FILES"],
					[
						"FORUM_ID" => $data["FORUM_ID"],
						"TOPIC_ID" => ($data["NEW_TOPIC"] === "Y" ? 0 : $data["TOPIC_ID"]),
						"MESSAGE_ID" => 0,
						"USER_ID" => $data["AUTHOR_ID"]
					]
				);
				if (!$res->isSuccess())
				{
					$result->setErrors($res->getErrors());
				}
				else
				{
					/*@var Main\ORM\Objectify\EntityObject $object*/
					$object = $event->getParameter("object");
					/*@var Main\Dictionary $object->customData*/
					$object->sysSetRuntime("FILES", $data["FILES"]);
					$object->sysSetRuntime("UPLOAD_DIR", $strUploadDir);
				}
			}
			unset($data["FILES"]);
		}
		//endregion

		$data["POST_MESSAGE_CHECK"] = md5($data["POST_MESSAGE"] . (array_key_exists("FILES", $data) ? serialize($data["FILES"]) : ""));

		//region Deduplication
		$forum = Forum\Forum::getById($data["FORUM_ID"]);
		$deduplication = null;
		if (array_key_exists("AUX", $data))
		{
			if ($data["AUX"] == "Y")
			{
				$deduplication = false;
			}
			unset($data["AUX"]);
		}
		if (array_key_exists("DEDUPLICATION", $data))
		{
			$deduplication = $data["DEDUPLICATION"] == "Y";
			unset($data["DEDUPLICATION"]);
		}
		if ($deduplication === null)
		{
			$deduplication = $forum["DEDUPLICATION"] === "Y";
		}
		if ($deduplication && $data["NEW_TOPIC"] !== "Y")
		{
			if (self::getLastMessageHashInTopic($data["TOPIC_ID"]) === $data["POST_MESSAGE_CHECK"])
			{
				$result->addError(new EntityError(Loc::getmessage("F_ERR_MESSAGE_ALREADY_EXISTS"), "onBeforeMessageAdd"));
				return $result;
			}
		}
		//endregion

		$data["POST_MESSAGE"] = Main\Text\Emoji::encode($data["POST_MESSAGE"]);

		//region Filter
		if (Main\Config\Option::get("forum", "FILTER", "Y") == "Y")
		{
			$data["POST_MESSAGE_FILTER"] = \CFilterUnquotableWords::Filter($data["POST_MESSAGE"]);
			$filteredFields = self::getFilteredFields();
			$res = [];
			foreach ($filteredFields as $key)
			{
				$res[$key] = array_key_exists($key, $data) ? $data[$key] : "";
				if (!empty($res[$key]))
				{
					$res[$key] = \CFilterUnquotableWords::Filter($res[$key]);
					if ($res[$key] == '')
					{
						$res[$key] = "*";
					}
				}
			}
			$data["HTML"] = serialize($res);
		}
		//endregion

		$fields = $event->getParameter("fields");
		if ($data != $fields)
		{
			foreach ($fields as $key => $val)
			{
				if (!array_key_exists($key, $data))
				{
					$result->unsetField($key);
				}
				else if ($data[$key] == $val)
				{
					unset($data[$key]);
				}
			}
			$result->modifyFields($data);
		}
		return $result;
	}

	/**
	 * @param Main\ORM\Event $event
	 * @return Main\ORM\EventResult
	 */
	public static function onAdd(Main\ORM\Event $event)
	{
		$result = new Main\ORM\EventResult();
		if (Main\Config\Option::get("forum", "MESSAGE_HTML", "N") == "Y")
		{
			$fields = $event->getParameter("fields");
			$object = $event->getParameter("object");

			if ($files = $object->sysGetRuntime("FILES"))
			{
				File::saveFiles(
					$files,
					[
						"FORUM_ID" => $fields["FORUM_ID"],
						"TOPIC_ID" => $fields["TOPIC_ID"],
						"MESSAGE_ID" => 0,
						"USER_ID" => $fields["AUTHOR_ID"],
					],
					($object->sysGetRuntime("UPLOAD_DIR") ?: "forum/upload"));
				$object->sysSetRuntime("FILES", $files);
			}

			$parser = new \forumTextParser(LANGUAGE_ID);
			$allow = \forumTextParser::GetFeatures(\Bitrix\Forum\Forum::getById($fields["FORUM_ID"]));
			$allow["SMILES"] = ($fields["USE_SMILES"] != "Y" ? "N" : $allow["SMILES"]);
			$result->modifyFields([
				"POST_MESSAGE_HTML" => $parser->convert($fields["POST_MESSAGE_FILTER"] ?: $fields["POST_MESSAGE"], $allow, "html", $files)
			]);
		}
		return $result;
	}


	/**
	 * @param Main\ORM\Event $event
	 * @return void
	 */
	public static function onAfterAdd(Main\ORM\Event $event)
	{
		$object = $event->getParameter("object");

		if ($files = $object->sysGetRuntime("FILES"))
		{
			$id = $event->getParameter("id");
			$id = is_array($id) && array_key_exists("ID", $id) ? $id["ID"] : $id;
			$fields = $event->getParameter("fields");
			File::saveFiles(
				$files,
				[
					"FORUM_ID" => $fields["FORUM_ID"],
					"TOPIC_ID" => $fields["TOPIC_ID"],
					"MESSAGE_ID" => $id,
					"USER_ID" => $fields["AUTHOR_ID"],
				],
				($object->sysGetRuntime("UPLOAD_DIR") ?: "forum/upload"));
		}
	}

	public static function getDataById($id, $ttl = 84600)
	{
		if (!array_key_exists($id, self::$messageById))
		{
			self::$messageById[$id] = self::getList([
				"select" => ["*"],
				"filter" => ["ID" => $id],
				"cache" => [
					"ttl" => $ttl
				]
			])->fetch();
		}
		return self::$messageById[$id];
	}

	/**
	 * @param Main\ORM\Event $event
	 * @return Main\ORM\EventResult|void
	 * @throws Main\ObjectException
	 */
	public static function onBeforeUpdate(Main\ORM\Event $event)
	{
		$result = new Main\ORM\EventResult();
		/** @var array $data */
		$data = $event->getParameter("fields");
		$id = $event->getParameter("id");
		$id = $id["ID"];
		$strUploadDir = array_key_exists("UPLOAD_DIR", $data) ? $data["UPLOAD_DIR"] : "forum";
		self::modifyMessageFields($data);
		if (Main\Config\Option::get("forum", "FILTER", "Y") == "Y" &&
			!empty(array_intersect(self::getFilteredFields(), array_keys($data))))
		{
			$forFilter = $data;
			if (
				array_intersect(self::getFilteredFields(), array_keys($data)) !== self::getFilteredFields() &&
				($message = MessageTable::getDataById($id))
			)
			{
				$forFilter = array_merge($message, $forFilter);
			}
			$res = [];
			foreach (self::getFilteredFields() as $key)
			{
				$res[$key] = array_key_exists($key, $forFilter) ? $forFilter[$key] : "";
				if (!empty($res[$key]))
				{
					$res[$key] = \CFilterUnquotableWords::Filter($res[$key]);
					if ($res[$key] == '' )
					{
						$res[$key] = "*";
					}
				}
			}
			$data["HTML"] = serialize($res);
		}
		if (array_key_exists("POST_MESSAGE", $data))
		{
			$data["POST_MESSAGE"] = Main\Text\Emoji::encode($data["POST_MESSAGE"]);
			if (Main\Config\Option::get("forum", "FILTER", "Y") == "Y")
			{
				$data["POST_MESSAGE_FILTER"] = \CFilterUnquotableWords::Filter($data["POST_MESSAGE"]);
			}
		}
		unset($data["AUX"]);
		unset($data["DEDUPLICATION"]);

		//region Files
		if (array_key_exists("ATTACH_IMG", $data) && !empty($data["ATTACH_IMG"]))
		{
			if (!array_key_exists("FILES", $data))
			{
				$data["FILES"] = [];
			}
			$data["FILES"][] = $data["ATTACH_IMG"];
			unset($data["ATTACH_IMG"]);
		}
		if (array_key_exists("FILES", $data))
		{
			$data["FILES"] = is_array($data["FILES"]) ? $data["FILES"] : [$data["FILES"]];
			if (!empty($data["FILES"]))
			{
				$fileFields = $data + MessageTable::getDataById($id);
				$res = Forum\File::checkFiles(
					Forum\Forum::getById($fileFields["FORUM_ID"]),
					$data["FILES"],
					[
						"FORUM_ID" => $fileFields["FORUM_ID"],
						"TOPIC_ID" => $fileFields["TOPIC_ID"],
						"MESSAGE_ID" => $id,
						"USER_ID" => $fileFields["AUTHOR_ID"]
					]
				);
				if (!$res->isSuccess())
				{
					$result->setErrors($res->getErrors());
				}
				else
				{
					/*@var Main\ORM\Objectify\EntityObject $object*/
					$object = $event->getParameter("object");
					/*@var Main\Dictionary $object->customData*/
					$object->sysSetRuntime("FILES", $data["FILES"]);
					$object->sysSetRuntime("UPLOAD_DIR", $strUploadDir);
					$object->sysSetRuntime("FILE_FIELDS", $fileFields);
				}
			}
			unset($data["FILES"]);
		}
		//endregion
		$fields = $event->getParameter("fields");
		if ($data != $fields)
		{
			foreach ($fields as $key => $val)
			{
				if (!array_key_exists($key, $data))
				{
					$result->unsetField($key);
				}
				else if ($data[$key] == $val)
				{
					unset($data[$key]);
				}
			}
			$result->modifyFields($data);
		}
		return $result;
	}
	/**
	 * @param Main\ORM\Event $event
	 * @return Main\ORM\EventResult|void
	 */
	public static function onUpdate(Main\ORM\Event $event)
	{
		$id = $event->getParameter("id");
		$id = $id["ID"];
		$message = self::getDataById($id);

		$fields = $event->getParameter("fields") + $message;
		$object = $event->getParameter("object");

		if ($files = $object->sysGetRuntime("FILES"))
		{
			File::saveFiles(
				$files,
				[
					"FORUM_ID" => $fields["FORUM_ID"],
					"TOPIC_ID" => $fields["TOPIC_ID"],
					"MESSAGE_ID" => $id,
					"USER_ID" => $fields["AUTHOR_ID"],
				],
				($object->sysGetRuntime("UPLOAD_DIR") ?: "forum/upload"));
		}
		if (Main\Config\Option::get("forum", "MESSAGE_HTML", "N") == "Y")
		{
			$result = new Main\ORM\EventResult();
			$parser = new \forumTextParser(LANGUAGE_ID);
			$allow = \forumTextParser::GetFeatures(\Bitrix\Forum\Forum::getById($fields["FORUM_ID"]));
			$allow["SMILES"] = ($fields["USE_SMILES"] != "Y" ? "N" : $allow["SMILES"]);
			$result->modifyFields([
				"POST_MESSAGE_HTML" => $parser->convert($fields["POST_MESSAGE_FILTER"] ?: $fields["POST_MESSAGE"], $allow, "html", $files)
			]);
			return $result;
		}
	}

	/**
	-	 * @param Main\ORM\Event $event
	-	 * @return void
	-	 */
	public static function onAfterUpdate(Main\ORM\Event $event)
	{
		$id = $event->getParameter("id");
		$id = $id["ID"];
		unset(self::$messageById[$id]);
	}
	/**
	 * @param Result $result
	 * @param mixed $primary
	 * @param array $data
	 * @throws ArgumentException
	 * @throws Main\SystemException
	 */
	public static function checkFields(Result $result, $primary, array $data)
	{
		parent::checkFields($result, $primary, $data);
		if ($result->isSuccess())
		{
			try
			{
				if (array_key_exists("FORUM_ID", $data) && ForumTable::getMainData($data["FORUM_ID"]) === null)
				{
					throw new Main\ObjectNotFoundException(Loc::getMessage("F_ERR_INVALID_FORUM_ID"));
				}
				if (array_key_exists("TOPIC_ID", $data))
				{
					$topic = \Bitrix\Forum\TopicTable::query()->setSelect(['STATE'])
						->where('ID', $data["TOPIC_ID"])
						->fetch();

					if (!$topic)
					{
						throw new Main\ObjectNotFoundException(Loc::getMessage("F_ERR_TOPIC_IS_NOT_EXISTS"));
					}
					if ($topic["STATE"] == Topic::STATE_LINK)
					{
						throw new Main\ObjectPropertyException(Loc::getMessage("F_ERR_TOPIC_IS_LINK"));
					}
				}
			}
			catch (\Exception $e)
			{
				$result->addError(new Error(
					$e->getMessage()
				));
			}
		}
	}

	private static function getLastMessageHashInTopic(int $topicId): ?string
	{
		$res = MessageTable::query()
			->setSelect(['ID', 'POST_MESSAGE_CHECK'])
			->where('TOPIC_ID', '=', $topicId)
			->where('APPROVED', '=', 'Y')
			->setOrder(['ID' => 'DESC'])
			->setLimit(1)
			->exec()
			->fetch()
		;
		return $res ? $res['POST_MESSAGE_CHECK'] : null;
	}
}

class Message extends Internals\Entity
{
	use Forum\Internals\EntityFabric;

	public const APPROVED_APPROVED = "Y";
	public const APPROVED_DISAPPROVED = "N";

	protected function init()
	{
		if (!($this->data = MessageTable::getById($this->id)->fetch()))
		{
			throw new Main\ObjectNotFoundException("Message with id {$this->id} is not found.");
		}
		$this->authorId = intval($this->data["AUTHOR_ID"]);
	}

	public function edit(array $fields)
	{
		$result = self::update($this->getId(), $fields);

		if ($result->isSuccess() )
		{
			$this->data = MessageTable::getById($result->getId())->fetch();

			Forum\Integration\Search\Message::index(
				Forum\Forum::getById($this->getForumId()),
				Forum\Topic::getById($this->data["TOPIC_ID"]),
				$this->data
			);
		}

		return $result;
	}

	public function remove(): Main\ORM\Data\DeleteResult
	{
		$result = self::delete($this->getId());

		if ($result->isSuccess())
		{
			if ($topic = Forum\Topic::getById($this->data['TOPIC_ID']))
			{
				$decrementStatisticResult = $topic->decrementStatistic($this->data);
				if ($this->data['NEW_TOPIC'] === 'Y' && $decrementStatisticResult->getData())
				{
					if (!($newFirstMessage = $decrementStatisticResult->getData()) || empty($newFirstMessage))
					{
						$newFirstMessage = MessageTable::getList([
							'select' => ['*'],
							'filter' => ['TOPIC_ID' => $this->getId()],
							'order' => ['ID' => 'ASC'],
							'limit' => 1
						])->fetch();
					}
					Forum\Integration\Search\Message::index(
						Forum\Forum::getById($topic->getForumId()),
						$topic,
						$newFirstMessage
					);
				}
			}

			if ($forum = Forum\Forum::getById($this->getForumId()))
			{
				$forum->decrementStatistic($this->data);
			}

			Forum\Integration\Search\Message::deleteIndex($this->data);

			if ($this->data['AUTHOR_ID'] > 0 && ($author = User::getById($this->data['AUTHOR_ID'])))
			{
				$author->decrementStatistic($this->data);
			}
		}

		return $result;
	}

	/**
	 * @param Topic $parentObject
	 * @param array $fields
	 */
	public static function create($parentObject, array $fields)
	{
		$topic = Forum\Topic::getInstance($parentObject);
		$result = self::add($topic, $fields);
		if (!$result->isSuccess() )
		{
			return $result;
		}

		$message = MessageTable::getDataById($result->getId());
		$forum = Forum\Forum::getById($topic->getForumId());
		//region Update statistic & Seacrh
		User::getById($message["AUTHOR_ID"])->incrementStatistic($message);
		$topic->incrementStatistic($message);
		$forum->incrementStatistic($message);
		Forum\Integration\Search\Message::index($forum, $topic, $message);
		//endregion

		return $result;
	}

	public static function update($id, array &$fields)
	{
		$result = new Main\ORM\Data\UpdateResult();
		$result->setPrimary(["ID" => $id]);
		$data = [];
		$temporaryFields = ['AUX', 'AUX_DATA'];

		foreach (array_merge([
			"USE_SMILES",
			"POST_MESSAGE",
			"ATTACH_IMG",
			"FILES",
			"AUTHOR_NAME",
			"AUTHOR_EMAIL",
			"EDITOR_ID",
			"EDITOR_NAME",
			"EDITOR_EMAIL",
			"EDIT_REASON",
			"EDIT_DATE",
			'SERVICE_TYPE',
			'SERVICE_DATA',
			'SOURCE_ID',
			'PARAM1',
			'PARAM2',
			'XML_ID',
		], $temporaryFields) as $field)
		{
			if (array_key_exists($field, $fields))
			{
				$data[$field] = $fields[$field];
			}
		}
		if (!empty(array_diff_key($fields, $data)))
		{
			global $USER_FIELD_MANAGER;
			$data += array_intersect_key($fields, $USER_FIELD_MANAGER->getUserFields(MessageTable::getUfId()));
		}

		if (($events = GetModuleEvents("forum", "onBeforeMessageUpdate", true)) && !empty($events))
		{
			$strUploadDir = "forum";
			global $APPLICATION;
			foreach ($events as $ev)
			{
				$APPLICATION->ResetException();
				if (ExecuteModuleEventEx($ev, array($id, &$data, &$strUploadDir)) === false)
				{
					$errorMessage = Loc::getMessage("FORUM_EVENT_BEFOREUPDATE_ERROR");
					if (($ex = $APPLICATION->GetException()) && ($ex instanceof \CApplicationException))
					{
						$errorMessage = $ex->getString();
					}

					$result->addError(new Main\Error($errorMessage, "onBeforeMessageUpdate"));
					return $result;
				}
			}
			$data["UPLOAD_DIR"] = $strUploadDir;
		}

		foreach ($temporaryFields as $field)
		{
			unset($data[$field]);
		}

		if (isset($fields['EDITOR_ID']))
		{
			$authContext = new Main\Authentication\Context();
			$authContext->setUserId($fields['EDITOR_ID']);
			$data = [
				'fields' => $data,
				'auth_context' => $authContext
			];
		}

		$dbResult = MessageTable::update($id, $data);

		if (!$dbResult->isSuccess())
		{
			$result->addErrors($dbResult->getErrors());
		}
		else
		{
			$message = MessageTable::getDataById($id);
			foreach (GetModuleEvents("forum", "onAfterMessageUpdate", true) as $event)
			{
				ExecuteModuleEventEx($event, [$id, $data, $message]);
			}
		}
		return $result;
	}
	/**
	 * @param Topic $topic
	 * @param array $fields
	 */
	public static function add(Forum\Topic $topic, array $fields): Main\ORM\Data\AddResult
	{
		$data = [
			"FORUM_ID" => $topic->getForumId(),
			"TOPIC_ID" => $topic->getId(),

			"USE_SMILES" => $fields["USE_SMILES"],
			"NEW_TOPIC" => (isset($fields["NEW_TOPIC"]) && $fields["NEW_TOPIC"] === "Y" ? "Y" : "N"),
			"APPROVED" => $topic["APPROVED"] === Topic::APPROVED_DISAPPROVED || $fields["APPROVED"] === Message::APPROVED_DISAPPROVED ? Message::APPROVED_DISAPPROVED : Message::APPROVED_APPROVED,

			"POST_DATE" => $fields["POST_DATE"] ?: new Main\Type\DateTime(),
			"POST_MESSAGE" => $fields["POST_MESSAGE"],
			"ATTACH_IMG" => $fields["ATTACH_IMG"] ?? null,
			"FILES" => $fields["FILES"] ?? [],

			"AUTHOR_ID" => $fields["AUTHOR_ID"],
			"AUTHOR_NAME" => $fields["AUTHOR_NAME"],
			"AUTHOR_EMAIL" => $fields["AUTHOR_EMAIL"],
			"AUTHOR_IP" => $fields["AUTHOR_IP"] ?? null,
			"AUTHOR_REAL_IP" =>  $fields["AUTHOR_REAL_IP"] ?? null,
			"GUEST_ID" =>  $fields["GUEST_ID"] ?? null,
		];

		if (!empty(array_diff_key($fields, $data)))
		{
			global $USER_FIELD_MANAGER;
			$data += array_intersect_key($fields, $USER_FIELD_MANAGER->getUserFields(MessageTable::getUfId()));
		}

		$temporaryFields = ['AUX', 'AUX_DATA'];
		$additionalFields = array_merge(['SERVICE_TYPE', 'SERVICE_DATA', 'SOURCE_ID', 'PARAM1', 'PARAM2', 'XML_ID'], $temporaryFields);
		foreach ($additionalFields as $key)
		{
			if (array_key_exists($key, $fields))
			{
				$data[$key] = $fields[$key];
			}
		}

		$result = new Main\ORM\Data\AddResult();

		if (($events = GetModuleEvents("forum", "onBeforeMessageAdd", true)) && !empty($events))
		{
			$strUploadDir = "forum";
			global $APPLICATION;

			foreach ($events as $ev)
			{
				$APPLICATION->ResetException();
				if (ExecuteModuleEventEx($ev, array(&$data, &$strUploadDir)) === false)
				{
					$errorMessage = Loc::getMessage("FORUM_EVENT_BEFOREADD_ERROR");
					if (($ex = $APPLICATION->GetException()) && ($ex instanceof \CApplicationException))
					{
						$errorMessage = $ex->getString();
					}

					$result->addError(new Main\Error($errorMessage, "onBeforeMessageAdd"));
					return $result;
				}
			}
			$data["UPLOAD_DIR"] = $strUploadDir;
		}

		foreach ($temporaryFields as $field)
		{
			unset($data[$field]);
		}

		$authContext = new Main\Authentication\Context();
		$authContext->setUserId($fields['AUTHOR_ID']);

		$dbResult = MessageTable::add([
			"fields" => $data,
			"auth_context" => $authContext
		]);

		if (!$dbResult->isSuccess())
		{
			$result->addErrors($dbResult->getErrors());
		}
		else
		{
			$id = $dbResult->getId();
			$result->setId($dbResult->getId());

			$message = MessageTable::getDataById($id);
			$forum = Forum\Forum::getById($topic->getForumId());
			foreach (GetModuleEvents("forum", "onAfterMessageAdd", true) as $event)
			{
				ExecuteModuleEventEx($event, [$id, $message, $topic, $forum, $data]);
			}
		}
		return $result;
	}

	public static function delete(int $id): Main\ORM\Data\DeleteResult
	{
		$result = new Main\ORM\Data\DeleteResult();

		if (!($message = MessageTable::getDataById($id)))
		{
			$result->addError(new Main\Error( Loc::getMessage("FORUM_MESSAGE_HAS_NOT_BEEN_FOUND")));
			return $result;
		}

		global $APPLICATION, $USER_FIELD_MANAGER;
		if (($events = GetModuleEvents("forum", "onBeforeMessageDelete", true))
			&& !empty($events))
		{
			foreach ($events as $ev)
			{
				$APPLICATION->ResetException();
				if (ExecuteModuleEventEx($ev, [$id, $message]) === false)
				{
					$errorMessage = Loc::getMessage("FORUM_EVENT_BEFOREDELETE_ERROR");
					if (($ex = $APPLICATION->GetException()) && ($ex instanceof \CApplicationException))
					{
						$errorMessage = $ex->getString();
					}

					$result->addError(new Main\Error($errorMessage, "onBeforeMessageDelete"));
					break;
				}
			}
		}

		if ($result->isSuccess())
		{
			if ($message['PARAM1'] == 'VT' && $message['PARAM2'] > 0
				&& IsModuleInstalled('vote') && Main\Loader::includeModule('vote'))
			{
				\CVote::Delete($message['PARAM2']);
			}
			$USER_FIELD_MANAGER->Delete("FORUM_MESSAGE", $id);
			FileTable::deleteBatch(['MESSAGE_ID' => $id]);
			MessageTable::delete($id);

			if (!($nextMessage = MessageTable::getList([
				'select' => ['ID'],
				'filter' => [
					'TOPIC_ID' => $message['TOPIC_ID'],
				],
				'order' => ['ID' => 'ASC'],
				'limit' => 1
			])->fetch()))
			{
				Topic::delete($message['TOPIC_ID']);
			}
			else if ($message['NEW_TOPIC'] === 'Y')
			{
				MessageTable::update($nextMessage['ID'], ['NEW_TOPIC' => 'Y']);
			}
			/***************** Event onBeforeMessageAdd ************************/
			foreach (GetModuleEvents("forum", "onAfterMessageDelete", true) as $event)
			{
				ExecuteModuleEventEx($event, [$id, $message]);
			}
			/***************** /Event ******************************************/
		}
		return $result;
	}
}
