<?
namespace Bitrix\Forum\Statistic;
use Bitrix\Forum\MessageTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

class TopicMembersStepper extends \Bitrix\Main\Update\Stepper
{
	protected static $moduleId = "forum";

	public static function getTitle()
	{
		return Loc::getMessage("FORUM_TOPIC_MEMBERS_STEPPER_TITLE");
	}
	/**
	 * @inheritDoc
	 */
	public function execute(array &$option)
	{
		$res = Option::get("forum", "stat.user.recalc.topic", "");
		$res = empty($res) ? [] : unserialize($res);
		if (empty($res) || !is_array($res))
		{
			return self::FINISH_EXECUTION;
		}
		$state = reset($res);
		$topicId = key($res);
		if (empty($state))
		{
			$state["LAST_ID"] = 0;
		}
		$dbRes = MessageTable::getList([
			"select" => ["AUTHOR_ID"],
			"filter" => ["TOPIC_ID" => $topicId, ">AUTHOR_ID" => $state["LAST_ID"]],
			"limit" => 10,
			"offset" => $state["LAST_ID"],
			"order" => ["AUTHOR_ID" => "asc"]
		]);
		$count = 0;
		while ($r = $dbRes->fetch())
		{
			$count++;
			$user = \Bitrix\Forum\User::getById($r["AUTHOR_ID"]);
			$user->calcStatistic();
			$state["LAST_ID"] = $r["AUTHOR_ID"];
		}
		if ($count < 10)
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
			Option::delete("forum", ["name" => "stat.user.recalc.topic"]);
			return self::FINISH_EXECUTION;
		}
		Option::set("forum", "stat.user.recalc.topic", serialize($res));
		return self::CONTINUE_EXECUTION;
	}

	public static function calc(int $topicId)
	{
		$res = Option::get("forum", "stat.user.recalc.topic", "");
		if (!empty($res))
			$res = unserialize($res);
		$res = is_array($res) ? $res : [];
		$res[$topicId] = [];
		Option::set("forum", "stat.user.recalc.topic", serialize($res));
		static::bind(0);
	}
}
