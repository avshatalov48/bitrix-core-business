<?php

namespace Bitrix\Seo\Checkout\Services;

use Bitrix\Main\Error;
use Bitrix\Seo\Checkout\Response;

/**
 * Class ResponseYookassa
 * @package Bitrix\Seo\Checkout\Services
 */
class ResponseYookassa extends Response
{
	const TYPE_CODE = 'yookassa';

	/**
	 * @param $data
	 */
	public function parse($data)
	{
		if (isset($data['errors']))
		{
			$this->addError(new Error($data['errors']['message'], $data['errors']['code']));
		}
		else
		{
			if ($data['data'])
			{
				$this->setData($data['data']);
			}
		}
	}
}