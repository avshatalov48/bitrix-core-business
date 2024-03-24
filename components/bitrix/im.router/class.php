<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Type\Date,
	\Bitrix\Main\HttpApplication;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
	die();

Loc::loadMessages(__FILE__);

class ImRouterComponent extends \CBitrixComponent
{
	private const NETWORK_LINE = 'networkLines';

	/** @var HttpRequest $request */
	protected $request = array();
	protected $errors = array();
	protected $aliasData = array();

	private function showFullscreenChat()
	{
		$this->includeComponentTemplate();
	}

	private function showBlankPage()
	{
		define('SKIP_TEMPLATE_AUTH_ERROR', true);

		$this->setTemplateName("blank");
		$this->includeComponentTemplate();

		return true;
	}

	private function showLiveChat()
	{
		define('SKIP_TEMPLATE_AUTH_ERROR', true);

		$this->arResult['CONTEXT'] = $this->request->get('iframe') == 'Y'? 'IFRAME': 'NORMAL';
		$this->arResult['CONFIG_ID'] = $this->aliasData['ENTITY_ID'];

		$this->setTemplateName("livechat");
		$this->includeComponentTemplate();

		return true;
	}

	private function showJitsiConf()
	{
		$this->arResult['ALIAS'] = $this->aliasData['ALIAS'];
		$this->arResult['CHAT_ID'] = $this->aliasData['ENTITY_ID'];

		$this->setTemplateName("jitsiconf");
		$this->includeComponentTemplate();

		return true;
	}

	private function showCall()
	{
		define('SKIP_TEMPLATE_AUTH_ERROR', true);

		if (Loader::includeModule('ui'))
		{
			\Bitrix\Main\UI\Extension::load('ui.roboto');
		}

		$this->arResult['ALIAS'] = $this->aliasData['ALIAS'];
		$this->arResult['CHAT_ID'] = $this->aliasData['ENTITY_ID'];

		$this->setTemplateName("call");
		$this->includeComponentTemplate();

		return true;
	}

	private function showNonExistentCall() : bool
	{
		define('SKIP_TEMPLATE_AUTH_ERROR', true);

		$this->arResult['WRONG_ALIAS'] = true;

		$this->setTemplateName("call");
		$this->includeComponentTemplate();

		return true;
	}

	public function executeComponent()
	{
		if (!$this->checkModules())
		{
			$this->showErrors();
			return;
		}

		$this->request = \Bitrix\Main\Context::getCurrent()->getRequest();

		$this->arResult['MESSENGER_V2'] = \Bitrix\Im\Settings::isLegacyChatActivated()  ? 'N' : 'Y';
		$this->arResult['WRONG_ALIAS'] = false;

		if ($this->request->get('alias'))
		{
			$videoconfFlag = $this->request->get('videoconf');
			$this->aliasData = \Bitrix\Im\Alias::get($this->request->get('alias'));
			if ($this->aliasData['ENTITY_TYPE'] == \Bitrix\Im\Alias::ENTITY_TYPE_LIVECHAT && IsModuleInstalled('imopenlines'))
			{
				$this->showLiveChat();
			}
			//wrong alias
			else if ($this->aliasData['ENTITY_TYPE'] === \Bitrix\Im\Alias::ENTITY_TYPE_JITSICONF)
			{
				$this->showJitsiConf();
			}
			else if (isset($videoconfFlag) && !$this->aliasData)
			{
				$this->showNonExistentCall();
			}
			//correct alias
			else if ($this->aliasData['ENTITY_TYPE'] == \Bitrix\Im\Alias::ENTITY_TYPE_VIDEOCONF)
			{
				$this->showCall();
			}
			else if ($this->request->get('iframe') == 'Y')
			{
				$this->showBlankPage();
			}
			else
			{
				LocalRedirect('/');
			}
		}
		else
		{
			global $USER;
			if ($USER->IsAuthorized() && !\Bitrix\Im\User::getInstance()->isConnector())
			{
				$this->checkNetworkLines();
				$this->showFullscreenChat();
			}
			else
			{
				LocalRedirect('/');
			}
		}
	}

	private function checkNetworkLines(): void
	{
		if (!str_starts_with($this->request['IM_DIALOG'], self::NETWORK_LINE))
		{
			return;
		}

		$code = substr($this->request['IM_DIALOG'], strlen(self::NETWORK_LINE));
		if (!preg_match('/^[a-f0-9]{32}$/i', $code))
		{
			LocalRedirect('/online/');
		}

		$botId = \Bitrix\ImBot\Bot\Network::join($code);
		if ($botId > 0)
		{
			LocalRedirect("/online/?IM_DIALOG={$botId}");
		}

		LocalRedirect('/online/');
	}

	protected function checkModules()
	{
		if(!Loader::includeModule('im'))
		{
			$this->errors[] = Loc::getMessage('IM_COMPONENT_MODULE_NOT_INSTALLED');
			return false;
		}
		return true;
	}

	protected function hasErrors()
	{
		return (count($this->errors) > 0);
	}

	protected function showErrors()
	{
		if(count($this->errors) <= 0)
		{
			return;
		}

		foreach($this->errors as $error)
		{
			ShowError($error);
		}
	}
}