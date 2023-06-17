<?php

namespace Bitrix\Sale\Controller\Action\PaySystem;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class ActivatePaySystemAction
 * @package Bitrix\Sale\Controller\Action\PaySystem
 * @example BX.ajax.runAction("sale.paysystem.entity.activatePaySystem", { data: { id:'' }});
 * @internal
 */
class ActivatePaySystemAction extends Sale\Controller\Action\BaseAction
{
	private function checkParams(int $id): Sale\Result
	{
		$result = new Sale\Result();

		if (!Sale\PaySystem\Manager::isExist($id))
		{
			$result->addError(new Main\Error("PaySystem with id '{$id}' not found"));
		}

		return $result;
	}

	public function run(int $id)
	{
		$checkParamsResult = $this->checkParams($id);
		if (!$checkParamsResult->isSuccess())
		{
			$this->addErrors($checkParamsResult->getErrors());
			return;
		}

		$activateResult = $this->activatePaySystem($id);
		if (!$activateResult->isSuccess())
		{
			$this->addErrors($activateResult->getErrors());
		}
	}

	private function activatePaySystem($id): Main\ORM\Data\UpdateResult
	{
		return Sale\PaySystem\Manager::update($id, ['ACTIVE' => 'Y']);
	}
}