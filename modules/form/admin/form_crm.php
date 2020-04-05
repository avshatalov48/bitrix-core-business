<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/prolog.php");

$FORM_RIGHT = $APPLICATION->GetGroupRight("form");

if($FORM_RIGHT<="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CModule::IncludeModule('form');

$action = $_REQUEST['action'];

$result = '{"result":"error"}';
if (check_bitrix_sessid())
{
	switch($action)
	{
		case 'get_fields':
		case 'check':
			$CRM_ID = intval($_REQUEST['ID']);

			if ($CRM_ID > 0)
			{
				$arAuth = null;
				if (strlen($_REQUEST['LOGIN']) > 0 && strlen($_REQUEST['PASSWORD']) > 0)
				{
					$arAuth = array('LOGIN' => $_REQUEST['LOGIN'], 'PASSWORD' => $_REQUEST['PASSWORD']);
				}

				$link = new CFormCrmSender($CRM_ID, $arAuth);
				$arFields = $link->GetFields($_REQUEST['reload']=='Y');

				if (is_array($arAuth))
				{
					$authHash = $link->GetAuthHash();
				}

				if (is_array($arFields) && count($arFields) > 0)
				{
					$result = '{"result":"ok","fields":'.CUtil::PhpToJsObject($arFields).(is_array($arAuth)?',"auth_hash":"'.$authHash.'"':'').'}';
				}
				else
				{
					$res = $link->GetLastResult();
					if ($res)
					{
						$result = '{"result":"error","error":"'.CUtil::JSEscape($res->field('error_message')).'"}';
					}
				}
			}
		break;

		case 'add_lead':
			$FORM_ID = intval($_REQUEST['FORM_ID']);
			$RESULT_ID = intval($_REQUEST['RESULT_ID']);

			if ($FORM_ID > 0 && $RESULT_ID > 0)
			{
				$leadId = CFormCrm::AddLead($FORM_ID, $RESULT_ID);
				if ($leadId > 0)
				{
					$result = '{"result":"ok",ID:'.intval($leadId).'}';
				}
				else
				{
					if ($ex = $APPLICATION->GetException())
					{
						$result = '{"result":"error","error":"'.CUtil::JSEscape($ex->GetString()).'"}';
					}
				}
			}
		break;
	}
}
else
{
	$result = '{"result":"error","error":"session_expired"}';
}

if ($result)
{
	$APPLICATION->RestartBuffer();
	echo $result;
}

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
?>