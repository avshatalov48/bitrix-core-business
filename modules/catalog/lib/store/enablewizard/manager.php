<?php

namespace Bitrix\Catalog\Store\EnableWizard;

use Bitrix\Catalog\Config\State;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\UI\Extension;

class Manager
{
	private const MODE_OPTION_NAME = 'store_control_mode';

	public static function getAvailableModes(): array
	{
		$result = [
			ModeList::B24,
		];

		if (in_array(Application::getInstance()->getLicense()->getRegion(), ['ru', 'by', 'kz'], true))
		{
			$result[] = ModeList::ONEC;
		}

		return $result;
	}

	public static function getCurrentMode(): ?string
	{
		if (!State::isEnabledInventoryManagement())
		{
			return null;
		}

		return self::getMode();
	}

	public static function isOnecMode(): bool
	{
		return self::getCurrentMode() === ModeList::ONEC;
	}

	public static function enable(string $mode, array $options = []): Result
	{
		$disableResult = self::disableCurrent();
		if (!$disableResult->isSuccess())
		{
			return $disableResult;
		}

		$enableResult = self::getEnabler($mode)->enable($options);
		if ($enableResult->isSuccess())
		{
			self::setMode($mode);

			if (Loader::includeModule('pull'))
			{
				\CPullWatch::AddToStack(
					'CATALOG_INVENTORY_MANAGEMENT_CHANGED',
					[
						'module_id' => 'crm',
						'command' => 'onCatalogInventoryManagementEnabled',
					],
				);
			}
		}

		return $enableResult;
	}

	public static function disable(): Result
	{
		$disableResult = self::disableCurrent();
		if ($disableResult->isSuccess())
		{
			self::resetMode();

			if (Loader::includeModule('pull'))
			{
				\CPullWatch::AddToStack(
					'CATALOG_INVENTORY_MANAGEMENT_CHANGED',
					[
						'module_id' => 'crm',
						'command' => 'onCatalogInventoryManagementDisabled',
					],
				);
			}
		}

		return $disableResult;
	}

	public static function showEnabledJsNotificationIfNeeded(): void
	{
		Extension::load(['catalog.store-enable-wizard']);
		?>
		<script>
			BX.ready(
				function()
				{
					BX.Catalog.Store.EnableWizardOpener.showEnabledNotificationIfNeeded();
				}
			);
		</script>
		<?php
	}

	private static function disableCurrent(): Result
	{
		$currentMode = self::getCurrentMode();
		if ($currentMode)
		{
			return self::getEnabler($currentMode)->disable();
		}

		return new Result();
	}

	private static function getEnabler(string $mode): Enabler
	{
		return match ($mode)
		{
			ModeList::B24 => new B24Enabler(),
			ModeList::ONEC => new OnecEnabler(),
		};
	}

	private static function setMode(string $mode): void
	{
		if (!ModeList::isValidMode($mode))
		{
			return;
		}

		Option::set('catalog', self::MODE_OPTION_NAME, $mode);
	}

	private static function resetMode(): void
	{
		Option::set('catalog', self::MODE_OPTION_NAME);
	}

	private static function getMode(): string
	{
		$mode = Option::get('catalog', self::MODE_OPTION_NAME);

		return ModeList::isValidMode($mode) ? $mode : ModeList::B24;
	}
}
