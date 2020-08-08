<?php

namespace Bitrix\Main\UserField\Engine;

class SettingsArea implements \Bitrix\Main\Engine\Response\ContentArea\ContentAreaInterface
{
	protected $userField;

	public function __construct(array $userField)
	{
		$this->userField = $userField;
	}

	public function getHtml()
	{
		$renderer = new \Bitrix\Main\UserField\Renderer($this->userField, [
			'mode' => 'main.admin_settings',
			'NAME' => 'settings',
		]);

		return $renderer->render();
	}
}