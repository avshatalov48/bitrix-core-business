<?php

namespace Bitrix\Calendar\Core\Handlers;

use Bitrix\Calendar\Sync\Handlers;

class HandlersMap
{
	/**
	 * @param string $handlerName
	 * @return string
	 */
	public static function getHandler(string $handlerName): string
	{
		return self::getHandlersList()[$handlerName];
	}

	/**
	 * @return string[]
	 */
	private static function getHandlersList(): array
	{
		return [
			'instancesChain' => InstancesChainHandler::class,
			'parentEvent' => ReceivingParentEventHandler::class,
			'identifierVendorEvent' => IdentifierEventHandler::class,
			'updateParentExdateHandler' => UpdateMasterExdateHandler::class,
			'syncEventMergeHandler' => Handlers\SyncEventMergeHandler::class,
			'masterPushHandler' => Handlers\MasterPushHandler::class,
		];
	}
}
