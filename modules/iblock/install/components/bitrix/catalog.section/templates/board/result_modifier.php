<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

foreach($arResult['ITEMS'] as $k=>$arElement)
{
	$user_id = $arElement['DISPLAY_PROPERTIES']['USER_ID']['DISPLAY_VALUE'];
	if ($user_id)
	{
		$rsUSER = CUser::GetById($user_id);
		$f=$rsUSER->Fetch();
		$arResult['ITEMS'][$k]['DISPLAY_PROPERTIES']['USER_ID']['DISPLAY_VALUE'] = CUser::FormatName(CSite::GetNameFormat(false), array("NAME" => $f['NAME'], "LAST_NAME" => $f['LAST_NAME'], "SECOND_NAME" => $f['SECOND_NAME'], "LOGIN" => $f['LOGIN']));
	}
}
?>
