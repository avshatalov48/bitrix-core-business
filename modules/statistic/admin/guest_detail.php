<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if ($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$ID = intval($ID);
$guest = CGuest::GetByID($ID);
ClearVars("f_");

$APPLICATION->SetTitle(GetMessage("STAT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
?>
<table class="edit-table" cellspacing="0" cellpadding="0" border="0"><tr><td>
<table cellspacing="0" cellpadding="0" border="0" class="internal">
	<? if ($arGuest = $guest->ExtractFields("f_")) : ?>
	<tr>
		<td valign="top" nowrap>ID:</td>
		<td valign="top" width="100%">&nbsp;<span title="<?echo ($f_ID==$_SESSION["SESS_GUEST_ID"]) ? GetMessage("STAT_CURRENT_GUEST") : ""?>"><span class="<?echo ($f_ID==$_SESSION["SESS_GUEST_ID"]) ? "stat_attention" : ""?>"><?echo $f_ID?></span></span>&nbsp;</td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_SESSIONS")?></td>
		<td valign="top">&nbsp;<a target="_blank" title="<?echo GetMessage("STAT_VIEW_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_guest_id=<?echo $f_ID?>&amp;find_guest_id_exact_match=Y&amp;set_filter=Y"><?echo $f_SESSIONS?></a>&nbsp;<span class="<?echo ($f_SESSIONS>1) ? "stat_oldguest" : "stat_newguest"?>"><?echo ($f_SESSIONS>1) ? GetMessage("STAT_OLD_GUEST") : GetMessage("STAT_NEW_GUEST")?></span></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_EVENTS")?></td>
		<td valign="top">&nbsp;<a target="_blank" title="<?echo GetMessage("STAT_VIEW_EVENTS_LIST")?>" href="event_list.php?lang=<?=LANGUAGE_ID?>&find_guest_id=<?echo $f_ID?>&find_guest_id_exact_match=Y&set_filter=Y"><?echo $f_C_EVENTS?></a></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_HITS")?></td>
		<td valign="top">&nbsp;<a target="_blank" title="<?echo GetMessage("STAT_VIEW_HITS_LIST")?>" href="hit_list.php?lang=<?=LANGUAGE_ID?>&find_guest_id=<?echo $f_ID?>&find_guest_id_exact_match=Y&set_filter=Y"><?echo $f_HITS?></a></td>
	</tr>
	<tr class="heading">
		<td colspan="2" align="center"><?echo GetMessage("STAT_FIRST_ENTER")?></td>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_DATE")?></td>
		<td valign="top">&nbsp;<?echo $f_FIRST_DATE?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_SESSION_ID")?></td>
		<td valign="top">&nbsp;<a target="_blank" href="session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_id=<?echo $f_FIRST_SESSION_ID?>&amp;find_id_exact_match=Y&amp;set_filter=Y"><?echo $f_FIRST_SESSION_ID?></a></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_HITS")?></td>
		<td valign="top">&nbsp;<a target="_blank" href="hit_list.php?lang=<?=LANGUAGE_ID?>&amp;find_session_id=<?echo $f_FIRST_SESSION_ID?>&amp;find_session_id_exact_match=Y&amp;set_filter=Y"><?echo $f_FSESSION_HITS?></a></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_TIME")?></td>
		<td valign="top" nowrap>&nbsp;<?
			$hours = intval($f_FSESSION_TIME/3600);
			if ($hours>0) :
				echo $hours."&nbsp;".GetMessage("STAT_HOUR")."&nbsp;";
				$f_FSESSION_TIME = $f_FSESSION_TIME - $hours*3600;
			endif;
			echo intval($f_FSESSION_TIME/60)."&nbsp;".GetMessage("STAT_MIN")."&nbsp;";
			echo ($f_FSESSION_TIME%60)."&nbsp;".GetMessage("STAT_SEC");
			?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_FROM")?></td>
		<td valign="top">&nbsp;<?echo StatAdminListFormatURL($arGuest["FIRST_URL_FROM"], array(
			"new_window" => true,
			"chars_per_line" => 40,
			"line_delimiter" => "<wbr>",
			"kill_sessid" => $STAT_RIGHT < "W",
		))?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_TO")?></td>
		<td valign="top"><?if (strlen($f_FIRST_SITE_ID)>0):?>[<a title="<?=GetMessage("STAT_SITE")?>" href="/bitrix/admin/site_edit.php?LID=<?=$f_FIRST_SITE_ID?>&lang=<?=LANGUAGE_ID?>"><?=$f_FIRST_SITE_ID?></a>]&nbsp;<?endif;?>&nbsp;<?echo StatAdminListFormatURL($arGuest["FIRST_URL_TO"], array(
			"new_window" => true,
			"attention" => $f_FIRST_URL_TO_404=="Y",
			"chars_per_line" => 40,
			"line_delimiter" => "<wbr>",
			"kill_sessid" => $STAT_RIGHT < "W",
		))?></td>
	</tr>
	<? if (intval($f_FIRST_ADV_ID)>0) : ?>
	<tr>
		<td valign="top"><?echo GetMessage("STAT_ADV")?></td>
		<td valign="top">&nbsp;<a target="_blank" href="adv_list.php?lang=<?=LANGUAGE_ID?>&amp;find_id=<?echo $f_FIRST_ADV_ID?>&amp;find_id_exact_match=Y&amp;set_filter=Y"><?echo $f_FIRST_ADV_ID?></a> (<a target="_blank" title="<?echo GetMessage("STAT_VIEW_SESSIONS_LIST_BY_REF_1")?>" href="session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_referer1=<?echo $f_FIRST_REFERER1?>&amp;find_referer12_exact_match=Y&amp;set_filter=Y"><?echo $f_FIRST_REFERER1?></a> / <a target="_blank" title="<?echo GetMessage("STAT_VIEW_SESSIONS_LIST_BY_REF_2")?>" href="session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_referer2=<?echo $f_FIRST_REFERER2?>&amp;find_referer12_exact_match=Y&amp;set_filter=Y"><?echo $f_FIRST_REFERER2?></a> / <a target="_blank" title="<?echo GetMessage("STAT_VIEW_SESSIONS_LIST_BY_REF_3")?>" href="session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_referer3=<?echo $f_FIRST_REFERER3?>&amp;find_referer3_exact_match=Y&amp;set_filter=Y"><?echo $f_FIRST_REFERER3?></a> )</td>
	</tr>
	<? endif; ?>
	<tr class="heading">
		<td colspan="2" align="center"><?echo GetMessage("STAT_LAST_ENTER")?></td>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_DATE")?></td>
		<td valign="top">&nbsp;<?echo $f_LAST_DATE?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_SESSION_ID")?></td>
		<td valign="top"><a target="_blank" href="session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_id=<?echo $f_LAST_SESSION_ID?>&amp;find_id_exact_match=Y&amp;set_filter=Y"><?echo $f_LAST_SESSION_ID?></a></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_HITS")?></td>
		<td valign="top">&nbsp;<a target="_blank" href="hit_list.php?lang=<?=LANGUAGE_ID?>&amp;find_session_id=<?echo $f_LAST_SESSION_ID?>&amp;find_session_id_exact_match=Y&amp;set_filter=Y"><?echo $f_LSESSION_HITS?></a></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_TIME")?></td>
		<td valign="top" nowrap>&nbsp;<?
			$hours = intval($f_LSESSION_TIME/3600);
			if ($hours>0) :
				echo $hours."&nbsp;".GetMessage("STAT_HOUR")."&nbsp;";
				$f_LSESSION_TIME = $f_LSESSION_TIME - $hours*3600;
			endif;
			echo intval($f_LSESSION_TIME/60)."&nbsp;".GetMessage("STAT_MIN")."&nbsp;";
			echo ($f_LSESSION_TIME%60)."&nbsp;".GetMessage("STAT_SEC");
			?></td>
	</tr>
	<? if (intval($f_LAST_USER_ID)>0) : ?>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_USER")?></td>
		<td valign="top">[<a target="_blank" title="<?echo GetMessage("STAT_EDIT_USER")?>" href="user_edit.php?lang=<?=LANGUAGE_ID?>&amp;ID=<?echo $f_LAST_USER_ID?>"><?echo $f_LAST_USER_ID?></a>]&nbsp;(<?echo $f_LOGIN?>)&nbsp;<?echo $f_USER_NAME?><? echo ($f_LAST_USER_AUTH!="Y") ? "&nbsp;<span class=\"stat_notauth\">".GetMessage("STAT_NOT_AUTH")."</span>" : "";?></td>
	</tr>
	<? endif; ?>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_LAST_PAGE")?></td>
		<td valign="top"><?if (strlen($f_LAST_SITE_ID)>0):?>[<a title="<?=GetMessage("STAT_SITE")?>" href="/bitrix/admin/site_edit.php?LID=<?=$f_LAST_SITE_ID?>&amp;lang=<?=LANGUAGE_ID?>"><?=$f_LAST_SITE_ID?></a>]&nbsp;<?endif;?>&nbsp;<?echo StatAdminListFormatURL($arGuest["LAST_URL_LAST"], array(
			"new_window" => true,
			"attention" => $f_LAST_URL_LAST_404=="Y",
			"chars_per_line" => 40,
			"line_delimiter" => "<wbr>",
			"kill_sessid" => $STAT_RIGHT < "W",
		))?></td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_IP")?></td>
		<td>&nbsp;<? $arr = explode(".",$f_LAST_IP) ?><?=GetWhoisLink($f_LAST_IP)?>&nbsp;[<a target="_blank" title="<?echo GetMessage("STAT_ADD_TO_STOPLIST_TITLE")?>" href="stoplist_edit.php?lang=<?=LANGUAGE_ID?>&amp;net1=<?echo $arr[0]?>&amp;net2=<?echo $arr[1]?>&amp;net3=<?echo $arr[2]?>&amp;net4=<?echo $arr[3]?>"><?echo GetMessage("STAT_STOP")?></a>]&nbsp;(<?echo @gethostbyaddr($f_LAST_IP)?>)</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_REGION")?>:</td>
		<td>&nbsp;<?echo $f_REGION_NAME?></td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_COUNTRY")?>:</td>
		<td>&nbsp;<?
		if (strlen($f_LAST_COUNTRY_ID)>0) :
		?><?echo "[".$f_LAST_COUNTRY_ID."] ".$f_COUNTRY_NAME?><?
		endif;
		?></td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STAT_CITY")?>:</td>
		<td>
			<?if (strlen($f_LAST_CITY_INFO) > 0):?>
				<table cellpadding="1" cellspacing="1" border="0">
				<?
				$obCity = new CCity($arGuest["LAST_CITY_INFO"]);
				$arCity = $obCity->GetFullInfo();
				foreach($arCity as $FIELD_ID => $arField):?>
					<tr><td><?echo $arField["TITLE"]?>:</td><td>&nbsp;<?echo $arField["VALUE"]?></td></tr>
				<?endforeach?>
				</table>
			<?elseif (strlen($f_LAST_CITY_ID) > 0):?>
				<?echo "[".$f_LAST_CITY_ID."] ".$f_CITY_NAME?>
			<?endif;?>
			&nbsp;
		</td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_USER_AGENT")?></td>
		<td valign="top">&nbsp;<?echo $f_LAST_USER_AGENT?></td>
	</tr>
	<? if (intval($f_LAST_ADV_ID)>0) : ?>
	<tr>
		<td valign="top"><?echo GetMessage("STAT_ADV")?></td>
		<td valign="top">&nbsp;<a target="_blank" href="adv_list.php?lang=<?=LANGUAGE_ID?>&amp;find_id=<?echo $f_LAST_ADV_ID?>&amp;find_id_exact_match=Y&amp;set_filter=Y"><?echo $f_LAST_ADV_ID?></a><?if ($f_LAST_ADV_BACK=="Y") echo "<font class=\"star\">*"?> (<a target="_blank" title="<?echo GetMessage("STAT_VIEW_SESSIONS_LIST_BY_REF_1")?>" href="session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_referer1=<?echo $f_LAST_REFERER1?>&amp;find_referer12_exact_match=Y&amp;set_filter=Y"><?echo $f_LAST_REFERER1?></a> / <a target="_blank" title="<?echo GetMessage("STAT_VIEW_SESSIONS_LIST_BY_REF_2")?>" href="session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_referer2=<?echo $f_LAST_REFERER2?>&amp;find_referer12_exact_match=Y&amp;set_filter=Y"><?echo $f_LAST_REFERER2?></a> / <a target="_blank" title="<?echo GetMessage("STAT_VIEW_SESSIONS_LIST_BY_REF_3")?>" href="session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_referer3=<?echo $f_LAST_REFERER3?>&amp;find_referer3_exact_match=Y&amp;set_filter=Y"><?echo $f_LAST_REFERER3?></a> )</td>
	</tr>
	<? endif; ?>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_LANGUAGE")?></td>
		<td valign="top">&nbsp;<?echo htmlspecialcharsEx(urldecode($f_LAST_LANGUAGE))?></td>
	</tr>
	<?if($USER->IsAdmin()):?>
	<tr>
		<td valign="top" nowrap>
			<?echo GetMessage("STAT_COOKIE")?></td>
		<td>
			<?if($f_LAST_COOKIE):?>
				<div style="overflow:auto;"><?echo str_replace("\n", "<br>", htmlspecialcharsEx(urldecode($f_LAST_COOKIE)))?></div>
			<?else:?>
				&nbsp;
			<?endif?>
		</td>
	</tr>
	<?endif?>

	<? else : ?>
	<tr>
		<td>
			<?echo GetMessage("STAT_NOT_FOUND")?></td>
	</tr>
	<? endif; ?>
</table></td></tr></table>
<?echo BeginNote(), "* - ", GetMessage("STAT_ADV_BACK"), EndNote();?>
<input type="button" onClick="window.close()" value="<?echo GetMessage("STAT_CLOSE")?>">
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
