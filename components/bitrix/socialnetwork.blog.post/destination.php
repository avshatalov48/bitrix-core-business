<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

$arResult["SELECTOR_VERSION"] = (!empty($arParams["SELECTOR_VERSION"]) ? intval($arParams["SELECTOR_VERSION"]) : 1);

$user_id = (int)$USER->GetID();

$arResult["FEED_DESTINATION"] = [];

$bAllowToAll = \Bitrix\Socialnetwork\ComponentHelper::getAllowToAllDestination();

if ($arResult["SELECTOR_VERSION"] < 2)
{
	$dataAdditional = array();
	$arResult["DEST_SORT"] = CSocNetLogDestination::getDestinationSort(array(
		"DEST_CONTEXT" => "BLOG_POST",
		"ALLOW_EMAIL_INVITATION" => $arResult["ALLOW_EMAIL_INVITATION"]
	), $dataAdditional);

	$arResult["FEED_DESTINATION"]['LAST'] = array();
	CSocNetLogDestination::fillLastDestination(
		$arResult["DEST_SORT"],
		$arResult["FEED_DESTINATION"]['LAST'],
		array(
			"EMAILS" => ($arResult["ALLOW_EMAIL_INVITATION"] ? 'Y' : 'N'),
			"DATA_ADDITIONAL" => $dataAdditional
		)
	);

	$limit = 500;

	$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
	$cacheId = 'blog_post_form_dest_'.SITE_ID.'_'.$user_id.'_'.$limit;
	$cacheDir = '/blog/form/dest/'.SITE_ID.'/'.$user_id;

	$obCache = new CPHPCache;
	if($obCache->InitCache($cacheTtl, $cacheId, $cacheDir))
	{
		$tmp = $obCache->getVars();
		$arResult["FEED_DESTINATION"]['SONETGROUPS'] = $tmp['groups'];
		$limitReached = $tmp['limitReached'];
	}
	else
	{
		$obCache->StartDataCache();

		$limitReached = false;
		$arResult["FEED_DESTINATION"]['SONETGROUPS'] = CSocNetLogDestination::getSocnetGroup(
			array(
				'features' => array("blog", array("premoderate_post", "moderate_post", "write_post", "full_post")),
				'limit' => $limit
			), $limitReached
		);
		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$CACHE_MANAGER->StartTagCache($cacheDir);
			foreach($arResult["FEED_DESTINATION"]['SONETGROUPS'] as $val)
			{
				$CACHE_MANAGER->RegisterTag("sonet_features_G_".$val["entityId"]);
				$CACHE_MANAGER->RegisterTag("sonet_group_".$val["entityId"]);
			}
			$CACHE_MANAGER->RegisterTag("sonet_user2group_U".$user_id);
			$CACHE_MANAGER->EndTagCache();
		}
		$obCache->EndDataCache(array(
			'groups' => $arResult["FEED_DESTINATION"]['SONETGROUPS'],
			'limitReached' => $limitReached
		));
	}
	if (
		!$limitReached
		&& CSocNetUser::isCurrentUserModuleAdmin()
	)
	{
		$limitReached = true;
	}

	$arResult["FEED_DESTINATION"]['SONETGROUPS_LIMITED'] = ($limitReached ? 'Y' : 'N');

	$arResult["FEED_DESTINATION"]['SELECTED'] = Array();

	if ($arResult["bExtranetUser"])
	{
		if(!empty($arResult["FEED_DESTINATION"]['LAST']['SONETGROUPS']))
		{
			foreach ($arResult["FEED_DESTINATION"]['LAST']['SONETGROUPS'] as $val)
			{
				$arResult["FEED_DESTINATION"]['SELECTED'][$val] = "sonetgroups";
			}
		}
		else
		{
			foreach ($arResult["FEED_DESTINATION"]['SONETGROUPS'] as $k => $val)
			{
				$arResult["FEED_DESTINATION"]['SELECTED'][$k] = "sonetgroups";
			}
		}
	}
	elseif ($bAllowToAll)
	{
		$arResult["FEED_DESTINATION"]['SELECTED']['UA'] = 'groups';
	}

	if ($arResult["bExtranetUser"])
	{
		$arResult["FEED_DESTINATION"]['EXTRANET_USER'] = 'Y';
		$arResult["FEED_DESTINATION"]['USERS'] = CSocNetLogDestination::GetExtranetUser();
	}
	else
	{
		$arResult["FEED_DESTINATION"]['EXTRANET_USER'] = 'N';

		if(!empty($arResult["FEED_DESTINATION"]['LAST']['USERS']))
		{
			foreach ($arResult["FEED_DESTINATION"]['LAST']['USERS'] as $value)
			{
				$arResult["dest_users"][] = str_replace('U', '', $value);
			}

			$arResult["FEED_DESTINATION"]['USERS'] = CSocNetLogDestination::GetUsers(array(
				'id' => $arResult["dest_users"],
				'CRM_ENTITY' => IsModuleInstalled('crm')
			));

			if ($arResult["ALLOW_EMAIL_INVITATION"])
			{
				CSocNetLogDestination::fillEmails($arResult["FEED_DESTINATION"]);
			}
		}
	}

	// intranet structure
	$arStructure = CSocNetLogDestination::GetStucture(array("LAZY_LOAD" => true));
	$arResult["FEED_DESTINATION"]['DEPARTMENT'] = $arStructure['department'];
	$arResult["FEED_DESTINATION"]['DEPARTMENT_RELATION'] = $arStructure['department_relation'];
	$arResult["FEED_DESTINATION"]['DEPARTMENT_RELATION_HEAD'] = $arStructure['department_relation_head'];

	$arResult["FEED_DESTINATION"]["USERS_VACATION"] = Bitrix\Socialnetwork\Integration\Intranet\Absence\User::getDayVacationList();
}

$arResult["FEED_DESTINATION"]["DENY_TOALL"] = !$bAllowToAll;
?>