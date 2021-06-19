<?
namespace Bitrix\Forum\Update;

use Bitrix\Main\ORM\Objectify\State;
use \Bitrix\Main\Update\Stepper;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Forum\Integration;
use \Bitrix\Main\Loader;
use \Bitrix\Forum;
use \Bitrix\Main;
use \Bitrix\Socialnetwork;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

class LivefeedConvertServiceMessage extends Stepper
{
	protected static $moduleId = "forum";
	protected const MESSAGE_LIMIT = 500;

	public function execute(array &$result)
	{
		if (!(
			Loader::includeModule("forum")
			&& Loader::includeModule("socialnetwork")
		))
		{
			return self::finishExecution();
		}

		if (!array_key_exists("handlers", $result) || !is_array($result["handlers"]))
		{
			$result["handlers"] = array_merge(self::getSocialnetworkHandlersPostText(), ["innerForum"]);
			$result["steps"] = count($result["handlers"]);
			$result["count"] = 0;
		}

		if ($postMessage = reset($result["handlers"]))
		{
			$status = self::FINISH_EXECUTION;
			Main\Config\Option::set("forum", "LivefeedConvertServiceMessageStepper", "inProgress");
			if ($postMessage === "innerForum")
			{
				$status = $this->replaceServiceField($result["lastId"]);
			}
			else if ($handler = (new Socialnetwork\CommentAux\HandlerManager())->getHandlerByPostText($postMessage))
			{
				$status = $this->convert($handler, $result["lastId"]);
			}
			if ($status === self::FINISH_EXECUTION)
			{
				array_shift($result["handlers"]);
				unset($result["lastId"]);
			}
			else
			{
				$result["lastId"] = $status;
			}
			$result["count"] = $result["steps"] - count($result["handlers"]);
		}

		return count($result["handlers"]) > 0 ? self::CONTINUE_EXECUTION : self::finishExecution();
	}

	protected static function finishExecution()
	{
		Main\Config\Option::delete("forum", ["name" => "LivefeedConvertServiceMessageStepper"]);
		return self::FINISH_EXECUTION;
	}

	public function convert(Socialnetwork\CommentAux\Base $handler, $lastId)
	{
		$comments = $this->getCommentsCollection(["POST_MESSAGE" => $handler::getPostText()], $lastId);
		$rowsCount = $comments->count();
		$lastId = 0;
		if ($rowsCount > 0)
		{
			$dbRes = Socialnetwork\LogCommentTable::getList([
				"select" => ["ID", "EVENT_ID", "SOURCE_ID", "SHARE_DEST"],
				"filter" => [
					"SOURCE_ID" => $comments->getIdList(),
					"%RATING_TYPE_ID" => "FORUM",
				]
			]);
			$socnetComments = [];
			while ($res = $dbRes->fetch())
			{
				$socnetComments[$res["SOURCE_ID"]] = $res;
			}
			$comments->rewind();
			while ($comment = $comments->current())
			{
				$socnetInfo = array_key_exists($comment["ID"], $socnetComments) ? $socnetComments[$comment["ID"]] : ["SHARE_DEST" => ""];
				if ($handler instanceof Socialnetwork\CommentAux\TaskInfo && !empty($socnetInfo["SHARE_DEST"]))
				{
					$postMessage = self::decodeSocnetText($socnetInfo["SHARE_DEST"], 'serialized');
					if (!is_array($postMessage))
					{
						$postMessage = self::decodeSocnetText($socnetInfo["SHARE_DEST"], 'bar');
						if (empty($postMessage))
						{
							$postMessage = false;
						}
					}

					if (is_array($postMessage))
					{
						$comment->setServiceType(Forum\Comments\Service\Manager::TYPE_TASK_INFO);
						$serviceData = Json::encode(is_array($postMessage) ? $postMessage : []);
						$comment->setServiceData($serviceData);
						$comment->setPostMessage(Forum\Comments\Service\Manager::find([
							"SERVICE_TYPE" => Forum\Comments\Service\Manager::TYPE_TASK_INFO
						])->getText($serviceData));
						$comment->setPostMessageHtml($socnetInfo["SHARE_DEST"]);
						$comment->setPostMessageFilter($handler::getPostText());
					}
				}
				elseif ($handler instanceof Socialnetwork\CommentAux\CreateTask)
				{
					$postMessage = self::decodeSocnetText($socnetInfo["SHARE_DEST"], 'bar');
					if (is_array($postMessage))
					{
						$comment->setServiceType(Forum\Comments\Service\Manager::TYPE_TASK_CREATED);
						$serviceData = Json::encode(is_array($postMessage) ? $postMessage : []);
						$comment->setServiceData($serviceData);
						$comment->setPostMessage(Forum\Comments\Service\Manager::find([
							"SERVICE_TYPE" => Forum\Comments\Service\Manager::TYPE_TASK_CREATED
						])->getText($serviceData));
						$comment->setPostMessageHtml($socnetInfo["SHARE_DEST"]);
						$comment->setPostMessageFilter($handler::getPostText());
					}
				}
				elseif ($handler instanceof Socialnetwork\CommentAux\FileVersion && !empty($socnetInfo["SHARE_DEST"]))
				{
					if (false && !empty($socnetInfo["SHARE_DEST"]))
					{
						$comment->setPostMessageFilter($handler::getPostText());
					}
				}

				if ($comment->state !== State::RAW)
				{
					$comment->save();
				}
				$lastId = $comment->getId();
				$comments->next();
			}
		}
		return $rowsCount < static::MESSAGE_LIMIT ? self::FINISH_EXECUTION : $lastId;
	}

	protected function getCommentsCollection(array $filter, $lastId)
	{
		return Forum\MessageTable::getList([
			"select" => ["ID", "POST_MESSAGE", "SERVICE_TYPE"],
			"filter" => $filter + ($lastId > 0 ? [
				"<ID" => $lastId
			] : []),
			"limit" => self::MESSAGE_LIMIT,
			"order" => ["ID" => "DESC"]
		])->fetchCollection();
	}
	protected static function getSocialnetworkHandlers()
	{
		static $result = null;
		if ($result === null)
		{
			$result = [];
			if (Loader::includeModule("socialnetwork"))
			{
				$result = [
					new Socialnetwork\CommentAux\CreateTask,
/*					new Socialnetwork\CommentAux\FileVersion,
					new Socialnetwork\CommentAux\Share,*/
					new Socialnetwork\CommentAux\TaskInfo
				];
			}
		}
		return $result;
	}

	public static function getSocialnetworkHandlersPostText()
	{
		$result = [];
		/* @var $handler Socialnetwork\CommentAux\Base */
		foreach (self::getSocialnetworkHandlers() as $handler)
		{
			$result[] = $handler->getPostText();
		}
		return $result;
	}

	protected static function decodeSocnetText($text = '', $type = 'serialized')
	{
		$result = [];
		if ($type === 'serialized')
		{
			$result = @unserialize($text, ["allowed_classes" => false]);
		}
		elseif ($type === 'bar')
		{
			$paramsList = explode('|', $text);
			if (!empty($paramsList))
			{
				foreach($paramsList as $pair)
				{
					list($key, $value) = explode('=', $pair);
					if (isset($key) && isset($value))
					{
						$result[$key] = $value;
					}
				}
			}
		}

		return $result;
	}

	public function replaceServiceField($lastId)
	{
		$comments = $this->getCommentsCollection([
			"!SERVICE_TYPE" => null,
			"SERVICE_DATA" => null
		], $lastId);
		$rowsCount = $comments->count();

		$lastId = 0;
		if ($rowsCount > 0)
		{
			$currentLang = Main\Localization\Loc::getCurrentLang();
			$defaultLanguage= Main\Localization\Loc::getDefaultLang(LANGUAGE_ID);
			if ($currentLang !== $defaultLanguage)
			{
				Main\Localization\Loc::setCurrentLang($defaultLanguage);
			}
			while ($comment = $comments->current())
			{
				$serviceData = $comment->getPostMessage();
				if ($handler = Forum\Comments\Service\Manager::find([
					"SERVICE_TYPE" => $comment->getServiceType()
				]))
				{
					$postMessage = $handler->getText($serviceData);
					if ($postMessage !== '')
					{
						$comment->setPostMessage($postMessage);
						$comment->setServiceData(is_null($serviceData) ? Json::encode([]) : $serviceData);
						$comment->save();
					}
				}
				$lastId = $comment->getId();
				$comments->next();
			}
			if ($currentLang !== $defaultLanguage)
			{
				Main\Localization\Loc::setCurrentLang($currentLang);
			}
		}
		return $rowsCount < static::MESSAGE_LIMIT ? self::FINISH_EXECUTION : $lastId;
	}
}
?>