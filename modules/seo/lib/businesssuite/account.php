<?php

namespace Bitrix\Seo\BusinessSuite;

use Bitrix\Seo\BusinessSuite\DTO;

abstract class Account extends AbstractBase
{
	public function getProfile() : ?DTO\Profile
	{
		$response = $this->getRequest()->send([
			'methodName' => $this->getMethodName('profile'),
			'parameters' => []
		]);

		if ($response->isSuccess() && $data = $response->fetch())
		{
			return
				(new DTO\Profile())
					->setId($data['ID'])
					->setPicture($data['PICTURE']['data']['url'] ?? null)
					->setLink($data['LINK'])
					->setName($data['NAME'])
				;
		}

		return null;
	}
}