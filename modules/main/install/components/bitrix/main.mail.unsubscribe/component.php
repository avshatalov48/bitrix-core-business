<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

//$APPLICATION->SetTitle(GetMessage('MAIN_MAIL_UNSUBSCRIBE_TITLE'));

$arParams['ABUSE'] = isset($arParams['ABUSE']) ? $arParams['ABUSE'] === 'Y' : false;

$messageDictionary = array(
	'1000' => GetMessage('MAIN_MAIL_UNSUBSCRIBE_ERROR_UNSUB'),
	'1001' => GetMessage('MAIN_MAIL_UNSUBSCRIBE_ERROR_NOT_SELECTED'),
);

$this->setFrameMode(false);

try
{
	$arTag = \Bitrix\Main\Mail\Tracking::parseSignedTag(is_string($_REQUEST['tag']) ? $_REQUEST['tag'] : '');
	$arTag['IP'] = $_SERVER['REMOTE_ADDR'];

	$arResult = array();
	$arResult['FORM_URL'] = $APPLICATION->getCurPageParam("",array('success'));
	$arResult['LIST'] = \Bitrix\Main\Mail\Tracking::getSubscriptionList($arTag);

	$context = \Bitrix\Main\Context::getCurrent();
	$request = $context->getRequest();
	$isOneClickUnsub = $request->get('List-Unsubscribe') === 'One-Click';

	if (
		$request->getRequestMethod() === 'POST'
		&& (
			(array_key_exists('MAIN_MAIL_UNSUB_BUTTON', $_POST) && check_bitrix_sessid())
			|| $isOneClickUnsub === true
		)
	)
	{
		$unsubscribeListFromForm = is_array($_POST['MAIN_MAIL_UNSUB']) ? $_POST['MAIN_MAIL_UNSUB'] : array();

		$arUnsubscribeList = array();
		foreach($arResult['LIST'] as $key => $unsubItem)
		{
			if (
				in_array($unsubItem['ID'], $unsubscribeListFromForm)
				|| $isOneClickUnsub === true
			)
			{
				$arUnsubscribeList[] = $unsubItem['ID'];
				$arSubList[$key]['SELECTED'] = true;
			}
			else
			{
				$arResult['LIST'][$key]['SELECTED'] = false;
			}
		}

		$messageResult = null;
		if(!empty($arUnsubscribeList))
		{
			$arTag['FIELDS']['ABUSE'] = false;
			$arTag['FIELDS']['ABUSE_TEXT'] = null;
			$arTag['FIELDS']['UNSUBSCRIBE_LIST'] = $arUnsubscribeList;
			if (isset($_REQUEST['ABUSE']) && $_REQUEST['ABUSE'] === 'Y')
			{
				$arTag['FIELDS']['ABUSE'] = true;
				$arTag['FIELDS']['ABUSE_TEXT'] = $_REQUEST['ABUSE_TEXT'] ?? null;
			}

			$result = \Bitrix\Main\Mail\Tracking::unsubscribe($arTag);
			if ($result)
			{
				$messageResult = '0';
			}
			else
			{
				$messageResult = '1000';
			}
		}
		else
		{
			$messageResult = '1001';
		}

		if($messageResult !== null)
		{
			LocalRedirect($APPLICATION->GetCurPageParam("r=" . $messageResult, array("r")));
		}
	}
	else
	{
		if(isset($_REQUEST['r']) && is_numeric($_REQUEST['r']))
		{
			if($_REQUEST['r'] == '0')
			{
				$arResult['DATA_SAVED'] = 'Y';
			}
			elseif(isset($messageDictionary[$_REQUEST['r']]))
			{
				$arResult['WARNING'] = $messageDictionary[$_REQUEST['r']];
			}
		}
	}
}
catch (\Bitrix\Main\Security\Sign\BadSignatureException $exception)
{
	$arResult['ERROR'] = GetMessage('MAIN_MAIL_UNSUBSCRIBE_ERROR_SECURITY');
}

$siteName = '';
/*
$siteName = \COption::getOptionString('bitrix24', 'site_title', '');
if (!$siteName)
{
	$siteName = \COption::getOptionString('main', 'site_name', '');
}
if (!$siteName)
{
	$site = \CSite::GetArrayByID(SITE_ID);
	if (!empty($site))
	{
		$siteName = $site['NAME'];
	}
}
*/
$arResult['SITE_NAME'] = $siteName;

$componentTemplate = null;
if (isset($arParams['PAGE']) && $arParams['PAGE'] === 'Y')
{
	$componentTemplate = 'page';
}
$this->IncludeComponentTemplate($componentTemplate);
