<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Help;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Error;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Page\Asset;

class LandingBaseComponent extends \CBitrixComponent
{
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

		return $init;
	}

	/**
	 * Check var in arParams. If no exists, create with default val.
	 * @param type $var Variable.
	 * @param type $default Default value.
	 * @return void
	 */
	protected function checkParam($var, $default)
	{
		if (!isset($this->arParams[$var]))
		{
			$this->arParams[$var] = $default;
		}
	}

	/**
	 * Add one more error.
	 * @param string $code Code of error (lang code).
	 * @param string $message Optional message.
	 * @return void
	 */
	protected function addError($code, $message = '')
	{
		if ($message == '')
		{
			$message = Loc::getMessage($code);
		}
		$this->errors[$code] = new Error($message != '' ? $message : $code, $code);
	}

	/**
	 * Collect errors from result.
	 * @param Entity\AddResult|UpdateResult|DeleteResult $result Result.
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
	protected function getErrors($string = true)
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
	 * @return void
	 */
	protected function refresh()
	{
		$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
		$uriString = $request->getRequestUri();
		\LocalRedirect($uriString);
	}

	/**
	 * Get some var from request.
	 * @param string $var Code of var.
	 * @return mixed
	 */
	protected function request($var)
	{
		static $request = null;

		if ($request === null)
		{
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$request = $context->getRequest();
		}

		return isset($request[$var]) ? $request[$var] : '';
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
			$res = $class::getList(array(
				'select' => array_merge(array(
					'*',

					'CREATED_BY_LOGIN' => 'CREATED_BY.LOGIN',
					'CREATED_BY_LOGIN' => 'CREATED_BY.LOGIN',
					'CREATED_BY_NAME' => 'CREATED_BY.NAME',
					'CREATED_BY_SECOND_NAME' => 'CREATED_BY.SECOND_NAME',
					'CREATED_BY_LAST_NAME' => 'CREATED_BY.LAST_NAME',

					'MODIFIED_BY_LOGIN' => 'MODIFIED_BY.LOGIN',
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
							: 0,
				'runtime' => isset($params['runtime'])
							? $params['runtime']
							: array()
			));
			while ($row = $res->fetch())
			{
				$items[$row['ID']] = $row;
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
			$params['filter']['=TYPE'] = $this->arParams['TYPE'];
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
		$googleImagesKey = \Bitrix\Landing\Manager::getOption(
			'googleImages',
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
	 * @return string
	 */
	public function getMessageType($code)
	{
		$mess = Loc::getMessage($code . '_' . $this->arParams['TYPE']);
		if (!$mess)
		{
			$mess = Loc::getMessage($code);
		}
		return $mess;
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();
		$action = $this->request('action');
		$param = $this->request('param');
		$additional = $this->request('additional');

		// some action
		if ($action && is_callable(array($this, 'action' . $action)))
		{
			if (
				check_bitrix_sessid() &&
				$this->{'action' . $action}($param, $additional)
				|| !check_bitrix_sessid()
			)
			{
				$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
				$curUri = new \Bitrix\Main\Web\Uri($request->getRequestUri());
				$curUri->deleteParams(array('sessid', 'action', 'param', 'additional'));
				\localRedirect($curUri->getUri());
			}
		}

		$this->arResult['FATAL'] = !$init;
		$this->arResult['ERRORS'] = $this->getErrors();

		$this->IncludeComponentTemplate($this->template);
	}
}