<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if($arResult['userField']['EDIT_IN_LIST'] === 'Y')
{
	print CAdminCalendar::CalendarDate(
		$arResult['additionalParameters']['NAME'],
		$arResult['additionalParameters']['VALUE'],
		20,
		true
	);
}
elseif(!empty($arResult['additionalParameters']['VALUE']))
{
	print $arResult['additionalParameters']['VALUE'];
}
else
{
	print '&nbsp;';
}