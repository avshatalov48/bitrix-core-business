<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogProductCardIblockSectionField
	extends \CBitrixComponent
	implements Controllerable, Errorable
{
	use ErrorableImplementation;

	private $iblockId;
	private $productId;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
	}

	protected function showErrors()
	{
		foreach ($this->getErrors() as $error)
		{
			ShowError($error);
		}
	}

	public function configureActions()
	{
		return [];
	}

	protected function listKeysSignedParameters()
	{
		return [
			'PRODUCT_ID',
			'IBLOCK_ID',
			'SELECTED_SECTION_IDS',
		];
	}

	public function onPrepareComponentParams($params)
	{
		$params['IBLOCK_ID'] = $params['IBLOCK_ID'] ?? 0;
		$params['PRODUCT_ID'] = $params['PRODUCT_ID'] ?? 0;

		$params['SELECTED_SECTION_IDS'] = $params['SELECTED_SECTION_IDS'] ?? [];

		if (!is_array($params['SELECTED_SECTION_IDS']))
		{
			$params['SELECTED_SECTION_IDS'] = [];
		}

		return parent::onPrepareComponentParams($params);
	}

	public function executeComponent()
	{
		if ($this->checkModules() && $this->checkPermissions() && $this->checkRequiredParameters())
		{
			$this->initializeSections();

			$this->errorCollection->clear();
			$this->includeComponentTemplate();
		}

		$this->showErrors();
	}

	protected function checkModules()
	{
		if (!Loader::includeModule('catalog'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Module "catalog" is not installed.');

			return false;
		}

		return true;
	}

	protected function checkPermissions()
	{
		return true;
	}

	protected function checkRequiredParameters()
	{
		if (!$this->hasIblockId())
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Iblock id not found.');

			return false;
		}

		return true;
	}

	private function hasIblockId(): bool
	{
		return $this->arParams['IBLOCK_ID'] > 0;
	}

	private function hasProductId(): bool
	{
		return $this->arParams['PRODUCT_ID'] > 0;
	}

	private function hasSelectedSectionIds(): bool
	{
		return !empty($this->arParams['SELECTED_SECTION_IDS']);
	}

	protected function initializeSections()
	{
		$sectionIds = $this->getSelectedSections();
		$this->arResult['LIST'] = [];

		foreach ($this->getSectionsData($sectionIds) as $id => $name)
		{
			$this->arResult['LIST'][] = [
				'id' => $id,
				'name' => $name,
				'data' => [],
			];
		}
	}

	private function getSectionsData(array $sectionIds)
	{
		$sections = [];

		if (!empty($sectionIds))
		{
			$sectionList = CIBlockSection::GetList(
				[], // ['left_margin' => 'asc'],
				['ID' => $sectionIds],
				false,
				['ID', 'NAME']
			);
			while ($section = $sectionList->Fetch())
			{
				$sections[$section['ID']] = $section['NAME'];
			}
		}

		return $sections;
	}

	private function getSelectedSections()
	{
		$sectionIds = [];

		if ($this->hasSelectedSectionIds())
		{
			$sectionIds = $this->arParams['SELECTED_SECTION_IDS'];
		}
		elseif ($this->hasProductId())
		{
			$sectionIds = $this->loadSectionsForProduct($this->arParams['PRODUCT_ID']);
		}

		return $sectionIds;
	}

	private function loadSectionsForProduct($productId)
	{
		$sectionIds = [];

		$result = CIBlockElement::GetElementGroups(
			$productId,
			true,
			['ID', 'IBLOCK_ELEMENT_ID']
		);
		while ($group = $result->Fetch())
		{
			$sectionIds[] = (int)$group['ID'];
		}

		return $sectionIds;
	}
}