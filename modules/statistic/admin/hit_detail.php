<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if ($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$ID = intval($ID);
$hit = CHit::GetByID($ID);
ClearVars("f_");

$APPLICATION->SetTitle(GetMessage("STAT_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php")
?>
<table class="edit-table" cellspacing="0" cellpadding="0" border="0"><tr><td>
<table cellspacing="0" cellpadding="0" border="0" class="internal">
	<? if ($arRes = $hit->ExtractFields("f_")) : ?>
	<tr>
		<td valign="top" nowrap>ID:</td>
		<td>
			<?echo $f_ID?><?
			if (intval($f_STOP_LIST_ID)>0) :
			?>&nbsp;[&nbsp;<a target="_blank" href="stoplist_list.php?lang=<?=LANGUAGE_ID?>&amp;find_id=<?=intval($f_STOP_LIST_ID)?>&amp;find_id_exact_match=Y&amp;set_filter=Y"><span class="stat_attention"><b><?=GetMessage("STAT_STOPED")?></b></span></a>&nbsp;]<?
			endif;
			?>
		</td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_SESSION_ID")?></td>
		<td>
			<a target="_blank" href="session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_id=<?echo $f_SESSION_ID?>&amp;find_id_exact_match=Y&amp;set_filter=Y"><?echo $f_SESSION_ID?></a>
		</td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_TIME")?></td>
		<td><?echo $f_DATE_HIT?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_REFERER")?></td>
		<td><?echo StatAdminListFormatURL($arRes["URL_FROM"], array(
			"new_window" => true,
			"chars_per_line" => 40,
			"line_delimiter" => "<wbr>",
			"kill_sessid" => $STAT_RIGHT < "W",
		))?>&nbsp;</td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_PAGE")?></td>
		<td><?if (strlen($f_SITE_ID)>0):?>[<a title="<?=GetMessage("STAT_SITE")?>" href="/bitrix/admin/site_edit.php?LID=<?=$f_SITE_ID?>&amp;lang=<?=LANGUAGE_ID?>"><?=$f_SITE_ID?></a>]&nbsp;<?endif;?><?echo StatAdminListFormatURL($arRes["URL"], array(
			"new_window" => true,
			"attention" => $f_URL_404=="Y",
			"chars_per_line" => 40,
			"line_delimiter" => "<wbr>",
			"kill_sessid" => $STAT_RIGHT < "W",
		))?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_USER")?></td>
		<td><?
			if ($f_USER_ID>0) :
				echo "[<a target=\"_blank\" title=\"".GetMessage("STAT_EDIT_USER")."\" href=\"user_edit.php?lang=".LANG."&amp;ID=".$f_USER_ID."\">$f_USER_ID</a>] ($f_LOGIN) $f_USER_NAME";
				echo ($f_USER_AUTH!="Y") ? "<span class=\"stat_notauth\"> ".GetMessage("STAT_NOT_AUTH")."</span>" : "";
			else :
				echo "<font class=\"tablebodytext\">".GetMessage("STAT_NOT_REGISTERED")."";
			endif;
			?>&nbsp;<? echo ($f_NEW_GUEST=="Y") ? "<font class=\"newguest\">".GetMessage("STAT_NEW_GUEST")."&nbsp;" : "<span class=\"stat_oldguest\">".GetMessage("STAT_OLD_GUEST")."</span>&nbsp;";
			echo "[<a title=\"".GetMessage("STAT_VIEW_SESSION_LIST")."\" target=\"_blank\" href=\"session_list.php?lang=".LANG."&amp;find_guest_id=".$f_GUEST_ID."&amp;find_guest_id_exact_match=Y&amp;set_filter=Y\">".$f_GUEST_ID."</a>]";
			?>
		</td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_IP")?></td>
		<td>
			<? $arr = explode(".",$f_IP) ?><?=GetWhoisLink($f_IP)?>&nbsp;[<a target="_blank" title="<?echo GetMessage("STAT_ADD_TO_STOPLIST_TITLE")?>" href="stoplist_edit.php?lang=<?=LANGUAGE_ID?>&amp;net1=<?echo $arr[0]?>&amp;net2=<?echo $arr[1]?>&amp;net3=<?echo $arr[2]?>&amp;net4=<?echo $arr[3]?>"><?echo GetMessage("STAT_STOP")?></a>]
		</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_COUNTRY")?></td>
		<td><?
			if (strlen($f_COUNTRY_ID)>0):
			?><?echo "[".$f_COUNTRY_ID."] ".$f_COUNTRY_NAME?><?
			endif;
			?>
		</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_CITY")?></td>
		<td>	<?if(strlen($f_CITY_ID) > 0):?>
				<?echo "[".$f_CITY_ID."] ".$f_CITY_NAME?>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_REGION")?>:</td>
		<td>&nbsp;<?echo $f_REGION_NAME?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_USER_AGENT")?></td>
		<td><?echo $f_USER_AGENT?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_METHOD")?></td>
		<td><?echo $f_METHOD?></td>
	</tr>
	<?if($USER->IsAdmin()):?>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_COOKIES")?></td>
		<td>
			<?if($f_COOKIES):?>
				<div style="overflow:auto;"><?echo str_replace("\n", "<br>", htmlspecialcharsEx(urldecode($f_COOKIES)))?></div>
			<?else:?>
				&nbsp;
			<?endif?>
		</td>
	</tr>
	<?endif?>
	<?else:?>
	<tr>
		<td><?echo GetMessage("STAT_NOT_FOUND")?></td>
	</tr>
	<?endif?>
</table></td></tr></table>
<input type="button" onClick="window.close()" value="<?echo GetMessage("STAT_CLOSE")?>">

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
