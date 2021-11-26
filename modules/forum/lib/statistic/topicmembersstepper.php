<?
namespace Bitrix\Forum\Statistic;

use Bitrix\Forum;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class TopicMembersStepper extends Main\Update\Stepper
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
		$res = Main\Config\Option::get("forum", "stat.user.recalc.topic", "");
		$res = unserialize($res, ["allowed_classes" => false]);
		if (is_array($res) && !empty($res))
		{
			Forum\Statistic\User::calcForTopics(array_keys($res));
			Main\Config\Option::delete("forum", ["name" => "stat.user.recalc.topic"]);
		}
		return self::FINISH_EXECUTION;
	}

	public static function calc(int $topicId)
	{
		Forum\Statistic\User::runForTopic($topicId);
	}
}
