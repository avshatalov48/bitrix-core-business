<?php

namespace Bitrix\Socialservices\Controller;

use Bitrix\Main\Service\MicroService\BaseReceiver;
use Bitrix\Socialservices\UserTable;

class ZoomUser extends BaseReceiver
{
	protected $zoomSocServ;

	public function deauthorizeAction(string $socServLogin, array $payload): void
	{
		$result = UserTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=LOGIN' => $socServLogin,
				'=EXTERNAL_AUTH_ID' => 'zoom',
			]
		]);

		while ($user = $result->fetch())
		{
			$deleteResult = UserTable::delete($user['ID']);
		}

		//we send compliance request only once, even if a zoom user was connected on several Bitrix24.
		if (!empty($payload) && isset($deleteResult) && $deleteResult->isSuccess())
		{
			$this->zoomSocServ = new \CSocServZoom();
			$this->zoomSocServ->sendComplianceRequest($payload);
		}
	}
}