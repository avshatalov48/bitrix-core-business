<?php

namespace Bitrix\Forum\Comments;

use Bitrix\Forum\Internals\Error\ErrorCollection;
use Bitrix\Forum;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Forum\Internals\Error\Error;
use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;
use \Bitrix\Main\ArgumentException;

Loc::loadMessages(__FILE__);

class Comment extends BaseObject
{

	const ERROR_PARAMS_MESSAGE = 'params0006';
	const ERROR_PERMISSION = 'params0007';
	const ERROR_MESSAGE_IS_NULL = 'params0008';
	const ERROR_PARAMS_TYPE = 'params0009';


	/* @var integer */
	private $id = 0;
	/* @var array */
	private $message = null;

	private function prepareFields(array &$params, ErrorCollection $errorCollectionParam)
	{
		$result = array(
			"FORUM_ID" => $this->topic["FORUM_ID"],
			"TOPIC_ID" => $this->topic["ID"],
			"POST_MESSAGE" => trim($params["POST_MESSAGE"]),
			"AUTHOR_ID" => $params["AUTHOR_ID"],
			"AUTHOR_NAME" => trim($params["AUTHOR_NAME"]),
			"AUTHOR_EMAIL" => trim($params["AUTHOR_EMAIL"]),
			"USE_SMILES" => ($params["USE_SMILES"] == "Y" ? "Y" : "N"),
			"APPROVED" => $this->topic["APPROVED"],
			"XML_ID" => $this->getEntity()->getXmlId(),
			"AUX" => ($params["AUX"] ?? 'N'),
			"AUX_DATA" => ($params["AUX_DATA"] ?? ''),
		) + array_intersect_key($params, array_flip([
			"POST_DATE", "SOURCE_ID",
			"AUTHOR_IP", "AUTHOR_REAL_IP",
			"GUEST_ID"
		]));

		$errorCollection = new ErrorCollection();
		if (isset($params["SERVICE_TYPE"]))
		{
			if (!in_array($params["SERVICE_TYPE"], \Bitrix\Forum\Comments\Service\Manager::getTypesList()))
			{
				$errorCollection->addOne(new Error(Loc::getMessage("FORUM_CM_ERR_TYPE_INCORRECT"), self::ERROR_PARAMS_TYPE));
			}
			else
			{
				$result["SERVICE_TYPE"] = $params["SERVICE_TYPE"];
				if (!isset($params["SERVICE_DATA"]))
				{
					if (($result["SERVICE_TYPE"] === \Bitrix\Forum\Comments\Service\Manager::TYPE_TASK_INFO ||
						$result["SERVICE_TYPE"] === \Bitrix\Forum\Comments\Service\Manager::TYPE_TASK_CREATED)
						&& JSon::decode($result["POST_MESSAGE"]) == $params["AUX_DATA"])
					{
						$params["SERVICE_DATA"] = $result["POST_MESSAGE"];
						$result["POST_MESSAGE"] = "";
					}
					else
					{
						$params["SERVICE_DATA"] = Json::encode($params["AUX_DATA"] ?? []);
					}
				}
				$result["SERVICE_DATA"] = $params["SERVICE_DATA"];
				if ($result["POST_MESSAGE"] == "" &&
					($handler = \Bitrix\Forum\Comments\Service\Manager::find(
						["SERVICE_TYPE" => $result["SERVICE_TYPE"]]
					)))
				{
					$result["POST_MESSAGE"] = $handler->getText($result["SERVICE_DATA"]);
				}
			}
		}
		if ($result["POST_MESSAGE"] == '')
		{
			$errorCollection->addOne(new Error(Loc::getMessage("FORUM_CM_ERR_EMPTY_TEXT"), self::ERROR_PARAMS_MESSAGE));
		}

		if ($result["AUTHOR_NAME"] == '' && $result["AUTHOR_ID"] > 0)
			$result["AUTHOR_NAME"] = self::getUserName($result["AUTHOR_ID"]);
		if ($result["AUTHOR_NAME"] == '')
			$errorCollection->addOne(new Error(Loc::getMessage("FORUM_CM_ERR_EMPTY_AUTHORS_NAME"), self::ERROR_PARAMS_MESSAGE));

		if (is_array($params["FILES"]) && in_array($this->forum["ALLOW_UPLOAD"], array("Y", "F", "A")))
		{
			$result["FILES"] = array();
			foreach ($params["FILES"] as $key => $val)
			{
				if (intval($val["FILE_ID"]) > 0 && $val["del"] !== "Y")
				{
					unset($val["del"]);
				}
				$result["FILES"][$key] = $val;
			}
			$res = array(
				"FORUM_ID" => $this->forum["ID"],
				"TOPIC_ID" => $this->topic["ID"],
				"MESSAGE_ID" => 0,
				"USER_ID" => $result["AUTHOR_ID"],
				"FORUM" => $this->forum
			);
			if (!\CForumFiles::checkFields($result["FILES"], $res, "NOT_CHECK_DB"))
			{
				$text = "File upload error.";
				if (($ex = $this->getApplication()->getException()) && $ex)
					$text = $ex->getString();
				$errorCollection->addOne(new Error($text, self::ERROR_PARAMS_MESSAGE));
			}
		}
		if ($result["APPROVED"] != "N")
		{
			$result["APPROVED"] = ($this->forum["MODERATION"] != "Y" || $this->getEntity()->canModerate($this->getUser()->getId())) ? "Y" : "N";
		}
		if ($errorCollection->hasErrors())
		{
			$errorCollectionParam->add($errorCollection->toArray());
			return false;
		}

		global $USER_FIELD_MANAGER;
		$ufData = array_intersect_key(
			$params,
			$USER_FIELD_MANAGER->getUserFields(Forum\MessageTable::getUfId()),
		);
		if (!empty($ufData))
		{
			$USER_FIELD_MANAGER->editFormAddFields(Forum\MessageTable::getUfId(), $result, ["FORM" => $ufData]);
		}

		$params = $result;
		return true;
	}

	public function appendUserFields(array &$params): static
	{
		global $USER_FIELD_MANAGER;

		$USER_FIELD_MANAGER->editFormAddFields(Forum\MessageTable::getUfId(), $params);

		return $this;
	}

	private function updateStatisticModule($messageId)
	{
		if (Loader::includeModule("statistic"))
		{
			$forumEvent1 = $this->forum["EVENT1"];
			$forumEvent2 = $this->forum["EVENT2"];
			$forumEvent3 = $this->forum["EVENT3"];
			if (empty($forumEvent3))
			{
				$site = (array) \CForumNew::getSites($this->forum["ID"]);
				$forumEvent3 = \CForumNew::preparePath2Message((array_key_exists(SITE_ID, $site) ? $site[SITE_ID] : reset($site)),
					array(
						"FORUM_ID" => $this->forum["ID"],
						"TOPIC_ID" => $this->topic["ID"],
						"MESSAGE_ID" => $messageId
					)
				);
			}
			\CStatistics::set_Event($forumEvent1, $forumEvent2, $forumEvent3);
		}
	}

	/**
	 * Adds new comment
	 * @param array $params
	 * @return array|false
	 */
	public function add(array $params)
	{
		$aux = (isset($params['AUX']) && $params['AUX'] === "Y");
		$auxData = ($params['AUX_DATA'] ?? '');

		$params = array(
			"SOURCE_ID" => $params["SOURCE_ID"] ?? 0,

			"POST_DATE" => array_key_exists("POST_DATE", $params) ? $params["POST_DATE"] : new \Bitrix\Main\Type\DateTime(),
			"POST_MESSAGE" => trim($params["POST_MESSAGE"]),
			"FILES" => $params["FILES"] ?? null,

			"USE_SMILES" => $params["USE_SMILES"],

			"AUTHOR_ID" => $this->getUser()->getId(),
			"AUTHOR_NAME" => trim($params["AUTHOR_NAME"] ?? ''),
			"AUTHOR_EMAIL" => trim($params["AUTHOR_EMAIL"] ?? ''),

			"AUTHOR_IP" => $params["AUTHOR_IP"] ?? "<no address>",
			"AUTHOR_REAL_IP" => $params["AUTHOR_REAL_IP"] ?? "<no address>",
			"GUEST_ID" => $params["GUEST_ID"] ?? null,

			"AUX" => $params["AUX"] ?? null,
			"AUX_DATA" => $auxData,
			"SERVICE_TYPE" => ($params["SERVICE_TYPE"] ?? null),
			"SERVICE_DATA" => ($params["SERVICE_DATA"] ?? null),
		) + array_filter($params, fn($key) => strpos($key, 'UF_') === 0, ARRAY_FILTER_USE_KEY);

		if ($this->prepareFields($params, $this->errorCollection))
		{
			/***************** Events OnBeforeCommentAdd ******************/
			$event = new Event("forum", "OnBeforeCommentAdd", [
				$this->getEntity()->getType(),
				$this->getEntity()->getId(),
				$params
			]);
			$event->send($this);
			if($event->getResults())
			{
				foreach($event->getResults() as $eventResult)
				{
					if($eventResult->getType() != EventResult::SUCCESS)
					{
						$run = false;
						break;
					}
				}
			}
			/***************** /Events *****************************************/

			$topic = \Bitrix\Forum\Topic::getById($params["TOPIC_ID"]);
			$result = \Bitrix\Forum\Message::create($topic, $params);

			if ($result->isSuccess())
			{
				$mid = $result->getId();

				if (!$aux)
				{
					$this->updateStatisticModule($mid);
					\CForumMessage::sendMailMessage($mid, array(), false, "NEW_FORUM_MESSAGE");
				}

				$this->setComment($mid);

				if (
					!$aux // create task from livefeed
					|| $auxData <> '' // tasks commentposter, add to livefeed
				)
				{
					$event = new Event("forum", "OnAfterCommentAdd", array(
							$this->getEntity()->getType(),
							$this->getEntity()->getId(),
							array(
								"TOPIC_ID" => $this->topic["ID"],
								"MESSAGE_ID" => $mid,
								"PARAMS" => $params,
								"MESSAGE" => $this->getComment(),
								"AUX_DATA" => $auxData
							))
					);
					$event->send();
				}

				return $this->getComment();
			}
			$this->errorCollection->addFromResult($result);
		}
		return false;
	}
	/**
	 * Edit new comment
	 * @param array $params
	 * @return array|false
	 */
	public function edit(array $params)
	{
		$paramsRaw = $params;
		if ($this->message === null)
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage("FORUM_CM_ERR_COMMENT_IS_LOST1"), self::ERROR_MESSAGE_IS_NULL));
		}
		else
		{
			$run = true;
			$fields = array(
				$this->getEntity()->getType(),
				$this->getEntity()->getId(),
				array(
					"TOPIC_ID" => $this->topic["ID"],
					"MESSAGE_ID" => $this->message["ID"],
					"PARAMS" => &$paramsRaw,
					"ACTION" => "EDIT",
					"MESSAGE" => $this->getComment()
				)
			);
			/***************** Events OnBeforeCommentUpdate ******************/
			$event = new Event("forum", "OnBeforeCommentUpdate", $fields);
			$event->send($this);
			if($event->getResults())
			{
				foreach($event->getResults() as $eventResult)
				{
					if($eventResult->getType() != EventResult::SUCCESS)
					{
						$run = false;
						break;
					}
				}
			}
			/***************** /Events *****************************************/
			if (!$run)
			{
				$text = Loc::getMessage("ADDMESS_ERROR_EDIT_MESSAGE");
				if (($str = $this->getApplication()->getException()) && $str)
					$text = $str->getString();
				$this->errorCollection->addOne(new Error($text, self::ERROR_PARAMS_MESSAGE));
			}
			else if (($params = array(
				"POST_MESSAGE" => trim($params["POST_MESSAGE"]),
				"AUTHOR_ID" => $this->message["AUTHOR_ID"],
				"AUTHOR_NAME" => $params["AUTHOR_NAME"] ?? $this->message["AUTHOR_NAME"],
				"AUTHOR_EMAIL" => $params["AUTHOR_EMAIL"] ?? $this->message["AUTHOR_EMAIL"],
				"USE_SMILES" => $params["USE_SMILES"] ?? 'Y',
				"FILES" => $params["FILES"] ?? [],
				"AUX" => $params["AUX"] ?? null,
				"AUX_DATA" => $params["AUX_DATA"] ?? null,
				) + array_filter($params, fn($key) => strpos($key, 'UF_') === 0, ARRAY_FILTER_USE_KEY))
				&& $this->prepareFields($params, $this->errorCollection)
			)
			{
				if (array_key_exists("POST_DATE", $paramsRaw))
				{
					$params["POST_DATE"] = $paramsRaw["POST_DATE"];
				}
				if (array_key_exists("EDIT_REASON", $paramsRaw))
				{
					$params += array(
						"EDITOR_ID" => $this->getUser()->getId(),
						"EDITOR_NAME" => trim($paramsRaw["EDITOR_NAME"]),
						"EDITOR_EMAIL" => trim($paramsRaw["EDITOR_EMAIL"]),
						"EDIT_REASON" => trim($paramsRaw["EDIT_REASON"]),
						"EDIT_DATE" => ""
					);
					if ($params["EDITOR_NAME"] == '')
						$params["EDITOR_NAME"] = ($params["EDITOR_ID"] > 0 ? self::getUserName($params["EDITOR_ID"]) : Loc::getMessage("GUEST"));
				}
				$result = \Bitrix\Forum\Message::getById($this->message["ID"])->edit($params);
				if ($result->isSuccess())
				{
					$mid = $this->message["ID"];
					unset($GLOBALS["FORUM_CACHE"]["MESSAGE"][$mid]);
					unset($GLOBALS["FORUM_CACHE"]["MESSAGE_FILTER"][$mid]);

					if ($params["AUTHOR_ID"] != $this->getUser()->getId() || Option::get("forum", "LOGS", "Q") < "U")
					{
						$resLog = array();
						foreach ($paramsRaw as $key => $val)
						{
							if (!isset($this->message[$key]) || $val == $this->message[$key])
								continue;
							else if ($key == "FILES")
								$resLog["FILES"] = GetMessage("F_ATTACH_IS_MODIFIED");
							else
								$resLog[$key] = array(
									"before" => $this->message[$key],
									"after" => $val
								);
						}
						if (!empty($resLog))
						{
							$resLog["FORUM_ID"] = $this->forum["ID"];
							$resLog["TOPIC_ID"] = $this->topic["ID"];
							$resLog["TITLE"] = $this->topic["TITLE"];
							\CForumEventLog::log("message", "edit", $this->message["ID"], serialize($resLog));
						}
					}
					$this->updateStatisticModule($mid);
					\CForumMessage::sendMailMessage($mid, array(), false, "EDIT_FORUM_MESSAGE");

					$this->setComment($mid);
					$fields["PARAMS"] = $params;
					/***************** Events OnAfterCommentUpdate *******************/
					$event = new Event("forum", "OnAfterCommentUpdate", $fields);
					$event->send();
					/***************** /Events *****************************************/
					return $this->getComment();
				}
				$this->errorCollection->addFromResult($result);
			}
		}
		return false;
	}

	public function delete()
	{
		if ($this->message === null)
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage("FORUM_CM_ERR_COMMENT_IS_LOST2"), self::ERROR_MESSAGE_IS_NULL));
		}
		else
		{
			$run = true;
			$fields = array(
				$this->getEntity()->getType(),
				$this->getEntity()->getId(),
				array(
					"TOPIC_ID" => $this->topic["ID"],
					"MESSAGE_ID" => $this->message["ID"],
					"MESSAGE" => $this->getComment(),
					"ACTION" => "DEL"
				));
			/***************** Events OnBeforeCommentDelete ******************/
			$event = new Event("forum", "OnBeforeCommentDelete", $fields);
			$event->send($this);
			if($event->getResults())
			{
				foreach($event->getResults() as $eventResult)
				{
					if($eventResult->getType() != EventResult::SUCCESS)
					{
						$run = false;
						break;
					}
				}
			}
			/***************** /Events *****************************************/
			if ($run && \CForumMessage::delete($this->message["ID"]))
			{
				\CForumEventLog::log("message", "delete", $this->message["ID"], serialize($this->message + array("TITLE" => $this->topic["TITLE"])));
				/***************** Events OnCommentDelete ************************/
				$event = new Event("forum", "OnCommentDelete", $fields);
				$event->send();
				/***************** Events OnAfterCommentUpdate *********************/
				$event = new Event("forum", "OnAfterCommentUpdate", $fields); // It is not a mistake
				$event->send();
				/***************** /Events *****************************************/
			}
			else
			{
				$text = Loc::getMessage("FORUM_CM_ERR_DELETE");
				if (($ex = $this->getApplication()->getException()) && $ex)
					$text = $ex->getString();
				$this->errorCollection->addOne(new Error($text, self::ERROR_PARAMS_MESSAGE));
			}
		}
		return true;
	}

	public function moderate($show)
	{
		if ($this->message === null)
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage("FORUM_CM_ERR_COMMENT_IS_LOST3"), self::ERROR_MESSAGE_IS_NULL));
		}
		else
		{
			$run = true;
			$fields = array(
				$this->getEntity()->getType(),
				$this->getEntity()->getId(),
				array(
					"TOPIC_ID" => $this->topic["ID"],
					"MESSAGE_ID" => $this->message["ID"],
					"MESSAGE" => $this->getComment(),
					"ACTION" => $show ? "SHOW" : "HIDE",
					"PARAMS" => array("APPROVED" => ($show ? "Y" : "N"))
				));
			/***************** Events OnBeforeCommentModerate ****************/
			$event = new Event("forum", "OnBeforeCommentModerate", $fields);
			$event->send($this);
			if($event->getResults())
			{
				foreach($event->getResults() as $eventResult)
				{
					if($eventResult->getType() != EventResult::SUCCESS)
					{
						$run = false;
						break;
					}
				}
			}
			/***************** /Events *****************************************/
			if ($run && $this->message["APPROVED"] == $fields[2]["PARAMS"]["APPROVED"] || ($mid = \CForumMessage::update($this->message["ID"], $fields[2]["PARAMS"])) > 0)
			{
				$this->setComment($this->message["ID"]);
				/***************** Event onMessageModerate ***********************/
				$event = new Event("forum", "onMessageModerate", array($this->message["ID"], ($show ? "SHOW" : "HIDE"), $this->message, $this->topic));
				$event->send();
				/***************** Events OnCommentModerate ************************/
				$event = new Event("forum", "OnCommentModerate", $fields);
				$event->send();
				/***************** Events OnAfterCommentUpdate *********************/
				$event = new Event("forum", "OnAfterCommentUpdate", $fields); // It is not a mistake
				$event->send();
				/***************** /Events *****************************************/
				$res = serialize(array(
					"ID" => $this->message["ID"],
					"AUTHOR_NAME" => $this->message["AUTHOR_NAME"],
					"POST_MESSAGE" => $this->message["POST_MESSAGE"],
					"TITLE" => $this->topic["TITLE"],
					"TOPIC_ID" => $this->topic["ID"],
					"FORUM_ID" => $this->topic["FORUM_ID"]));
				\CForumMessage::sendMailMessage($this->message["ID"], array(), false, ($show ? "NEW_FORUM_MESSAGE" : "EDIT_FORUM_MESSAGE"));
				\CForumEventLog::log("message", ($show ? "approve" : "unapprove"), $this->message["ID"], $res);
				return $this->getComment();
			}
			else
			{
				$text = Loc::getMessage("FORUM_CM_ERR_MODERATE");
				if (($ex = $this->getApplication()->getException()) && $ex)
					$text = $ex->getString();
				$this->errorCollection->addOne(new Error($text, self::ERROR_PARAMS_MESSAGE));
			}
		}
		return false;
	}

	public function canEdit()
	{
		$result = false;
		if ($this->message === null)
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage("FORUM_CM_ERR_COMMENT_IS_LOST4"), self::ERROR_MESSAGE_IS_NULL));
		}
		else
		{
			$result = ($this->getEntity()->canEdit($this->getUser()->getId()) || (
					((int) $this->message["AUTHOR_ID"] > 0) &&
					((int) $this->message["AUTHOR_ID"] == (int) $this->getUser()->getId()) &&
					$this->getEntity()->canEditOwn($this->getUser()->getId())
				));
		}
		return $result;
	}

	/**
	 * @return bool
	 */
	public function canEditOwn()
	{
		return $this->getEntity()->canEditOwn($this->getUser()->getId());
	}

	/**
	 * @return bool
	 */
	public function canDelete()
	{
		return $this->canEdit();
	}

	public function setComment($id)
	{
		$id = intval($id);
		$message = ($id > 0 ? \CForumMessage::getById($id) : null);
		if (!empty($message))
		{
			if ($message["TOPIC_ID"] != $this->topic["ID"])
			{
				throw new ArgumentException(Loc::getMessage("ACCESS_DENIED"), self::ERROR_PERMISSION);
			}
			$this->id = $id;
			$this->message = $message;
		}
	}

	public function getComment()
	{
		return $this->message;
	}

	/**
	 * Creates new
	 * @param Feed $feed
	 * @param $id
	 * @return Comment
	 */
	public static function createFromId(Feed $feed, $id)
	{
		$forum = $feed->getForum();
		$comment = new Comment($forum["ID"], $feed->getEntity()->getFullId(), $feed->getUser()->getId());
		$comment->getEntity()->setPermission($feed->getUser()->getId(), $feed->getEntity()->getPermission($feed->getUser()->getId()));
		$comment->setComment($id);
		return $comment;
	}
	/**
	 * Creates new
	 * @param Feed $feed
	 * @return Comment
	 */
	public static function create(Feed $feed)
	{
		$forum = $feed->getForum();
		$comment = new Comment($forum["ID"], $feed->getEntity()->getFullId(), $feed->getUser()->getId());
		$comment->getEntity()->setPermission($feed->getUser()->getId(), $feed->getEntity()->getPermission($feed->getUser()->getId()));
		return $comment;
	}
}
