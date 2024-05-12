<?php

namespace Bitrix\Forum\Comments\Service;

class Manager
{
	public const TYPE_TASK_INFO = 1;
	public const TYPE_TASK_CREATED = 2;
	public const TYPE_ENTITY_CREATED = 3;
	public const TYPE_FORUM_DEFAULT = 127;

	public static function getTypesList(): array
	{
		return [
			static::TYPE_TASK_INFO,
			static::TYPE_TASK_CREATED,
			static::TYPE_ENTITY_CREATED,
			static::TYPE_FORUM_DEFAULT,
		];
	}

	final public static function find(array $params = [])
	{
		$commentType = (isset($params['SERVICE_TYPE']) ? (int)$params['SERVICE_TYPE'] : 0);

		if ($commentType <= 0)
		{
			return false;
		}

		switch ($commentType)
		{
			case static::TYPE_TASK_INFO:
				$result = new TaskInfo();
				break;
			case static::TYPE_TASK_CREATED:
				$result = new TaskCreated();
				break;
			case static::TYPE_ENTITY_CREATED:
				$result = new EntityCreated();
				break;
			default:
				$result = new ServiceDefault();
		}

		return $result;
	}
}
