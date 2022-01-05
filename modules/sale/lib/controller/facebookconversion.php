<?php

namespace Bitrix\Sale\Controller;

use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FacebookConversion extends \Bitrix\Main\Engine\Controller
{
	public function configureActions()
	{
		return [
			'customizeProduct' => [
				'-prefilters' => [
					Authentication::class
				]
			],
			'contact' => [
				'-prefilters' => [
					Authentication::class
				]
			],
		];
	}

	public function contactAction($contactBy): void
	{
		\Bitrix\Sale\Internals\FacebookConversion::onContactHandler($contactBy);
	}

	public function customizeProductAction(string $offerId): void
	{
		\Bitrix\Sale\Internals\FacebookConversion::onCustomizeProductHandler((int)$offerId);
	}
}
