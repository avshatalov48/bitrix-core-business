<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
define('STOP_STATISTICS', true);
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define("PUBLIC_AJAX_MODE", true);
define("NOT_CHECK_PERMISSIONS", true);

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
Loc::loadMessages(__FILE__);
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

$defaultValues = array(
	'UF_XML_ID' => '',
	'ID' => 0,
	'UF_NAME' => '',
	'UF_LINK' => '',
	'UF_SORT' => 100,
	'UF_FILE' => 0,
	'MULTIPLE' => 'N',
	'UF_DEF' => 'N',
	'UF_DESCRIPTION' => '',
	'UF_FULL_DESCRIPTION' => ''
);

if ($USER->IsAuthorized() && check_bitrix_sessid() && isset($_REQUEST['IBLOCK_ID']))
{
	if (!Loader::includeModule('highloadblock') || !Loader::includeModule('iblock'))
	{
		echo Bitrix\Main\Web\Json::encode(array('ERROR' => 'SS_MODULE_NOT_INSTALLED'));
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
		die();
	}

	$iblockID = (int)$_REQUEST['IBLOCK_ID'];
	if ($iblockID < 0)
	{
		echo Bitrix\Main\Web\Json::encode(array('ERROR' => 'SS_IBLOCK_ID_ABSENT'));
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
		die();
	}
	elseif ($iblockID === 0)
	{
		if (!$USER->IsAdmin())
		{
			echo Bitrix\Main\Web\Json::encode(array('ERROR' => 'SS_NO_ADMIN'));
			require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
			die();
		}
	}
	else
	{
		$rsIBlocks = CIBlock::GetList(
			array(),
			array(
				'ID' => $iblockID,
				'CHECK_PERMISSIONS' => 'N'
			)
		);
		$arIBlock = $rsIBlocks->Fetch();
		if ($arIBlock)
		{
			if (!CIBlockRights::UserHasRightTo($iblockID, $iblockID, "iblock_edit"))
			{
				echo Bitrix\Main\Web\Json::encode(array('ERROR' => 'SS_ACCESS_DENIED'));
				require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
				die();
			}
		}
		else
		{
			echo Bitrix\Main\Web\Json::encode(array('ERROR' => 'SS_IBLOCK_ABSENT'));
			require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
			die();
		}
	}

	CUtil::JSPostUnescape();

	$multiple = 'N';
	if (isset($_REQUEST['multiple']) && $_REQUEST['multiple'] === 'Y')
		$multiple = 'Y';
	$defaultValues['MULTIPLE'] = $multiple;

	function addTableXmlIDCell($intPropID, $arPropInfo)
	{
		return '<input type="text" onchange="getDirectoryTableHead(this);" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][UF_XML_ID]" id="PROPERTY_VALUES_XML_'.$intPropID.'" value="'.htmlspecialcharsbx($arPropInfo['UF_XML_ID']).'" size="20" style="width:90%">';
	}

	function addTableIdCell($intPropID, $arPropInfo)
	{
		return '<input type="hidden" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][ID]" id="PROPERTY_VALUES_ID_'.$intPropID.'" value="'.htmlspecialcharsbx($arPropInfo['ID']).'">';
	}

	function addTableNameCell($intPropID, $arPropInfo)
	{
		return '<input type="text" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][UF_NAME]" id="PROPERTY_VALUES_NAME_'.$intPropID.'" value="'.htmlspecialcharsbx($arPropInfo['UF_NAME']).'" size="20" style="width:90%">';
	}

	function addTableLinkCell($intPropID, $arPropInfo)
	{
		return '<input type="text" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][UF_LINK]" id="PROPERTY_VALUES_LINK_'.$intPropID.'" value="'.htmlspecialcharsbx($arPropInfo['UF_LINK']).'" size="35" style="width:90%">';
	}

	function addTableSortCell($intPropID, $arPropInfo)
	{
		$sort = (isset($arPropInfo['UF_SORT']) && intval($arPropInfo['UF_SORT']) > 0) ? intval($arPropInfo['UF_SORT']) : 100;
		return '<input type="text" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][UF_SORT]" id="PROPERTY_VALUES_SORT_'.$intPropID.'" value="'.$sort.'" size="5" maxlength="11">';
	}

	function addTableFileCell($intPropID, $arPropInfo)
	{
		static $maxImageSize = null;
		static $filemanIncluded = null;
		if (null === $maxImageSize)
		{
			$maxImageSize = array(
				"W" => COption::GetOptionString("iblock", "list_image_size"),
				"H" => COption::GetOptionString("iblock", "list_image_size"),
			);
		}

		$arPropInfo["UF_FILE"] = intval($arPropInfo["UF_FILE"]);
		if ($filemanIncluded === null)
		{
			$filemanIncluded = Loader::includeModule('fileman');
		}
		if (!$filemanIncluded)
			return '';

		$strShowFile = '';
		if (0 < $arPropInfo["UF_FILE"])
		{
			$strShowFile = CFile::ShowFile(
				$arPropInfo["UF_FILE"],
				0,
				$maxImageSize['W'],
				$maxImageSize['H'],
				false
			);
			if ('' !== $strShowFile)
				$strShowFile .= '<br>';


		}

		return $strShowFile.CFile::InputFile(
			"PROPERTY_DIRECTORY_VALUES[$intPropID][FILE]",
			20,
			$arPropInfo["UF_FILE"],
			false, 0, "IMAGE", "", 0, "class=typeinput", "", true, false);
	}

	function addTableDefCell($intPropID, $arPropInfo)
	{
		return '<input type="'.('Y' == $arPropInfo['MULTIPLE'] ? 'checkbox' : 'radio').'" name="PROPERTY_VALUES_DEF'.('Y' == $arPropInfo['MULTIPLE'] ? '[]' : '').'" id="PROPERTY_VALUES_DEF_'.$intPropID.'" value="'.$intPropID.'" '.('1' == $arPropInfo['UF_DEF'] ? 'checked="checked"' : '').'>';
	}

	function addTableDescriptionCell($intPropID, $arPropInfo)
	{
		return '<input type="text" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][UF_DESCRIPTION]" id="PROPERTY_VALUES_DESCRIPTION_'.$intPropID.'" value="'.(is_string($arPropInfo['UF_DESCRIPTION']) ? htmlspecialcharsbx($arPropInfo['UF_DESCRIPTION']) : '').'" style="width:90%">';
	}

	function addTableFullDescriptionCell($intPropID, $arPropInfo)
	{
		return '<input type="text" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][UF_FULL_DESCRIPTION]" id="PROPERTY_VALUES_FULL_DESCRIPTION_'.$intPropID.'" value="'.(is_string($arPropInfo['UF_FULL_DESCRIPTION']) ? htmlspecialcharsbx($arPropInfo['UF_FULL_DESCRIPTION']) : '').'" style="width:90%">';
	}

	function addTableDelField($intPropID)
	{
		return '<div style="background: url(/bitrix/panel/main/images/bx-admin-sprite-small-1.png) no-repeat 6px -2446px; display: inline-block; cursor: pointer; height: 23px; margin:0; opacity: 0.7; vertical-align: middle; width: 23px;" onclick="this.parentNode.parentNode.style.display = \'none\'; BX(\'PROPERTY_VALUES_DELETE_'.$intPropID.'\').value = \'Y\'"></div>
			<input type="hidden" name="PROPERTY_DIRECTORY_VALUES['.$intPropID.'][UF_DELETE]" id="PROPERTY_VALUES_DELETE_'.$intPropID.'" value="N">';
	}

	function addTableRow($intPropID, $arPropInfo, $fields, $json = false)
	{
		if (empty($fields))
			return '';
		if ($json)
		{
			$result = array();

			$result[] = array(
				'style' => array(
					'verticalAlign' => 'top'
				),
				'html' => addTableDelField($intPropID).addTableIdCell($intPropID, $arPropInfo)
			);
			if (isset($fields['UF_NAME']))
			{
				$result[] = array(
					'style' => array(
						'verticalAlign' => 'top'
					),
					'html' => addTableNameCell($intPropID, $arPropInfo)
				);
			}
			if (isset($fields['UF_SORT']))
			{
				$result[] = array(
					'style' => array(
						'verticalAlign' => 'top'
					),
					'html' => addTableSortCell($intPropID, $arPropInfo)
				);
			}
			if (isset($fields['UF_XML_ID']))
			{
				$result[] = array(
					'style' => array(
						'verticalAlign' => 'top'
					),
					'html' => addTableXmlIDCell($intPropID, $arPropInfo)
				);
			}
			if (isset($fields['UF_FILE']))
			{
				$result[] = array(
					'style' => array(
						'verticalAlign' => 'top'
					),
					'html' => addTableFileCell($intPropID, $arPropInfo)
				);
			}
			if (isset($fields['UF_LINK']))
			{
				$result[] = array(
					'style' => array(
						'verticalAlign' => 'top'
					),
					'html' => addTableLinkCell($intPropID, $arPropInfo)
				);
			}
			if (isset($fields['UF_DEF']))
			{
				$result[] = array(
					'style' => array(
						'verticalAlign' => 'top',
						'textAlign' => 'center'
					),
					'html' => addTableDefCell($intPropID, $arPropInfo)
				);
			}
			if (isset($fields['UF_DESCRIPTION']))
			{
				$result[] = array(
					'style' => array(
						'verticalAlign' => 'top'
					),
					'html' => addTableDescriptionCell($intPropID, $arPropInfo)
				);
			}
			if (isset($fields['UF_FULL_DESCRIPTION']))
			{
				$result[] = array(
					'style' => array(
						'verticalAlign' => 'top'
					),
					'html' => addTableFullDescriptionCell($intPropID, $arPropInfo)
				);
			}
		}
		else
		{
			$result = '<tr id="hlbl_property_tr_'.$intPropID.'">';
			$result .= '<td style="vertical-align: top;">'.addTableDelField($intPropID).addTableIdCell($intPropID, $arPropInfo).'</td>';
			if (isset($fields['UF_NAME']))
			{
				$result .= '<td style="vertical-align: top;">'.addTableNameCell($intPropID, $arPropInfo).'</td>';
			}
			if (isset($fields['UF_SORT']))
			{
				$result .= '<td style="vertical-align: top;">'.addTableSortCell($intPropID, $arPropInfo).'</td>';
			}
			if (isset($fields['UF_XML_ID']))
			{
				$result .= '<td style="vertical-align: top;">'.addTableXmlIDCell($intPropID, $arPropInfo).'</td>';
			}
			if (isset($fields['UF_FILE']))
			{
				$result .= '<td style="vertical-align: top;">'.addTableFileCell($intPropID, $arPropInfo).'</td>';
			}
			if (isset($fields['UF_LINK']))
			{
				$result .= '<td style="vertical-align: top;">'.addTableLinkCell($intPropID, $arPropInfo).'</td>';
			}
			if (isset($fields['UF_DEF']))
			{
				$result .= '<td style="vertical-align: top; text-align:center;">'.addTableDefCell($intPropID, $arPropInfo).'</td>';
			}
			if (isset($fields['UF_DESCRIPTION']))
			{
				$result .= '<td style="vertical-align: top;">'.addTableDescriptionCell($intPropID, $arPropInfo).'</td>';
			}
			if (isset($fields['UF_FULL_DESCRIPTION']))
			{
				$result .= '<td style="vertical-align: top;">'.addTableFullDescriptionCell($intPropID, $arPropInfo).'</td>';
			}
			$result .= '</tr>';
		}
		return $result;
	}

	function addHeadRow($fields, &$colCount)
	{
		$result = '<tr class="heading"><td></td>';
		if (isset($fields['UF_NAME']))
		{
			$result .= '<td>'.Loc::getMessage('HIBLOCK_PROP_DIRECTORY_NAME').'</td>';
			$colCount++;
		}
		if (isset($fields['UF_SORT']))
		{
			$result .= '<td>'.Loc::getMessage('HIBLOCK_PROP_DIRECTORY_SORT').'</td>';
			$colCount++;
		}
		if (isset($fields['UF_XML_ID']))
		{
			$result .= '<td>'.Loc::getMessage('HIBLOCK_PROP_DIRECTORY_XML_ID').'</td>';
			$colCount++;
		}
		if (isset($fields['UF_FILE']))
		{
			$result .= '<td>'.Loc::getMessage('HIBLOCK_PROP_DIRECTORY_FILE').'</td>';
			$colCount++;
		}
		if (isset($fields['UF_LINK']))
		{
			$result .= '<td>'.Loc::getMessage('HIBLOCK_PROP_DIRECTORY_LINK').'</td>';
			$colCount++;
		}
		if (isset($fields['UF_DEF']))
		{
			$result .= '<td>'.Loc::getMessage('HIBLOCK_PROP_DIRECTORY_DEF').'</td>';
			$colCount++;
		}
		if (isset($fields['UF_DESCRIPTION']))
		{
			$result .= '<td>'.Loc::getMessage('HIBLOCK_PROP_DIRECTORY_DECSRIPTION').'</td>';
			$colCount++;
		}
		if (isset($fields['UF_FULL_DESCRIPTION']))
		{
			$result .= '<td>'.Loc::getMessage('HIBLOCK_PROP_DIRECTORY_FULL_DESCRIPTION').'</td>';
			$colCount++;
		}
		$result .= '</tr>';
		return $result;
	}

	function addEmptyDefaultRow($property, $fields)
	{
		if (empty($fields) || !isset($fields['UF_DEF']))
			return '';

		if ($property['MULTIPLE'] != 'N')
			return '';

		$result = '<tr id="hlbl_property_tr_empty">';

		$leftSide = [
			'UF_NAME' => true,
			'UF_SORT' => true,
			'UF_XML_ID' => true,
			'UF_FILE' => true,
			'UF_LINK' => true
		];

		$rightSide = [
			'UF_DESCRIPTION' => true,
			'UF_FULL_DESCRIPTION' => true
		];

		$countLeft = count(array_intersect_key($fields, $leftSide)) + 1;
		$result .= '<td colspan="'.$countLeft.'" style="text-align: center;">'.Loc::getMessage('HIBLOCK_PROP_DIRECTORY_EMPTY_DEFAULT_VALUE').'</td>';

		$result .= '<td style="text-align:center;">'.
		'<input type="radio" name="PROPERTY_VALUES_DEF" id="PROPERTY_VALUES_DEF_EMPTY" value="-1" '.($property['UF_DEF'] == '1' ? 'checked="checked"' : '').'>'.
		'</td>';

		$countRight = count(array_intersect_key($fields, $rightSide));
		if ($countRight > 0)
			$result .= '<td colspan="'.$countRight.'">&nbsp;</td>';

		$result .= '</tr>';

		return $result;
	}

	$rowNumber = (int)(isset($_REQUEST['rowNumber']) ? $_REQUEST['rowNumber'] : 0);
	$currentRowNumber = 0;
	$colCount = 1;
	$hlBlockID = (string)(isset($_REQUEST['hlBlock']) ? $_REQUEST['hlBlock'] : '');
	$result = '';
	$entityDataClass = null;

	$hlblockFields = $defaultValues;
	$orderFields = array();
	if ($hlBlockID !== '' && $hlBlockID !== '-1')
	{
		$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList(array("filter" => array("=TABLE_NAME" => $hlBlockID)))->fetch();
		$entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
		$entityDataClass = $entity->getDataClass();
		$hlblockFields = $entityDataClass::getEntity()->getFields();
		if (isset($hlblockFields['UF_SORT']))
			$orderFields['UF_SORT'] = 'ASC';
		if (isset($hlblockFields['UF_NAME']))
			$orderFields['UF_NAME'] = 'ASC';
	}

	if (isset($_REQUEST['addEmptyRow']) && $_REQUEST['addEmptyRow'] === 'Y')
	{
		$APPLICATION->RestartBuffer();
		echo Bitrix\Main\Web\Json::encode(addTableRow('n'.$rowNumber, $defaultValues, $hlblockFields, true));
	}
	else
	{
		if ($hlBlockID !== '')
		{
			if (isset($_REQUEST['getTitle']) && $_REQUEST['getTitle'] === 'Y')
			{
				$result .= addHeadRow($hlblockFields, $colCount);
			}

			$existDefault = false;
			$rowsList = '';
			$rsData = $entityDataClass::getList(array(
				'order' => $orderFields
			));
			while ($arData = $rsData->fetch())
			{
				if (isset($hlblockFields['UF_DEF']) && $arData['UF_DEF'] == '1')
					$existDefault = true;

				$arData['MULTIPLE'] = $multiple;
				$rowsList .= addTableRow($arData['ID'], $arData, $hlblockFields, false);
			}
			unset($arData, $rsData);

			$result .= addEmptyDefaultRow(
				['MULTIPLE' => $multiple, 'UF_DEF' => ($existDefault ? '0' : '1')],
				$hlblockFields
			);

			$result .= $rowsList;

			if ($rowsList == '')
				$result .= addTableRow('n'.$rowNumber, $defaultValues, $hlblockFields, false);

			unset($rowsList);
		}

		$result .= '<tr style="display: none;"><td colspan="'.$colCount.'"><input type="hidden" id="IB_MAX_ROWS_COUNT" value="'.$rowNumber.'"></td></tr>';
		echo $result;
	}
}
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');