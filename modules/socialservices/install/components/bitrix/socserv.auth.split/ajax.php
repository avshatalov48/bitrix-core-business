<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CSocialServicesAuthSplitAjaxController extends \Bitrix\Main\Engine\Controller
{
	public function logoutAction()
	{
		global $USER;

		$USER->SetParam("AUTH_ACTION_SKIP_LOGOUT", true);
		\Bitrix\Main\UserAuthActionTable::addLogoutAction($USER->GetID());
	}
}
