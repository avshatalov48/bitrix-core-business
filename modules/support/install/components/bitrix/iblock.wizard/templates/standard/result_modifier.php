<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$langfile = __DIR__."/lang/".LANGUAGE_ID."/".basename(__FILE__);
if (file_exists($langfile))
	__IncludeLang($langfile);

if ($arResult['CURRENT_STEP']==1)
{
	$arResult['FIELDS'] = array_merge(
				array(
					array(
						'NAME'	=> GetMessage('WZ_MESS_TITLE'),
						'FIELD_ID' => 'wz_title',
						'FIELD_VALUE' => htmlspecialcharsbx($_REQUEST['wizard']['wz_title']),
						'FIELD_TYPE' => 'text'
					)
				),
				$arResult['FIELDS']
	);

	if ($arParams['SHOW_COUPON_FIELD']=='Y')
		$arResult['FIELDS'][] = array(
			'NAME'	=> GetMessage('WZ_COUPON'),
			'PREVIEW_TEXT' => GetMessage('WZ_MESS_PREVIEW'),
			'DETAIL_TEXT' => GetMessage('WZ_MESS_DETAIL'),
			'FIELD_ID' => 'wz_coupon',
			'FIELD_VALUE' => htmlspecialcharsbx($_REQUEST['wizard']['wz_coupon']),
			'FIELD_TYPE' => 'text'
		);
}
else
{
	$arResult['HIDDEN'][] = array('wz_title', htmlspecialcharsbx($_REQUEST['wizard']['wz_title']));
	
	if ($arParams['SHOW_COUPON_FIELD']=='Y')
	{
		$arResult['HIDDEN'][] = array('wz_coupon', htmlspecialcharsbx($_REQUEST['wizard']['wz_coupon']));
		if ($arResult['CURRENT_STEP']==2 && $_REQUEST['wizard']['wz_coupon'])
		{
			CModule::IncludeModule('support');

			CTimeZone::Disable();
			$rs = CSupportSuperCoupon::GetList(false,array('ACTIVE'=>'Y','COUPON'=>$_REQUEST['wizard']['wz_coupon']));
			CTimeZone::Enable();

			if ($f = $rs->Fetch())
			{
				if(date('Ymd',time()) > date('Ymd',MakeTimeStamp($f['ACTIVE_TO'])))
					$arResult['ERROR'] = str_replace("#DATE_ACTIVE#",$f['ACTIVE_TO'],GetMessage('WZ_COUPON_ERR1'));
				elseif($f['COUNT_TICKETS']<1)
					$arResult['ERROR'] = GetMessage('WZ_COUPON_ERR2');
				else
					$arResult['MESSAGE'] = GetMessage('WZ_ACCEPTED').($f['COUNT_TICKETS']-1);
					
			}
			else
				$arResult['ERROR'] = GetMessage('WZ_COUPON_ERR0');
		}
	}
}
?>
