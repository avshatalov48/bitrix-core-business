<?php

namespace Bitrix\Socialservices\Controller;

use Bitrix\Main\Service\MicroService\BaseReceiver;
use Bitrix\Socialservices\UserTable;

class ZoomUser extends BaseReceiver
{
	public function deauthorizeAction(string $socServLogin, array $payload): void
	{
		$result = UserTable::getList([
			'select' => ['ID', 'USER_ID'],
			'filter' => [
				'=LOGIN' => $socServLogin,
				'=EXTERNAL_AUTH_ID' => 'zoom',
			]
		]);

		while ($user = $result->fetch())
		{
			$deleteResult = UserTable::delete($user['ID']);

			//clean cache to update zoom connect page
			$cacheId = 'zoom' . '|' . $user['USER_ID'];
			$cache = \Bitrix\Main\Data\Cache::createInstance();
			$cache->clean($cacheId, \CZoomInterface::CACHE_DIR_CONNECT_INFO);
		}
	}
}