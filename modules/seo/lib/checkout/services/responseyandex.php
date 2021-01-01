<?php

namespace Bitrix\Seo\Checkout\Services;

use Bitrix\Main\Error;
use Bitrix\Seo\Checkout\Response;

/**
 * Class ResponseYandex
 * @deprecated
 * @package Bitrix\Seo\Checkout\Services
 */
class ResponseYandex extends Response
{
	const TYPE_CODE = 'yandex';

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