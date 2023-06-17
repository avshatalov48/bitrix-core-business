<?php

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\Component;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogIblockSectionFieldController extends Controller
{
	public function lazyLoadAction($iblockId, $selectedSectionIds, $productId = null): ?Component
	{
		return new Component(
			'bitrix:catalog.productcard.iblocksectionfield',
			'',
			[
				'IBLOCK_ID' => (int)$iblockId,
				'SELECTED_SECTION_IDS' => $selectedSectionIds,
				'PRODUCT_ID' => (int)$productId,
			]
		);
	}

	public function getSectionsAction($iblockId): array
	{
		$sectionsTree = CIBlockSection::GetTreeList(
			['IBLOCK_ID' => $iblockId],
			['ID', 'NAME', 'DEPTH_LEVEL']
		);

		$allSections = [];

		while ($section = $sectionsTree->fetch())
		{
			$allSections[$section['ID']] = [
				'id' => $section['ID'],
				'name' => $section['NAME'],
				'data' => [],
			];
		}

		return [
			[
				'id' => 'all',
				'name' => Loc::getMessage('CPISF_ALL_SECTIONS_TITLE'),
				'items' => array_values($allSections),
			],
		];
	}

	public function addSectionAction($iblockId, $name): array
	{
		$sectionObject = new \CIBlockSection();

		$fields = [
			'IBLOCK_ID' => $iblockId,
			'NAME' => $name,
		];
		$code = $sectionObject->generateMnemonicCode($name, $iblockId);
		if ($code !== null)
		{
			$fields['CODE'] = $code;
		}
		$ID = $sectionObject->Add($fields);

		if (empty($ID))
		{
			$this->addError(new \Bitrix\Main\Error($sectionObject->LAST_ERROR));

			return [];
		}

		return [
			'id' => $ID,
			'name' => $name,
		];
	}
}
