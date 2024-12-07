<?php

namespace Bitrix\Location\Infrastructure\Service\CustomFieldsService;

use Bitrix\Main\IO\Path;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(Path::combine(__DIR__, 'customfields.php'));

class SevastopolCustomFields extends CustomFields
{
	/**
	 * @inheritDoc
	 */
	protected function getInfo(): array
	{
		$country = $this->currentRegion === 'ru'
			? Loc::getMessage('LOCATION_ISTRUCTURE_CUSTOM_FIELDS_RUSSIA')
			: Loc::getMessage('LOCATION_ISTRUCTURE_CUSTOM_FIELDS_UKRAINE');

		return [
			'country' => $country,
		];
	}
}
