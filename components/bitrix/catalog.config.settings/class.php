<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Config\CatalogSettings;
use Bitrix\Catalog\Store\EnableWizard\OnecAppManager;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loader::includeModule('catalog');

class CatalogConfigSettingsComponent extends \CBitrixComponent implements Controllerable, Errorable
{
	private ErrorCollection $errorCollection;

	public function onPrepareComponentParams($arParams)
	{
		$this->errorCollection = new ErrorCollection();

		return parent::onPrepareComponentParams($arParams);
	}

	public function executeComponent()
	{
		if (!$this->checkViewPermissions())
		{
			$this->includeErrorComponent(Loc::getMessage('CAT_CONFIG_SETTINGS_NO_PERMISSION'));

			return;
		}

		$catalogSettings = new CatalogSettings();
		$this->arResult['settings'] = $catalogSettings->get()->toArray();

		$this->includeComponentTemplate();
	}

	private function checkViewPermissions(): bool
	{
		$accessController = AccessController::getCurrent();

		return
			(
				$accessController->check(ActionDictionary::ACTION_CATALOG_READ)
				&& $accessController->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS)
			)
			|| $accessController->check(ActionDictionary::ACTION_RESERVED_SETTINGS_ACCESS)
		;
	}

	protected function includeErrorComponent(string $errorMessage, string $description = null): void
	{
		global $APPLICATION;

		$APPLICATION->IncludeComponent(
			'bitrix:ui.info.error',
			'',
			[
				'TITLE' => $errorMessage,
				'DESCRIPTION' => $description,
			]
		);
	}

	public function saveAction(array $data): array
	{
		$filteredData = $this->filterSaveParamsByPermissions($data);
		if (empty($filteredData))
		{
			$this->errorCollection->add([new Error(Loc::getMessage('CAT_CONFIG_SETTINGS_NO_PERMISSION'))]);

			return ['success' => false];
		}

		$catalogSettings = new CatalogSettings($data);
		$result = $catalogSettings->save();
		if ($result->isSuccess())
		{
			return ['success' => true];
		}

		$this->errorCollection->add($result->getErrors());

		return ['success' => false];
	}

	public function refreshAppLinkAction(): array
	{
		return OnecAppManager::getStatusUrl();
	}

	private function filterSaveParamsByPermissions(array $params): array
	{
		$accessController = AccessController::getCurrent();

		if (
			$accessController->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS)
			&& $accessController->check(ActionDictionary::ACTION_RESERVED_SETTINGS_ACCESS)
		)
		{
			return $params;
		}

		if (
			$accessController->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS)
			&& !$accessController->check(ActionDictionary::ACTION_RESERVED_SETTINGS_ACCESS)
		)
		{
			$result = $params;
			unset($result['reservationSettings']);

			return $result;
		}

		if (
			!$accessController->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS)
			&& $accessController->check(ActionDictionary::ACTION_RESERVED_SETTINGS_ACCESS)
		)
		{
			return [
				'reservationSettings' => $params['reservationSettings'],
			];
		}

		return [];
	}

	public function configureActions()
	{
		return [];
	}

	public function getErrors(): array
	{
		return $this->errorCollection->toArray();
	}

	public function getErrorByCode($code): Error
	{
		return $this->errorCollection->getErrorByCode($code);
	}
}
