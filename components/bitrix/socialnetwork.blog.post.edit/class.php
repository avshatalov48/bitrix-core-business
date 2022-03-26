<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

final class SocialnetworkBlogPostEdit extends \Bitrix\Socialnetwork\Component\BlogPostEdit
{
	public function convertRequestData(): void
	{
		\Bitrix\Socialnetwork\ComponentHelper::convertSelectorRequestData($_POST, [
			'perms' => $this->arResult['perms'],
		]);
	}

	public function executeComponent()
	{
		return $this->__includeComponent();
	}
}
