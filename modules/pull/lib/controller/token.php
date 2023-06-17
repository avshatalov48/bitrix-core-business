<?php

namespace Bitrix\Pull\Controller;

use Bitrix\Main\Engine;
use Bitrix\Main\Error;
use Bitrix\Pull\Model\PushTable;

class Token extends Engine\Controller
{
	function removeAction(string $token)
	{
		$tokenData = PushTable::getList([
			"filter" => ["=DEVICE_TOKEN" => $token]
		])->fetch();

		if (!$tokenData)
		{
			$this->addError(new Error("No token found", "NOT_FOUND"));
			return;
		}

		$deleteResult = PushTable::delete($tokenData["ID"]);
		if (!$deleteResult->isSuccess())
		{
			$this->addErrors($deleteResult->getErrors());
		}
	}

	public function configureActions()
	{
		$result = parent::configureActions();
		$result['remove'] = array(
			'-prefilters' => array(
				Engine\ActionFilter\Csrf::class,
				Engine\ActionFilter\Authentication::class,
			)
		);
		return $result;
	}
}