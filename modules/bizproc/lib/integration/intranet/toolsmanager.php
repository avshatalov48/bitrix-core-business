<?php

namespace Bitrix\Bizproc\Integration\Intranet;

use Bitrix\Intranet\Settings\Tools;
use Bitrix\Main\Loader;

final class ToolsManager
{
	private static ?ToolsManager $instance = null;
	private bool $canUseIntranetToolsManager;

	private const AUTOMATION_TOOL_ID = 'automation';
	private const BIZPROC_TOOL_ID = 'bizproc';
	private const ROBOTS_TOOL_ID = 'robots';
	private const SCRIPTS_TOOL_ID = 'bizproc_script';

	public static function getInstance(): self
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct()
	{
		$this->canUseIntranetToolsManager = (
			Loader::includeModule('intranet')
			&& class_exists(\Bitrix\Intranet\Settings\Tools\ToolsManager::class)
		);
	}

	public function isAutomationAvailable(): bool
	{
		return $this->check(self::AUTOMATION_TOOL_ID);
	}

	public function isBizprocAvailable(): bool
	{
		return $this->check(self::BIZPROC_TOOL_ID) && $this->isAutomationAvailable();
	}

	public function isRobotsAvailable(): bool
	{
		return $this->check(self::ROBOTS_TOOL_ID) && $this->isAutomationAvailable();
	}

	public function isScriptsAvailable(): bool
	{
		return $this->check(self::SCRIPTS_TOOL_ID) && $this->isAutomationAvailable();
	}

	public function getBizprocUnavailableContent()
	{
		ob_start();
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			'bitrix:intranet.tool.inaccessibility',
			'',
			[
				'SLIDER_CODE' => 'limit_automation_off',
			],
		);

		return ob_get_clean();
	}

	public function getBizprocUnavailableSliderCode()
	{
		return 'limit_automation_off';
	}

	public function getRobotsUnavailableSliderCode()
	{
		return 'limit_crm_rules_off';
	}

	private function check(string $toolId): bool
	{
		if ($this->canUseIntranetToolsManager)
		{
			return Tools\ToolsManager::getInstance()->checkAvailabilityByToolId($toolId);
		}

		return true;
	}
}
