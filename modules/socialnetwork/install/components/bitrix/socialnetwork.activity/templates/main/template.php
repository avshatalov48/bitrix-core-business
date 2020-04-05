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

	<?
	if ($arResult["Events"] && is_array($arResult["Events"]) && count($arResult["Events"]) > 0)
	{
		$ind = 0;
		$bFirst = true;		
		foreach ($arResult["Events"] as $date => $arEvents)
		{
			if (!$bFirst)
			{
				?><div class="sonet-profile-line"></div><?
			}		
			?>
			<?= $date ?><br />
			<?
			foreach ($arEvents as $arEvent)
			{
				?>
				<br /><span class="sonet-log-date"><?=$arEvent["LOG_TIME_FORMAT"]?></span><br />
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
							"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
							"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_USER"],
							"SHOW_FIELDS" => $arParams["SHOW_FIELDS_TOOLTIP"],
							"USER_PROPERTY" => $arParams["USER_PROPERTY_TOOLTIP"],
							"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
							"SHOW_YEAR" => $arParams["SHOW_YEAR"],
							"CACHE_TYPE" => $arParams["CACHE_TYPE"],
							"CACHE_TIME" => $arParams["CACHE_TIME"],
							"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
							"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
							"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
							"INLINE" => "Y",
						),
						false,
						array("HIDE_ICONS" => "Y")
					);
				}
				?>:
				<?= $arEvent["TITLE_FORMAT"] ?>
				<?
				$bFirst = false;
				$ind++;
			}
		}
	}
	else
	{
		echo GetMessage("SONET_ACTIVITY_T_NO_UPDATES");
	}
}
?>