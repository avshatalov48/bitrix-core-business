<?
define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

CComponentUtil::__IncludeLang("/bitrix/components/bitrix/desktop/", "/admin_settings_all.php");

if (false == check_bitrix_sessid() || !$GLOBALS["USER"]->IsAuthorized())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
	die();
}

$APPLICATION->SetAdditionalCSS('/bitrix/themes/.default/pubstyles.css');

$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_admin_index", array(), false);
if(!is_array($arUserOptions))
	$arUserOptions = Array();

/******* POST **********/

if ($REQUEST_METHOD == "POST" && $_REQUEST['desktop_backurl'] && mb_strpos($_REQUEST['desktop_backurl'], "/") === 0)
	$desktop_backurl = $_REQUEST['desktop_backurl'];
else
	$desktop_backurl = "";

if($REQUEST_METHOD=="POST" && $_REQUEST['save'] == 'Y')
{
	CUtil::JSPostUnescape();

	if (!is_array($ids)) 
		$ids = array();

	$arValues = $_POST;

	$arUserOptionsTmp = Array();
	for($i=0; $i<count($ids); $i++)
	{
		$num = $ids[$i];

		if(${"del_".$num}=="Y")
			continue;

		$arTmp = $arUserOptions[$num-1];
		
		if(trim($arValues["text_".$num]) <> '')
			$arTmp["NAME"] = $arValues["text_".$num];

		$arUserOptionsTmp[] = $arTmp;
	}

	CUserOptions::SetOption("intranet", "~gadgets_admin_index", $arUserOptionsTmp, false, false);	
?>
	<script bxrunfirst="true">
	top.BX.WindowManager.Get().Close();
	top.BX.showWait();
	top.location.href = '<?=htmlspecialcharsbx(CUtil::JSEscape($desktop_backurl))?>';
	</script>
<?
	die();
}
/******* /POST **********/
$obJSPopup = new CJSPopup('',
	array(
		'TITLE' => GetMessage('CMDESKTOP_ADMIN_SETTINGS_ALL_DIALOG_TITLE'),
		'ARGS' => "lang=".urlencode($_GET["lang"])."&site=".urlencode($_GET["site"])."&back_url=".urlencode($_GET["back_url"])."&path=".urlencode($_GET["path"])."&name=".urlencode($_GET["name"])
	)
);

// ======================== Show titlebar ============================= //
$obJSPopup->ShowTitlebar();
?>
<script src="/bitrix/js/main/dd.js" type="text/javascript"></script>

<?
// ======================== Show content ============================= //
$obJSPopup->StartContent();
?>
<style type="text/css">
div.bx-core-dialog-content div.bx-desktopset-current-row {background-color: #EAF8DF !important;}
div.bx-core-dialog-content div.view-area {white-space: nowrap; overflow: hidden; width: 220px; padding: 2px; display: block; cursor: text; -moz-box-sizing: border-box; -webkit-box-sizing:border-box; background-position: right center; background-repeat: no-repeat; border: 1px solid white;}
div.bx-core-dialog-content div.bx-desktopset-current-row div.edit-field {border: 1px solid #EAF8DF !important; background-color: #EAF8DF !important;}
div.bx-core-dialog-content div.bx-desktopset-current-row div.edit-field-active {border-color: #434B50 #ADC0CF #ADC0CF #434B50 !important; background-color: white !important;}
</style>
<input type="hidden" name="save" value="Y" />
<table border="0" cellpadding="0" cellspacing="0" class="bx-width100 internal">
<thead>
	<tr class="heading">
		<td width="0"></td>
		<td width="50%"><b><?echo GetMessage("CMDESKTOP_ADMIN_SETTINGS_ALL_NAME")?></b></td>
		<td width="0"></td>
		<td width="0"></td>
		<td width="0"></td>
	</tr>
</thead>
</table>

<div id="bx_desktopset_layout" class="bx-desktopset-layout"><?
$itemcnt = 0;

for($i=1; $i<=count($arUserOptions); $i++):
	$itemcnt++;
	$arUserOption = $arUserOptions[$i-1];
	?><div class="bx-desktopset-placement" id="bx_desktopset_placement_<?=$i?>"><div class="bx-edit-desktopset-item" id="bx_desktopset_row_<?=$i?>"><table border="0" cellpadding="0" cellspacing="0" class="bx-width100 internal"><tbody>
	<tr>
		<td>
			<input type="hidden" name="ids[]" value="<?=$i?>" />
			<input type="hidden" name="del_<?=$i?>" value="N" />
			<span class="rowcontrol drag" title="<?=GetMessage('CMDESKTOP_ADMIN_SETTINGS_ALL_DRAG')?>"></span>
		</td>
		</td>
		<td>
			<div onmouseout="rowMouseOut(this)" onmouseover="rowMouseOver(this)" class="edit-field view-area" id="view_area_text_<?=$i?>" onclick="editArea('text_<?=$i?>')" title="<?=GetMessage('CMDESKTOP_ADMIN_SETTINGS_ALL_TOOLTIP_TEXT_EDIT')?>"><?=($arUserOption["NAME"] <> ''?htmlspecialcharsbx($arUserOption["NAME"]):GetMessage('CMDESKTOP_ADMIN_SETTINGS_ALL_DIALOG_DESKTOP').$i)?></div>
			<div class="edit-area" id="edit_area_text_<?=$i?>" style="display: none;"><input type="text" style="width: 220px;" name="text_<?echo $i?>" value="<?=($arUserOption["NAME"] <> ''?htmlspecialcharsbx($arUserOption["NAME"]):GetMessage('CMDESKTOP_ADMIN_SETTINGS_ALL_DIALOG_DESKTOP').$i)?>" onblur="viewArea('text_<?=$i?>')" /></div>
		</td>
		<td><span onclick="dsMoveUp(<?=$i?>)" class="rowcontrol up" style="visibility: <?=($i == 1 ? 'hidden' : 'visible')?>" title="<?=GetMessage('CMDESKTOP_ADMIN_SETTINGS_ALL_TOOLTIP_UP')?>"></span></td>
		<td><span onclick="dsMoveDown(<?=$i?>)" class="rowcontrol down" style="visibility: <?=($i == count($arUserOptions) ? 'hidden' : 'visible')?>" title="<?=GetMessage('CMDESKTOP_ADMIN_SETTINGS_ALL_TOOLTIP_DOWN')?>"></span></td>
		<td><span onclick="dsDelete(<?=$i?>)" class="rowcontrol delete" title="<?=GetMessage('CMDESKTOP_ADMIN_SETTINGS_ALL_TOOLTIP_DELETE')?>"></span></td>
	</tr>
	</tbody></table></div></div><?
endfor?></div>
<input type="hidden" name="itemcnt" value="<?echo $itemcnt?>" />
<input type="hidden" name="desktop_backurl" value="<?=htmlspecialcharsbx(CUtil::JSEscape($desktop_backurl))?>">
<script type="text/javascript">
var currentRow = null;

var GLOBAL_bDisableActions = false;
var GLOBAL_bDisableDD = false;

var jsDSMess = {
	noname: '<?=CUtil::JSEscape(GetMessage('CMDESKTOP_ADMIN_SETTINGS_ALL_JS_NONAME'))?>'
}

function dsCheckIcons()
{
	var obLayout = BX('bx_desktopset_layout');

	for (var i = 0, num = obLayout.childNodes.length; i < num; i++)
	{
		if (
			obLayout.childNodes[i].tagName
			&& obLayout.childNodes[i].tagName == 'DIV'
			&& obLayout.childNodes[i].className == 'bx-desktopset-placement'
		)
		{
			var obTbody = obLayout.childNodes[i].firstChild.firstChild.tBodies[0];

			obTbody.rows[0].cells[2].firstChild.style.visibility = (i == 0 ? 'hidden' : 'visible');
			obTbody.rows[0].cells[3].firstChild.style.visibility = (i == num-1 ? 'hidden' : 'visible');
//			obTbody.rows[0].cells[0].firstChild.value = 10 * (i+1);
		}
	}
}

function dsMoveUp(i)
{
	if (GLOBAL_bDisableActions)
		return;

	var obRow = BX('bx_desktopset_row_' + i);
	var obPlacement = obRow.parentNode;

	var index = obPlacement.id.substring(24);
	if (1 >= index)
		return;

	var obNewPlacement = obPlacement.previousSibling;
	var obSwap = obNewPlacement.firstChild;

	obPlacement.removeChild(obRow);
	obNewPlacement.removeChild(obSwap);
	obPlacement.appendChild(obSwap);
	obNewPlacement.appendChild(obRow);

	setCurrentRow(obRow);
	dsCheckIcons();
}

function dsMoveDown(i)
{
	if (GLOBAL_bDisableActions)
		return;

	var obRow = BX('bx_desktopset_row_' + i);
	var obPlacement = obRow.parentNode;
	var obNewPlacement = obPlacement.nextSibling;
	if (null == obNewPlacement)
		return;

	var obSwap = obNewPlacement.firstChild;

	obPlacement.removeChild(obRow);
	obNewPlacement.removeChild(obSwap);
	obPlacement.appendChild(obSwap);
	obNewPlacement.appendChild(obRow);

	setCurrentRow(obRow);
	dsCheckIcons();
}

function dsDelete(i)
{
	if (GLOBAL_bDisableActions)
		return;

	var obInput = <?echo $obJSPopup->jsPopup?>.GetForm()['del_' + i];
	var obPlacement = BX('bx_desktopset_row_' + i).parentNode;

	obInput.value = 'Y';

	if (obPlacement.firstChild == currentRow) currentRow = null;

	obPlacement = BX.remove(obPlacement);
	dsCheckIcons();
}

var currentEditingRow = null;

function editArea(area, bSilent)
{
	if (GLOBAL_bDisableActions)
		return;

	jsDD.Disable();
	GLOBAL_bDisableDD = true;

	jsDD.allowSelection();
	l = BX('bx_desktopset_layout');
	l.ondrag = l.onselectstart = null;
	l.style.MozUserSelect = '';

	if (null == bSilent) bSilent = false;

	var obEditArea = BX('edit_area_' + area);
	var obViewArea = BX('view_area_' + area);

	obEditArea.style.display = 'block';
	obViewArea.style.display = 'none';

	if (!bSilent)
	{
		obEditArea.firstChild.focus();

		if (BX.browser.IsIE())
			setTimeout(function () {setCurrentRow(obViewArea.parentNode.parentNode.parentNode.parentNode.parentNode)}, 30);
		else
			setCurrentRow(obViewArea.parentNode.parentNode.parentNode.parentNode.parentNode);
	}

	return obEditArea;
}

function viewArea(area, bSilent)
{
	if (GLOBAL_bDisableActions)
		return;

	jsDD.Enable();
	GLOBAL_bDisableDD = false;

	l = BX('bx_desktopset_layout');
	l.ondrag = l.onselectstart = BX.False;
	l.style.MozUserSelect = 'none';

	if (null == bSilent) bSilent = false;

	var obEditArea = BX('edit_area_' + area);
	var obViewArea = BX('view_area_' + area);

	obEditArea.firstChild.value = BX.util.trim(obEditArea.firstChild.value);

	obViewArea.innerHTML = '';
	BX.adjust(obViewArea, {text:obEditArea.firstChild.value.length > 0 ? obEditArea.firstChild.value : jsDSMess.noname})

	obEditArea.style.display = 'none';
	obViewArea.style.display = 'block';

	currentEditingRow = null;
	setCurrentRow(obViewArea.parentNode.parentNode.parentNode.parentNode.parentNode);

	return obViewArea;
}

function setCurrentRow(i)
{
	i = BX(i);

	if (null != currentRow) BX.removeClass(currentRow, 'bx-desktopset-current-row')

	BX.addClass(i, 'bx-desktopset-current-row');
	currentRow = i;
}

function rowMouseOut(obArea)
{
	obArea.className = 'edit-field view-area';
	obArea.style.backgroundColor = '';
}

function rowMouseOver (obArea)
{
	if (GLOBAL_bDisableActions || jsDD.bPreStarted)
		return;

	//obArea.className = 'edit-field-active view-area';
	//obArea.style.backgroundColor = 'white';
}

/* DD handlers */
function BXDD_DragStart()
{
	if (GLOBAL_bDisableDD)
		return false;

	this.BXOldPlacement = this.parentNode;

	var id = this.id.substring(18);
	rowMouseOut(viewArea('text_' + id));

	GLOBAL_bDisableActions = true;

	return true;
}

function BXDD_DragStop()
{
	this.BXOldPlacement = false;

	setTimeout('GLOBAL_bDisableActions = false', 50);

	return true;
}

function BXDD_DragHover(obPlacement, x, y)
{
	if (GLOBAL_bDisableDD)
		return false;

	if (obPlacement == this.BXOldPlacement)
		return false;

	var obSwap = obPlacement.firstChild;

	this.BXOldPlacement.removeChild(this);
	obPlacement.removeChild(obSwap);
	this.BXOldPlacement.appendChild(obSwap);
	obPlacement.appendChild(this);

	this.BXOldPlacement = obPlacement;

	dsCheckIcons();

	return true;
}

BX.ready(function ()
{
	jsDD.Reset();

<?
for($i=1; $i<=count($arUserOptions); $i++):
?>
	jsDD.registerDest(BX('bx_desktopset_placement_<?=$i?>'));

	var obEl = BX('bx_desktopset_row_<?=$i?>');
	obEl.onbxdragstart = BXDD_DragStart;
	obEl.onbxdragstop = BXDD_DragStop;
	obEl.onbxdraghover = BXDD_DragHover;
	jsDD.registerObject(obEl);
<?
endfor;
?>
	jsDD.registerContainer(BX.WindowManager.Get().GetContent());
	l = BX('bx_desktopset_layout');
	l.ondrag = l.onselectstart = BX.False;
	l.style.MozUserSelect = 'none';
});
</script>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
?>