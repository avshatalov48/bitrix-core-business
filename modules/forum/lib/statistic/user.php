<?
namespace Bitrix\Forum\Statistic;

use Bitrix\Forum\UserTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Forum;

Loc::loadMessages(__FILE__);

class User extends Main\Update\Stepper
{
	protected static $moduleId = "forum";
	private static $limit = 10;

	public static function getTitle()
	{
		return 'User statistic calculation';
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
SELECT ID, ENTITY_ID 
FROM b_forum_service_statistic_queue
WHERE ENTITY_TYPE='USER'
ORDER BY ID ASC
LIMIT {$limit}
SQL
		);

		$last = null;
		while (($res = $dbRes->fetch()))
		{
			if ($usr = Forum\User::getById($res['ENTITY_ID']))
			{
				$usr->calculateStatistic();
			}
			$last = $res;
		}

		if ($last)
		{
			Main\Application::getConnection()->queryExecute(<<<SQL
DELETE FROM b_forum_service_statistic_queue WHERE ID >= {$last['ID']} AND ENTITY_TYPE='USER'
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
INSERT INTO b_forum_service_statistic_queue (ENTITY_TYPE, ENTITY_ID)
SELECT 'USER', AUTHOR_ID
FROM b_forum_message 
WHERE TOPIC_ID = {$topicId} AND AUTHOR_ID > 0 AND APPROVED='Y'
GROUP BY AUTHOR_ID
ON CONFLICT (ENTITY_TYPE, ENTITY_ID) DO NOTHING
SQL
			);
		}
		else
		{
			Main\Application::getConnection()->queryExecute(<<<SQL
INSERT IGNORE INTO b_forum_service_statistic_queue (ENTITY_TYPE, ENTITY_ID)
SELECT 'USER', AUTHOR_ID
FROM b_forum_message 
WHERE TOPIC_ID = {$topicId} AND AUTHOR_ID > 0 AND APPROVED='Y'
GROUP BY AUTHOR_ID
SQL
			);
		}

		self::bind(0);
	}
	
	public static function calcForTopics(array $topicIds)
	{
		$topicIds = implode(', ', array_map('intval', $topicIds));
		if ($topicIds === '')
		{
			return;
		}
		if (Main\Application::getConnection()->getType() === 'pgsql')
		{
			Main\Application::getConnection()->queryExecute(<<<SQL
INSERT INTO b_forum_service_statistic_queue (ENTITY_TYPE, ENTITY_ID)
SELECT 'USER', AUTHOR_ID
FROM b_forum_message 
WHERE TOPIC_ID IN ({$topicIds}) AND AUTHOR_ID > 0 AND APPROVED='Y'
GROUP BY AUTHOR_ID
ON CONFLICT (ENTITY_TYPE, ENTITY_ID) DO NOTHING
SQL
			);
		}
		else
		{
			Main\Application::getConnection()->queryExecute(<<<SQL
INSERT IGNORE INTO b_forum_service_statistic_queue (ENTITY_TYPE, ENTITY_ID)
SELECT 'USER', AUTHOR_ID
FROM b_forum_message 
WHERE TOPIC_ID IN ({$topicIds}) AND AUTHOR_ID > 0 AND APPROVED='Y'
GROUP BY AUTHOR_ID
SQL
			);
		}

		self::bind(300);
	}
}
