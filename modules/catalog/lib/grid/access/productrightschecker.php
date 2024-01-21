<?php

namespace Bitrix\Catalog\Grid\Access;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Iblock\Grid\Access\IblockRightsChecker;
use Bitrix\Main\Loader;

Loader::requireModule('iblock');

class ProductRightsChecker extends IblockRightsChecker
{
	private AccessController $controller;

	public function __construct(int $iblockId, ?AccessController $controller = null)
	{
		parent::__construct($iblockId);
		$this->controller = $controller ?? AccessController::getCurrent();
	}

	public function canEditPrices(): bool
	{
		return $this->controller->check(ActionDictionary::ACTION_PRICE_EDIT);
	}

	#region override

	public function canAddElement(int $elementId): bool
	{
		return $this->controller->check(ActionDictionary::ACTION_PRODUCT_ADD);
	}

	public function canEditElement(int $elementId): bool
	{
		return $this->controller->check(ActionDictionary::ACTION_PRODUCT_EDIT);
	}

	public function canEditElements(): bool
	{
		return $this->controller->check(ActionDictionary::ACTION_PRODUCT_EDIT);
	}

	public function canEditSection(int $sectionId): bool
	{
		return $this->controller->check(ActionDictionary::ACTION_PRODUCT_EDIT);
	}

	public function canDeleteElement(int $elementId): bool
	{
		return $this->controller->check(ActionDictionary::ACTION_PRODUCT_DELETE);
	}

	public function canDeleteElements(): bool
	{
		return $this->controller->check(ActionDictionary::ACTION_PRODUCT_DELETE);
	}

	public function canDeleteSection(int $sectionId): bool
	{
		return $this->controller->check(ActionDictionary::ACTION_PRODUCT_DELETE);
	}

	public function canBindElementToSection(int $sectionId): bool
	{
		return $this->controller->check(ActionDictionary::ACTION_PRODUCT_EDIT);
	}

	public function canBindSectionToSection(int $sectionId): bool
	{
		return $this->controller->check(ActionDictionary::ACTION_PRODUCT_EDIT);
	}

	#endregion override
}
