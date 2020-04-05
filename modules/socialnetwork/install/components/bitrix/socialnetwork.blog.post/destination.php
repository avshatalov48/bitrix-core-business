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

$user_id = IntVal($USER->GetID());

CJSCore::Init(array('socnetlogdest'));

$arResult["DEST_SORT"] = CSocNetLogDestination::GetDestinationSort(array(
	"DEST_CONTEXT" => "BLOG_POST",
	"ALLOW_EMAIL_INVITATION" => $arResult["ALLOW_EMAIL_INVITATION"]
));

$arResult["FEED_DESTINATION"]['LAST'] = array();
CSocNetLogDestination::fillLastDestination(
	$arResult["DEST_SORT"],
	$arResult["FEED_DESTINATION"]['LAST'],
	array(
		"EMAILS" => ($arResult["ALLOW_EMAIL_INVITATION"] ? 'Y' : 'N')
	)
);

$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
$cacheId = 'blog_post_form_dest_'.SITE_ID.'_'.$user_id;
$cacheDir = '/blog/form/dest/'.SITE_ID.'/'.$user_id;

$obCache = new CPHPCache;
if($obCache->InitCache($cacheTtl, $cacheId, $cacheDir))
	$arResult["FEED_DESTINATION"]['SONETGROUPS'] = $obCache->GetVars();
else
{
	$obCache->StartDataCache();
	$arResult["FEED_DESTINATION"]['SONETGROUPS'] = CSocNetLogDestination::GetSocnetGroup(Array('features' => array("blog", array("premoderate_post", "moderate_post", "write_post", "full_post"))));
	if(defined("BX_COMP_MANAGED_CACHE"))
	{
		$GLOBALS["CACHE_MANAGER"]->StartTagCache($cacheDir);
		foreach($arResult["FEED_DESTINATION"]['SONETGROUPS'] as $val)
		{
			$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_features_G_".$val["entityId"]);
			$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_group_".$val["entityId"]);
		}
		$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_user2group_U".$user_id);
		$GLOBALS["CACHE_MANAGER"]->EndTagCache();
	}
	$obCache->EndDataCache($arResult["FEED_DESTINATION"]['SONETGROUPS']);
}

$arDestUser = Array();
$arResult["FEED_DESTINATION"]['SELECTED'] = Array();

$bAllowToAll = \Bitrix\Socialnetwork\ComponentHelper::getAllowToAllDestination();

if (
	CModule::IncludeModule('extranet') 
	&& !CExtranet::IsIntranetUser()
)
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

// intranet structure
$arStructure = CSocNetLogDestination::GetStucture(array("LAZY_LOAD" => true));
$arResult["FEED_DESTINATION"]['DEPARTMENT'] = $arStructure['department'];
$arResult["FEED_DESTINATION"]['DEPARTMENT_RELATION'] = $arStructure['department_relation'];
$arResult["FEED_DESTINATION"]['DEPARTMENT_RELATION_HEAD'] = $arStructure['department_relation_head'];

if (CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser())
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

$arResult["FEED_DESTINATION"]["USERS_VACATION"] = Bitrix\Socialnetwork\Integration\Intranet\Absence\User::getDayVacationList();

$arResult["FEED_DESTINATION"]["DENY_TOALL"] = !$bAllowToAll;
?>