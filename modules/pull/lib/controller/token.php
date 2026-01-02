<?php

namespace Bitrix\Pull\Controller;

use Bitrix\Main\Engine;
use Bitrix\Main\Error;
use Bitrix\Pull\Model\PushTable;

class Token extends Engine\Controller
{
	function removeAction(string $token)
	{
		$tokenData = PushTable
			::query()
			->addSelect('ID')
			->where('DEVICE_TOKEN', $token)
			->unionAll(
				PushTable
					::query()
					->addSelect('ID')
					->where('VOIP_TOKEN', $token)
			)->fetch()
		;


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
		$result['remove'] = [
			'-prefilters' => [
				Engine\ActionFilter\Csrf::class,
				Engine\ActionFilter\Authentication::class,
			],
		];
		return $result;
	}
}