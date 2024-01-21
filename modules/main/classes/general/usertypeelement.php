<?php

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\UserField\Types\ElementType;

Loader::includeModule('iblock');

/**
 * Class CUserTypeIBlockElement
 * @deprecated deprecated since main 20.0.800
 * @see ElementType
 */
class CUserTypeIBlockElement extends CUserTypeEnum
{
	private static $iblockIncluded = null;

	public static function getUserTypeDescription()
	{
		if(self::isIblockIncluded())
		{
			return ElementType::getUserTypeDescription();
		}

		return [
			'USER_TYPE_ID' => 'iblock_element',
			'CLASS_NAME' => 'CUserTypeIBlockElement',
			'DESCRIPTION' => Loc::getMessage('USER_TYPE_IBEL_DESCRIPTION'),
			'BASE_TYPE' => 'int',
			'VIEW_CALLBACK' => array(__CLASS__, 'GetPublicView'),
			'EDIT_CALLBACK' => array(__CLASS__, 'GetPublicEdit'),
		];
	}

	function prepareSettings($userField)
	{
		if(self::isIblockIncluded())
		{
			return ElementType::prepareSettings($userField);
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

		$element_id = (int)$userField['SETTINGS']['DEFAULT_VALUE'];
		if($element_id <= 0)
		{
			$element_id = '';
		}

		$active_filter = ($userField['SETTINGS']['ACTIVE_FILTER'] === 'Y' ? 'Y' : 'N');

		return [
			'DISPLAY' => $disp,
			'LIST_HEIGHT' => ($height < 1 ? 1 : $height),
			'IBLOCK_ID' => $iblock_id,
			'DEFAULT_VALUE' => $element_id,
			'ACTIVE_FILTER' => $active_filter,
		];
	}

	function getSettingsHtml($userField, $additionalParameters, $varsFromForm)
	{
		if(self::isIblockIncluded())
		{
			return ElementType::getSettingsHtml($userField, $additionalParameters, $varsFromForm);
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
				<td>' . Loc::getMessage('USER_TYPE_IBEL_DISPLAY') . ':</td>
				<td>
					<input type="text" size="6" name="' . $additionalParameters['NAME'] . '[IBLOCK_ID]" value="' . htmlspecialcharsbx($iblock_id) . '">
				</td>
			</tr>
			';

		if($varsFromForm)
		{
			$ACTIVE_FILTER = ($GLOBALS[$additionalParameters['NAME']]['ACTIVE_FILTER'] === 'Y' ? 'Y' : 'N');
		}
		elseif(is_array($userField))
		{
			$ACTIVE_FILTER = ($userField['SETTINGS']['ACTIVE_FILTER'] === 'Y' ? 'Y' : 'N');
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
				<td>' . Loc::getMessage('USER_TYPE_IBEL_DEFAULT_VALUE') . ':</td>
				<td>
					<input type="text" size="8" name="' . $additionalParameters['NAME'] . '[DEFAULT_VALUE]" value="' . htmlspecialcharsbx($value) . '">
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
				<label><input type="radio" name="' . $additionalParameters['NAME'] . '[DISPLAY]" value="LIST" ' . ("LIST" == $value ? 'checked="checked"' : '') . '>' . Loc::getMessage('USER_TYPE_IBEL_LIST') . '</label><br>
				<label><input type="radio" name="' . $additionalParameters['NAME'] . '[DISPLAY]" value="CHECKBOX" ' . ("CHECKBOX" == $value ? 'checked="checked"' : '') . '>' . Loc::getMessage('USER_TYPE_IBEL_CHECKBOX') . '</label><br>
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
			<td>' . Loc::getMessage('USER_TYPE_IBEL_LIST_HEIGHT') . ':</td>
			<td>
				<input type="text" name="' . $additionalParameters['NAME'] . '[LIST_HEIGHT]" size="10" value="' . $value . '">
			</td>
		</tr>
		';

		$result .= '
		<tr>
			<td>' . Loc::getMessage('USER_TYPE_IBEL_ACTIVE_FILTER') . ':</td>
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
			return ElementType::checkFields($userField, $value);
		}
		return [];
	}

	public static function getList($userField)
	{
		if(self::isIblockIncluded())
		{
			return ElementType::getList($userField);
		}

		return false;
	}

	protected static function getEnumList(&$userField, $additionalParameters = array())
	{
		if(self::isIblockIncluded())
		{
			ElementType::getEnumList($userField, $additionalParameters);
		}

		return;
	}

	function onSearchIndex($userField)
	{
		if(self::isIblockIncluded())
		{
			return ElementType::onSearchIndex($userField);
		}

		return '';
	}

	public static function isIblockIncluded(): bool
	{
		return Loader::includeModule('iblock');
	}
}

class CIBlockElementEnum extends CDBResult
{
	/**
	 * @deprecated
	 */
	public static function getTreeList($iblockId, $activeFilter = 'N')
	{
		$result = false;

		if($iblockId > 0 && Loader::includeModule('iblock'))
		{
			$filter = ['IBLOCK_ID' => $iblockId];
			if($activeFilter === 'Y')
			{
				$filter['ACTIVE'] = 'Y';
			}

			$result = CIBlockElement::GetList(
				['NAME' => 'ASC', 'ID' => 'ASC'],
				$filter,
				false,
				false,
				['ID', 'NAME']
			);

			if($result)
			{
				$result = new self($result);
			}
		}

		return $result;
	}

	function getNext($textHtmlAuto = true, $useTilda = true)
	{
		$result = parent::getNext($textHtmlAuto, $useTilda);

		if($result)
		{
			$result['VALUE'] = $result['NAME'];
		}

		return $result;
	}
}

function getIblockElementLinkById($id)
{
	if(Loader::includeModule('iblock'))
	{
		static $iblockElementUrl = [];

		if(isset($iblockElementUrl[$id]))
		{
			return $iblockElementUrl[$id];
		}

		$elements = CIBlockElement::getList([], ['=ID' => $id], false, false, ['DETAIL_PAGE_URL']);
		$ar = $elements->GetNext();

		return ($iblockElementUrl[$id] = $ar['DETAIL_PAGE_URL']);
	}

	return false;
}