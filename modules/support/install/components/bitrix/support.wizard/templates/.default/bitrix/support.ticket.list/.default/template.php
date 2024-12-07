<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
$bADS = $bDemo == 'Y' || $bAdmin == 'Y' || $bSupportTeam == 'Y';

?>

<script>BX.loadCSS('<? echo CUtil::JSEscape( $this->GetFolder() ); ?>/style.css');</script>

<a href="<?=$APPLICATION->GetCurPage()."?show_wizard=Y"?>"><?=GetMessage("SUP_ASK")?></a>

<br />
<br />


<?

$APPLICATION->IncludeComponent(
	"bitrix:main.interface.grid",
	"",
	array(
		"GRID_ID"=>$arResult["GRID_ID"],
		"HEADERS"=>array(
			array("id"=>"LAMP", "name"=> GetMessage('SUP_LAMP'), "sort"=>"s_lamp", "default"=>true),
			array("id"=>"ID", "name"=>GetMessage('SUP_ID'), "sort"=>"s_id", "default"=>true),
			array("id"=>"TITLE", "name"=>GetMessage('SUP_TITLE'), "default"=>true),
			array("id"=>"TIMESTAMP_X", "name"=>GetMessage('SUP_TIMESTAMP'), "sort"=>"s_timestamp_x", "default"=>true),
			array("id"=>"MODIFIED_BY", "name"=>GetMessage('SUP_MODIFIED_BY'), "default"=>true),
			array("id"=>"MESSAGES", "name"=>GetMessage('SUP_MESSAGES'),  "default"=>true),
			array("id"=>"STATUS_NAME", "name"=>GetMessage('SUP_STATUS'), "default"=>true)
		),
		"SORT"=>$arResult["SORT"],
		"SORT_VARS"=>$arResult["SORT_VARS"],
		"ROWS"=>$arResult["ROWS"],
		"FOOTER"=>array(array("title"=>GetMessage('SUP_TOTAL'), "value"=>$arResult["ROWS_COUNT"])),
		"ACTION_ALL_ROWS"=>true,
		"EDITABLE"=>false,
		"NAV_OBJECT"=>$arResult["NAV_OBJECT"],
		//"AJAX_MODE"=>$arParams["AJAX_MODE"],
		"AJAX_ID"=>$arParams["AJAX_ID"],
		//"AJAX_OPTION_JUMP"=>"N",
		//"AJAX_OPTION_STYLE"=>"Y",
		"FILTER"=>$arResult["FILTER"],
	),
	$component
);
?>


<br />
<table class="support-ticket-hint">
	<tr>
		<td><div class="support-lamp-red"></div></td>
		<td> - <?=$bADS ? GetMessage("SUP_RED_ALT_SUP") : GetMessage("SUP_RED_ALT_2")?></td>
	</tr>
	<?if ($bADS):?>
		<tr>
			<td><div class="support-lamp-yellow"></div></td>
			<td> - <?=GetMessage("SUP_YELLOW_ALT_SUP")?></td>
		</tr>
	<?endif;?>
	<tr>
		<td><div class="support-lamp-green"></div></td>
		<td> - <?=GetMessage("SUP_GREEN_ALT")?></td>
	</tr>
	<?if ($bADS):?>
		<tr>
			<td><div class="support-lamp-green-s"></div></td>
			<td> - <?=GetMessage("SUP_GREEN_S_ALT_SUP")?></td>
		</tr>
	<?endif;?>
	<tr>
		<td><div class="support-lamp-grey"></div></td>
		<td> - <?=GetMessage("SUP_GREY_ALT")?></td>
	</tr>
</table>