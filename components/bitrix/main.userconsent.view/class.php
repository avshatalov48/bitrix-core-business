<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Context;
use Bitrix\Main\UserConsent\Agreement;
use Bitrix\Main\UserConsent\AgreementLink;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class MainUserConsentViewComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['ID'] = isset($this->arParams['ID']) ? intval($this->arParams['ID']) : null;
		$this->arParams['REPLACE'] = is_array($this->arParams['REPLACE']) ? $this->arParams['REPLACE'] : array();
		$this->arParams['SECURITY_CODE'] = isset($this->arParams['SECURITY_CODE']) ? $this->arParams['SECURITY_CODE'] : null;

		if (!isset($this->arParams['PARAMS']))
		{
			$this->arParams['PARAMS'] = Context::getCurrent()->getRequest()->toArray();
		}
		if (!is_array($this->arParams['PARAMS']))
		{
			$this->arParams['PARAMS'] = array();
		}
	}

	protected function prepareResult()
	{
		if ($this->arParams['ID'])
		{
			$agreement = new Agreement($this->arParams['ID'], $this->arParams['REPLACE']);
			$agreementData = $agreement->getData();
			if ($agreementData['SECURITY_CODE'] != $this->arParams['SECURITY_CODE'])
			{
				$agreement = null;
			}
		}
		else
		{
			$agreement = AgreementLink::getAgreementFromUriParameters($this->arParams['PARAMS']);
		}

		if (!$agreement)
		{
			//use user-friendly text instead of AgreementLink::getErrors();
			$this->errors->add(array(new Error(Loc::getMessage('MAIN_USER_CONSENT_VIEW_ERROR'))));
			return false;
		}
		else
		{
			$this->arResult['TEXT'] = $agreement->getText();
		}

		return true;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();
		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}
}