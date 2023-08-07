<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/
define("STOP_STATISTICS", "Y");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

ClearVars();

$TICKET_ID = intval($TICKET_ID);
$url = $APPLICATION->GetCurPage()."?TICKET_ID=".$TICKET_ID."&OWNER_USER_ID=".intval($OWNER_USER_ID)."&lang=".LANGUAGE_ID ."&ONLINE_AUTO_REFRESH=".intval($ONLINE_AUTO_REFRESH);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");?>
<?
$lamp = CTicket::GetStatus($TICKET_ID);
$ticket = CTicket::GetByID($TICKET_ID);
$arTicket = $ticket->ExtractFields();
?>
<table cellspacing=0 cellpadding=0 class="support-online">
	<?
	$mode = strlen($mode)>0 ? $mode : false;
	CTicket::UpdateOnline($TICKET_ID, false, $mode);
	$rs = CTicket::GetOnline($TICKET_ID);
	while ($ar = $rs->GetNext()) :
		$is_support = "";
		if (intval($OWNER_USER_ID)==$ar["USER_ID"]) $is_support = "N";
		elseif(CTicket::IsSupportTeam($ar["USER_ID"]) || CTicket::IsAdmin($ar["USER_ID"]) || CTicket::IsDemo($ar["USER_ID"])) $is_support = "Y";
	?>
	<tr>
		<td valign="top" width="16%"><?
			if ($is_support=="Y"):
				if ($ar["CURRENT_MODE"]=="edit"):
				?><img src="/bitrix/images/support/sup_write.gif" width="15" height="13" border="0" alt="<?=GetMessage("SUP_EDIT_MODE_ALT")?>"><?
				else:
				?><img src="/bitrix/images/support/sup_view.gif" width="17" height="13" border="0" alt="<?=GetMessage("SUP_VIEW_MODE_ALT")?>"><?
				endif;
			elseif ($is_support=="N"):
				?><img src="/bitrix/images/support/client.gif" width="11" height="13" border="0" alt="<?=GetMessage("SUP_CLIENT_MODE_ALT")?>"><?
			endif;
			?></font></td>

		<td width="84%" valign="top"><?
			if (strlen(trim($ar["USER_NAME"]))>0):
				?><a title="<?=GetMessage("SUP_USER_PROFILE")?>" target="_blank" href="/bitrix/admin/user_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$ar["USER_ID"]?>"><?=$ar["USER_NAME"]?></a><?
			else:
				?><a title="<?=GetMessage("SUP_USER_PROFILE")?>" target="_blank" href="/bitrix/admin/user_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$ar["USER_ID"]?>"><?=$ar["USER_LOGIN"]?></a>
				<?
			endif;
			?></td>
	</tr>
	<?endwhile;?>
</table>
<?
if (strlen($lamp)>0) 
{
	$lamp_alt = GetMessage("SUP_".strtoupper($lamp)."_ALT");
	//$lamp = "/bitrix/images/support/$lamp.gif";?>
<script type="text/javascript">
<!--
document.body.className='support-online-body';

<?if (intval($ONLINE_AUTO_REFRESH)>0):?>
setTimeout(function(){window.location='<?=CUtil::JSEscape($url)?>'},<?=intval($ONLINE_AUTO_REFRESH)?>000 );
<?endif?>

//-->
</script>
<table cellspacing=0 cellpadding=0 class="support-online">
	<tr>
		<td nowrap><?=GetMessage("SUP_TICKET_STATUS")?>:</td>
		<td width="100%"><div class="lamp-<?=str_replace("_","-",$lamp)?>" title="<?=$lamp_alt?>"></td>
	</tr>
	</table>
<?}?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");?>