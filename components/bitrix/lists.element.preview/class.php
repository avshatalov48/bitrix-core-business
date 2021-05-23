<?

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class ListsElementPreviewComponent extends \CBitrixComponent
{
	protected function prepareData()
	{
		$elementIblock = CIBlock::GetArrayByID((int)$this->arParams["listId"]);
		$this->arResult['ENTITY_NAME'] = $elementIblock['ELEMENT_NAME'];
		$this->arResult["FIELDS"] = $this->getElementFields($this->arParams['listId'], $this->arParams['elementId']);
		foreach($this->arResult['FIELDS'] as $fieldId => &$field)
		{
			if($field['TYPE'] == 'NAME')
			{
				$this->arResult['ENTITY_TITLE'] = HtmlFilter::encode($field['VALUE']);
			}

			$field['HTML'] = \Bitrix\Lists\Field::renderField($field);

			if($field['SETTINGS']['SHOW_FIELD_PREVIEW'] !== 'Y')
			{
				unset($this->arResult['FIELDS'][$fieldId]);
				continue;
			}
		}
	}

	protected function getElementFields($iblockId, $elementId)
	{
		$totalResult = array();
		$list = new CList($iblockId);
		$listFields = $list->getFields();

		foreach ($listFields as $fieldId => $field)
		{
			$totalResult[$fieldId] = $field;
		}

		$elementQuery = CIBlockElement::getList(
			array(),
			array("IBLOCK_ID" => $iblockId, "=ID" => $elementId),
			false,
			false,
			array('*')
		);
		if(is_a($elementQuery, 'CIBlockResult'))
		{
			if ($elementObject = $elementQuery->getNextElement())
			{
				$elementNewData = $elementObject->getFields();
				if(is_array($elementNewData))
				{
					foreach($elementNewData as $fieldId => $fieldValue)
					{
						if(!$list->is_field($fieldId))
							continue;

						if(isset($totalResult[$fieldId]["NAME"]))
						{
							$totalResult[$fieldId]["VALUE"] = $elementNewData["~".$fieldId];
						}
					}
				}
			}
		}

		if ($elementObject)
		{
			$query = \CIblockElement::getPropertyValues($iblockId, array('ID' => $elementId));
			if($propertyValues = $query->fetch())
			{
				foreach($propertyValues as $id => $values)
				{
					if($id == "IBLOCK_ELEMENT_ID")
						continue;
					$fieldId = "PROPERTY_".$id;
					$totalResult[$fieldId]["VALUE"] = $values;
				}
			}
		}
		else
		{
			$totalResult = array();
		}

		return $totalResult;
	}

	public function executeComponent()
	{
		$this->prepareData();
		if(is_array($this->arResult['FIELDS']) && count($this->arResult['FIELDS']) > 0)
		{
			$this->includeComponentTemplate();
		}
	}
}