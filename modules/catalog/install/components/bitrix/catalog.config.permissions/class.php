<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Component\PermissionConfig;
use Bitrix\UI\Toolbar\Facade\Toolbar;

Loader::requireModule('catalog');
Loc::loadMessages(__FILE__);

class CatalogConfigPermissionsComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errorCollection */
	private $errorCollection;

	/**
	 * @inheritDoc
	 */
	public function executeComponent()
	{
		global $APPLICATION;

		/**
		 * @var \CMain $APPLICATION
		 */

		$this->errorCollection = new ErrorCollection();

		if (!$this->checkAccessPermissions())
		{
			$this->errorCollection->setError(new Error(Loc::getMessage('CATALOG_WRONG_PERMISSION')));
			$this->printErrors();
			return;
		}

		$isSetTitle = ($this->arParams['SET_TITLE'] ?? 'Y') === 'Y';
		if ($isSetTitle)
		{
			$APPLICATION->SetTitle(
				Loc::getMessage('CATALOG_CONFIG_ROLE_EDIT_COMP_ACCESS_RIGHTS')
			);
		}

		$this->initResult();
		$this->includeComponentTemplate();
	}

	/**
	 * @return void
	 */
	private function initResult(): void
	{
		$this->arResult['ERRORS'] = array();
		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';
		$this->arResult['NAME'] = Loc::getMessage('CATALOG_CONFIG_ROLE_EDIT_COMP_TEMPLATE_NAME');

		$configPermissions = new PermissionConfig();

		$this->arResult['USER_GROUPS'] = $configPermissions->getUserGroups();
		$this->arResult['ACCESS_RIGHTS'] = $configPermissions->getAccessRights();
	}

	/**
	 * Check can user view and change rights.
	 *
	 * @return bool
	 */
	private function checkAccessPermissions(): bool
	{
		return AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_RIGHTS_EDIT);
	}

	/**
	 * Show component errors.
	 *
	 * @return void
	 */
	private function printErrors(): void
	{
		Toolbar::deleteFavoriteStar();
		foreach ($this->errorCollection as $error)
		{
			$this->includeErrorComponent($error->getMessage());
		}
	}

	/**
	 * Include errors component.
	 *
	 * @param string $errorMessage
	 * @param string|null $description
	 *
	 * @return void
	 */
	protected function includeErrorComponent(string $errorMessage, string $description = null): void
	{
		global $APPLICATION;

		$APPLICATION->IncludeComponent(
			"bitrix:ui.info.error",
			"",
			[
				'TITLE' => $errorMessage,
				'DESCRIPTION' => $description,
			]
		);
	}
}
