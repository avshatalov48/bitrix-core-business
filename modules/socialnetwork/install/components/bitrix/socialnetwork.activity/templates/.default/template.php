<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (strlen($arResult["FatalError"])>0)
{
	?>
	<span class='errortext'><?=$arResult["FatalError"]?></span><br /><br />
	<?
}
else
{
	if(strlen($arResult["ErrorMessage"])>0)
	{
		?>
		<span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br />
		<?
	}
	?>
	<script language="JavaScript">
	<!--
		function SoNetSwitchBody(ind, val)
		{
			var el = document.getElementById("sonet_message_" + ind);
			if (el)
			{
				if (val)
					el.style.display = "block";
				else
					el.style.display = "none";
			}
			el = document.getElementById("sonet_message_link_" + ind);
			if (el)
			{
				if (val)
					el.style.display = "none";
				else
					el.style.display = "block";
			}
		}
	//-->
	</script>

	<form method="POST" name="log_filter">
	<select name="flt_event_id" onChange="javascript:document.log_filter.submit()">
		<?
		foreach ($arResult["Features"] as $featureID)
		{
			$featureName = GetMessage(toUpper("SONET_ACTIVITY_T_".$featureID));
			?><option value="<?=$featureID?>" <?=($featureID == $_REQUEST["flt_event_id"] ? "selected" : "")?>><?=$featureName?></option><?
		}
		?>
	</select>
	</form>
	<br><br>
	<?
	
	if ($arResult["Events"] && is_array($arResult["Events"]) && count($arResult["Events"]) > 0)
	{
		$ind = 0;
		foreach ($arResult["Events"] as $date => $arEvents)
		{
			?>
			<h4><?= $date ?></h4>
			<?
			$bFirst = true;
			foreach ($arEvents as $arEvent)
			{
				if (!$bFirst)
				{
					?><div class="sonet-profile-line"></div><?
				}
				?>
				<span class="sonet-log-date"><?=$arEvent["LOG_TIME_FORMAT"]?></span><br />
				<?
				if ($arEvent["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
					echo Str_Replace("#NAME#", "<a href=\"".$arEvent["ENTITY_PATH"]."\">".$arEvent["ENTITY_NAME"]."</a>", GetMessage("SONET_ACTIVITY_T_GROUP_TITLE"));
				else
				{
					echo GetMessage("SONET_ACTIVITY_T_USER_TITLE1");
					
					$APPLICATION->IncludeComponent("bitrix:main.user.link",
						'',
						array(
							"ID" => $arEvent["ENTITY_ID"],
							"HTML_ID" => "log_".$arEvent["ENTITY_ID"],
							"NAME" => $arEvent["USER_NAME"],
							"LAST_NAME" => $arEvent["USER_LAST_NAME"],
							"SECOND_NAME" => $arEvent["USER_SECOND_NAME"],
							"LOGIN" => $arEvent["USER_LOGIN"],
							"USE_THUMBNAIL_LIST" => "N",
							"PROFILE_URL" => $arEvent["ENTITY_PATH"],
							"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
							"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
							"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
							"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
							"SHOW_YEAR" => $arParams["SHOW_YEAR"],
							"CACHE_TYPE" => $arParams["CACHE_TYPE"],
							"CACHE_TIME" => $arParams["CACHE_TIME"],
							"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
							"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
							"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
							"INLINE" => "Y",
						),
						false,
						array("HIDE_ICONS" => "Y")
					);
				}
				?>:
				<?= $arEvent["TITLE_FORMAT"] ?>
				<?if (StrLen($arEvent["MESSAGE_FORMAT"]) > 0):?>
					<div id="sonet_message_<?= $ind ?>" class="sonet-log-message" style="display:none;">
						<br />
						<?= $arEvent["MESSAGE_FORMAT"]; ?><br />
						<a href="javascript:SoNetSwitchBody(<?= $ind ?>, false)"><?= GetMessage("SONET_ACTIVITY_T_SWITCH1") ?></a>
					</div>
					<div id="sonet_message_link_<?= $ind ?>" class="sonet-log-message" style="display:block;">
						<br />
						<a href="javascript:SoNetSwitchBody(<?= $ind ?>, true)"><?= GetMessage("SONET_ACTIVITY_T_SWITCH2") ?></a>
					</div>
				<?endif;?>
				<?
				$bFirst = false;
				$ind++;
			}
			?>
			<br /><br />
			<?
		}
	}
	else
	{
		echo GetMessage("SONET_ACTIVITY_T_NO_UPDATES");
	}
}
?>