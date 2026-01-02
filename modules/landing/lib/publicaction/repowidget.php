<?php
namespace Bitrix\Landing\PublicAction;

use Bitrix\Landing;
use Bitrix\Landing\Error;
use Bitrix\Landing\PublicAction;
use Bitrix\Landing\Subtype;
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

	/**
	 * Some fixes in fields and manifest, specific by scope (mainpage widget or any)
	 * @param array $fields
	 * @param array $manifest
	 * @param Error $error - object for set errors
	 * @return array
	 */
	protected static function onRegisterBefore(array &$fields, array &$manifest, Error $error): void
	{
		if (!isset($fields['WIDGET_PARAMS']) && !is_array($fields['WIDGET_PARAMS']))
		{
			$error->addError(
				'REQUIRED_FIELD_NO_EXISTS',
				Loc::getMessage('LANDING_WIDGET_FIELD_NO_EXISTS', ['#field#' => 'WIDGET_PARAMS'])
			);

			return;
		}

		$requiredParams = [
			'rootNode',
			'handler',
			'demoData',
		];
		foreach ($requiredParams as $param)
		{
			if (!isset($fields['WIDGET_PARAMS'][$param]))
			{
				$error->addError(
					'REQUIRED_PARAM_NO_EXISTS',
					Loc::getMessage('LANDING_WIDGET_PARAM_NO_EXISTS', ['#param#' => $param])
				);

				return;
			}
		}

		// Can set only available fields to manifest. Security!
		$manifest = [];

		$manifest['block']['type'] = mb_strtolower(Landing\Site\Type::SCOPE_CODE_MAINPAGE);
		$manifest['block']['subtype'] = self::SUBTYPE_WIDGET;
		$manifest['block']['subtype_params'] = [
			'rootNode' => $fields['WIDGET_PARAMS']['rootNode'] ?? null,
			'demoData' => $fields['WIDGET_PARAMS']['demoData'] ?? null,
			'handler' => $fields['WIDGET_PARAMS']['handler'] ?? null,
			'style' => $fields['WIDGET_PARAMS']['style'] ?? null,
			'lang' => $fields['WIDGET_PARAMS']['lang'] ?? null,
		];

		$manifest = Scope\Mainpage::prepareBlockManifest($manifest);
	}

	/**
	 * Enable or disable widgets debug logging
	 * @param string $appCode
	 * @param bool $enable
	 * @return PublicActionResult
	 */
	public static function debug(bool $enable): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$result->setResult(false);

		$app = PublicAction::restApplication();
		if (
			$app
			&& isset($app['CODE'])
			&& !empty(Landing\Repo::getAppByCode($app['CODE']))
		)
		{
			Subtype\WidgetVue::setAppDebug($app['CODE'], $enable);
			$result->setResult(true);
		}
		else
		{
			$error->addError(
				'APP_NOT_FOUND',
				Loc::getMessage('LANDING_WIDGET_APP_NOT_FOUND')
			);
			$result->setError($error);
		}

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
	 * @param array $params Params ORM array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getList(array $params = array()): PublicActionResult
	{
		$result = new PublicActionResult();

		$listRes = parent::getList($params);
		if ($listRes->isSuccess())
		{
			$result->setResult([]);

			$listAll = $listRes->getResult();
			if (is_array($listAll) && !empty($listAll))
			{
				$listAll = array_filter($listAll, function ($item) {
					$isType = isset($item['MANIFEST']['block']['type'])
						&& in_array(
							mb_strtolower(Landing\Site\Type::SCOPE_CODE_MAINPAGE),
							(array)$item['MANIFEST']['block']['type'],
							true
						)
					;

					$isSubtype =
						isset($item['MANIFEST']['block']['subtype'])
						&& in_array(
							self::SUBTYPE_WIDGET,
							(array)$item['MANIFEST']['block']['subtype'],
							true
						)
					;

					return $isType && $isSubtype;
				});

				$result->setResult($listAll);
			}

			return $result;
		}

		return $listRes;
	}
}
