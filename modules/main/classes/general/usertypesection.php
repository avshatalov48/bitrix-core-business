<?php

use Bitrix\Iblock\UserField\Types\SectionType;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class CUserTypeIBlockSection
 * @deprecated deprecated since main 20.0.800
 */
class CUserTypeIBlockSection extends CUserTypeEnum
{
	public static function getUserTypeDescription()
	{
		if(self::isIblockIncluded())
		{
			return SectionType::getUserTypeDescription();
		}

		return [
			'USER_TYPE_ID' => 'iblock_section',
			'CLASS_NAME' => 'CUserTypeIBlockSection',
			'DESCRIPTION' => Loc::getMessage('USER_TYPE_IBSEC_DESCRIPTION'),
			'BASE_TYPE' => 'int',
			'VIEW_CALLBACK' => array(__CLASS__, 'GetPublicView'),
			'EDIT_CALLBACK' => array(__CLASS__, 'GetPublicEdit'),
		];
	}

	function prepareSettings($userField)
	{
		if(self::isIblockIncluded())
		{
			return SectionType::prepareSettings($userField);
		}

		$height = (int)$userField['SETTINGS']['LIST_HEIGHT'];

		$disp = $userField['SETTINGS']['DISPLAY'];
		if($disp != 'CHECKBOX' && $disp != 'LIST')
		{
			$disp = 'LIST';
		}

		$iblock_id = (int)$userField['SETTINGS']['IBLOCK_ID'];
		if($iblock_id <= 0)
		{
			$iblock_id = '';
		}

		$section_id = (int)$userField['SETTINGS']['DEFAULT_VALUE'];
		if($section_id <= 0)
		{
			$section_id = '';
		}

		$active_filter = ($userField['SETTINGS']['ACTIVE_FILTER'] === 'Y' ? 'Y' : 'N');

		return [
			'DISPLAY' => $disp,
			'LIST_HEIGHT' => ($height < 1 ? 1 : $height),
			'IBLOCK_ID' => $iblock_id,
			'DEFAULT_VALUE' => $section_id,
			'ACTIVE_FILTER' => $active_filter,
		];
	}

	function getSettingsHtml($userField, $additionalParameters, $varsFromForm)
	{
		if(self::isIblockIncluded())
		{
			return SectionType::getSettingsHtml($userField, $additionalParameters, $varsFromForm);
		}

		$result = '';

		if($varsFromForm)
		{
			$iblock_id = $GLOBALS[$additionalParameters['NAME']]['IBLOCK_ID'];
		}
		elseif(is_array($userField))
		{
			$iblock_id = $userField['SETTINGS']['IBLOCK_ID'];
		}
		else
		{
			$iblock_id = '';
		}

		$result .= '
			<tr>
				<td>' . Loc::getMessage('USER_TYPE_IBSEC_DISPLAY') . ':</td>
				<td>
					<input type="text" size="6" name="' . $additionalParameters['NAME'] . '[IBLOCK_ID]" value="' . htmlspecialcharsbx($value) . '">
				</td>
			</tr>
			';

		if($varsFromForm)
		{
			$ACTIVE_FILTER = ($GLOBALS[$additionalParameters['NAME']]['ACTIVE_FILTER'] === 'Y' ? 'Y' : 'N');
		}
		elseif(is_array($userField))
		{
			$ACTIVE_FILTER = $userField['SETTINGS']['ACTIVE_FILTER'] === 'Y' ? 'Y' : 'N';
		}
		else
		{
			$ACTIVE_FILTER = 'N';
		}

		if($varsFromForm)
		{
			$value = $GLOBALS[$additionalParameters['NAME']]['DEFAULT_VALUE'];
		}
		elseif(is_array($userField))
		{
			$value = $userField['SETTINGS']['DEFAULT_VALUE'];
		}
		else
		{
			$value = '';
		}

		$result .= '
			<tr>
				<td>' . Loc::getMessage('USER_TYPE_IBSEC_DEFAULT_VALUE') . ':</td>
				<td>
					<input type="text" size="8" name="' . $additionalParameters["NAME"] . '[DEFAULT_VALUE]" value="' . htmlspecialcharsbx($value) . '">
				</td>
			</tr>
			';

		if($varsFromForm)
		{
			$value = $GLOBALS[$additionalParameters['NAME']]['DISPLAY'];
		}
		elseif(is_array($userField))
		{
			$value = $userField['SETTINGS']['DISPLAY'];
		}
		else
		{
			$value = 'LIST';
		}
		$result .= '
		<tr>
			<td class="adm-detail-valign-top">' . Loc::getMessage('USER_TYPE_ENUM_DISPLAY') . ':</td>
			<td>
				<label><input type="radio" name="' . $additionalParameters['NAME'] . '[DISPLAY]" value="LIST" ' . ("LIST" == $value ? 'checked="checked"' : '') . '>' . Loc::getMessage('USER_TYPE_IBSEC_LIST') . '</label><br>
				<label><input type="radio" name="' . $additionalParameters['NAME'] . '[DISPLAY]" value="CHECKBOX" ' . ("CHECKBOX" == $value ? 'checked="checked"' : '') . '>' . Loc::getMessage('USER_TYPE_IBSEC_CHECKBOX') . '</label><br>
			</td>
		</tr>
		';

		if($varsFromForm)
		{
			$value = (int)$GLOBALS[$additionalParameters['NAME']]['LIST_HEIGHT'];
		}
		elseif(is_array($userField))
		{
			$value = (int)$userField['SETTINGS']['LIST_HEIGHT'];
		}
		else
		{
			$value = 5;
		}

		$result .= '
		<tr>
			<td>' . Loc::getMessage('USER_TYPE_IBSEC_LIST_HEIGHT') . ':</td>
			<td>
				<input type="text" name="' . $additionalParameters['NAME'] . '[LIST_HEIGHT]" size="10" value="' . $value . '">
			</td>
		</tr>
		';

		$result .= '
		<tr>
			<td>' . Loc::getMessage('USER_TYPE_IBSEC_ACTIVE_FILTER') . ':</td>
			<td>
				<input type="checkbox" name="' . $additionalParameters['NAME'] . '[ACTIVE_FILTER]" value="Y" ' . ($ACTIVE_FILTER === 'Y' ? 'checked="checked"' : '') . '>
			</td>
		</tr>
		';

		return $result;
	}

	function checkFields($userField, $value)
	{
		if(self::isIblockIncluded())
		{
			return SectionType::checkFields($userField, $value);
		}
		return [];
	}

	public static function getList($userField)
	{
		if(self::isIblockIncluded())
		{
			return SectionType::getList($userField);
		}

		return false;
	}

	protected static function getEnumList(&$userField, $additionalParameters = array())
	{
		if(self::isIblockIncluded())
		{
			SectionType::getEnumList($userField, $additionalParameters);
		}
		return false;
	}

	function onSearchIndex($userField)
	{
		if(self::isIblockIncluded())
		{
			return SectionType::onSearchIndex($userField);
		}

		return '';
	}

	public static function isIblockIncluded(): bool
	{
		return Loader::includeModule('iblock');
	}
}

class CIBlockSectionEnum extends CDBResult
{
	public static function getTreeList($iblockId, $activeFilter='N')
	{
		$result = false;

		if(CModule::IncludeModule('iblock'))
		{
			$filter = ['IBLOCK_ID'=>$iblockId];
			if($activeFilter === 'Y')
			{
				$filter['GLOBAL_ACTIVE'] = 'Y';
			}

			$result = CIBlockSection::GetList(
				['left_margin'=>'asc'],
				$filter,
				false,
				['ID', 'DEPTH_LEVEL', 'NAME', 'SORT', 'XML_ID', 'ACTIVE', 'IBLOCK_SECTION_ID']
			);

			if($result)
			{
				$result = new CIBlockSectionEnum($result);
			}
		}

		return $result;
	}

	function getNext($textHtmlAuto=true, $useTilda=true)
	{
		$result = parent::getNext($textHtmlAuto, $useTilda);

		if($result)
		{
			$result['VALUE'] = str_repeat(' . ', $result['DEPTH_LEVEL']) . $result['NAME'];
		}

		return $result;
	}
}