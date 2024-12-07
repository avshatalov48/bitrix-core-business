<?php

namespace Bitrix\Sale;

use Bitrix\Main\Localization\Loc;
use Sale\Handlers\Delivery\YandextaxiHandler;
use Bitrix\Sale\Delivery\Services\Manager;

class EventLogAuditTypeRepository
{
	private const AUDIT_TYPES = [
		'SALE_DELIVERY_CREATE_OBJECT_ERROR',
	];
	private const SALE_DELIVERY_YANDEX_TAXI_AUDIT_TYPE = 'SALE_DELIVERY_YANDEX_TAXI';

	public static function getAuditTypes(): array
	{
		$result = [];

		foreach (self::AUDIT_TYPES as $auditType)
		{
			$result[$auditType] = self::getName($auditType);
		}

		Manager::getHandlersList();
		if (
			class_exists(YandextaxiHandler::class)
			&& YandextaxiHandler::isHandlerCompatible()
		)
		{
			$result[self::SALE_DELIVERY_YANDEX_TAXI_AUDIT_TYPE] = self::getName(
				self::SALE_DELIVERY_YANDEX_TAXI_AUDIT_TYPE
			);
		}

		return $result;
	}

	private static function getName(string $auditType): string
	{
		return '[' . $auditType . '] ' . Loc::getMessage('SALE_AUDIT_TYPE_' . $auditType);
	}
}
