<?php

namespace Bitrix\Socialservices\Controller;

use \Bitrix\Main\Context,
	\Bitrix\Main\Engine,
	\Bitrix\Main\Engine\ActionFilter\Csrf,
	\Bitrix\Main\Engine\ActionFilter\Authentication;


class AuthFlow extends Engine\Controller
{
	private const APPLE_OAUTH_URL = 'https://appleid.apple.com/auth/authorize';

	public function configureActions(): array
	{
		return [
			'signInApple' => [
				'-prefilters' => [
					Csrf::class,
					Authentication::class
				]
			]
		];
	}

	public function signInAppleAction(): void
	{
		$redirectUrl = $this->getRequest()->getQuery('url');

		if (strpos($redirectUrl, self::APPLE_OAUTH_URL) === 0)
		{
			LocalRedirect($redirectUrl, true);
			die();
		}
	}
}