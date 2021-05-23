<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arUserPhotos = array();
$arResult['ARRAY_IDS'] = array();
$arUserIds = array();
foreach ($arResult["MESSAGES"] as $i => $res)
{
	$arResult['ARRAY_IDS'][] = $res["ID"];
	$user_id = $res['AUTHOR_ID'];
	$arUserIds[] = $user_id;
	if (!isset($arUserPhotos[$user_id]))
	{
		$dbUser = CUser::GetByID($user_id);
		$user = $dbUser->Fetch();
		if ($user['PERSONAL_PHOTO'] > 0)
		{
			$photo = CFile::ResizeImageGet($user['PERSONAL_PHOTO'], array("width" => 40, "height" => 40));
			$arUserPhotos[$user_id] = $photo['src'];
		}
		else
		{
			$arUserPhotos[$user_id] = "";
		}
	}

	$arResult["MESSAGES"][$i]["AUTHOR_URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("USER_ID" => $user_id, "user_id" => $user_id));
	$arResult["MESSAGES"][$i]["AUTHOR_PHOTO"] = $arUserPhotos[$user_id];
}

if ($arParams['FETCH_USER_ALIAS'])
	CPGalleryInterface::HandleUserAliases($arUserIds, $arParams['IBLOCK_ID']);
?>