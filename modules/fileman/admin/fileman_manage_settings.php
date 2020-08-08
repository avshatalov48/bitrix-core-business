<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
if (!$USER->CanDoOperation('fileman_edit_existent_files') || !check_bitrix_sessid())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

if (!isset($_REQUEST['target']) || !isset($_REQUEST['edname']))
	die();

$edname =  $_REQUEST['edname'];
$target =  $_REQUEST['target'];

if ($target == 'toolbars' &&  isset($_POST['tlbrset']))
{
	$tlbrset = $_POST['tlbrset'];
	$resultString = "";
	$_string = '';
	foreach($tlbrset as $tlbrname => $tlbr)
	{
		$resultString .= $tlbrname.":";
		$resultString .= $tlbr['show'].",";
		$resultString .= $tlbr['docked'].",";
		$resultString .= "[";
		foreach($tlbr['position'] as $tlbrpos)
			$resultString .= (mb_substr($tlbrpos, -2) == "px"? mb_substr($tlbrpos, 0, -2) : $tlbrpos).";";
		$resultString .= "]";
		$resultString .= "||";
	}
	$resultString = mb_substr($resultString, 0, -2);

	CUserOptions::SetOption("fileman", "toolbar_settings_".$edname, _addslashes($resultString));
}

if (isset($_REQUEST['tooltips']) && $target == 'tooltips')
	CUserOptions::SetOption("fileman", "show_tooltips".$edname, $_REQUEST['tooltips'] == "N" ? "N" : "Y");

if (isset($_REQUEST['visual_effects']) && $target == "visual_effects")
	CUserOptions::SetOption("fileman", "visual_effects".$edname, ($_REQUEST['visual_effects'] == "N" ? "N" : "Y"));

if (isset($_REQUEST['render_components']) && $target == 'render_components')
	CUserOptions::SetOption("fileman", "render_components", $_REQUEST['render_components'] == "Y");

if ($target == 'taskbars')
{
	// Taskbars
	if (isset($_POST['tskbrset']))
	{
		$taskbars = $_POST['tskbrset'];
		$res = array();
		foreach($taskbars as $name => $taskbar)
		{
			if ($taskbar['set'] != 2)
				$taskbar['set'] = 3;

			$res[$name] = array(
				'show' => $taskbar['show'] == "true",
				'set' => $taskbar['set'],
				'active' => $taskbar['active'] == "true"
			);
		}

		CUserOptions::SetOption("fileman", "taskbar_settings_".$edname, serialize($res));
	}

	// Taskbarsets
	if (isset($_POST['tskbrsetset']))
	{
		$tskbrsetset = $_POST['tskbrsetset'];
		$res = array();
		foreach($tskbrsetset as $iNum => $tskbrset)
		{
			if ($iNum != 2)
				$iNum = 3;

			$res[$iNum] = array(
				'show' => $tskbrset['show'] == "true",
				'size' => intval($tskbrset['size'])
			);
		}

		CUserOptions::SetOption("fileman", "taskbarset_settings_".$edname, serialize($res));
	}
}

if ($target == 'get_all')
{
	//Get toolbar settings
	$toolbar_settings = stripslashes(CUserOptions::GetOption("fileman", "toolbar_settings_".$edname));

	if ($toolbar_settings)
		getToolbarSettings($toolbar_settings);

	//Get taskbar settings
	$taskbars = CUserOptions::GetOption("fileman", "taskbar_settings_".$edname, false);
	if ($taskbars !== false && CheckSerializedData($taskbars))
		$taskbars = unserialize($taskbars);
	else
		$taskbars = false;

	?><script><?
	if (is_array($taskbars))
	{
		?>window.arTaskbarSettings = [];<?
		foreach($taskbars as $tname => $tskbr)
		{
			// Display settings
			?>window.arTaskbarSettings["<?= CUtil::JSEscape($tname)?>"] = {show: <?= $tskbr['show'] ? 'true' : 'false'?>, set: <?= $tskbr['set'] == 2 ? 2 : 3?>, active: <?= $tskbr['active'] ? 'true' : 'false'?>};<?
		}
	}
	?></script><?

	//Get taskbarset settings
	?><script><?
	$taskbarset = CUserOptions::GetOption("fileman", "taskbarset_settings_".$edname, false);
	if ($taskbarset !== false && CheckSerializedData($taskbarset))
		$taskbarset = unserialize($taskbarset);
	else
		$taskbarset = false;

	if (is_array($taskbarset))
	{
		?>window.arTBSetsSettings = [];<?
		foreach($taskbarset as $iNum => $tskbrset)
		{
			if ($iNum != 2)
				$iNum = 3;
			?>window.arTBSetsSettings["<?= intval($iNum)?>"] = {show: <?= $tskbrset['show'] ? 'true' : 'false'?>, size: <?= intval($tskbrset['size'])?>};
			<?
		}
	}
	?></script><?

	$show_tooltips = CUserOptions::GetOption("fileman", "show_tooltips".$edname, "Y");
	$visualEffects = CUserOptions::GetOption("fileman", "visual_effects".$edname, "Y");

	displayJSAddSett($show_tooltips, $visualEffects);
}

if ($target == 'unset')
{
	CUserOptions::DeleteOption("fileman", "toolbar_settings_".$edname);
	CUserOptions::DeleteOption("fileman", "taskbar_settings_".$edname);
	CUserOptions::DeleteOption("fileman", "taskbarset_settings_".$edname);
	CUserOptions::DeleteOption("fileman", "show_tooltips".$edname);
	CUserOptions::DeleteOption("fileman", "visual_effects".$edname);
}

if ($target == 'text_type' && isset($_REQUEST['type']))
{
	if (in_array($_REQUEST['type'], array('text', 'html', 'editor')))
		CUserOptions::SetOption('fileman', "type_selector_".$edname.(isset($_REQUEST['key']) ? $_REQUEST['key'] : ""), $_REQUEST['type']);
}

function displayJSAddSett($tooltips, $visualEffects)
{?>
<script>
window.__show_tooltips = <?echo $tooltips == "N" ? "false" : "true";?>;
window.__visual_effects = <?echo $visualEffects == "N" ? "false" : "true";?>;
</script>
<?}

function displayJSToolbar($tlbrname,$show,$docked,$arPos)
{
	?>
	<script>
	var _ar = {};
	_ar.show = <?echo($show == 'true' ? 'true' : 'false');?>;
	_ar.docked = <?echo($docked=='true' ? 'true' : 'false');?>;
	<?if ($docked=='true'):?>
		_ar.position = [<?= intval($arPos[0])?>,<?= intval($arPos[1])?>,<?= intval($arPos[2])?>];
	<?else:?>
		_ar.position = {
			x : '<?echo CUtil::JSEscape(mb_substr($arPos[0], -2) == "px"? mb_substr($arPos[0], 0, -2) : $arPos[0]);?>',
			y : '<?echo CUtil::JSEscape(mb_substr($arPos[1], -2) == "px"? mb_substr($arPos[1], 0, -2) : $arPos[1]);?>'
		};
	<?endif;?>
	window.arToolbarSettings["<?= CUtil::JSEscape($tlbrname)?>"] = _ar;
	</script>
	<?
}

function displayJSTaskbar($tskbrname,$show,$docked,$arPos,$auto)
{
	?>
	<script>
	var _ar = [];
	_ar.show = <?= (($show=='true' && $auto!='true') ? 'true' : 'false');?>;
	_ar.docked = <?= ($docked=='true' ? 'true' : 'false');?>;
	window.arTaskbarSettings["<?=$tskbrname;?>"] = _ar;
	</script>
	<?
}

function _addslashes($str)
{
	$pos2 = mb_strpos(mb_strtolower($str), "\n");
	if ($pos2 !== false)
	{
		$str = str_replace("\r","",$str);
		$str = str_replace("\n","\\n",$str);
	}
	return CUtil::addslashes($str);
}


function getToolbarSettings($settings)
{
	?>
	<script>
	window.arToolbarSettings = [];
	</script>
	<?
	$res = explode("||", $settings);
	$len = count($res);

	for ($i=0; $i<$len; $i++)
	{
		$tmp = explode(":", $res[$i]);
		$tlbrname = $tmp[0];
		$tmp2 = explode(",", $tmp[1]);
		$show = $tmp2[0];
		$docked = $tmp2[1];
		$position = explode(";", mb_substr($tmp2[2], 1, -1));
		displayJSToolbar($tlbrname,$show,$docked,$position);
	}
}

function getTaskbarSettings($settings)
{
	?>
	<script>
	window.arTaskbarSettings = [];
	</script>
	<?
	$res = explode("||", $settings);
	$len = count($res);

	for ($i=0; $i<$len; $i++)
	{
		$tmp = explode(":", $res[$i]);
		$tskbrname = $tmp[0];
		$tmp2 = explode(",", $tmp[1]);
		$show = $tmp2[0];
		$docked = $tmp2[1];
		$position = explode(";", mb_substr($tmp2[2], 1, -1));
		$auto = ($tmp2[3]) ? $tmp2[3] : '';
		displayJSTaskbar($tskbrname,$show,$docked,$position,$auto);
	}
}
?>