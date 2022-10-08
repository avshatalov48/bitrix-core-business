<?php

namespace Bitrix\Main\Rest;

use Bitrix\Rest\Event\EventBind;
use Bitrix\Rest\Event\EventBindInterface;
use Bitrix\Rest\RestException;

class UserField implements EventBindInterface
{
	const EVENT_ON_AFTER_ADD = 'OnAfterUserTypeAdd';
	const EVENT_ON_AFTER_UPDATE = 'OnAfterUserTypeUpdate';
	const EVENT_ON_AFTER_DELETE = 'OnAfterUserTypeDelete';

	/**
	 * @inheritDoc
	 */
	public static function getHandlers(): array
	{
		return (new EventBind(self::class))->getHandlers(static::getBindings());
	}

	/**
	 *
	 * Get bindings from PHP events to REST events
	 *
	 * @return string[]
	 */
	protected static function getBindings(): array
	{
		return [
			self::EVENT_ON_AFTER_ADD => 'main.on.user.type.add',
			self::EVENT_ON_AFTER_UPDATE => 'main.on.user.type.update',
			self::EVENT_ON_AFTER_DELETE => 'main.on.user.type.delete',
		];
	}

	/**
	 * @inheritDoc
	 */
	public static function getCallbackRestEvent(): array
	{
		return [self::class, 'processItemEvent'];
	}

	/**
	 *
	 * Handler for result improvement to REST event handlers
	 *
	 * @param array $arParams
	 * @param array $arHandler
	 * @return array[]
	 * @throws RestException
	 */
	public static function processItemEvent(array $arParams, array $arHandler): array
	{
		$item = $arParams[0] ?? null;
		$id = $arParams[1] ?? null;

		$id = $id?:$item['ID'];

		if (!$id)
		{
			throw new RestException('id not found trying to process event');
		}

		return [
			'FIELDS' => [
				'ID' => $id
			],
		];
	}
}