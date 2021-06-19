<?
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
	die();

Loc::loadMessages(__FILE__);

class CB24ConnectorOpenlineInfoComponent extends \CBitrixComponent
{
	private $data = array();

	private function formatOperatorMessage()
	{
		$message = Array();
		if ($this->data['USER_FULL_NAME'])
		{
			$message[] = '[b]'.Loc::getMessage('AUTH_IMOL_USER').'[/b]: '.$this->data['USER_FULL_NAME'].($this->data['USER_LOGIN']? ' ('.$this->data['USER_LOGIN'].')': '');
		}
		else if ($this->data['USER_EMAIL'])
		{
			$message[] = '[b]'.Loc::getMessage('AUTH_IMOL_MAIL').'[/b]: '.$this->data['USER_EMAIL'];
		}
		if ($this->data['SESSION_FIRST_VISIT'])
		{
			$message[] = '[b]'.Loc::getMessage('AUTH_IMOL_FIRST_VISIT').'[/b]: '.Loc::getMessage('AUTH_IMOL_FIRST_VISIT_'.$this->data['SESSION_FIRST_VISIT']);
		}
		if ($this->data['SESSION_COUNTRY'])
		{
			$message[] = '[b]'.Loc::getMessage('AUTH_IMOL_COUNTRY').'[/b]: '.$this->data['SESSION_COUNTRY'];
		}
		if ($this->data['SESSION_SEARCHER'])
		{
			$message[] = '[b]'.Loc::getMessage('AUTH_IMOL_SEARCHER').'[/b]: '.$this->data['SESSION_SEARCHER'];
		}
		if ($this->data['SESSION_SEARCHER_PHRASE'])
		{
			$message[] = '[b]'.Loc::getMessage('AUTH_IMOL_SEARCHER_PHRASE').'[/b]: '.$this->data['SESSION_SEARCHER_PHRASE'];
		}
		$message[] = '';
		$message[] = '[b]'.Loc::getMessage('AUTH_IMOL_SITE').'[/b]: #VAR_HOST#';
		$message[] = '[b]'.Loc::getMessage('AUTH_IMOL_PAGE').'[/b]: #VAR_PAGE#';

		if ($this->arParams['DATA'])
		{
			$message[] = '';
			$message[] = $this->arParams['DATA'];
		}

		$event = new \Bitrix\Main\Event("b24connector", "onOpenlineInfoFormatOperatorMessage", Array('DATA' => $this->data));
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			$eventResult = $eventResult->getParameters();
			if (is_string($eventResult) && $eventResult <> '')
			{
				$message[] = '';
				$message[] = $eventResult;
			}
		}

		$this->data['FIRST_MESSAGE'] = implode('[br]', $message);
	}

	private function prepareAuthData()
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");
		$licence = md5("BITRIX".\CUpdateClient::GetLicenseKey()."LICENCE");

		global $USER;
		if ($USER->GetID() > 0)
		{
			$this->data['HASH'] = md5('user'.$USER->GetID().$licence);
			$this->data['USER_ID'] = $USER->GetID();
			$this->data['USER_NAME'] = $USER->GetFirstName();
			$this->data['USER_LAST_NAME'] = $USER->GetLastName();
			$this->data['USER_FULL_NAME'] = $USER->GetFirstName();
			$this->data['USER_EMAIL'] = $USER->GetEmail();
			$this->data['USER_LOGIN'] = $USER->GetLogin();
		}
		else
		{
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$request = $context->getRequest();

			$cookieValue = $request->getCookieRaw('LIVECHAT_GUEST_HASH');
			if (preg_match("/^[a-fA-F0-9]{32}$/i", $cookieValue))
			{
				$this->data['HASH'] = $cookieValue;
			}
			else if (\Bitrix\Main\ModuleManager::isModuleInstalled('statistic') && intval($_SESSION["SESS_SEARCHER_ID"]) <= 0 && intval($_SESSION["SESS_GUEST_ID"]) > 0)
			{
				$this->data['HASH'] = md5('stat_guest'.$_SESSION["SESS_GUEST_ID"].$licence);
			}
			else
			{
				$this->data['HASH'] = md5('guest'.time().\bitrix_sessid().$licence);
			}

			$_SESSION['LIVECHAT_GUEST_HASH'] = $this->data['HASH'];
			setcookie('LIVECHAT_GUEST_HASH', $this->data['HASH'], time() + 31536000, '/');
		}

		return true;
	}

	private function prepareSessionData()
	{
		$this->data['SESSION_SEARCHER'] = '';
		$this->data['SESSION_SEARCHER_PHRASE'] = '';
		$this->data['SESSION_FIRST_VISIT'] = '';
		$this->data['SESSION_COUNTRY'] = '';

		if (!\Bitrix\Main\Loader::includeModule('statistic'))
			return false;

		$this->data['SEARCHER'] = '';
		if ($_SESSION["FROM_SEARCHER_ID"])
		{
			$res = \CSearcher::GetByID($_SESSION["FROM_SEARCHER_ID"]);
			if ($searcher = $res->Fetch())
			{
				$this->data['SESSION_SEARCHER'] = $searcher['NAME'];
			}
		}

		if ($_SESSION["SESS_SEARCH_PHRASE"])
		{
			$this->data['SESSION_SEARCHER_PHRASE'] = $_SESSION["SESS_SEARCH_PHRASE"];
		}

		if ($_SESSION["GUEST_NEW"])
		{
			$this->data['SESSION_FIRST_VISIT'] = $_SESSION["SESS_GUEST_NEW"];
		}

		if ($_SESSION["GUEST_NEW"])
		{
			$this->data['SESSION_COUNTRY'] = $_SESSION["SESS_GUEST_NEW"];
		}

		if ($_SESSION["SESS_COUNTRY_ID"] != "N0")
		{
			$filter = array(
				"ID" => $_SESSION["SESS_COUNTRY_ID"]
			);
			$res = \CCountry::GetList("s_name", "desc", $filter);
			if ($ar = $res->Fetch())
			{
				$this->data['SESSION_COUNTRY'] = $ar['REFERENCE'];
			}
		}

		return true;
	}

	private function prepareVariableForTemplate()
	{
		foreach($this->data as $key => $value)
		{
			$this->arResult[$key] = $value;
		}

		$this->arResult['GA_MARK'] = $this->arParams['GA_MARK'];
		$this->arResult['CONFIG'] = Array(
			'user' => Array(
				'hash' => $this->data['HASH'],
				'name' => $this->data['USER_NAME'],
				'lastName' => $this->data['USER_LAST_NAME'],
				'email' => $this->data['USER_EMAIL'],
			),
			'firstMessage' => $this->data['FIRST_MESSAGE']
		);
	}

	public function executeComponent()
	{
		$this->prepareAuthData();
		$this->prepareSessionData();
		$this->formatOperatorMessage();
		$this->prepareVariableForTemplate();

		$this->includeComponentTemplate();
	}
}