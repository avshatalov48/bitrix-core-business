<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem;
use Bitrix\Main\Web\Uri;

Loc::loadLanguageFile(__FILE__);

class SalePaySystemRegistrationRobokassa extends \CBitrixComponent
{
	private ErrorCollection $errorCollection;

	public function onPrepareComponentParams($params)
	{
		$this->errorCollection = new ErrorCollection();

		$this->checkModules();

		$this->initResult();

		return parent::onPrepareComponentParams($params);
	}

	private function checkModules(): void
	{
		$requiredModules = ['sale'];

		foreach ($requiredModules as $requiredModule)
		{
			if (!Loader::includeModule($requiredModule))
			{
				$this->errorCollection->setError(
					new Error(
						Loc::getMessage(
							'SALE_SPRR_COMPONENT_MODULE_ERROR',
							[
								'#MODULE_ID#' => $requiredModule,
							]
						)
					)
				);
			}
		}
	}

	private function hasPermission(): bool
	{
		global $APPLICATION;
		$saleModulePermissions = $APPLICATION->GetGroupRight('sale');

		return $saleModulePermissions >= 'W';
	}

	private function subscribeToPullEvents(): void
	{
		if (Loader::includeModule('pull'))
		{
			global $USER;
			\CPullWatch::Add($USER->GetID(), 'SALE_PAYSYSTEM_ROBOKASSA_REGISTRATION');
		}
	}

	private function printErrors(): void
	{
		foreach ($this->errorCollection as $error)
		{
			ShowError($error);
		}
	}

	private function initResult(): void
	{
		$this->arResult = [
			'SITE_URL' => '',
			'RESULT_URL' => '',
			'SUCCESS_URL' => '',
			'FAIL_URL' => '',
			'CALLBACK_URL' => '',
		];
	}

	private function prepareResult(): void
	{
		$request = Application::getInstance()->getContext()->getRequest();
		$protocol = $request->isHttps() ? 'https' : 'http';
		$domain = "{$protocol}://{$request->getHttpHost()}";
		
		$this->arResult['SITE_URL'] = $domain;
		$this->arResult['RESULT_URL'] = "{$domain}/bitrix/tools/sale_ps_result.php";
		$this->arResult['SUCCESS_URL'] = "{$domain}/bitrix/tools/sale/paysystem/robokassa/redirect.php";
		$this->arResult['FAIL_URL'] = "{$domain}/bitrix/tools/sale/paysystem/robokassa/redirect.php";
		$this->arResult['CALLBACK_URL'] = $this->getCallbackUrl($domain);
	}

	private function getCallbackUrl(string $domain): string
	{
		$callbackUrl = "{$domain}/bitrix/tools/sale/paysystem/robokassa/register_callback.php";

		$signedDomain = (new PaySystem\Robokassa\DomainSigner($domain))->signDomain();

		$callbackUri = new Uri($callbackUrl);
		$callbackUri->addParams(['signed_domain' => $signedDomain]);

		return $callbackUri->getUri();
	}

	public function executeComponent()
	{
		if (!$this->hasPermission())
		{
			return;
		}

		if (!$this->errorCollection->isEmpty())
		{
			$this->printErrors();
			return;
		}

		$this->prepareResult();
		$this->subscribeToPullEvents();

		$this->includeComponentTemplate();
	}
}