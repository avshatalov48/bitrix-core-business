<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$ID = intval($ID);
$session = CSession::GetByID($ID);
ClearVars("f_");

$APPLICATION->SetTitle(GetMessage("STAT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php")
?>
<table class="edit-table" cellspacing="0" cellpadding="0" border="0"><tr><td>
<table cellspacing="0" cellpadding="0" border="0" class="internal">
	<? if ($arRes = $session->ExtractFields("f_")) : ?>
	<tr>
		<td nowrap><?echo GetMessage("STAT_ID")?></td>
		<td width="100%"><?
			if ($_SESSION["SESS_SESSION_ID"]==$f_ID) :
				echo "<span class=\"stat_attention\">$f_ID</span>";
			else :
				echo $f_ID;
			endif;
			?><?

			if (intval($f_STOP_LIST_ID)>0) :
			?>&nbsp;[&nbsp;<a target="_blank" href="stoplist_list.php?lang=<?=LANGUAGE_ID?>&find_id=<?=intval($f_STOP_LIST_ID)?>&find_id_exact_match=Y&set_filter=Y"><b><?=GetMessage("STAT_STOPED")?></b></a>&nbsp]<?
			endif;
			?></td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_NUM_PAGES")?></td>
		<td>&nbsp;<a target="_blank" title="<? echo GetMessage("STAT_VIEW_HITS_LIST_2")?>" href="hit_list.php?lang=<?=LANGUAGE_ID?>&find_session_id=<?echo $f_ID?>&find_session_id_exact_match=Y&set_filter=Y&rand=<?echo rand()?>"><?echo $f_HITS?></a></td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_DATE_FIRST")?></td>
		<td nowrap>&nbsp;<?=$f_DATE_FIRST?></td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_DATE_LAST")?></td>
		<td nowrap>&nbsp;<?=$f_DATE_LAST?></td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_TIME")?></td>
		<td nowrap>&nbsp;<?
			$hours = intval($f_SESSION_TIME/3600);
			if ($hours>0) :
				echo $hours."&nbsp;".GetMessage("STAT_HOUR")."&nbsp;";
				$f_SESSION_TIME = $f_SESSION_TIME - $hours*3600;
			endif;
			echo intval($f_SESSION_TIME/60)."&nbsp;".GetMessage("STAT_MIN");
			echo ($f_SESSION_TIME%60)."&nbsp;".GetMessage("STAT_SEC");
			?></td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_USER")?></td>
		<td>&nbsp;<?
			if ($f_USER_ID>0) :
				echo "[<a target=\"_blank\" title=\"".GetMessage("STAT_EDIT_USER")."\" href=\"user_edit.php?lang=".LANG."&ID=".$f_USER_ID."\">$f_USER_ID</a>] ($f_LOGIN) $f_USER_NAME";
				echo ($f_USER_AUTH!="Y") ? "&nbsp;<span class=\"stat_notauth\">".GetMessage("STAT_NOT_AUTH")."</span>" : "";
			else :
				echo "".GetMessage("STAT_NOT_REGISTERED")."";
			endif;
			?>&nbsp;<?
			echo "[<a target=\"_blank\" title=\"".GetMessage("STAT_VIEW_HITS_LIST_1")."\" href=\"hit_list.php?lang=".LANG."&find_guest_id=".$f_GUEST_ID."&find_guest_id_exact_match=Y&set_filter=Y\">".$f_GUEST_ID."</a>]&nbsp;";

			echo ($f_NEW_GUEST=="Y") ? "<span class=\"stat_newguest\">".GetMessage("STAT_NEW_GUEST")."</span>" : "<span class=\"stat_oldguest\">".GetMessage("STAT_OLD_GUEST")."</span>";

			?>&nbsp;</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_IP_FIRST")?></td>
		<td>&nbsp;<? $arr = explode(".",$f_IP_FIRST) ?><?=GetWhoisLink($f_IP_FIRST)?>&nbsp;[<a target="_blank" title="<?echo GetMessage("STAT_ADD_TO_STOPLIST_TITLE")?>" href="stoplist_edit.php?lang=<?=LANGUAGE_ID?>&amp;net1=<?echo $arr[0]?>&amp;net2=<?echo $arr[1]?>&amp;net3=<?echo $arr[2]?>&amp;net4=<?echo $arr[3]?>"><?echo GetMessage("STAT_STOP")?></a>]</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_IP_LAST")?></td>
		<td>&nbsp;<? $arr = explode(".",$f_IP_LAST) ?><?=GetWhoisLink($f_IP_LAST)?>&nbsp;[<a target="_blank" title="<?echo GetMessage("STAT_ADD_TO_STOPLIST_TITLE")?>" href="stoplist_edit.php?lang=<?=LANGUAGE_ID?>&amp;net1=<?echo $arr[0]?>&amp;net2=<?echo $arr[1]?>&amp;net3=<?echo $arr[2]?>&amp;net4=<?echo $arr[3]?>"><?echo GetMessage("STAT_STOP")?></a>]</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_COUNTRY")?>:</td>
		<td>&nbsp;<?
			if ($f_COUNTRY_ID <> ''):
			?><?echo "[".$f_COUNTRY_ID."] ".$f_COUNTRY_NAME?><?
			endif;
			?></td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_REGION")?>:</td>
		<td>&nbsp;<?echo $f_REGION_NAME?></td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_CITY")?>:</td>
		<td>&nbsp;<?
			if ($f_CITY_ID <> ''):
			?><?echo "[".$f_CITY_ID."] ".$f_CITY_NAME?><?
			endif;
			?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_REFERER")?></td>
		<td>&nbsp;<?echo StatAdminListFormatURL($arRes["URL_FROM"], array(
			"new_window" => true,
			"chars_per_line" => 40,
			"line_delimiter" => "<wbr>",
			"kill_sessid" => $STAT_RIGHT < "W",
		))?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_URL_TO")?></td>
		<td><?if ($f_FIRST_SITE_ID <> ''):?>[<a title="<?=GetMessage("STAT_SITE")?>" href="/bitrix/admin/site_edit.php?LID=<?=$f_FIRST_SITE_ID?>&lang=<?=LANGUAGE_ID?>"><?=$f_FIRST_SITE_ID?></a>]&nbsp;<?endif;?>&nbsp;<?echo StatAdminListFormatURL($arRes["URL_TO"], array(
			"new_window" => true,
			"attention" => $f_URL_TO_404=="Y",
			"chars_per_line" => 40,
			"line_delimiter" => "<wbr>",
			"kill_sessid" => $STAT_RIGHT < "W",
		))?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_LAST_PAGE")?></td>
		<td><?if ($f_LAST_SITE_ID <> ''):?>[<a title="<?=GetMessage("STAT_SITE")?>" href="/bitrix/admin/site_edit.php?LID=<?=$f_LAST_SITE_ID?>&lang=<?=LANGUAGE_ID?>"><?=$f_LAST_SITE_ID?></a>]&nbsp;<?endif;?>&nbsp;<?echo StatAdminListFormatURL($arRes["URL_LAST"], array(
			"new_window" => true,
			"attention" => $f_URL_LAST_404=="Y",
			"chars_per_line" => 40,
			"line_delimiter" => "<wbr>",
			"kill_sessid" => $STAT_RIGHT < "W",
		))?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_ADV")?></td>
		<td>&nbsp;<? if (intval($f_ADV_ID)>0) :
			?><a href="adv_list.php?lang=<?=LANGUAGE_ID?>&find_id_exact_match=Y&find_id=<?echo $f_ADV_ID?>&set_filter=Y"><?echo $f_ADV_ID?></a><?if ($f_ADV_BACK=="Y") echo "*"?>&nbsp;(<?
			echo $f_REFERER1." / ".$f_REFERER2." / ".$f_REFERER3;
			?>)<?
			endif;
			?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_USER_AGENT")?></td>
		<td valign="top">&nbsp;<?echo $f_USER_AGENT?></td>
	<? else : ?>
	<tr>
		<td><?echo GetMessage("STAT_NOT_FOUND")?></td>
	</tr>
	<? endif; ?>
</table></td></tr></table>
<input type="button" onClick="window.close()" value="<?echo GetMessage("STAT_CLOSE")?>">
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
