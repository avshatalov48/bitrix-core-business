<?php

namespace Bitrix\Catalog\Filter\DataProvider;

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Grid\Column;
use Bitrix\Main\Localization\Loc;

class StoreDataProvider extends \Bitrix\Main\Filter\EntityDataProvider
{
	public function getSettings()
	{
		// TODO: Implement getSettings() method.
	}

	public function prepareFields()
	{
		$fields = [
			'ID' => $this->createField('ID', [
				"name" => "ID",
				"type" => "number",
				"default" => true
			]),
			'SITE_ID' => $this->createField('SITE_ID', [
				"name" => Loc::getMessage("STORE_SITE_ID"),
				"type" => "list",
				'partial' => true,
			]),
			'ACTIVE' => $this->createField('ACTIVE', [
				"name" => Loc::getMessage("STORE_ACTIVE"),
				"type" => "list",
				'partial' => true,
			]),
			'IS_DEFAULT' => $this->createField('IS_DEFAULT', [
				"name" => Loc::getMessage("IS_DEFAULT"),
				"type" => "list",
				'partial' => true,
			]),
			'TITLE' => $this->createField('TITLE', [
				"name" => Loc::getMessage("TITLE"),
				'partial' => true,
			]),
			'CODE' => $this->createField('CODE', [
				"name" => Loc::getMessage("STORE_CODE"),
			]),
			'XML_ID' => $this->createField('XML_ID', [
				"name" => Loc::getMessage("STORE_XML_ID"),
			]),
			'ISSUING_CENTER' => $this->createField('ISSUING_CENTER', [
				"name" => Loc::getMessage("ISSUING_CENTER"),
				"type" => "list",
				'partial' => true,
			]),
			'SHIPPING_CENTER' => $this->createField('SHIPPING_CENTER', [
				"name" => Loc::getMessage("SHIPPING_CENTER"),
				"type" => "list",
				'partial' => true,
			]),
			'ADDRESS' => $this->createField('ADDRESS', [
				"name" => Loc::getMessage("ADDRESS"),
				'partial' => true,
			]),
			'PHONE' => $this->createField('PHONE', [
				"name" => Loc::getMessage("PHONE"),
				'partial' => true,
			]),
			'EMAIL' => $this->createField('EMAIL', [
				"id" => "",
				"name" => "E-mail",
				'partial' => true,
			]),
		];

		if (!$this->allowedShippingCenterField())
		{
			unset($fields['SHIPPING_CENTER']);
		}

		return $fields;
	}

	protected function getFieldName($fieldID)
	{
		return Loc::getMessage("STORE_{$fieldID}_NAME");
	}

	public function prepareFieldData($fieldID)
	{
		$checkboxFields = ['ACTIVE', 'ISSUING_CENTER', 'IS_DEFAULT'];
		if ($this->allowedShippingCenterField())
		{
			$checkboxFields[] = 'SHIPPING_CENTER';
		}
		if (in_array($fieldID, $checkboxFields))
		{
			return [
				'items' => [
					"Y" => Loc::getMessage('MAIN_YES'),
					"N" => Loc::getMessage('MAIN_NO'),
				]
			];
		}

		if ($fieldID === 'SITE_ID')
		{
			$listSite = [];
			$sitesQueryObject = \CSite::getList("sort", "asc", ["ACTIVE" => "Y"]);
			while ($site = $sitesQueryObject->fetch())
			{
				$listSite[$site["LID"]] = $site["NAME"]." [".$site["LID"]."]";
			}

			return ['items' => $listSite];
		}
	}

	public function getGridColumns()
	{
		$columns = [];
		$columns[] = [
			"id" => "ID",
			"name" => "ID",
			"sort" => "ID",
			"default" => true
		];
		$columns[] = [
			"id" => "SORT",
			"name" => Loc::getMessage("CSTORE_SORT"),
			"sort" => "SORT",
			"default" => true
		];
		$columns[] = [
			"id" => "TITLE",
			"name" => Loc::getMessage("TITLE"),
			"sort" => "TITLE",
			"default" => true,
			'width' => 175,
		];
		$columns[] = [
			"id" => "ACTIVE",
			"name" => Loc::getMessage("STORE_ACTIVE"),
			"sort" => "ACTIVE",
			"default" => true
		];
		$columns[] = [
			"id" => "IS_DEFAULT",
			"name" => Loc::getMessage("IS_DEFAULT"),
			"sort" => "IS_DEFAULT",
			"default" => true
		];
		$columns[] = [
			"id" => "ADDRESS",
			"name" => Loc::getMessage("ADDRESS"),
			"sort" => "",
			"default" => true
		];
		$columns[] = [
			"id" => "IMAGE_ID",
			"name" => Loc::getMessage("STORE_IMAGE"),
			"sort" => "",
			"default" => false
		];
		$columns[] = [
			"id" => "DESCRIPTION",
			"name" => Loc::getMessage("DESCRIPTION"),
			"sort" => "",
			"default" => true
		];
		$columns[] = [
			"id" => "GPS_N",
			"name" => Loc::getMessage("GPS_N"),
			"sort" => "GPS_N",
			"default" => false
		];
		$columns[] = [
			"id" => "GPS_S",
			"name" => Loc::getMessage("GPS_S"),
			"sort" => "GPS_S",
			"default" => false
		];
		$columns[] = [
			"id" => "PHONE",
			"name" => Loc::getMessage("PHONE"),
			"sort" => "",
			"default" => true
		];
		$columns[] = [
			"id" => "SCHEDULE",
			"name" => Loc::getMessage("SCHEDULE"),
			"sort" => "",
			"default" => true
		];
		$columns[] = [
			"id" => "DATE_MODIFY",
			"name" => Loc::getMessage("DATE_MODIFY"),
			"sort" => "DATE_MODIFY",
			"default" => true
		];
		$columns[] = [
			"id" => "MODIFIED_BY",
			"name" => Loc::getMessage("MODIFIED_BY"),
			"sort" => "MODIFIED_BY",
			"default" => true
		];
		$columns[] = [
			"id" => "DATE_CREATE",
			"name" => Loc::getMessage("DATE_CREATE"),
			"sort" => "DATE_CREATE",
			"default" => false
		];
		$columns[] = [
			"id" => "USER_ID",
			"name" => Loc::getMessage("USER_ID"),
			"sort" => "USER_ID",
			"default" => false
		];
		$columns[] = [
			"id" => "EMAIL",
			"name" => "E-mail",
			"sort" => "EMAIL",
			"default" => false
		];
		$columns[] = [
			"id" => "ISSUING_CENTER",
			"name" => Loc::getMessage("ISSUING_CENTER"),
			"sort" => "ISSUING_CENTER",
			"default" => false
		];
		if ($this->allowedShippingCenterField())
		{
			$columns[] = [
				"id" => "SHIPPING_CENTER",
				"name" => Loc::getMessage("SHIPPING_CENTER"),
				"sort" => "SHIPPING_CENTER",
				"default" => false
			];
		}
		$columns[] = [
			"id" => "SITE_ID",
			"name" => Loc::getMessage("STORE_SITE_ID"),
			"sort" => "SITE_ID",
			"default" => true
		];
		$columns[] = [
			"id" => "CODE",
			"name" => Loc::getMessage("STORE_CODE"),
			"sort" => "CODE",
			"default" => false
		];
		$columns[] = [
			"id" => "XML_ID",
			"name" => Loc::getMessage("STORE_XML_ID"),
			"sort" => "XML_ID",
			"default" => false
		];

		return $columns;
	}

	protected function allowedShippingCenterField(): bool
	{
		return \CCatalogStoreControlUtil::isAllowShowShippingCenter();
	}
}
