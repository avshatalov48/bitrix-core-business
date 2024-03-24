<?php

namespace Bitrix\Forum\Internals;

use \Bitrix\Main;
use \Bitrix\Forum;

class MessageCleaner extends Main\Update\Stepper
{
	protected static int $limit = 10;
	protected static $moduleId = "forum";

	public static function getTitle(): string
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
SELECT ID, FORUM_ID, TOPIC_ID, MESSAGE_ID, NEW_TOPIC, APPROVED, PARAM1, PARAM2, AUTHOR_ID
FROM b_forum_service_deleted_message
ORDER BY ID ASC
LIMIT {$limit}
SQL
		);
		$votes = [];
		global $USER_FIELD_MANAGER;
		$last = null;
		while ($message = $dbRes->fetch())
		{
			if ($message["PARAM1"] == 'VT')
			{
				$votes[] = $message["PARAM2"];
			}

			$selectSql = "SELECT * FROM b_forum_file where MESSAGE_ID=" . intval($message['MESSAGE_ID']) . " ORDER BY ID ASC";

			$dbFileRes = Main\Application::getConnection()->query($selectSql);

			if ($dbFileRes && ($file = $dbFileRes->fetch()))
			{
				do
				{
					\CFile::Delete($file["FILE_ID"]);
				}
				while ($file = $dbFileRes->fetch());
				$deleteSql = "DELETE FROM b_forum_file where MESSAGE_ID=" . intval($message['MESSAGE_ID']);
				Main\Application::getConnection()->queryExecute($deleteSql);
			}

			$USER_FIELD_MANAGER->Delete("FORUM_MESSAGE", $message["MESSAGE_ID"]);
			$limit--;
			$last = $message;
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
		if (Main\Application::getConnection()->getType() === 'pgsql')
		{
			Main\Application::getConnection()->queryExecute(<<<SQL
INSERT INTO b_forum_service_deleted_message 
	(FORUM_ID, TOPIC_ID, MESSAGE_ID, NEW_TOPIC, APPROVED, PARAM1, PARAM2, AUTHOR_ID)
SELECT FORUM_ID, TOPIC_ID, ID, NEW_TOPIC, APPROVED, PARAM1, PARAM2, AUTHOR_ID
FROM b_forum_message 
WHERE TOPIC_ID = {$topicId}
ON CONFLICT (MESSAGE_ID) DO NOTHING
SQL
			);
		}
		else
		{
			Main\Application::getConnection()->queryExecute(<<<SQL
INSERT IGNORE INTO b_forum_service_deleted_message 
	(FORUM_ID, TOPIC_ID, MESSAGE_ID, NEW_TOPIC, APPROVED, PARAM1, PARAM2, AUTHOR_ID)
SELECT FORUM_ID, TOPIC_ID, ID, NEW_TOPIC, APPROVED, PARAM1, PARAM2, AUTHOR_ID
FROM b_forum_message 
WHERE TOPIC_ID = {$topicId}
SQL
			);
		}

		self::bind(0);
	}
}
