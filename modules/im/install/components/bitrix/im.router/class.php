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

	public function executeComponent()
	{
		if (!$this->checkModules())
		{
			$this->showErrors();
			return;
		}

		$this->request = \Bitrix\Main\Context::getCurrent()->getRequest();

		if ($this->request->get('alias'))
		{
			$this->aliasData = \Bitrix\Im\Alias::get($this->request->get('alias'));
			if ($this->aliasData['ENTITY_TYPE'] == \Bitrix\Im\Alias::ENTITY_TYPE_OPEN_LINE && IsModuleInstalled('imopenlines'))
			{
				$this->showLiveChat();
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
				$this->showFullscreenChat();
			}
			else
			{
				LocalRedirect('/');
			}
		}
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