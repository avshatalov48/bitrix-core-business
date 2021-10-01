<?php

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Consent\ConsentResponseFactory;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	ShowError('Module `sender` not installed');
	die();
}

final class SenderConsent extends \Bitrix\Sender\Internals\CommonSenderComponent
{
	protected function checkRequiredParams()
	{
		return (
			parent::checkRequiredParams() &&
			isset($this->arParams['CONSENT'], $this->arParams['TAG'], $this->arParams['TYPE']) ||
			isset($this->arParams['TEST'])
		);
	}

	public function executeComponent()
	{
		self::initParams();
		self::checkRequiredParams() ? self::prepareResult() : self::setError('SENDER_CONSENT_WRONG_LINK');
		$this->includeComponentTemplate();
	}

	protected function initParams()
	{
		parent::initParams();
		$this->arParams['CONSENT'] = $this->request->get('consent') ?? null;
		$this->arParams['TAG'] = $this->request->get('tag') ?? null;
		$this->arParams['TYPE'] = $this->request->get('type') ?? null;
		$this->arParams['TEST'] = $this->request->get('test') ?? null;
		$this->arParams['SHOW_HTML_META'] = $this->arParams['SHOW_HTML_META'] ?? 'N';
	}

	protected function prepareResult()
	{
		try
		{
			['CONSENT' => $consent, 'TAG' => $tag, 'TYPE' => $type, 'TEST' => $test] = $this->arParams;
			$response = ConsentResponseFactory::getConsentResponse($type);
			if (
//				!$test &&
				$response)
			{
				switch ($consent)
				{
					case $this->arResult['METHOD'] = 'apply':
						$this->arResult['SUCCESS'] = $result = $response->loadData($tag)->apply();
						$result ?: self::setError("SENDER_CONSENT_ERROR_APPLY");
						break;
					case $this->arResult['METHOD'] = 'reject':
						$this->arResult['SUCCESS'] = $result = $response->loadData($tag)->reject();
						$result ?: self::setError("SENDER_CONSENT_ERROR_REJECT");
						break;
					default:
						$this->arResult['METHOD'] = null;
						$this->arResult['SUCCESS'] = false;
						$this->setError("SENDER_CONSENT_WRONG_LINK");
						break;
				}
			} elseif (!$test)
			{
				self::setError("SENDER_CONSENT_WRONG_LINK");
			}
		} catch (\Exception $exception)
		{
			self::setError("SENDER_CONSENT_WRONG_LINK");
		}
	}

	private function setError($errorCode)
	{
		$this->arResult['ERROR'] = Loc::getMessage($errorCode);
	}

	public function getEditAction()
	{
		return null;
	}

	public function getViewAction()
	{
		return null;
	}
}