<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Controller\Engine\ActionFilter\CheckWritePermission;

Loc::loadLanguageFile(__FILE__);

class SalePaySystemSettingsRobokassa extends \CBitrixComponent implements Controllerable, Errorable
{
	protected ErrorCollection $errorCollection;

	protected static array $requiredModules = ['sale'];

	public function configureActions()
	{
		return [
			'save' => [
				'+prefilters' => [
					new CheckWritePermission(),
				]
			],
		];
	}

	public function onPrepareComponentParams($params)
	{
		$this->errorCollection = new ErrorCollection();

		$this->checkModules();
		$this->initResult();

		return parent::onPrepareComponentParams($params);
	}

	private function checkModules(): void
	{
		foreach (static::$requiredModules as $requiredModule)
		{
			if (!Loader::includeModule($requiredModule))
			{
				$this->errorCollection->setError(
					new Error(
						Loc::getMessage(
							'SALE_SPSR_COMPONENT_MODULE_ERROR',
							[
								'#MODULE_ID#' => $requiredModule,
							]
						)
					)
				);
			}
		}
	}

	protected function hasPermission(): bool
	{
		global $APPLICATION;
		$saleModulePermissions = $APPLICATION->GetGroupRight('sale');

		return $saleModulePermissions >= 'W';
	}

	private function printErrors(): void
	{
		foreach ($this->errorCollection as $error)
		{
			ShowError($error);
		}
	}

	private function getSettings(): array
	{
		return (new PaySystem\Robokassa\ShopSettings())->get();
	}

	private function getSettingsName(): array
	{
		return [
			'ROBOXCHANGE_SHOPLOGIN' => Loc::getMessage('SALE_SPSR_ROBOXCHANGE_SHOPID'),
			'ROBOXCHANGE_SHOPPASSWORD' => Loc::getMessage('SALE_SPSR_ROBOXCHANGE_SHOPPASSWORD'),
			'ROBOXCHANGE_SHOPPASSWORD2' => Loc::getMessage('SALE_SPSR_ROBOXCHANGE_SHOPPASSWORD2'),
		];
	}

	private function initResult(): void
	{
		$this->arResult = [
			'ENTITY_FIELDS' => [],
			'ENTITY_CONFIG' => [],
			'ENTITY_DATA' => [],
			'PAY_SYSTEM_NAME' => '',
		];
	}

	private function prepareResult(): void
	{
		$this->arResult['PAY_SYSTEM_NAME'] = Loc::getMessage('SALE_SPSR_COMPONENT_PAY_SYSTEM_NAME');

		$settings = $this->getSettings();
		$settingsName = $this->getSettingsName();

		if ($settingsName)
		{
			$this->arResult['ENTITY_FIELDS'] = $this->getEntityFields($settingsName);
			$this->arResult['ENTITY_CONFIG'] = $this->getEntityConfig($settingsName);
		}

		if ($settings)
		{
			$this->arResult['ENTITY_DATA'] = $settings;
		}
	}

	private function getEntityFields(array $settingsName): array
	{
		$result = [];

		foreach ($settingsName as $code => $name)
		{
			$result[] = [
				'title' => $name,
				'name' => $code,
				'type' => 'text',
				'editable' => true,
				'optionFlags' => 1,
			];
		}

		return $result;
	}

	private function getEntityConfig(array $settingsName): array
	{
		$settings = array_keys($settingsName);

		$elements = [];

		foreach ($settings as $code)
		{
			$elements[] = ['name' => $code];
		}

		return [
			[
				'title' => Loc::getMessage('SALE_SPSR_COMPONENT_SECTION_TITLE'),
				'name' => 'paysystem_settings',
				'type' => 'section',
				'elements' => $elements,
				'hint' => Loc::getMessage('SALE_SPSR_COMPONENT_SECTION_TITLE_HINT'),
				'data' => [
					'isChangeable' => true,
					'isRemovable' => false,
				],
			],
		];
	}

	public function executeComponent()
	{
		if (!$this->hasPermission())
		{
			$this->errorCollection->setError(new Error(Loc::getMessage('SALE_SPSR_COMPONENT_ACCESS_DENIED_ERROR')));
		}

		if (!$this->errorCollection->isEmpty())
		{
			$this->printErrors();
			return;
		}

		$this->prepareResult();

		$this->includeComponentTemplate();
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Error
	 */
	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	/**
	 * @return void
	 */
	public function saveAction(): array
	{
		$shopSettings = new PaySystem\Robokassa\ShopSettings();
		$fields = $this->request->get('data') ?: [];

		$currentSettings = $this->getSettings();
		if ($currentSettings)
		{
			$preparedSettings = $this->prepareSettings($fields, $currentSettings);
			$saveResult = $shopSettings->update($preparedSettings);
		}
		else
		{
			$preparedSettings = $this->prepareSettings($fields);
			$saveResult = $shopSettings->add($preparedSettings);
		}

		if ($saveResult->isSuccess())
		{
			$this->prepareResult();

			return [
				'ENTITY_DATA' => $this->arResult['ENTITY_DATA'],
			];
		}

		$this->errorCollection->add($saveResult->getErrors());
		return [];
	}

	private function prepareSettings(array $settings, array $currentSettings = []): array
	{
		$result = [];

		$settingsCodeList = PaySystem\Robokassa\ShopSettings::getSettingsCoded();
		foreach ($settingsCodeList as $settingsCode)
		{
			if (isset($settings[$settingsCode]))
			{
				$result[$settingsCode] = trim($settings[$settingsCode]);
			}
		}

		foreach ($currentSettings as $code => $value)
		{
			if (!isset($result[$code]))
			{
				$result[$code] = $value;
			}
		}

		return $result;
	}
}