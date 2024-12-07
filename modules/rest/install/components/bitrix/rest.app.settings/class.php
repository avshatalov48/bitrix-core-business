<?php

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\FormConfig\EventType;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\Rest\Event\Sender;
use Bitrix\Main\Engine\CurrentUser;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class RestAppSettingsComponent extends CBitrixComponent implements Controllerable, Errorable
{
	private ErrorCollection $errorCollection;

	public function onPrepareComponentParams($arParams): array
	{
		$this->errorCollection = new ErrorCollection();

		return $arParams;
	}

	public function executeComponent()
	{
		if (!$this->errorCollection->isEmpty())
		{
			return;
		}

		if (!isset($this->arParams['CONFIG']) || empty($this->arParams['CONFIG']))
		{
			return;
		}

		Toolbar::deleteFavoriteStar();

		$this->arResult = $this->prepareConfig($this->arParams['CONFIG']);

		$this->includeComponentTemplate();
	}

	private function prepareConfig($config)
	{
		$result = [];
		if (is_string($config['title']))
		{
			$result['TITLE'] = htmlspecialcharsbx($config['title']);
		}

		if (is_array($config['steps']))
		{
			$result['STEPS'] = $config['steps'];
		}

		if (is_array($config['form']))
		{
			if (isset($config['form']['action']) && !empty($config['form']['action']))
			{
				$result['HANDLER'] = $config['form']['action'];
			}

			if (isset($config['form']['clientId']) && !empty($config['form']['clientId']))
			{
				$result['CLIENT_ID'] = $config['form']['clientId'];
			}

			if (isset($config['form']['redirect']) && !empty($config['form']['redirect']))
			{
				$result['REDIRECT'] = $config['form']['redirect'];
			}

			if (isset($config['form']['saveCaption']) && !empty($config['form']['saveCaption']))
			{
				$result['SAVE_BUTTON'] = $config['form']['saveCaption'];
			}

			if (isset($config['form']['cancelCaption']) && !empty($config['form']['cancelCaption']))
			{
				$result['CANCEL_BUTTON'] = $config['form']['cancelCaption'];
			}
		}

		return $result;
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

	public function reloadAction(string $clientId, array $settings): AjaxJson
	{
		$formData = $settings;

		try
		{
			$app = new \Bitrix\Rest\App($clientId);
			$responseData = $app->fetchAppFormConfig($formData, EventType::Change);
			$responseData = Json::decode($responseData);
			if (isset($responseData['errors']) && is_array($responseData['errors']))
			{
				$errors = $responseData['errors'];
				foreach ($errors as $error)
				{
					$this->errorCollection->setError(
						new Error($error['message'] ?? '')
					);
				}
			}
		}
		catch (ArgumentException $e)
		{
			$this->errorCollection->setError(new Error($e->getMessage()));
		}

		if ($this->errorCollection->count() > 0)
		{
			return AjaxJson::createError($this->errorCollection);
		}
		$responseData = $this->prepareConfig($responseData);

		return AjaxJson::createSuccess($responseData);
	}
}
