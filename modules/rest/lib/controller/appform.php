<?php

namespace Bitrix\Rest\Controller;

use Bitrix\Bitrix24\CurrentUser;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Loader;
use Bitrix\Pull\Event;
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

		$appForm = new \Bitrix\Rest\AppForm($config, $transport);

		return ['success' => $appForm->sendShowMessage()];
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