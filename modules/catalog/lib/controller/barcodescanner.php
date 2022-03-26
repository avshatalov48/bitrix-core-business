<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Pull\Event;

final class BarcodeScanner extends \Bitrix\Main\Engine\Controller
{
	/**
	 * @param string $id
	 * @return array|null
	 */
	public function sendMobilePushAction(string $id): ?array
	{
		if (!$this->getCurrentUser()->canDoOperation('catalog_read'))
		{
			$this->addError(new Error(Loc::getMessage('BARCODE_SCANNER_ACCESS_DENIED')));
			return null;
		}

		if (!Loader::includeModule('pull'))
		{
			$this->addError(new Error(Loc::getMessage('BARCODE_SCANNER_PULL_MODULE_REQUIRED')));
			return null;
		}

		/**
		 * System push waking app when it is not in active state
		 */
		(new \CPushManager())->sendMessage([
			[
				'USER_ID' => $this->getCurrentUser()->getId(),
				'APP_ID' => 'Bitrix24',
				'EXPIRY' => 0,
				'PARAMS'=> [
					'TYPE' => 'CATALOG_BARCODE_SCANNER',
					'ID'=> $id,
				],
				'ADVANCED_PARAMS' => [
					'senderName' => Loc::getMessage('BARCODE_SCANNER_PUSH_TITLE'),
					'senderMessage' => Loc::getMessage('BARCODE_SCANNER_PUSH_TEXT')
				]
			]
		]);

		/**
		 * Local push for app in active state
		 */
		Event::add(
			$this->getCurrentUser()->getId(),
			[
				'module_id' => 'catalog',
				'command' => 'OpenBarcodeScanner',
				'params' => [
					'id' => $id,
				]
			]
		);

		return [];
	}
}
