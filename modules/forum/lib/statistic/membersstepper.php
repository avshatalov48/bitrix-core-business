<?
namespace Bitrix\Forum\Statistic;
use Bitrix\Forum\UserTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class MembersStepper extends \Bitrix\Main\Update\Stepper
{
	protected static $moduleId = "forum";
	private static $offset = 50;

	public static function getTitle()
	{
		return Loc::getMessage("FORUM_MEMBERS_STEPPER_TITLE");
	}
	/**
	 * @inheritDoc
	 */
	public function execute(array &$option)
	{
		$return = self::FINISH_EXECUTION;

		$dbRes = UserTable::getList([
			"select" => ["ID", "USER_ID"],
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
				$user = \Bitrix\Forum\User::getById($r["USER_ID"]);
				$user->calcStatistic();
				$option["last_id"] = $r["ID"];
			} while ($r = $dbRes->fetch());
		}

		$option["steps"] = $option["max_id"] - $option["last_id"];
		$option["count"] = $option["max_id"];

		return $return;
	}
}
