<?php

use Bitrix\Main;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\UpdateSystem\PortalInfo;
use Bitrix\Rest\EInvoice;
use Bitrix\Rest\Marketplace;
use Bitrix\Bitrix24;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class RestEInvoiceInstallerComponent extends CBitrixComponent implements Controllerable, Errorable
{
	private ErrorCollection $errorCollection;

	public function onPrepareComponentParams($arParams): array
	{
		$this->errorCollection = new ErrorCollection();

		return $arParams;
	}

	public function executeComponent()
	{
		$this->arResult['applications'] = $this->getApplicationsConfig();

		if (Main\Loader::includeModule('bitrix24'))
		{
			$this->arResult['formConfiguration'] = [
				'from_domain' => defined('BX24_HOST_NAME') ? BX24_HOST_NAME
					: Main\Context::getCurrent()?->getRequest()->getHttpHost(),
				'b24_plan' => Bitrix24\License::getCurrent()->getCode(),
			];
		}
		else
		{
			$this->arResult['formConfiguration'] = [
				'from_domain' => Main\Context::getCurrent()?->getRequest()->getHttpHost(),
				'b24_plan' => (new PortalInfo())->getLicenseType(),
			];
		}

		$this->includeComponentTemplate();
	}

	public function getApplicationsConfig(): array
	{
		$applications = EInvoice::getApplicationList();
		$config = [];

		if ($applications)
		{
			foreach ($applications as $application)
			{
				$config[] = [
					'name' => $application['NAME'],
					'code' => $application['CODE'],
				];
			}
		}

		return $config;
	}

	public function installApplicationByCodeAction(string $code): AjaxJson
	{
		$result = Marketplace\Application::install($code);

		if (isset($result['errorDescription']) && $result['errorDescription'])
		{
			$this->errorCollection->setError(new Error($result['errorDescription']));

			return AjaxJson::createError($this->errorCollection);
		}

		return AjaxJson::createSuccess($result);
	}

	public function configureActions(): array
	{
		return [];
	}

	public function getErrors(): array
	{
		return $this->errorCollection->toArray();
	}

	public function getErrorByCode($code): ?Error
	{
		return $this->errorCollection->getErrorByCode($code);
	}
}

