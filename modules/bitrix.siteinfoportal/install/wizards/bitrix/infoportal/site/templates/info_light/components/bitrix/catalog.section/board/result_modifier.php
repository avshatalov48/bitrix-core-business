<?
foreach($arResult['ITEMS'] as $k=>$arElement)
{
	$user_id = $arElement['DISPLAY_PROPERTIES']['USER_ID']['DISPLAY_VALUE'];
	if ($user_id)
	{
		$rsUSER = CUser::GetById($user_id);
		$f=$rsUSER->Fetch();
		$arResult['ITEMS'][$k]['DISPLAY_PROPERTIES']['USER_ID']['DISPLAY_VALUE'] = $f['NAME'].' '.$f['LAST_NAME'];
	}
}
?>
