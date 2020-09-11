<?
namespace Bitrix\Forum\Integration\Search;
use Bitrix\Disk\Internals\DeletedLogManager;
use Bitrix\Forum\MessageTable;
use Bitrix\Main\Config\Option;

class Topic extends \Bitrix\Main\Update\Stepper
{
	protected static $moduleId = "forum";

	public static function getTitle()
	{
		return "Reindex topic";
	}
	/**
	 * @inheritDoc
	 */
	function execute(array &$option)
	{
		$res = Option::get("forum", "search.reindex.topic", "");
		$res = empty($res) ? [] : unserialize($res);
		if (empty($res) || !is_array($res) || !\Bitrix\Main\Loader::includeModule("search"))
		{
			return self::FINISH_EXECUTION;
		}

		$state = reset($res);
		$topicId = key($res);

		$state["reindexFirst"] = ($state["reindexFirst"] === true);

		$result = self::FINISH_EXECUTION;

		if ($state["reindexFirst"] === true)
		{
			if (
				($dbRes = \CForumMessage::GetList(array("ID" => "ASC"), array("TOPIC_ID" => $topicId, "NEW_TOPIC" => "Y", "GET_TOPIC_INFO" => "Y", "GET_FORUM_INFO" => "Y", "FILTER" => "Y"))) &&
				($message = $dbRes->fetch())
			)
			{
				\CForumMessage::Reindex($message["ID"], $message);
			}
		}
		else
		{
			$state["LAST_ID"] = ($state["LAST_ID"] > 0 ? $state["LAST_ID"] : 0);
			$limit = \Bitrix\Main\Config\Option::get("forum", "search_message_count", 20);
			$limit = ($limit > 0 ? $limit : 20);
			$dbRes = \Bitrix\Forum\MessageTable::getList([
				"select" => ["*"],
				"filter" => ["TOPIC_ID" => $topicId],
				"limit" => \Bitrix\Main\Config\Option::get("forum", "search_message_count", 20),
				"offset" => $state["LAST_ID"]
			]);
			if ($message = $dbRes->fetch())
			{
				$forum = \Bitrix\Forum\Forum::getById($message["FORUM_ID"]);
				if ($forum["INDEXATION"] != "Y")
				{
					\CSearch::DeleteIndex("forum", false, false, $message["TOPIC_ID"]);
				}
				else
				{
					$count = 0;
					$topic = \Bitrix\Forum\Topic::getById($message["TOPIC_ID"]);
					do
					{
						$count++;
						$message["FORUM_INFO"] = $forum->getData();
						$message["TOPIC_INFO"] = $topic->getData();
						\CForumMessage::Reindex($message["ID"], $message);
						$state["LAST_ID"] = $message["ID"];
					} while($message = $dbRes->fetch());
					if ($count >= $limit)
					{
						$result = self::CONTINUE_EXECUTION;
					}
				}
			}
		}

		if ($result === self::FINISH_EXECUTION)
		{
			array_shift($res);
		}
		else
		{
			$res[$topicId] = $state;
		}
		$option["steps"] = 1;
		$option["count"] = count($res);
		if (empty($res))
		{
			Option::delete("forum", ["name" => "search.reindex.topic"]);
			return self::FINISH_EXECUTION;
		}
		Option::set("forum", "search.reindex.topic", serialize($res));
		return self::CONTINUE_EXECUTION;
	}

	public static function reindexFirstMessage(int $topicId)
	{
		$res = Option::get("forum", "search.reindex.topic", "");
		if (!empty($res))
			$res = unserialize($res);
		$res = is_array($res) ? $res : [];
		$res[$topicId] = [
			"reindexFirst" => true
		];
		Option::set("forum", "search.reindex.topic", serialize($res));
		static::bind(0);
	}

	public static function reindex(int $topicId)
	{
		$res = Option::get("forum", "search.reindex.topic", "");
		if (!empty($res))
			$res = unserialize($res);
		$res = is_array($res) ? $res : [];
		$res[$topicId] = [];
		Option::set("forum", "search.reindex.topic", serialize($res));
		static::bind(0);
	}
}
