<?php

namespace Bitrix\Location\Infrastructure\Service\CustomFieldsService;

use Bitrix\Main\IO\Path;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
Loc::loadMessages(Path::combine(__DIR__, 'customfields.php'));

class KhersonCustomFields extends CustomFields
{
	/**
	 * @inheritDoc
	 */
	protected function getInfo(): array
	{
		if ($this->currentRegion === 'ru')
		{
			$country = Loc::getMessage('LOCATION_ISTRUCTURE_CUSTOM_FIELDS_RUSSIA');
		}
		else
		{
			$country = Loc::getMessage('LOCATION_ISTRUCTURE_CUSTOM_FIELDS_UKRAINE');
		}

		return [
			'country' => $country,
		];
	}
}
