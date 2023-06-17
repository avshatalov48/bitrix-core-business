<?php
namespace Bitrix\Sale\Controller\PaySystem;

use Bitrix\Main;
use Bitrix\Sale\Controller\Action;

/**
 * Class Entity
 * @package Bitrix\Sale\Controller\PaySystem
 */
class Entity extends Main\Engine\Controller
{
	/**
	 * @return \string[][]
	 */
	public function configureActions()
	{
		return [
			'addPaySystem' => [
				'class' => Action\PaySystem\AddPaySystemAction::class,
			],
			'registerYookassaWebhook' => [
				'class' => Action\PaySystem\RegisterYookassaWebhookAction::class,
			],
			'activatePaySystem' => [
				'class' => Action\PaySystem\ActivatePaySystemAction::class,
			],
			'deactivatePaySystem' => [
				'class' => Action\PaySystem\DeactivatePaySystemAction::class,
			],
		];
	}
}
