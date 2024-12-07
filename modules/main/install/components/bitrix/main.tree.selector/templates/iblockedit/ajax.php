<?php

define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);
define('NO_AGENT_CHECK', true);

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

if (isset($_REQUEST['admin']) && is_string($_REQUEST['admin']) && $_REQUEST['admin'] == 'Y')
	define('ADMIN_SECTION', true);
if (isset($_REQUEST['site']) && !empty($_REQUEST['site']))
{
	if (!is_string($_REQUEST['site']))
		die();
	if (preg_match('/^[a-z0-9_]{2}$/i', $_REQUEST['site']) === 1)
		define('SITE_ID', $_REQUEST['site']);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
Loc::loadMessages(__FILE__);

if(!CModule::IncludeModule('iblock'))
{
	ShowError(Loc::getMessage("BT_COMP_MTS_AJAX_ERR_MODULE_ABSENT"));
	die();
}

$iblock_id = intval($_REQUEST["IBLOCK_ID"]);

if (!CIBlockRights::UserHasRightTo($iblock_id, $iblock_id, "iblock_admin_display"))
{
	ShowError(Loc::getMessage('BT_COMP_MTS_AJAX_ERR_IBLOCK_ACCESS_DENIED'));
	die();
}

$arIBlock = CIBlock::GetArrayByID($iblock_id);

$strBanSym = trim($_REQUEST['BAN_SYM']);
$arBanSym = str_split($strBanSym,1);
$strRepSym = trim($_REQUEST['REP_SYM']);
$arRepSym = array_fill(0,sizeof($arBanSym),$strRepSym);

if (isset($_REQUEST['MODE']) && $_REQUEST['MODE'] == 'section')
{
	$arResult = array();
	$SECTION_ID = intval($_REQUEST['SECTION_ID']);

	$rsElements = CIBlockElement::GetList(
		array('ID' => 'ASC'),
		array(
			'IBLOCK_ID' => $arIBlock["ID"],
			'SECTION_ID' => $SECTION_ID,
			'CHECK_PERMISSIONS' => 'N',
		),
		false,
		false,
		array("ID", "NAME", "IBLOCK_SECTION_ID")
	);
	while($arElement = $rsElements->Fetch())
	{
		$arResult[] = array(
			"ID" => $arElement["ID"],
			"NAME" => str_replace($arBanSym,$arRepSym,$arElement["NAME"]),
			"SECTION_ID" => $arElement["IBLOCK_SECTION_ID"],
			"CONTENT" => '<div class="mts-name">'.htmlspecialcharsex(str_replace($arBanSym,$arRepSym,$arElement["NAME"])).'</div>',
		);
	}

	$APPLICATION->RestartBuffer();
	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo Json::encode(array("SECTION_ID" => intval($SECTION_ID), "arElements" => $arResult));
	CMain::FinalActions();
}
elseif (isset($_REQUEST['MODE']) && $_REQUEST['MODE'] == 'search')
{
	$arResult = array();

	$rsElements = CIBlockElement::GetList(
		array(),
		array(
			"IBLOCK_ID" => $arIBlock["ID"],
			"%NAME" => $_REQUEST['search'],
			'CHECK_PERMISSIONS' => 'N',
		),
		false,
		array("nTopCount" => 20),
		array("ID", "NAME", "IBLOCK_SECTION_ID")
	);

	while($arElement = $rsElements->Fetch())
	{
		$arResult[] = array(
			"ID" => $arElement["ID"],
			"NAME" => str_replace($arBanSym,$arRepSym,$arElement["NAME"]),
			"SECTION_ID" => $arElement["IBLOCK_SECTION_ID"],
		);
	}

	$APPLICATION->RestartBuffer();
	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo Json::encode($arResult);
	CMain::FinalActions();
}
else
{

$bMultiple = false;
if (isset($_REQUEST['multiple']) && $_REQUEST['multiple'] == 'Y')
	$bMultiple = true;
if (isset($_REQUEST['win_id']))
{
	$intCount = 0;
	$arWinList = array();
	$strWinTemplate = '/^[a-z][a-z0-9\.\,\:_\-]*$/i';
	$intCount = preg_match($strWinTemplate,$_REQUEST['win_id'],$arWinList);
	if (1 != $intCount || empty($arWinList))
	{
		ShowError(Loc::getMessage("BT_COMP_MTS_AJAX_ERR_WIN_ID_ABSENT"));
		die();
	}
	else
	{
		$win_id = $arWinList[0];
	}
}
else
{
	ShowError(Loc::getMessage("BT_COMP_MTS_AJAX_ERR_WIN_ID_ABSENT"));
	die();
}
$arOpenedSections = array();
$arValues = array();

if(isset($_REQUEST['value']))
{
	$arTempo = array();
	$arValues = explode(',', $_REQUEST['value']);
	foreach($arValues as $value)
	{
		$value = intval($value);
		if($value > 0)
			$arTempo[] = $value;
	}
	$arValues = $arTempo;

	if(!empty($arValues))
	{
		$rsElements = CIBlockElement::GetList(
			array(),
			array(
				"IBLOCK_ID" => $arIBlock["ID"],
				"=ID" => $arValues,
				'CHECK_PERMISSIONS' => 'N',
			),
			false,
			false,
			array("ID", "IBLOCK_SECTION_ID")
		);
		while($arElement = $rsElements->Fetch())
		{
			$arOpenedSections[] = $arElement['IBLOCK_SECTION_ID'];
		}
	}
}
?>
<div class="title">
<table cellspacing="0" width="100%">
	<tr>
		<td width="100%" class="title-text" onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById('<?echo $win_id?>'));">&nbsp;</td>
		<td width="0%"><a class="close" href="javascript:document.getElementById('<?echo $win_id?>').__object.CloseDialog();" title="<?=Loc::getMessage("CT_BMTS_WINDOW_CLOSE")?>"></a></td>
	</tr>
</table>
</div>
<script>
var current_selected = <?echo Json::encode(array_values($arValues)) ?>;
</script>
<div class="content" id="_f_popup_content" style="height: 403px; overflow-x: hidden; oveflow-y: auto; padding: 0;"><input id="bx_emp_search_control" type="text" style="width: 99.99%" value="" autocomplete="off" />

<script>
document.getElementById('<?echo $win_id?>').__object.InitControl('bx_emp_search_control');
</script>

<div class="mts-section-list" id="mts_search_layout">
<?
	function EmployeeDrawStructure($arStructure, $arSections, $key, $win_id)
	{
		foreach ($arStructure[$key] as $ID)
		{
			$arRes = $arSections[$ID];

			echo '<div class="mts-section'.($key == 0 ? '-first' : '').'" style="padding-left: '.(($arRes['DEPTH_LEVEL']-1)*15).'px" onclick="document.getElementById(\''.$win_id.'\').__object.LoadSection(\''.$ID.'\')" id="mts_section_'.$ID.'">';
			echo '<div class="mts-section-name mts-closed">'.$arRes['NAME'].'</div>';
			echo '</div>';

			echo '<div style="display: none" id="bx_children_'.$arRes['ID'].'">';
			if (is_array($arStructure[$ID]))
			{
				EmployeeDrawStructure($arStructure, $arSections, $ID, $win_id);
			}
			echo '<div class="mts-list" id="mts_elements_'.$ID.'" style="margin-left: '.($arRes['DEPTH_LEVEL']*15).'px"><i>'.Loc::getMessage('CT_BMTS_WAIT').'</i></div>';
			echo '</div>';

		}
	}

	$dbRes = CIBlockSection::GetTreeList(array(
		'IBLOCK_ID' => $arIBlock["ID"],
		'CHECK_PERMISSIONS' => 'N',
	));
	$arStructure = array(0 => array());
	$arSections = array();
	while ($arRes = $dbRes->GetNext())
	{
		if (!$arRes['IBLOCK_SECTION_ID'])
			$arStructure[0][] = $arRes['ID'];
		elseif (!is_array($arStructure[$arRes['IBLOCK_SECTION_ID']]))
			$arStructure[$arRes['IBLOCK_SECTION_ID']] = array($arRes['ID']);
		else
			$arStructure[$arRes['IBLOCK_SECTION_ID']][] = $arRes['ID'];

		$arSections[$arRes['ID']] = $arRes;
	}

	EmployeeDrawStructure($arStructure, $arSections, 0, $win_id);

	echo '<div style="display:none" id="mts_section_0">';
	echo '<div class="mts-section-name mts-closed"></div>';
	echo '</div>';

	echo '<div style="display: none" id="bx_children_0">';
	echo '<div class="mts-list" id="mts_elements_0" style="margin-left: '.($arRes['DEPTH_LEVEL']*15).'px"><i>'.Loc::getMessage('CT_BMTS_WAIT').'</i></div>';
	echo '</div>';


	if (count($arStructure[0]) <= 1 && empty($arOpenedSections))
	{
		$arOpenedSections[] = $arStructure[0][0];
	}

?>
<script>
var WIN_ID = document.getElementById('<?echo $win_id?>');
<?
	if (!empty($arOpenedSections))
	{
		$arSectionList = array();
		foreach ($arOpenedSections as $opened_section)
		{
			while ($opened_section > 0)
			{
				if (in_array($opened_section, $arSectionList))
					break;
				$arSectionList[] = $opened_section;

?>
WIN_ID.__object.LoadSection('<?echo intval($opened_section)?>', true);
<?
				$opened_section = $arSections[$opened_section]['IBLOCK_SECTION_ID'];
			}
		}
	}
?>
WIN_ID.__object.LoadSection('0', true);
</script>
	</div>
</div>
<div class="buttons">
	<input type="button" id="submitbtn" value="<?echo Loc::getMessage('CT_BMTS_SUBMIT')?>" onclick="document.getElementById('<?echo $win_id?>').__object.ElementSet();" />
	<input type="button" value="<?echo Loc::getMessage('CT_BMTS_CANCEL')?>" onclick="document.getElementById('<?echo $win_id?>').__object.CloseDialog();" />
</div>
<?
}
?>