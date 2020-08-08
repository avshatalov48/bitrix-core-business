<?
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CAllCatalogVat
{
	/**
	 * @deprecated deprecated since catalog 12.5.6
	 */
	public static function err_mess()
	{
		return "<br>Module: catalog<br>Class: CCatalogVat<br>File: ".__FILE__;
	}

	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $APPLICATION;
		$arMsg = array();
		$boolResult = true;

		$ACTION = mb_strtoupper($ACTION);
		if ('INSERT' == $ACTION)
			$ACTION = 'ADD';

		if (isset($arFields['SORT']))
		{
			$arFields['C_SORT'] = $arFields['SORT'];
			unset($arFields['SORT']);
		}

		if (array_key_exists('ID', $arFields))
		{
			unset($arFields['ID']);
		}

		if ('ADD' == $ACTION)
		{
			if (!isset($arFields['NAME']))
			{
				$boolResult = false;
				$arMsg[] = array('id' => 'NAME', "text" => Loc::getMessage('CVAT_ERROR_BAD_NAME'));
			}
			if (!isset($arFields['RATE']))
			{
				$boolResult = false;
				$arMsg[] = array('id' => 'RATE', "text" => Loc::getMessage('CVAT_ERROR_BAD_RATE'));
			}
			if (!isset($arFields['C_SORT']))
			{
				$arFields['C_SORT'] = 100;
			}
			if (!isset($arFields['ACTIVE']))
			{
				$arFields['ACTIVE'] = 'Y';
			}
		}

		if ($boolResult)
		{
			if (array_key_exists('NAME', $arFields))
			{
				$arFields['NAME'] = trim($arFields['NAME']);
				if ('' == $arFields['NAME'])
				{
					$boolResult = false;
					$arMsg[] = array('id' => 'NAME', "text" => Loc::getMessage('CVAT_ERROR_BAD_NAME'));
				}
			}
			if (array_key_exists('RATE', $arFields))
			{
				$arFields['RATE'] = doubleval($arFields['RATE']);
				if (0 > $arFields['RATE'] || 100 < $arFields['RATE'])
				{
					$boolResult = false;
					$arMsg[] = array('id' => 'RATE', "text" => Loc::getMessage('CVAT_ERROR_BAD_RATE'));
				}
			}
			if (array_key_exists('C_SORT', $arFields))
			{
				$arFields['C_SORT'] = intval($arFields['C_SORT']);
				if (0 >= $arFields['C_SORT'])
				{
					$arFields['C_SORT'] = 100;
				}
			}
			if (array_key_exists('ACTIVE', $arFields))
			{
				$arFields['ACTIVE'] = ($arFields['ACTIVE'] == 'Y' ? 'Y' : 'N');
			}
		}

		if (!$boolResult)
		{
			$obError = new CAdminException($arMsg);
			$APPLICATION->ResetException();
			$APPLICATION->ThrowException($obError);
		}
		return $boolResult;
	}

	/**
	 * @deprecated deprecated since catalog 20.0.200
	 * @see \Bitrix\Catalog\VatTable::getById()
	 *
	 * @param int $ID
	 * @return CDBResult|false
	 */
	public static function GetByID($ID)
	{
		return CCatalogVat::GetListEx(array(), array('ID' => $ID));
	}

	/**
	 * @deprecated deprecated since catalog 12.5.6
	 * @see CCatalogVat::GetListEx()
	 *
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param array $arFields
	 * @return CDBResult|false
	 */
	public static function GetList($arOrder = array('SORT' => 'ASC'), $arFilter = array(), $arFields = array())
	{
		if (is_array($arFilter))
		{
			if (array_key_exists('NAME', $arFilter) && array_key_exists('NAME_EXACT_MATCH', $arFilter))
			{
				if ('Y' == $arFilter['NAME_EXACT_MATCH'])
				{
					$arFilter['=NAME'] = $arFilter['NAME'];
					unset($arFilter['NAME']);
				}
				unset($arFilter['NAME_EXACT_MATCH']);
			}
		}
		return CCatalogVat::GetListEx($arOrder, $arFilter, false, false, $arFields);
	}

	/**
	 * @deprecated deprecated since catalog 12.5.6
	 * @see CCatalogVat::Add()
	 * @see CCatalogVat::Update()
	 *
	 * @param array $arFields
	 * @return int|false
	*/
	public static function Set($arFields)
	{
		if (isset($arFields['ID']) && intval($arFields['ID']) > 0)
		{
			return CCatalogVat::Update($arFields['ID'], $arFields);
		}
		else
		{
			return CCatalogVat::Add($arFields);
		}
	}

	public static function GetByProductID($PRODUCT_ID)
	{

	}
}