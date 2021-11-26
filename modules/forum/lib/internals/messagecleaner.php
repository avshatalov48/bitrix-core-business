<?
namespace Bitrix\Forum\Internals;

use \Bitrix\Main;
use \Bitrix\Forum;

class MessageCleaner extends Main\Update\Stepper
{
	protected static $limit = 10;
	protected static $moduleId = "forum";

	public static function getTitle()
	{
		return 'Message cleaner';
	}

	public function execute(array &$option)
	{
		$option["steps"] = 1;
		$option["count"] = 1;
		if (self::do() > 0)
		{
			return self::FINISH_EXECUTION;
		}
		return self::CONTINUE_EXECUTION;
	}

	private static function do()
	{
		$limit = self::$limit;
		$dbRes = Main\Application::getConnection()->query(<<<SQL
SELECT * 
FROM b_forum_service_deleted_message
ORDER BY ID ASC
LIMIT {$limit}
SQL
		);
		$votes = [];
		global $USER_FIELD_MANAGER;
		$last = null;
		while ($res = $dbRes->fetch())
		{
			if ($res["PARAM1"] == 'VT')
			{
				$votes[] = $res["PARAM2"];
			}
			$USER_FIELD_MANAGER->Delete("FORUM_MESSAGE", $res["ID"]);
			$limit--;
			$last = $res;
		}

		if (!empty($votes) && IsModuleInstalled("vote") && \CModule::IncludeModule("vote"))
		{
			array_map(function($voteId) {
				\CVote::Delete($voteId);
			}, $votes);
		}

		if ($last)
		{
			Main\Application::getConnection()->queryExecute(<<<SQL
DELETE FROM b_forum_service_deleted_message WHERE ID <= {$last['ID']}
SQL
			);
		}
		return $limit;
	}

	public static function runForTopic(int $topicId)
	{
		Main\Application::getConnection()->queryExecute(<<<SQL
INSERT IGNORE INTO b_forum_service_deleted_message 
	(FORUM_ID, TOPIC_ID, MESSAGE_ID, NEW_TOPIC, APPROVED, PARAM1, PARAM2, AUTHOR_ID)
SELECT FORUM_ID, TOPIC_ID, ID, NEW_TOPIC, APPROVED, PARAM1, PARAM2, AUTHOR_ID
FROM b_forum_message 
WHERE TOPIC_ID = {$topicId}
SQL
		);
		self::bind(0);
	}
}
