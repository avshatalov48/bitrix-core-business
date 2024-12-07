<?php
namespace Bitrix\Landing\PublicAction;

use Bitrix\Landing;
use Bitrix\Landing\Manager;
use Bitrix\Landing\PublicActionResult;
use Bitrix\Landing\Site\Scope;
use Bitrix\Rest;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Rest\UsageStatTable;

/**
 * Register widget with vue for Mainpage
 */
class RepoWidget extends Repo
{
	private const SUBTYPE_WIDGET = 'widgetvue';




	// tmp hide
	public static function register(string $code, array $fields, array $manifest = []): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$error->addError(
			'ILLEGAL_METHOD',
			Loc::getMessage('LANDING_WIDGET_METHOD_NOT_AVAILABLE')
		);
		$result->setError($error);

		return $result;
	}

	// tmp hide
	public static function unregister($code)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$error->addError(
			'ILLEGAL_METHOD',
			Loc::getMessage('LANDING_WIDGET_METHOD_NOT_AVAILABLE')
		);
		$result->setError($error);

		return $result;
	}




	/**
	 * Some fixes in fields and manifest, specific by scope (mainpage widget or any)
	 * @param array $fields
	 * @param array $manifest
	 * @return array
	 */
	protected static function onRegisterBefore(array $fields, array $manifest = []): array
	{
		if (isset($fields['WIDGET_PARAMS']) && is_array($fields['WIDGET_PARAMS']))
		{
			$manifest['block']['type'] = mb_strtolower(Landing\Site\Type::SCOPE_CODE_MAINPAGE);
			$manifest['block']['subtype'] = self::SUBTYPE_WIDGET;
			$manifest['block']['subtype_params'] = [
				'rootNode' => $fields['WIDGET_PARAMS']['rootNode'] ?? null,
				'data' => $fields['WIDGET_PARAMS']['data'] ?? null,
				'handler' => $fields['WIDGET_PARAMS']['handler'] ?? null,
				'lang' => $fields['WIDGET_PARAMS']['lang'] ?? null,
			];
		}

		$manifest = Scope\Mainpage::prepareBlockManifest($manifest);

		return [$fields, $manifest];
	}

	/**
	 * @param array $fields
	 * @param array $manifest
	 * @return array
	 */
	protected static function onRegisterBeforeSave(array $fields, array $manifest = []): array
	{
		$fields['CONTENT'] = str_replace('<st yle>', '<style>', $fields['CONTENT']);

		return [$fields, $manifest];
	}

	/**
	 * @param int $blockId
	 * @param array $params
	 * @return PublicActionResult
	 */
	public static function fetchData(int $blockId, array $params = []): PublicActionResult
	{
		// tmp hide
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$error->addError(
			'ILLEGAL_METHOD',
			Loc::getMessage('LANDING_WIDGET_METHOD_NOT_AVAILABLE')
		);
		$result->setError($error);

		return $result;



		$result = new PublicActionResult();
		$result->setResult(false);
		$error = new Landing\Error;

		$block = new Landing\Block($blockId);
		if (!$block->getId())
		{
			$error->addError(
				'BLOCK_NOT_FOUND',
				Loc::getMessage('LANDING_WIDGET_BLOCK_NOT_FOUND')
			);
			$result->setError($error);

			return $result;
		}

		if (!Loader::includeModule('rest'))
		{
			$error->addError(
				'REST_NOT_FOUND',
				Loc::getMessage('LANDING_WIDGETA_REST_NOT_FOUND')
			);
			$result->setError($error);

			return $result;
		}

		$repoId = $block->getRepoId();
		$app = Landing\Repo::getAppInfo($repoId);
		if (
			!$repoId
			|| empty($app)
			|| !isset($app['CODE'])
		)
		{
			$error->addError(
				'APP_NOT_FOUND',
				Loc::getMessage('LANDING_WIDGET_APP_NOT_FOUND')
			);
			$result->setError($error);

			return $result;
		}

		$manifest = $block->getManifest();
		if (
			!in_array(self::SUBTYPE_WIDGET, (array)$manifest['block']['subtype'], true)
			|| !is_array($manifest['block']['subtype_params'])
			|| !isset($manifest['block']['subtype_params']['handler'])
		)
		{
			$error->addError(
				'HANDLER_NOT_FOUND',
				Loc::getMessage('LANDING_WIDGET_HANDLER_NOT_FOUND')
			);

			return $result;
		}

		$auth = Rest\Application::getAuthProvider()->get(
			$app['CODE'],
			'landing',
			[],
			Manager::getUserId()
		);

		if (isset($auth['error']))
		{
			$error->addError(
				$auth['error'],
				$auth['error_description'] ?? ''
			);

			return $result;
		}
		$params['auth'] = $auth;
		$url = (string)$manifest['block']['subtype_params']['handler'];
		$http = new HttpClient();
		$data = $http->post(
			$url,
			$params
		);

		// todo: remove is_callable after rest's release
		if (
			Loader::includeModule('rest')
			&& is_callable(['Bitrix\Rest\UsageStatTable', 'logLandingWidget'])
		)
		{
			$type = empty($params) ? 'default' : 'with_params';
			UsageStatTable::logLandingWidget($app['CLIENT_ID'], $type);
			UsageStatTable::finalize();
		}

		if (isset($data['error']))
		{
			$error->addError(
				$data['error'],
				$data['error_description'] ?? ''
			);

			return $result;
		}

		$result->setResult($data);

		return $result;
	}

	/**
	 * Can't use this method for widgets - @see parent
	 * @param string $code App code.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getAppInfo($code)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$error->addError(
			'ILLEGAL_METHOD',
			Loc::getMessage('LANDING_WIDGET_METHOD_NOT_AVAILABLE')
		);
		$result->setError($error);

		return $result;
	}

	/**
	 * Can't use this method for widgets - @see parent
	 * @param array $fields Fields array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function bind(array $fields)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$error->addError(
			'ILLEGAL_METHOD',
			Loc::getMessage('LANDING_WIDGET_METHOD_NOT_AVAILABLE')
		);
		$result->setError($error);

		return $result;
	}

	/**
	 * Can't use this method for widgets - @see parent
	 * @param string $code Placement code.
	 * @param string $handler Handler path (if you want delete specific).
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function unbind($code, $handler = null)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$error->addError(
			'ILLEGAL_METHOD',
			Loc::getMessage('LANDING_WIDGET_METHOD_NOT_AVAILABLE')
		);
		$result->setError($error);

		return $result;
	}

	/**
	 * Can't use this method for widgets - @see parent
	 * @param array $params Params ORM array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getList(array $params = array())
	{
		// todo: how get getList if needed? add scope?

		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$error->addError(
			'ILLEGAL_METHOD',
			Loc::getMessage('LANDING_WIDGET_METHOD_NOT_AVAILABLE')
		);
		$result->setError($error);

		return $result;
	}
}
