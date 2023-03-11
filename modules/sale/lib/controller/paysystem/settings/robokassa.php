<?php

namespace Bitrix\Sale\Controller\PaySystem\Settings;

use Bitrix\Main;
use Bitrix\Sale;

class Robokassa extends Main\Engine\Controller
{
	/**
	 * @return array
	 */
	public function configureActions()
	{
		return [
			'delete' => [
				'prefilters' => [
					new Sale\Controller\Engine\ActionFilter\CheckWritePermission(),
				],
			],
			'getRegisterComponent' => [
				'prefilters' => [
					new Sale\Controller\Engine\ActionFilter\CheckWritePermission(),
				],
			],
		];
	}

	/**
	 * Deletes paysystem settings
	 *
	 * @example
	 * ```js
	 * BX.ajax.runAction("sale.paysystem.settings.robokassa.delete", {});
	 * ```
	 *
	 * @return void
	 */
	public function deleteAction(): void
	{
		(new Sale\PaySystem\Robokassa\ShopSettings())->delete();
	}

	/**
	 * Gets register component for paysystem
	 *
	 * @example
	 * ```js
	 * BX.ajax.runAction("sale.paysystem.settings.robokassa.getRegisterComponent", {});
	 * ```
	 *
	 * @return Main\Engine\Response\Component
	 */
	public function getRegisterComponentAction(): Main\Engine\Response\Component
	{
		return new Main\Engine\Response\Component(
			'bitrix:sale.paysystem.registration.robokassa',
			'.default'
		);
	}
}
