<?php

namespace Bitrix\Rest\Controller;

use Bitrix\Bitrix24\CurrentUser;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Json;
use Bitrix\Pull\Event;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\FormConfig\EventType;
use Bitrix\Rest\PullTransport;

class AppForm extends Controller
{
	public function showAction(string $config, \CRestServer $server = null): array
	{
		try
		{
			$transport = new PullTransport();
		}
		catch (\Exception $exception)
		{
			return [
				'error' => 'NOT_INSTALLED_MODULE',
				'error_description' => $exception->getMessage()
			];
		}

		$appForm = new \Bitrix\Rest\AppForm($config);

		return ['success' => $appForm->sendShowMessage($transport)];
	}

	public function getConfigAction(string $clientId, string $type, array $formData = []): AjaxJson
	{
		try
		{
			$app = new \Bitrix\Rest\App($clientId);
			$responseData = [
				'config' => $app->fetchAppFormConfig($formData, EventType::from($type))
			];

			return AjaxJson::createSuccess($responseData);
		}
		catch (ArgumentException $e)
		{
			$this->errorCollection->setError(new Error($e->getMessage()));
		}
	}

	public function configureActions(): array
	{
		return [
			'show' => [
				'+prefilters' => [
					new ActionFilter\Scope(ActionFilter\Scope::REST),
				],
			],
		];
	}
}