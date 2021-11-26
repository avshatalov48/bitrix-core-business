<?
namespace Bitrix\Forum\Statistic;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class Forum extends Main\Update\Stepper
{
	protected static $limit = 1;
	protected static $moduleId = "forum";

	public static function getTitle()
	{
		return Loc::getMessage("FORUM_STEPPER_TITLE");
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
WHERE ENTITY_TYPE='FORUM'
LIMIT {$limit}
ORDER BY ID ASC
SQL
		);

		$last = null;
		while ($res = $dbRes->fetch())
		{
			\Bitrix\Forum\Forum::getById($res["ENTITY_ID"])->calculateStatistic();
			$last = $res;
		}

		if ($last)
		{
			Main\Application::getConnection()->queryExecute(<<<SQL
DELETE FROM b_forum_service_statistic_queue WHERE ID >= {$last['ID']} AND ENTITY_TYPE='FORUM'
SQL
			);
		}
		return $limit;
	}

	public static function run(int $forumId)
	{
		Main\Application::getConnection()->queryExecute(<<<SQL
INSERT IGNORE INTO b_forum_service_statistic_queue (ENTITY_TYPE, ENTITY_ID) VALUES ('FORUM', {$forumId});
SQL
		);
		self::bind(0);
	}
}
