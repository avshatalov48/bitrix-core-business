<?php

namespace Bitrix\Landing\Controller;

use Bitrix\Main\Engine;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Landing;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Assets;
use Bitrix\Landing\Copilot\Connector;
use Bitrix\Rest;

class Vibe extends Controller
{
	private const SUBTYPE_WIDGET = 'widgetvue';

	public function getDefaultPreFilters(): array
	{
		return [
			new Engine\ActionFilter\Authentication(),
			new ActionFilter\Extranet(),
		];
	}

	/**
	 * Get core extensions and styles configs, load relations, load lang phrases
	 *
	 * @return array - array of assets by type
	 */
	public static function getCoreConfigAction(): array
	{
		$coreExts = [
			'main.core',
			'ui.design-tokens',
		];

		$assetsManager = (new Assets\Manager())
			->enableSandbox()
			->addAsset($coreExts)
		;

		$siteTemplatePath =
			(defined('SITE_TEMPLATE_PATH') ? SITE_TEMPLATE_PATH : '/bitrix/templates/bitrix24');
		$style = $siteTemplatePath . '/dist/bitrix24.bundle.css';
		$assetsManager->addAsset($style);

		return $assetsManager->getOutput();
	}

	/**
	 * Get extensions configs, load relations, load lang phrases
	 *
	 * @param array $extCodes - array of extensions codes
	 * @return array - array of assets by type
	 */
	public static function getAssetsConfigAction(array $extCodes): array
	{
		$assetsManager = (new Assets\Manager())
			->enableSandbox()
			->addAsset($extCodes)
		;

		return $assetsManager->getOutput();
	}

	/**
	 * @param int $blockId
	 * @param array $params
	 */
	public function fetchDataAction(int $blockId, array $params = [])
	{
		$block = new Landing\Block($blockId);
		if (!$block->getId())
		{
			$this->addError(
				new Error(Loc::getMessage('LANDING_WIDGET_BLOCK_NOT_FOUND'), 'BLOCK_NOT_FOUND')
			);

			return null;
		}

		if (!Loader::includeModule('rest'))
		{
			$this->addError(
				new Error(Loc::getMessage('LANDING_WIDGET_REST_NOT_FOUND'), 'REST_NOT_FOUND')
			);

			return null;
		}

		// check app
		$repoId = $block->getRepoId();
		$app = Landing\Repo::getAppInfo($repoId);
		if (
			!$repoId
			|| empty($app)
			|| !isset($app['CLIENT_ID'])
		)
		{
			$this->addError(
				new Error(Loc::getMessage('LANDING_WIDGET_APP_NOT_FOUND'), 'APP_NOT_FOUND')
			);

			return null;
		}

		// get auth
		$appHasAccess = \CRestUtil::checkAppAccess($app['ID'] ?? 0);
		if (!$appHasAccess)
		{
			$this->addError(
				// todo: open after translate
				// new Error(Loc::getMessage('LANDING_WIDGET_APP_NO_ACCESS'), 'APP_NO_ACCESS')
				new Error('Landing widget app has no access', 'APP_NO_ACCESS')
			);

			return null;
		}

		$auth = Rest\Application::getAuthProvider()?->get(
			$app['CLIENT_ID'],
			'landing',
			[],
			Manager::getUserId()
		);
		if ($auth && isset($auth['error']))
		{
			$this->addError(
				new Error(
					$auth['error_description'] ?? '',
					'APP_AUTH_ERROR__' . $auth['error']
				)
			);

			return null;
		}
		$params['auth'] = $auth;

		// check subtype
		$manifest = $block->getManifest();
		if (
			!in_array(self::SUBTYPE_WIDGET, (array)$manifest['block']['subtype'], true)
			|| !is_array($manifest['block']['subtype_params'])
			|| !isset($manifest['block']['subtype_params']['handler'])
		)
		{
			$this->addError(
				new Error(Loc::getMessage('LANDING_WIDGET_HANDLER_NOT_FOUND_2'), 'HANDLER_NOT_FOUND')
			);

			return null;
		}

		// request
		$url = (string)$manifest['block']['subtype_params']['handler'];
		$http = new HttpClient();
		$data = $http->post(
			$url,
			$params
		);

		if ($http->getStatus() !== 200)
		{
			$this->addError(
				new Error(Loc::getMessage('LANDING_WIDGET_HANDLER_NOT_ALLOW'), 'HANDLER_NOT_ALLOW')
			);

			return null;
		}

		$type = empty($params) ? 'fetch' : 'fetch_params';
		Rest\UsageStatTable::logLandingWidget($app['CLIENT_ID'], $type);
		Rest\UsageStatTable::finalize();

		if (isset($data['error']))
		{
			$this->addError(
				new Error($data['error'], $data['error_description'] ?? '')
			);

			return null;
		}

		return $data;
	}
}