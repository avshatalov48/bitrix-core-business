<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
?>
<div class="bx-interface-toolbar">
<table cellpadding="0" cellspacing="0" border="0" class="bx-interface-toolbar">
	<tr class="bx-top">
		<td class="bx-left"><div class="empty"></div></td>
		<td><div class="empty"></div></td>
		<td class="bx-right"><div class="empty"></div></td>
	</tr>
	<tr>
		<td class="bx-left"><div class="empty"></div></td>
		<td class="bx-content">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
				<td><div class="bx-section-separator bx-first"></div></td>
<?
$bWasSeparator = true;
foreach($arParams["BUTTONS"] as $index=>$item):
	if(!empty($item["NEWBAR"])):
?>
				</tr>
			</table>
		</td>
		<td class="bx-right"><div class="empty"></div></td>
	</tr>
	<tr class="bx-bottom">
		<td class="bx-left"><div class="empty"></div></td>
		<td><div class="empty"></div></td>
		<td class="bx-right"><div class="empty"></div></td>
	</tr>
	<tr class="bx-top">
		<td class="bx-left"><div class="empty"></div></td>
		<td><div class="empty"></div></td>
		<td class="bx-right"><div class="empty"></div></td>
	</tr>
	<tr>
		<td class="bx-left"><div class="empty"></div></td>
		<td class="bx-content">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
				<td><div class="bx-section-separator bx-first"></div></td>
<?
		$bWasSeparator = true;
		continue;
	endif;

	if(!empty($item["SEPARATOR"])):
?>
				<td><div class="bx-section-separator"></div></td>
<?
		$bWasSeparator = true;
	else:
		if(!$bWasSeparator):
?>
				<td><div class="bx-separator"></div></td>
<?
		endif;
		if(!empty($item["MENU"])):
?>
			<td>
				<script type="text/javascript">
				var jsMnu_<?=$arParams["TOOLBAR_ID"].'_'.$index?> = <?=CUtil::PhpToJSObject($item["MENU"])?>;
				</script>
				<a href="javascript:void(0);" hidefocus="true" 
					onclick="this.blur(); jsPopup_<?=$arParams["TOOLBAR_ID"]?>.ShowMenu(this, jsMnu_<?=$arParams["TOOLBAR_ID"].'_'.$index?>); return false;" 
					title="<?=$item["TITLE"]?>" class="bx-context-button<?=(!empty($item["ICON"])? ' bx-icon '.$item["ICON"]:'')?>"><?=$item["TEXT"]?><img src="<?=$this->GetFolder()?>/images/arr_down.gif" class="bx-arrow" alt=""></a></td>
<?		
		elseif($item["HTML"] <> ""):
?>
				<td><?=$item["HTML"]?></td>
<?
		else:
?>
				<td><a href="<?=$item["LINK"]?>" hidefocus="true" title="<?=$item["TITLE"]?>" <?=$item["LINK_PARAM"]?> class="bx-context-button<?=(!empty($item["ICON"])? ' bx-icon '.$item["ICON"]:'')?>"><?=$item["TEXT"]?></a></td>
<?
		endif;
		$bWasSeparator = false;
	endif;
endforeach;
?>
				</tr>
			</table>
		</td>
		<td class="bx-right"><div class="empty"></div></td>
	</tr>
	<tr class="bx-bottom">
		<td class="bx-left"><div class="empty"></div></td>
		<td><div class="empty"></div></td>
		<td class="bx-right"><div class="empty"></div></td>
	</tr>

	<tr class="bx-bottom-all">
		<td class="bx-left"><div class="empty"></div></td>
		<td><div class="empty"></div></td>
		<td class="bx-right"><div class="empty"></div></td>
	</tr>
</table>

<script type="text/javascript">
var jsPopup_<?=$arParams["TOOLBAR_ID"]?> = new PopupMenu('Popup<?=$arParams["TOOLBAR_ID"]?>');
</script>

</div>
