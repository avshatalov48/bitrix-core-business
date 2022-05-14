<?php
namespace Bitrix\Sale\Controller;

use Bitrix\Main;
use Bitrix\Sale\Controller\Action;

/**
 * Class Entity
 * @package Bitrix\Sale\Controller
 */
class Entity extends \Bitrix\Main\Engine\Controller
{
	/**
	 * @return \string[][]
	 */
	public function configureActions()
	{
		return [
			'addbasketitem' => [
				'class' => Action\Entity\AddBasketItemAction::class,
			],
			'deletebasketitem' => [
				'class' => Action\Entity\DeleteBasketItemAction::class,
			],
			'updatebasketitem' => [
				'class' => Action\Entity\UpdateBasketItemAction::class,
			],
			'changebasketitem' => [
				'class' => Action\Entity\ChangeBasketItemAction::class,
			],
			'userconsentrequest' => [
				'class' => Action\Entity\UserConsentRequestAction::class,
			],
			'getbasket' => [
				'class' => Action\Entity\GetBasketAction::class,
			],
			'saveorder' => [
				'class' => Action\Entity\SaveOrderAction::class,
			],
			'initiatepay' => [
				'class' => Action\Entity\InitiatePayAction::class,
			],
		];
	}
}
