<?php

namespace Bitrix\Calendar\Core\Mappers;

class Factory
{
	private static ?Section $sectionInstance = null;
	private static ?Event $eventInstance = null;
	private static ?SectionConnection $sectionConnectionInstance = null;
	private static ?EventConnection $eventConnectionInstance = null;
	private static ?SyncEvent $syncEventInstance = null;
	private static ?Connection $connectionInstance = null;

	public function getSection(): Section
	{
		if (!self::$sectionInstance)
		{
			self::$sectionInstance = new Section();
		}

		return self::$sectionInstance;
	}


	public function getEvent(): Event
	{
		if (!self::$eventInstance)
		{
			self::$eventInstance = new Event();
		}

		return self::$eventInstance;
	}

	public function getSectionConnection(): SectionConnection
	{
		if (!self::$sectionConnectionInstance)
		{
			self::$sectionConnectionInstance = new SectionConnection();
		}

		return self::$sectionConnectionInstance;
	}


	public function getEventConnection(): EventConnection
	{
		if (!self::$eventConnectionInstance)
		{
			self::$eventConnectionInstance = new EventConnection();
		}

		return self::$eventConnectionInstance;
	}

	public function getSyncEvent(): SyncEvent
	{
		if (!self::$syncEventInstance)
		{
			self::$syncEventInstance = new SyncEvent();
		}

		return self::$syncEventInstance;
	}

	public function getConnection(): Connection
	{
		if (!self::$connectionInstance)
		{
			self::$connectionInstance = new Connection();
		}

		return self::$connectionInstance;
	}
}
