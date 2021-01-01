<?
namespace Bitrix\Forum\Update;

use \Bitrix\Main\Update\Stepper;
use \Bitrix\Forum\Integration;
use \Bitrix\Main\Loader;
use \Bitrix\Main;
use \Bitrix\Forum\MessageTable;

class TopicServiceCounter extends Stepper
{
	protected static $moduleId = "forum";
	protected const TOPIC_LIMIT = 100;

	public function execute(array &$result)
	{
		if (!Loader::includeModule("forum"))
		{
			return self::finishExecution();
		}

		if (Main\Config\Option::get("forum", "LivefeedConvertServiceMessageStepper") === "inProgress")
		{
			return self::CONTINUE_EXECUTION;
		}

		if (!array_key_exists("lastId", $result) || (int)$result["lastId"] <= 0)
		{
			$result["lastId"] = 0;

			$res = MessageTable::getList([
				"select" => [ "CNT"],
				"filter" => [
					'>SERVICE_TYPE' => 0
				],
				'runtime' => [
					new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
				]
			]);
			$topicData = $res->fetch();
			$result["count"] = (int)$topicData['CNT'];
		}

		$filter = [
			'>SERVICE_TYPE' => 0
		];
		if ($result["lastId"] > 0)
		{
			$filter["<TOPIC_ID"] = $result["lastId"];
		}

		Main\Config\Option::set("forum", "TopicServiceCounterStepper", "inProgress");

		$lastId = 0;

		$res = MessageTable::getList([
			"select" => ["TOPIC_ID", "CNT"],
			"filter" => $filter,
			'runtime' => [
				new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			],
			"group" => ["TOPIC_ID"],
			"limit" => self::TOPIC_LIMIT,
			"order" => ["TOPIC_ID" => "DESC"]
		]);
		while($topicData = $res->fetch())
		{
			$topic = \Bitrix\Forum\EO_Topic::wakeUp($topicData["TOPIC_ID"]);
			$topic->setPostsService($topicData["CNT"]);
			$topic->save();
			$lastId = $topicData["TOPIC_ID"];
		}

		if ($lastId > 0)
		{
			$result["lastId"] = $lastId;
		}
		else
		{
			return self::finishExecution();
		}

		return self::CONTINUE_EXECUTION;
	}

	protected static function finishExecution()
	{
		Main\Config\Option::delete("forum", ["name" => "TopicServiceCounterStepper"]);
		return self::FINISH_EXECUTION;
	}
}
?>