<?
namespace Bitrix\Forum\Statistic;
use Bitrix\Forum\ForumTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ForumsStepper extends \Bitrix\Main\Update\Stepper
{
	protected static $moduleId = "forum";
	private static $offset = 1;

	public static function getTitle()
	{
		return Loc::getMessage("FORUM_FORUMS_STEPPER_TITLE");
	}
	/**
	 * @inheritDoc
	 */
	public function execute(array &$option)
	{
		$return = self::FINISH_EXECUTION;

		$dbRes = ForumTable::getList([
			"select" => ["ID"],
			"filter" => (array_key_exists("last_id", $option) ? ["<ID" => $option["last_id"]] : []),
			"limit" => self::$offset + 1,
			"order" => ["ID" => "DESC"]
		]);
		if ($r = $dbRes->fetch())
		{
			$count = 0;
			if (!array_key_exists("max_id", $option))
			{
				$option["max_id"] = $r["ID"];
			}
			do
			{
				$count++;
				if ($count > self::$offset)
				{
					$return = self::CONTINUE_EXECUTION;
					break;
				}
				\Bitrix\Forum\Forum::getById($r["ID"])->calcStat();
				$option["last_id"] = $r["ID"];
			} while ($r = $dbRes->fetch());
		}

		$option["steps"] = $option["max_id"] - $option["last_id"];
		$option["count"] = $option["max_id"];

		return $return;
	}
}
