<?php

namespace Bitrix\Sale\UI\Input;

use Bitrix\Main\Loader;
use Bitrix\Main\UI;
use Bitrix\Main\Web\Json;

class User
{
	protected static bool $uiIncluded;

	public static function renderSelector(): string
	{
		if (!isset(self::$uiIncluded))
		{
			self::$uiIncluded = Loader::includeModule('ui');
		}
		if (!self::$uiIncluded)
		{
			return '';
		}

		$config = Json::encode([
			'containerId' => 'user-id-container',
			'fieldName' => 'USER_ID',
			'multiple' => 'N',
			'collectionType' => 'int',
			'entities' => [
				[
					'id' => 'user',
					'dynamicLoad' => true,
					'dynamicSearch' => true,
				],
				[
					'id' => 'sale-user',
					'dynamicLoad' => true,
					'dynamicSearch' => true,
					'searchFields' => [
						['name' => 'email', 'type' => 'email'],
					],
					'itemOptions' => [
						'default' => [
							'avatar' => '/bitrix/images/sale/default-user.svg',
						],
					],
				],
			],
		]);

		UI\Extension::load('ui.field-selector');

		return <<<HTML
			<div id="user-id-container"></div>
			<script>
			(function() {
				const selector = new BX.UI.FieldSelector($config);
				selector.render();
			})();
			</script>
			HTML
		;
	}
}
