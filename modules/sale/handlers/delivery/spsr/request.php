<?php

namespace Sale\Handlers\Delivery\Spsr;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Result;
use Bitrix\Main\Error;

Loc::loadMessages(__FILE__);

/**
 * Class Request
 *
 * @package Sale\Handlers\Delivery\Spsr
 */
class Request
{
	/**
	 * @param $requestData
	 * @return Result
	 */
	public function send($requestData)
	{
		return (new Result())->addError(new Error('The company no longer exists'));
	}
	
	public function getServiceTypes($sid, array $knownServices)
	{
		return (new Result())->addError(new Error('The company no longer exists'));
	}

	public function getSidResult($login, $pass, $companyName)
	{
		return (new Result())->addError(new Error('The company no longer exists'));
	}

	public function getInvoicesInfo($sid, $icn, $lang, array $invoiceNumbers)
	{
		return (new Result())->addError(new Error('The company no longer exists'));
	}
}
