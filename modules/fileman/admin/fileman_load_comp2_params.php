<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
if (!$USER->CanDoOperation('fileman_view_file_structure') && !$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

function GetProperties($componentName, $curTemplate = '')
{
	$stid = (isset($_GET['stid'])) ? $_GET['stid'] : '';
	$curTemplate = (!$curTemplate || $curTemplate == '.default') ? '' : CUtil::JSEscape($curTemplate);
	$arTemplates = CComponentUtil::GetTemplatesList($componentName, $stid);
	$arCurVals = isset($_POST['curval']) ? CEditorUtils::UnJSEscapeArray($_POST['curval']) : Array();
	$loadHelp = (isset($_GET['loadhelp']) && $_GET['loadhelp']=="Y") ? true : false;

	if (is_array($arTemplates))
	{
		foreach ($arTemplates as $k => $arTemplate)
		{
			push2arComp2Templates($arTemplate['NAME'], $arTemplate['TEMPLATE'], $arTemplate['TITLE'], $arTemplate['DESCRIPTION']);
			$tName = (!$arTemplate['NAME'] || $arTemplate['NAME'] == '.default') ? '' : $arTemplate['NAME'];
			if ($tName == $curTemplate)
			{
				$arTemplateProps = CComponentUtil::GetTemplateProps($componentName, $arTemplate['NAME'], $stid, $arCurVals);
				if (is_array($arTemplateProps))
					foreach ($arTemplateProps as $k => $arTemplateProp)
						push2arComp2TemplateProps($componentName,$k,$arTemplateProp);
			}
		}
	}

	$arProps = CComponentUtil::GetComponentProps($componentName, $arCurVals);
	if ($loadHelp && is_array($arProps['PARAMETERS']))
		fetchPropsHelp($componentName);

	$bGroup = (isset($arProps['GROUPS']) && count($arProps['GROUPS']) > 0);
	if (is_array($arProps['GROUPS']))
	{
		foreach ($arProps['GROUPS'] as $k => $arGroup)
		{
			?>window.arComp2Groups.push({name: '<?= CUtil::JSEscape($k)?>', title: '<?= CUtil::JSEscape($arGroup['NAME'])?>'});<?
		}
	}

	if (is_array($arProps['PARAMETERS']))
		foreach ($arProps['PARAMETERS'] as $k => $arParam)
			push2arComp2Props($k, $arParam, (($bGroup) ? $arProps['GROUPS'] : false));
}

function fetchPropsHelp($componentName_)
{
	global $MESS;

	$componentName = str_replace("..", "", $componentName_);
	$componentName = str_replace(":", "/", $componentName);
	$lang = preg_replace("/[^a-zA-Z0-9_]/is", "", $_GET["lang"]);
	CComponentUtil::__IncludeLang("/bitrix/components/".$componentName, "/help/.tooltips.php", $lang);
	$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/".$componentName."/help/.tooltips.php";
	$arTooltips = array();
	if(file_exists($path))
		include($path);

	?>var arTT = {};<?
	if(is_array($arTooltips) && !empty($arTooltips))
	{
		foreach($arTooltips as $propName => $tooltip)
		{
			?>arTT["<?=CUtil::JSEscape($propName)?>"] = '<?=CUtil::JSEscape($tooltip);?>';<?
		}
	}
	elseif(is_array($MESS))
	{
		foreach($MESS as $propName => $tooltip)
		{
			if(substr($propName, -4) == '_TIP')
			{
			?>arTT["<?=CUtil::JSEscape(substr($propName, 0, -4))?>"] = '<?=CUtil::JSEscape($tooltip);?>';<?
			}
		}
	}
	?>window.arComp2Tooltips["<?= CUtil::JSEscape($componentName_)?>"] = arTT;<?
}

function push2arComp2Props($name, $arParam, $arGroup)
{
	$name = preg_replace("/[^a-zA-Z0-9_-]/is", "_", $name);
	?>
var p = {};
p.param_name = '<?=CUtil::JSEscape($name);?>';
<?
	if ($arGroup !== false && isset($arParam['PARENT']) && isset($arGroup[$arParam['PARENT']]))
	{
		?>p.group = '<?= $arParam['PARENT']?>';<?
	}

	if (is_array($arParam))
	{
		foreach ($arParam  as $k => $prop)
		{
			if ($k == 'TYPE' && $prop == 'FILE')
				$GLOBALS['arFD'][] = Array(
					'NAME' => CUtil::JSEscape($name),
					'TARGET' => isset($arParam['FD_TARGET']) ? $arParam['FD_TARGET'] : 'F',
					'EXT' => isset($arParam['FD_EXT']) ? $arParam['FD_EXT'] : '',
					'UPLOAD' => isset($arParam['FD_UPLOAD']) && $arParam['FD_UPLOAD'] && $arParam['FD_TARGET'] == 'F',
					'USE_ML' => isset($arParam['FD_USE_MEDIALIB']) && $arParam['FD_USE_MEDIALIB'],
					'ONLY_ML' => isset($arParam['FD_USE_ONLY_MEDIALIB']) && $arParam['FD_USE_ONLY_MEDIALIB'],
					'ML_TYPES' => isset($arParam['FD_MEDIALIB_TYPES']) ? $arParam['FD_MEDIALIB_TYPES'] : false
				);
			elseif (in_array($k, Array('FD_TARGET', 'FD_EXT','FD_UPLOAD', 'FD_MEDIALIB_TYPES', 'FD_USE_ONLY_MEDIALIB')))
				continue;

			if (is_array($prop))
			{
				?>p.<?= $k;?> = {};
				<?foreach ($prop as $k2 => $prop_):?>
				p.<?= $k;?>['<?= CUtil::JSEscape($k2)?>'] = '<?= CUtil::JSEscape($prop_)?>';<?= "\n"?>
				<?endforeach;?>
				<?
			}
			else
			{
				?>p.<?= $k;?> = '<? echo CUtil::JSEscape($prop);?>';<?
			}
			echo "\n";
		}
	}
?>window.arComp2Props.push(p);<?
}


function push2arComp2Templates($name,$template,$title,$description)
{
?>
window.arComp2Templates.push({
name : '<?=$name;?>',
template : '<?=$template;?>',
title	 : '<?=CUtil::JSEscape($title);?>',
description : '<?=CUtil::JSEscape($description);?>'
});
<?
}


function push2arComp2TemplateProps($componentName, $paramName, $arParam)
{
	?>var p2 = {param_name: '<?=CUtil::JSEscape($paramName)?>'};
<?
	foreach ($arParam  as $k => $prop)
	{
		if ($k == 'TYPE' && $prop == 'FILE')
		{
			$GLOBALS['arFD'][] = Array(
				'NAME' => CUtil::JSEscape($name),
				'TARGET' => isset($arParam['FD_TARGET']) ? $arParam['FD_TARGET'] : 'F',
				'EXT' => isset($arParam['FD_EXT']) ? $arParam['FD_TARGET'] : '',
				'UPLOAD' => isset($arParam['FD_UPLOAD']) ? $arParam['FD_UPLOAD'] : true,
				'USE_ML' => isset($arParam['FD_USE_MEDIALIB']) && $arParam['FD_USE_MEDIALIB'],
				'ONLY_ML' => isset($arParam['FD_USE_ONLY_MEDIALIB']) && $arParam['FD_USE_ONLY_MEDIALIB'],
				'ML_TYPES' => isset($arParam['FD_MEDIALIB_TYPES']) ? $arParam['FD_MEDIALIB_TYPES'] : false
			);
		}
		elseif (in_array($k, Array('FD_TARGET', 'FD_EXT','FD_UPLOAD')))
			continue;

		if (is_array($prop))
		{
?>p2.<? echo$k;?> = {<?
		echo "\n";
				$i=true;
				foreach ($prop as $k2 => $prop_)
				{
					if (!$i)
						echo",\n";
					else
						$i = false;

					echo '\''.CUtil::JSEscape($k2).'\' : \''.CUtil::JSEscape($prop_).'\'';
				}
			echo "\n";
?>}<?
		}
		else
		{
?>p2.<?= CUtil::JSEscape($k)?> = '<?= CUtil::JSEscape($prop)?>';<?
		}
		echo "\n";
	}
?>window.arComp2TemplateProps.push(p2);<?
}


function ShowFileDialogsScripts()
{
	global $arFD;
	$l = count($arFD);
	if ($l < 1)
		return;


	for($i = 0; $i < $l; $i++)
	{
		if ($arFD[$i]['USE_ML'])
		{
			$MLRes = CMedialib::ShowBrowseButton(
				array(
					'mode' => $arFD[$i]['ONLY_ML'] ? 'medialib' : 'select',
					'value' => '...',
					'event' => "BX_FD_".$arFD[$i]['NAME'],
					'id' => "bx_fd_input_".strtolower($arFD[$i]['NAME']),
					'MedialibConfig' => array(
						"event" => "bx_ml_event_".$arFD[$i]['NAME'],
						"arResultDest" => Array("FUNCTION_NAME" => "BX_FD_ONRESULT_".$arFD[$i]['NAME']),
						"types" => $arFD[$i]['ML_TYPES']
					),
					'bReturnResult' => true
				)
			);
			?>
			<script>window._bxMlBrowseButton_<?= strtolower($arFD[$i]['NAME'])?> = '<?= CUtil::JSEscape($MLRes)?>';</script>
			<?
		}
		CAdminFileDialog::ShowScript(Array
		(
			"event" => "BX_FD_".$arFD[$i]['NAME'],
			"arResultDest" => Array("FUNCTION_NAME" => "BX_FD_ONRESULT_".$arFD[$i]['NAME']),
			"arPath" => Array(),
			"select" => $arFD[$i]['TARGET'], // F - file only, D - folder only, DF - files & dirs
			"operation" => 'O',
			"showUploadTab" => $arFD[$i]['UPLOAD'],
			"showAddToMenuTab" => false,
			"fileFilter" => $arFD[$i]['EXT'],
			"allowAllFiles" => true,
			"SaveConfig" => true
		));
	}
}
?>
<script>
window.arComp2Templates = [];
window.arComp2Groups = [];
window.arComp2Props = [];
window.arComp2TemplateProps = [];
<?
$arFD = Array();
if (isset($_GET['cname']))
	GetProperties($_GET['cname'], $_GET['tname']);
?>
</script>
<?
ShowFileDialogsScripts();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");?>