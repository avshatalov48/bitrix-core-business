<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Help;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Error;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Page\Asset;

class LandingBaseComponent extends \CBitrixComponent
{
	/**
	 * Http status Forbidden.
	 */
	const ERROR_STATUS_FORBIDDEN = '403 Forbidden';

	/**
	 * Http status Not Found.
	 */
	const ERROR_STATUS_NOT_FOUND = '404 Not Found';

	/**
	 * Http status Service Unavailable.
	 */
	const ERROR_STATUS_UNAVAILABLE = '503 Service Unavailable';

	/**
	 * Navigation id.
	 */
	const NAVIGATION_ID = 'nav';

	/**
	 * Current user options.
	 * @var array|null
	 */
	protected $userOptions = null;

	/**
	 * Current errors.
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Current template.
	 * @var string
	 */
	protected $template = '';

	/**
	 * Last navigation result.
	 * @var \Bitrix\Main\UI\PageNavigation
	 */
	protected $lastNavigation = null;

	/**
	 * Current request
	 * @var \Bitrix\Main\HttpRequest
	 */
	protected $currentRequest = null;

	/**
	 * Init class' vars, check conditions.
	 * @return bool
	 */
	protected function init()
	{
		static $init = null;

		if ($init !== null)
		{
			return $init;
		}

		$init = true;

		Loc::loadMessages($this->getFile());

		if ($init && !Loader::includeModule('landing'))
		{
			$this->addError('LANDING_CMP_NOT_INSTALLED');
			$init = false;
		}
		$this->initRequest();

		return $init;
	}

	/**
	 * Get preview picture from cloud or not
	 * @return bool
	 */
	protected function previewFromCloud()
	{
		$disableCloud = Manager::isCloudDisable();
		return Manager::isB24() && !$disableCloud;
	}

	/**
	 * Http request initialization.
	 *
	 * @return void
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function initRequest()
	{
		if ($this->currentRequest !== null)
		{
			return;
		}
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$this->currentRequest = $context->getRequest();
		if ($this->currentRequest->isAjaxRequest())
		{
			$this->currentRequest->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());
		}
		unset($context);
	}

	/**
	 * Send only first http status.
	 * @param string $code Http status code.
	 * @return void
	 */
	protected function setHttpStatusOnce($code)
	{
		static $wasSend = false;

		if (!$wasSend)
		{
			$wasSend = true;
			\CHTTP::setStatus($code);
		}
	}

	/**
	 * Check var in arParams. If no exists, create with default val.
	 * @param string|int $var Variable.
	 * @param mixed $default Default value.
	 * @return void
	 */
	protected function checkParam($var, $default)
	{
		if (!isset($this->arParams[$var]))
		{
			$this->arParams[$var] = $default;
		}
		if (is_int($default))
		{
			$this->arParams[$var] = (int)$this->arParams[$var];
		}
		if (substr($var, 0, 1) !== '~')
		{
			$this->checkParam('~' . $var, $default);
		}
	}

	/**
	 * Add one more error.
	 * @param string $code Code of error (lang code).
	 * @param string $message Optional message.
	 * @param bool $fatal Is fatal error.
	 * @return void
	 */
	protected function addError($code, $message = '', $fatal = false)
	{
		if ($message == '')
		{
			$message = Loc::getMessage($code);
		}
		$this->errors[$code] = new Error($message != '' ? $message : $code, $code);
		if ($fatal)
		{
			$this->arResult['FATAL'] = true;
		}
	}

	/**
	 * Collect errors from result.
	 * @param Entity\AddResult|Entity\UpdateResult|Entity\DeleteResult $result Result.
	 * @return void
	 */
	protected function addErrorFromResult($result)
	{
		if (
			(
			$result instanceof Entity\AddResult ||
			$result instanceof Entity\UpdateResult ||
			$result instanceof Entity\DeleteResult
			) && !$result->isSuccess()
		)
		{
			foreach ($result->getErrors() as $error)
			{
				$this->addError(
					$error->getCode(),
					$error->getMessage()
				);
			}
		}
	}

	/**
	 * Copy Error from one to this.
	 * @param array|\Bitrix\Main\Error $errors Error or array of errors.
	 * @return void
	 */
	protected function setErrors($errors)
	{
		if (!is_array($errors))
		{
			$errors = array($errors);
		}
		foreach ($errors as $err)
		{
			if ($err instanceof Error)
			{
				$this->errors[$err->getCode()] = $err;
			}
		}
	}

	/**
	 * Get current errors.
	 * @param bool $string Convert Errors to string.
	 * @return array
	 */
	public function getErrors($string = true)
	{
		if ($string)
		{
			$errors = array();
			foreach ($this->errors as $error)
			{
				$errors[$error->getCode()] = $error->getMessage();
			}
			// replace some codes
			foreach ($errors as $code => $mess)
			{
				$mess = Loc::getMessage('LANDING_ERROR_' . $code);
				if ($mess)
				{
					$errors[$code] = Help::replaceHelpUrl($mess);
				}
			}
			return $errors;
		}
		else
		{
			return $this->errors;
		}
	}

	/**
	 * Get error from current by string code.
	 * @param string $code Error code.
	 * @return false|\Bitrix\Main\Error
	 */
	protected function getErrorByCode($code)
	{
		if (isset($this->errors[$code]))
		{
			return $this->errors[$code];
		}

		return false;
	}

	/**
	 * Get __FILE__.
	 * @return string
	 */
	protected function getFile()
	{
		return __FILE__;
	}

	/**
	 * Refresh current page.
	 * @param array $add New param.
	 * @return void
	 */
	protected function refresh(array $add = array())
	{
		$uriString = $this->currentRequest->getRequestUri();
		if ($add)
		{
			$uriSave = new \Bitrix\Main\Web\Uri($uriString);
			$uriSave->addParams($add);
			$uriString = $uriSave->getUri();
		}
		\LocalRedirect($uriString);
	}

	/**
	 * Get some var from request.
	 * @param string $var Code of var.
	 * @return mixed
	 */
	protected function request($var)
	{
		$result = $this->currentRequest[$var];
		return ($result !== null ? $result : '');
	}

	/**
	 * Return valid class from module.
	 * @param string $class Class name.
	 * @return string|false Full class name or false on failure.
	 */
	protected function getValidClass($class)
	{
		$class = '\\Bitrix\\Landing\\' . $class;
		if (
			class_exists($class) &&
			method_exists($class, 'getMap')
		)
		{
			return $class;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Gets last navigation object.
	 * @return \Bitrix\Main\UI\PageNavigation
	 */
	public function getLastNavigation()
	{
		return $this->lastNavigation;
	}

	/**
	 * Get items from some table.
	 * @param string $class Class code.
	 * @param array $params Params.
	 * @return array
	 */
	protected function getItems($class, $params = array())
	{
		$items = array();
		$class = $this->getValidClass($class);

		if ($class)
		{
			// make navigation
			if (isset($params['navigation']))
			{
				$this->lastNavigation = new \Bitrix\Main\UI\PageNavigation(
					$this::NAVIGATION_ID
				);
				$this->lastNavigation->allowAllRecords(false)
									->setPageSize($params['navigation'])
									->initFromUri();
				$params['offset'] = $this->lastNavigation->getOffset();
				$params['limit'] = $this->lastNavigation->getLimit();
			}

			/** @var Entity\DataManager $class */
			$res = $class::getList(array(
				'select' => array_merge(array(
					'*',

					'CREATED_BY_LOGIN' => 'CREATED_BY.LOGIN',
					'CREATED_BY_NAME' => 'CREATED_BY.NAME',
					'CREATED_BY_SECOND_NAME' => 'CREATED_BY.SECOND_NAME',
					'CREATED_BY_LAST_NAME' => 'CREATED_BY.LAST_NAME',

					'MODIFIED_BY_LOGIN' => 'MODIFIED_BY.LOGIN',
					'MODIFIED_BY_NAME' => 'MODIFIED_BY.NAME',
					'MODIFIED_BY_SECOND_NAME' => 'MODIFIED_BY.SECOND_NAME',
					'MODIFIED_BY_LAST_NAME' => 'MODIFIED_BY.LAST_NAME'
				), isset($params['select'])
							? $params['select']
							: array()),
				'filter' => isset($params['filter'])
							? $params['filter']
							: array(),
				'order' => isset($params['order'])
							? $params['order']
							: array(
								'ID' => 'asc'
							),
				'limit' => isset($params['limit'])
							? $params['limit']
							: null,
				'offset' => isset($params['offset'])
							? $params['offset']
							: null,
				'runtime' => isset($params['runtime'])
							? $params['runtime']
							: array(),
				'count_total' => isset($params['navigation'])
							? true
							: null
			));
			while ($row = $res->fetch())
			{
				$items[$row['ID']] = $row;
			}

			// make navigation
			if (isset($params['navigation']))
			{
				$this->lastNavigation->setRecordCount(
					$res->getCount()
				);
			}
		}

		return $items;
	}

	/**
	 * Get current sites.
	 * @param array $params Params.
	 * @return array
	 */
	protected function getSites($params = array())
	{
		if (!isset($params['filter']))
		{
			$params['filter'] = array();
		}
		if (
			isset($this->arParams['TYPE']) &&
			!isset($params['filter']['=TYPE'])
		)
		{
			if (
				Manager::isExtendedSMN() &&
				$this->arParams['TYPE'] == 'STORE'
			)
			{
				$params['filter']['=TYPE'] = [
					$this->arParams['TYPE'],
					'SMN'
				];
			}
			else
			{
				$params['filter']['=TYPE'] = $this->arParams['TYPE'];
			}
		}
		return $this->getItems('Site', $params);
	}

	/**
	 * Get current domains.
	 * @param array $params Params.
	 * @return array
	 */
	protected function getDomains($params = array())
	{
		\Bitrix\Landing\Domain::createDefault();
		return $this->getItems('Domain', $params);
	}

	/**
	 * Get current templates.
	 * @param array $params Params.
	 * @return array
	 */
	protected function getTemplates($params = array())
	{
		if (!isset($params['filter']))
		{
			$params['filter'] = array();
		}
		if (!isset($params['order']))
		{
			$params['order'] = array();
		}
		$params['filter']['=ACTIVE'] = 'Y';
		$params['order'] = array(
			'SORT' => 'ASC'
		);
		return $this->getItems('Template', $params);
	}

	/**
	 * Get some landings.
	 * @param array $params Params.
	 * @return array
	 */
	protected function getLandings($params = array())
	{
		return $this->getItems('Landing', $params);
	}

	/**
	 * Init script for initialization API keys.
	 * @return void
	 */
	public function initAPIKeys()
	{
		$googleImagesKey = Manager::getOption(
			'google_images_key',
			null
		);
		$googleImagesKey = \CUtil::jsEscape(
			(string) $googleImagesKey
		);
		$allowKeyChange = !preg_match(
			'/^[\w]+\.bitrix24\.[a-z]{2,3}$/i',
			$_SERVER['HTTP_HOST']
		);

		Asset::getInstance()->addString("
			<script>
				(function() {
					\"use strict\";
					BX.namespace(\"BX.Landing.Client.Google\");
					BX.Landing.Client.Google.key = \"".$googleImagesKey."\";
					BX.Landing.Client.Google.allowKeyChange = ".json_encode($allowKeyChange).";
				})();
			</script>
		");
	}

	/**
	 * Get loc::getMessage by type of site.
	 * @param string $code Mess code.
	 * @param array $replace Array for replace, e.g. array('#NUM#' => 5).
	 * @return string
	 */
	public function getMessageType($code, $replace = null)
	{
		static $codes = [];

		if (!array_key_exists($code, $codes))
		{
			$mess = Loc::getMessage($code . '_' . $this->arParams['TYPE'], $replace);
			if (!$mess)
			{
				$mess = Loc::getMessage($code, $replace);
			}
			$codes[$code] = $mess;
		}

		return $codes[$code];
	}

	/**
	 * Get actual rest path.
	 * @return string
	 */
	public function getRestPath()
	{
		return Manager::getRestPath();
	}

	/**
	 * Set timestamp for url.
	 * @param string $url Url.
	 * @return string
	 */
	protected function getTimestampUrl($url)
	{
		// temporary disable this function
		if (false && Manager::isB24())
		{
			return rtrim($url, '/') . '/?ts=' . time();
		}
		else
		{
			return $url;
		}
	}

	/**
	 * Gets instance of URI without some external params.
	 * @return \Bitrix\Main\Web\Uri
	 */
	protected function getUriInstance()
	{
		static $curUri = null;

		if ($curUri === null)
		{
			$curUri = new \Bitrix\Main\Web\Uri(
				$this->currentRequest->getRequestUri()
			);
			$curUri->deleteParams([
				'sessid', 'action', 'param', 'additional', 'code', 'tpl',
				'stepper', 'start', 'IS_AJAX', $this::NAVIGATION_ID
			]);
		}

		return $curUri;
	}

	/**
	 * Get URI without some external params.
	 * @param array $add Additional params.
	 * @return string
	 */
	protected function getUri(array $add = [])
	{
		$curUri = clone $this->getUriInstance();

		if ($add)
		{
			$curUri->addParams($add);
		}

		return $curUri->getUri();
	}

	/**
	 * Get URI path.
	 * @return string
	 */
	protected function getUriPath()
	{
		return $this->getUriInstance()->getPath();
	}

	/**
	 * Gets current file real name.
	 * @return string
	 */
	protected function getRealFile()
	{
		static $scriptName = null;

		if ($scriptName === null)
		{
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$server = $context->getServer();
			$scriptName = $server->get('REAL_FILE_PATH');
			if (!$scriptName)
			{
				$scriptName = $server->getScriptName();
			}
		}

		return $scriptName;
	}

	/**
	 * Gets tasks for access part.
	 * @return array
	 */
	protected function getAccessTasks()
	{
		return \Bitrix\Landing\Rights::getAccessTasks();
	}

	/**
	 * Gets settings link by error code.
	 * @param string $errorCode Error code.
	 * @return string
	 */
	public function getSettingLinkByError($errorCode)
	{
		$params = $this->arParams;
		if (preg_match('/^(PUBLIC_HTML_DISALLOWED)\[([S,L]{1})([\d]+)\]$/i', $errorCode, $matches))
		{
			if (
				$matches[2] == 'S' &&
				isset($params['SEF']['site_edit'])
			)
			{
				$editPage = $params['SEF']['site_edit'];
				$editPage = str_replace(
					'#site_edit#',
					$matches[3],
					$editPage
				);
			}
			else if (
				$matches[2] == 'L' &&
				isset($params['SEF']['landing_edit'])
			)
			{
				if (!isset($params['SITE_ID']))
				{
					$res = Landing::getList([
						'select' => [
							'SITE_ID'
						],
						'filter' => [
							'ID' => $matches[3]
						]
	 				]);
					if ($row = $res->fetch())
					{
						$params['SITE_ID'] = $row['SITE_ID'];
					}
					unset($row, $res);

				}
				$editPage = $params['SEF']['landing_edit'];
				$editPage = str_replace(
					['#site_show#', '#landing_edit#'],
					[$params['SITE_ID'], $matches[3]],
					$editPage
				);
			}
			if (isset($editPage))
			{
				$editPage .= '#' . strtolower($matches[1]);
				unset($params, $matches);
				return '<a href="' . $editPage . '">' . Loc::getMessage('LANDING_GOTO_EDIT') . '</a>';
			}
		}
		unset($params);

		return '';
	}

	/**
	 * Detect, if error occurred on small tarrifs.
	 * @param string $errorCode Error code.
	 * @return bool
	 */
	public function isTariffError($errorCode)
	{
		static $tariffsCodes = [
			'PUBLIC_PAGE_REACHED',
			'PUBLIC_SITE_REACHED',
			'TOTAL_SITE_REACHED',
			'PUBLIC_HTML_DISALLOWED'
		];

		foreach ($tariffsCodes as $code)
		{
			if (strpos($errorCode, $code) === 0)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Proxy rest methods, that we can redefine an answer.
	 * @throws ReflectionException
	 * @throws \Bitrix\Main\ArgumentException
	 * @return void
	 */
	protected function restProxy()
	{
		Manager::getApplication()->restartBuffer();
		header('Content-Type: application/json');
		$ajaxResult = \Bitrix\Landing\PublicAction::ajaxProcessing();

		// redefine errors
		if ($ajaxResult['type'] == 'error')
		{
			$ajaxResult['error_type'] = 'common';
			if (isset($ajaxResult['result']))
			{
				foreach ($ajaxResult['result'] as &$error)
				{
					if ($this->isTariffError($error['error']))
					{
						$ajaxResult['error_type'] = 'payment';
						$error['error_description'] .= $this->getSettingLinkByError(
							$error['error']
						);
					}
				}
				unset($error);
			}
		}

		echo \Bitrix\Main\Web\Json::encode($ajaxResult);
		\CMain::finalActions();
		unset($ajaxResult);
		die();
	}

	/**
	 * Initiates user options from storage.
	 * @return void
	 */
	protected function initUserOption(): void
	{
		if ($this->userOptions === null)
		{
			$this->userOptions = \CUserOptions::getOption('landing', 'editor_option');
			if (!is_array($this->userOptions))
			{
				$this->userOptions = [];
			}
		}
	}

	/**
	 * Save some data for current user.
	 * @param string $key Key of value.
	 * @param mixed $value Mixed value.
	 * @return void
	 */
	protected function setUserOption(string $key, $value): void
	{
		$this->initUserOption();
		$this->userOptions[$key] = $value;
		\CUserOptions::setOption('landing', 'editor_option', $this->userOptions);
	}

	/**
	 * Returns some user data by key.
	 * @param string $key Option key.
	 * @return mixed|null
	 */
	protected function getUserOption(string $key)
	{
		$this->initUserOption();
		if (array_key_exists($key, $this->userOptions))
		{
			return $this->userOptions[$key];
		}
		return null;
	}


	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if (!$init)
		{
			return;
		}

		$this->getRestPath();
		$action = $this->request('action');
		$param = $this->request('param');
		$additional = $this->request('additional');
		$componentName = $this->request('componentName');
		$this->arResult['CUR_URI'] = $this->getUri();

		// some action
		if ($this->request('actionType') == 'rest')
		{
			if (!$componentName || $this->getName() == $componentName)
			{
				$this->restProxy();
			}
		}
		else if (
			$action &&
			check_bitrix_sessid() &&
			$this->request('actionType') == 'json' &&
			is_callable(array($this, 'action' . $action))
		)
		{
			Manager::getApplication()->restartBuffer();
			header('Content-Type: application/json');
			echo \Bitrix\Main\Web\Json::encode(
				$this->{'action' . $action}($param, $additional)
			);
			\CMain::finalActions();
			die();
		}
		else if ($action && is_callable(array($this, 'action' . $action)))
		{
			if (
				check_bitrix_sessid() &&
				$this->{'action' . $action}($param, $additional)
				|| !check_bitrix_sessid()
			)
			{
				\localRedirect($this->arResult['CUR_URI']);
			}
		}

		if (!isset($this->arResult['FATAL']))
		{
			$this->arResult['FATAL'] = !$init;
		}
		$this->arResult['ERRORS'] = $this->getErrors();

		$this->IncludeComponentTemplate($this->template);
	}
}
