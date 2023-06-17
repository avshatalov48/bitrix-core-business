<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$rnd = rand();

$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/bitrix/favorites/styles.css');
?>
<?
if(!isset($arGadget["USERDATA"]["LINKS"]))
	$arGadget["USERDATA"]["LINKS"] = Array();

if($arParams["PERMISSION"]>"R")
{
	if(isset($_REQUEST['gdfavorites']) && $_REQUEST['gdfavorites']=='Y' && isset($_REQUEST['gdfav']) && $_REQUEST['gdfav']==$id)
	{
		if($_REQUEST['gdfvadd'] && $_SERVER['REQUEST_METHOD']=='POST')
		{
			$arGadget["USERDATA"]["LINKS"][] = Array("NAME"=>$_REQUEST['name'], "URL"=>$_REQUEST['url']);
			$arGadget["FORCE_REDIRECT"] = true;
		}

		if(isset($_REQUEST['gdfvdel']))
		{
			unset($arGadget["USERDATA"]["LINKS"][$_REQUEST['gdfvdel']]);
			$arGadget["FORCE_REDIRECT"] = true;
		}
	}
?>
<script>
function ShowHide<?=$rnd?>(flag)
{
	if(flag)
	{
		document.getElementById('gdfavoriteslink<?=$rnd?>').style.display = 'none';
		document.getElementById('gdfavoritesform<?=$rnd?>').style.display = 'block';
	}
	else
	{
		document.getElementById('gdfavoriteslink<?=$rnd?>').style.display = 'block';
		document.getElementById('gdfavoritesform<?=$rnd?>').style.display = 'none';
	}

	return false;
}

function EditMode<?=$rnd?>(flag)
{
	if(flag)
	{
		document.getElementById('gdfavoriteslink<?=$rnd?>').style.display = 'none';
		document.getElementById('gdfavoriteslink2<?=$rnd?>').style.display = 'block';
		var css = 'inline';
	}
	else
	{
		document.getElementById('gdfavoriteslink<?=$rnd?>').style.display = 'block';
		document.getElementById('gdfavoriteslink2<?=$rnd?>').style.display = 'none';
		var css = 'none';
	}

	var head = document.getElementsByTagName("HEAD");
	if(head)
	{
		var style = document.createElement("STYLE");
		head[0].appendChild(style);
		if(jsUtils.IsIE())
			document.styleSheets[document.styleSheets.length-1].cssText = '.gdfavdellink {display: '+css+';}';
		else if(document.getElementsByClassName)
		{
			var arEls = document.getElementsByClassName("gdfavdellink");
			for(var el=0; el<arEls.length; el++)
				arEls[el].style.display = css;
		}
		else
			style.appendChild(document.createTextNode('.gdfavdellink {display: '+css+';}'));
	}

	return false;
}

function Del<?=$rnd?>(id)
{
	var frm = document.getElementById("gdfavoritesformdel<?=$rnd?>");
	frm['gdfvdel'].value = id;
	frm.submit();
	return false;
}
</script>
<form action="<?=$arParams["UPD_URL"]?>" method="post" id="gdfavoritesformdel<?=$rnd?>">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="gdfavorites" value="Y">
	<input type="hidden" name="gdfav" value="<?=$id?>">
	<input type="hidden" name="gdfvdel" value="">
</form>



<div class="gdfavlinks">
<?foreach($arGadget["USERDATA"]["LINKS"] as $i=>$linkParam):?>
	<div class="gdfavlink">
<?
	if(!preg_match("'^(http://|https://|ftp://|/)'i", $linkParam["URL"]))
		$linkParam["URL"] = 'http://'.$linkParam["URL"];
?>
		<span class="gdfavlinka">&raquo; <a href="<?=htmlspecialcharsbx($linkParam["URL"])?>"><?=htmlspecialcharsbx(($linkParam["NAME"]!=''?$linkParam["NAME"]:$linkParam["URL"]))?></a> </span>
		<a class="gdfavdellink" href="javascript:void(0)" onclick="return Del<?=$rnd?>('<?=$i?>')"><?echo GetMessage("GD_FAVORITES_DEL")?></a><br>
	</div>
<?endforeach?>
</div>

<div id="gdfavoriteslink<?=$rnd?>" class="gdfavaddlink"><a href="javascript:void(0)" onclick="return ShowHide<?=$rnd?>(true);"><?echo GetMessage("GD_FAVORITES_ADD")?></a>
| <a href="javascript:void(0)" onclick="return EditMode<?=$rnd?>(true);"><?echo GetMessage("GD_FAVORITES_CH")?></a>
</div>

<div id="gdfavoriteslink2<?=$rnd?>" style="display: none;" class="gdfavaddlink"><a href="javascript:void(0)" onclick="return EditMode<?=$rnd?>(false);"><?echo GetMessage("GD_FAVORITES_CH_EXIT")?></a> </div>

<div id="gdfavoritesform<?=$rnd?>" style="display: none;" class="gdfavoritesform">
<form action="<?=$arParams["UPD_URL"]?>" method="post">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="gdfavorites" value="Y">
	<input type="hidden" name="gdfav" value="<?=$id?>">
	<table cellpadding="0" cellspacing="0" border="0"  class="gdfvaddtable">
		<tr><td colspan="2" class="gdfvaddheader"><?echo GetMessage("GD_FAVORITES_NEW_URL")?></td></tr>
		<tr><td class="gdfvaddleft"><?echo GetMessage("GD_FAVORITES_URL")?></td><td class="gdfvaddright"><input type="text" value="http://" name="url"></td></tr>
		<tr><td class="gdfvaddleft"><?echo GetMessage("GD_FAVORITES_NAME")?></td><td class="gdfvaddright"><input type="text" name="name"></td></tr>
		<tr><td class="gdfvaddleft">&nbsp;</td><td class="gdfvaddright"><input type="submit" name="gdfvadd" value="<?echo GetMessage("GD_FAVORITES_ADD_URL")?>"><input type="button" value="<?echo GetMessage("GD_FAVORITES_CANCEL_URL")?>" onclick="ShowHide<?=$rnd?>(false);"></td></tr>
	</table>
</form>
</div>
<?
}
else
{
?>
<div class="gdfavlinks">
<?foreach($arGadget["USERDATA"]["LINKS"] as $id=>$linkParam):?>
	<div class="gdfavlink">
<?
	if(!preg_match("'^(http://|https://|ftp://)'i", $linkParam["URL"]))
		$linkParam["URL"] = 'http://'.$linkParam["URL"];
?>
		<span class="gdfavlinka">&raquo; <a href="<?=htmlspecialcharsbx($linkParam["URL"])?>"><?=htmlspecialcharsbx(($linkParam["NAME"]!=''?$linkParam["NAME"]:$linkParam["URL"]))?></a> </span><br>
	</div>
<?endforeach?>
</div>
<?

}

?>
