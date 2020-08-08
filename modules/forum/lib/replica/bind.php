<?php
namespace Bitrix\Forum\Replica;

class Bind
{
	/** @var \Bitrix\Forum\Replica\TopicHandler */
	protected static $topicHandler = null;
	/** @var \Bitrix\Forum\Replica\MessageHandler */
	protected static $messageHandler = null;

	/**
	 * Initializes replication process on forum side.
	 *
	 * @return void
	 */
	public function start()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();

		\Bitrix\Replica\Client\HandlersManager::register(new ForumMessageAttachmentHandler);

		self::$topicHandler = new TopicHandler;
		\Bitrix\Replica\Client\HandlersManager::register(self::$topicHandler);
		$eventManager->addEventHandler("forum", "onAfterTopicAdd", array(self::$topicHandler, "onAfterTopicAdd"));
		$eventManager->addEventHandler("forum", "onAfterTopicUpdate", array(self::$topicHandler, "onAfterTopicUpdate"));
		$eventManager->addEventHandler("forum", "onAfterTopicDelete", array(self::$topicHandler, "onAfterTopicDelete"));

		self::$messageHandler = new MessageHandler;
		\Bitrix\Replica\Client\HandlersManager::register(self::$messageHandler);
		$eventManager->addEventHandler("forum", "onBeforeMessageAdd", array(self::$messageHandler, "onBeforeMessageAdd"));
		$eventManager->addEventHandler("forum", "onAfterMessageAdd", array(self::$messageHandler, "onAfterMessageAdd"));
		$eventManager->addEventHandler("forum", "onBeforeMessageUpdate", array(self::$messageHandler, "onBeforeMessageUpdate"));
		$eventManager->addEventHandler("forum", "onAfterMessageUpdate", array(self::$messageHandler, "onAfterMessageUpdate"));
		$eventManager->addEventHandler("forum", "onBeforeMessageDelete", array(self::$messageHandler, "onBeforeMessageDelete"));
		$eventManager->addEventHandler("forum", "onAfterMessageDelete", array(self::$messageHandler, "onAfterMessageDelete"));

		\Bitrix\Replica\Client\HandlersManager::register(new MessageRatingVoteHandler);
	}
}
