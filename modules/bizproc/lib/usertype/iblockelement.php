<?php
namespace Bitrix\Bizproc\UserType;

use Bitrix\Main,
	Bitrix\Bizproc\FieldType,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class IblockElement extends UserFieldBase
{
	/**
	 * @param FieldType $fieldType
	 * @param string $callbackFunctionName
	 * @param mixed $value
	 * @return string
	 */
	public static function renderControlOptions(FieldType $fieldType, $callbackFunctionName, $value)
	{
		if (is_array($value))
		{
			reset($value);
			$valueTmp = (int) current($value);
		}
		else
		{
			$valueTmp = (int) $value;
		}

		$iblockId = 0;
		if ($valueTmp > 0)
		{
			$idKey = ($fieldType->getType() === 'UF:iblock_section') ? 'SECTION_ID' : 'ID';
			$elementIterator = \CIBlockElement::getList([], [$idKey => $valueTmp], false, false, array('ID', 'IBLOCK_ID'));
			if ($element = $elementIterator->fetch())
			{
				$iblockId = $element['IBLOCK_ID'];
			}
		}

		if ($iblockId <= 0 && (int) $fieldType->getOptions() > 0)
		{
			$iblockId = (int) $fieldType->getOptions();
		}

		$defaultIBlockId = 0;

		$result = '<select id="WFSFormOptionsX" onchange="'
			.Main\Text\HtmlFilter::encode($callbackFunctionName).'(this.options[this.selectedIndex].value)">';

		$iblockTypeIterator = \CIBlockParameters::getIBlockTypes();
		foreach ($iblockTypeIterator as $iblockTypeId => $iblockTypeName)
		{
			$result .= '<optgroup label="'.Main\Text\HtmlFilter::encode($iblockTypeName).'">';

			$iblockIterator = \CIBlock::getList(['SORT' => 'ASC'], ['TYPE' => $iblockTypeId, 'ACTIVE' => 'Y']);
			while ($iblock = $iblockIterator->fetch())
			{
				$result .= '<option value="'.$iblock['ID'].'"'.(($iblock['ID'] == $iblockId) ? ' selected' : '').'>'
					.Main\Text\HtmlFilter::encode($iblock['NAME']).'</option>';
				if (($defaultIBlockId <= 0) || ($iblock['ID'] == $iblockId))
					$defaultIBlockId = $iblock['ID'];
			}

			$result .= '</optgroup>';
		}
		$result .= '</select><!--__defaultOptionsValue:'.$defaultIBlockId.'--><!--__modifyOptionsPromt:'
			.Loc::getMessage('BP_FIELDTYPE_UF_INFOBLOCK').'-->';
		$fieldType->setOptions($defaultIBlockId);

		return $result;
	}
}