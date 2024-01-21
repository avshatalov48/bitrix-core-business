<?php

namespace Bitrix\Catalog\Restriction;

use Bitrix\Intranet\Settings\Tools\ToolsManager;
use Bitrix\Main\Loader;
use CUtil;


class ToolAvailabilityManager
{
	private bool $canUseIntranetToolsManager;
	private const INVENTORY_MANAGEMENT_TOOL_ID = 'inventory_management';
	public const INVENTORY_MANAGEMENT_SLIDER_CODE = 'limit_store_inventory_management_off';

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

	public function checkInventoryManagementAvailability(): bool
	{
		return $this->check(self::INVENTORY_MANAGEMENT_TOOL_ID);
	}

	private function check(string $toolId): bool
	{
		if ($this->canUseIntranetToolsManager)
		{
			return ToolsManager::getInstance()->checkAvailabilityByToolId($toolId);
		}

		return true;
	}

	public function getInventoryManagementStubContent(): string
	{
		return $this->getStubComponentContent([
			'sliderCode' => self::INVENTORY_MANAGEMENT_SLIDER_CODE,
		]);
	}

	public function getInventoryManagementStubJs(): string
	{
		return $this->getJs(self::INVENTORY_MANAGEMENT_SLIDER_CODE);
	}

	private function getStubComponentContent(array $data = []): string
	{
		$params = [];

		if (!empty($data['sliderCode']))
		{
			$params['SLIDER_CODE'] = $data['sliderCode'];
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

	private function getJs(string $id): string
	{
		if (!Loader::includeModule('ui'))
		{
			return '';
		}

		return '
			top && top.BX.loadExt("ui.info-helper").then(() => {
				top.BX.UI.InfoHelper.show("' . CUtil::JSEscape($id) . '");
			});
		';
	}
}
