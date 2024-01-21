<?php

namespace Bitrix\Mail\Integration\Intranet;

use Bitrix\Main\Loader;
use Bitrix\Intranet\Settings\Tools;

final class ToolShowSettings
{
	private const MAIL_TOOL_ID = 'mail';
	private const MAIL_SLIDER_CODE = 'limit_contact_center_mail_off';

	private bool $isExistIntranetToolsManager;

	public function __construct()
	{
		$this->isExistIntranetToolsManager = (
			Loader::includeModule('intranet')
			&& class_exists('\Bitrix\Intranet\Settings\Tools\ToolsManager')
		);
	}

	public function isMailAvailable(): bool
	{
		if ($this->isExistIntranetToolsManager)
		{
			return Tools\ToolsManager::getInstance()->checkAvailabilityByToolId(self::MAIL_TOOL_ID);
		}

		return true;
	}

	public function getMailLimitSliderCode(): string
	{
		return self::MAIL_SLIDER_CODE;
	}
}