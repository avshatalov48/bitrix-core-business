<?
define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

if(!CModule::IncludeModule('lists'))
{
	ShowError(GetMessage("CT_BMTS_MODULE_NOT_INSTALLED"));
	die();
}

$iblock_id = intval($_REQUEST["IBLOCK_ID"]);

$lists_perm = CListPermissions::CheckAccess(
	$USER,
	$_REQUEST["IBLOCK_TYPE_ID"],
	$iblock_id,
	$_REQUEST["SOCNET_GROUP_ID"]
);
if($lists_perm < 0)
{
	switch($lists_perm)
	{
	case CListPermissions::WRONG_IBLOCK_TYPE:
		ShowError(GetMessage("CT_BMTS_WRONG_IBLOCK_TYPE"));
		die();
	case CListPermissions::WRONG_IBLOCK:
		ShowError(GetMessage("CT_BMTS_WRONG_IBLOCK"));
		die();
	default:
		ShowError(GetMessage("CT_BMTS_UNKNOWN_ERROR"));
		die();
	}
}
elseif(
	$lists_perm < CListPermissions::CAN_READ
	&& !CIBlockRights::UserHasRightTo($iblock_id, $iblock_id, "element_read")
)
{
	ShowError(GetMessage("CT_BMTS_ACCESS_DENIED"));
	die();
}

$arIBlock = CIBlock::GetArrayByID($iblock_id);

if ($_REQUEST['MODE'] == 'section')
{
	$arResult = array();
	$SECTION_ID = intval($_REQUEST['SECTION_ID']);
	//if($SECTION_ID == 0)
	//	$SECTION_ID = false;


	$rsElements = CIBlockElement::GetList(
		array('ID' => 'ASC'),
		array(
			'IBLOCK_ID' => $arIBlock["ID"],
			'SECTION_ID' => $SECTION_ID,
			'CHECK_PERMISSIONS' => $arParams['CAN_EDIT']? 'N': 'Y',
		),
		false,
		false,
		array("ID", "NAME", "IBLOCK_SECTION_ID")
	);
	while($arElement = $rsElements->Fetch())
	{
		$arResult[] = array(
			"ID" => $arElement["ID"],
			"NAME" => $arElement["NAME"],
			"SECTION_ID" => $arElement["IBLOCK_SECTION_ID"],
			"CONTENT" => '<div class="mts-name">'.htmlspecialcharsex($arElement["NAME"]).'</div>',
		);
	}

	$APPLICATION->RestartBuffer();
	Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJsObject(array("SECTION_ID" => intval($SECTION_ID), "arElements" => $arResult));
	die();
}
elseif ($_REQUEST['MODE'] == 'search')
{
	$arResult = array();

	$rsElements = CIBlockElement::GetList(
		array(),
		array(
			"IBLOCK_ID" => $arIBlock["ID"],
			"%NAME" => $_REQUEST['search'],
			'CHECK_PERMISSIONS' => $arParams['CAN_EDIT']? 'N': 'Y',
		),
		false,
		array("nTopCount" => 20),
		array("ID", "NAME", "IBLOCK_SECTION_ID")
	);

	while($arElement = $rsElements->Fetch())
	{
		$arResult[] = array(
			"ID" => $arElement["ID"],
			"NAME" => $arElement["NAME"],
			"SECTION_ID" => $arElement["IBLOCK_SECTION_ID"],
		);
	}

	$APPLICATION->RestartBuffer();
	Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJsObject($arResult);
	die();
}
else
{


$bMultiple = $_GET['multiple'] == 'Y';
$win_id = preg_replace("/[^a-z0-9_\\[\\]:.,_-]/i", "", $_REQUEST["win_id"]);
$arOpenedSections = array();
$arValues = array();

if(isset($_GET['value']))
{
	$arValues = array();
	foreach(explode(',', $_GET['value']) as $value)
	{
		$value = intval($value);
		if($value > 0)
			$arValues[$value] = $value;
	}

	if(count($arValues) > 0)
	{
		$rsElements = CIBlockElement::GetList(
			array(),
			array(
				"IBLOCK_ID" => $arIBlock["ID"],
				"=ID" => $arValues,
				'CHECK_PERMISSIONS' => $arParams['CAN_EDIT']? 'N': 'Y',
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
		<td width="0%"><a class="close" href="javascript:document.getElementById('<?echo $win_id?>').__object.CloseDialog();" title="<?=GetMessage("CT_BMTS_WINDOW_CLOSE")?>"></a></td>
	</tr>
</table>
</div>
<script>
var current_selected = <?echo CUtil::PhpToJsObject(array_values($arValues))?>;
</script>
<div class="content" id="_f_popup_content" style="height: 400px; overflow-x: hidden; oveflow-y: auto; padding: 0px;"><input id="bx_emp_search_control" type="text" style="width: 99.99%" value="" autocomplete="off" />

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
			echo '<div class="mts-list" id="mts_elements_'.$ID.'" style="margin-left: '.($arRes['DEPTH_LEVEL']*15).'px"><i>'.GetMessage('CT_BMTS_WAIT').'</i></div>';
			echo '</div>';

		}
	}

	$dbRes = CIBlockSection::GetTreeList(array(
		'IBLOCK_ID' => $arIBlock["ID"],
		'CHECK_PERMISSIONS' => $arParams['CAN_EDIT']? 'N': 'Y',
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
	echo '<div class="mts-list" id="mts_elements_0" style="margin-left: '.($arRes['DEPTH_LEVEL']*15).'px"><i>'.GetMessage('CT_BMTS_WAIT').'</i></div>';
	echo '</div>';


	if (count($arStructure[0]) <= 1 && count($arOpenedSections) <= 0)
	{
		$arOpenedSections[] = $arStructure[0][0];
	}

?>
<script>
<?
	if (count($arOpenedSections) > 0)
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
document.getElementById('<?echo $win_id?>').__object.LoadSection('<?echo intval($opened_section)?>', true);
<?
				$opened_section = $arSections[$opened_section]['IBLOCK_SECTION_ID'];
			}
		}
	}
?>
	document.getElementById('<?echo $win_id?>').__object.LoadSection('0', true);
</script>
	</div>
</div>
<div class="buttons">
	<input type="button" id="submitbtn" value="<?echo GetMessage('CT_BMTS_SUBMIT')?>" onclick="document.getElementById('<?echo $win_id?>').__object.ElementSet();" />
	<input type="button" value="<?echo GetMessage('CT_BMTS_CANCEL')?>" onclick="document.getElementById('<?echo $win_id?>').__object.CloseDialog();" />
</div>
<?
}
?>