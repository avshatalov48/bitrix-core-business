<?php
namespace Bitrix\Landing\Transfer;

use \Bitrix\Main\Event;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

//rest/dev/configuration/readme.php

class AppConfiguration
{
	/**
	 * Builds manifests for each placement.
	 * @param Event $event
	 * @return array
	 */
	public static function getManifestList(Event $event)
	{
		$manifestList = [];

		$codes = [
			'page', 'store', 'knowledge'
		];

		foreach ($codes as $code)
		{
			$codeUpper = strtoupper($code);
			$manifestList[] = [
				'CODE' => 'landing_' . $code,
				'VERSION' => 1,
				'ACTIVE' => 'Y',
				'PLACEMENT' => [
					'landing_' . $code
				],
				'USES' => [],
				'COLOR' => '#ff799c',
				'ICON' => '/bitrix/images/landing/landing_transfer.svg',
				'TITLE' => Loc::getMessage('LANDING_TRANSFER_GROUP_TITLE'),
				'DESCRIPTION' => Loc::getMessage('LANDING_TRANSFER_GROUP_DESC'),
				'EXPORT_ACTION_DESCRIPTION' => Loc::getMessage('LANDING_TRANSFER_EXPORT_ACTION_DESCRIPTION_' . $codeUpper),
				'EXPORT_TITLE_BLOCK' => Loc::getMessage('LANDING_TRANSFER_EXPORT_ACTION_TITLE_BLOCK_' . $codeUpper),
				'EXPORT_TITLE_PAGE' => Loc::getMessage('LANDING_TRANSFER_EXPORT_ACTION_TITLE_BLOCK_' . $codeUpper)
			];
		}

		return $manifestList;
	}
}
