<?
namespace Bitrix\Forum\Statistic;
use Bitrix\Forum\MessageTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

class Forum extends \Bitrix\Main\Update\Stepper
{
	protected static $moduleId = "forum";

	public static function getTitle()
	{
		return Loc::getMessage("FORUM_STEPPER_TITLE");
	}
	/**
	 * @inheritDoc
	 */
	public function execute(array &$option)
	{
		$res = Option::get("forum", "stat.forum.recalc", "");
		$res = empty($res) ? [] : unserialize($res);
		if (empty($res) || !is_array($res))
		{
			return self::FINISH_EXECUTION;
		}
		reset($res);
		$forumId = key($res);

		\Bitrix\Forum\Forum::getById($forumId)->calcStat();

		array_shift($res);

		$option["steps"] = 1;
		$option["count"] = count($res);
		if (empty($res))
		{
			Option::delete("forum", ["name" => "stat.forum.recalc"]);
			return self::FINISH_EXECUTION;
		}
		Option::set("forum", "stat.forum.recalc", serialize($res));
		return self::CONTINUE_EXECUTION;
	}

	public static function calc(int $forumId)
	{
		$res = Option::get("forum", "stat.forum.recalc", "");
		if (!empty($res))
			$res = unserialize($res);
		$res = is_array($res) ? $res : [];
		$res[$forumId] = [];
		Option::set("forum", "stat.forum.recalc", serialize($res));
		static::bind(0);
	}
}
