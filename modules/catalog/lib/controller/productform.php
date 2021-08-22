<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Security\Random;

class ProductForm extends Engine\Controller
{
	protected function getDefaultPreFilters()
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
				new ActionFilter\Scope(ActionFilter\Scope::AJAX),
			]
		);
	}

	public function setConfigAction($configName, $value): void
	{
		$formConfigs = [
			'showTaxBlock', 'showDiscountBlock', 'hiddenCompilationInfoMessage'
		];
		if (in_array($configName, $formConfigs, true))
		{
			$value = ($value === 'N') ? 'N' : 'Y';
			\CUserOptions::SetOption("catalog.product-form", $configName, $value);
		}
	}

	public function createBrandAction(array $fields): ?array
	{
		$iblockId = (int)$fields['iblockId'];
		$name = $fields['name'];

		if (empty($name))
		{
			$this->addError(new Error("Empty name"));

			return null;
		}

		if (!Loader::includeModule('highloadblock') || !Loader::includeModule('iblock'))
		{
			$this->addError(new Error("Modules is not included"));

			return null;
		}

		if (!\CIBlockSectionRights::UserHasRightTo($iblockId, 0, 'section_element_bind'))
		{
			$this->addError(new Error("User has no permissions to create product"));

			return null;
		}

		$propertySettings = PropertyTable::getList([
			'select' => ['ID', 'USER_TYPE_SETTINGS'],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'=ACTIVE' => 'Y',
				'=CODE' => 'BRAND_REF',
			],
			'limit' => 1,
		])
			->fetch()
		;

		if (!$propertySettings)
		{
			return null;
		}

		$propertySettings['USER_TYPE_SETTINGS'] = (
		$userTypeSettings = CheckSerializedData($propertySettings['USER_TYPE_SETTINGS'])
			? unserialize($propertySettings['USER_TYPE_SETTINGS'], ['allowed_classes' => false])
			: array()
		);

		if (empty($userTypeSettings['TABLE_NAME']))
		{
			return null;
		}

		$table = HL\HighloadBlockTable::getList(
			array(
				'select' => array('TABLE_NAME', 'NAME', 'ID'),
				'filter' => array('=TABLE_NAME' => $userTypeSettings['TABLE_NAME'])
			)
		)->fetch();

		$xmlId = Random::getString(16);
		$brandEntity = HL\HighloadBlockTable::compileEntity($table);
		$brandEntityClass = $brandEntity->getDataClass();
		$resultAdd = $brandEntityClass::add([
			'UF_NAME' => $name,
			'UF_XML_ID' => $xmlId,
		]);

		if (!$resultAdd->isSuccess())
		{
			$this->addErrors($resultAdd->getErrors());

			return null;
		}

		return [
			'id' => $xmlId,
		];
	}
}
