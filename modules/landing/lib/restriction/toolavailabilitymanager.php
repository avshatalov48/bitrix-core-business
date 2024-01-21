<?php

namespace Bitrix\Landing\Restriction;

use Bitrix\Intranet\Settings\Tools\ToolsManager;
use Bitrix\Main\Loader;


class ToolAvailabilityManager
{
	private bool $canUseIntranetToolsManager;

	public function __construct()
	{
		$this->canUseIntranetToolsManager = (
			Loader::includeModule('intranet')
			&& class_exists('\Bitrix\Intranet\Settings\Tools\ToolsManager')
		);
	}

	public static function getInstance(): self
	{
		return new self();
	}

	public function check(string $toolId): bool
	{
		if ($this->canUseIntranetToolsManager)
		{
			return ToolsManager::getInstance()->checkAvailabilityByToolId($toolId);
		}

		return true;
	}

	public function getStubComponentContent(string $sliderCode): string
	{
		$params = [];

		if (!empty($sliderCode))
		{
			$params['SLIDER_CODE'] = $sliderCode;
		}

		ob_start();
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			'bitrix:intranet.tool.inaccessibility',
			'',
			$params,
			null,
			['HIDE_ICONS' => 'Y'],
		);

		return ob_get_clean();
	}
}
