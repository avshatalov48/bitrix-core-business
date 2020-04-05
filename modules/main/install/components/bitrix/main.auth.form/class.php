<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Application;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Error;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Security\Mfa;

Loc::loadMessages(__FILE__);

class MainAuthFormComponent extends \CBitrixComponent
{
	/**
	 * Current errors.
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Fields code for form html.
	 * @var array
	 */
	protected $formFields = [
		'email' => 'USER_EMAIL',
		'login' => 'USER_LOGIN',
		'password' => 'USER_PASSWORD',
		'confirm_password' => 'USER_CONFIRM_PASSWORD',
		'remember' => 'USER_REMEMBER',
		'checkword' => 'USER_CHECKWORD',
		'otp' => 'USER_OTP',
		'otp_remember' => 'OTP_REMEMBER',
		'action' => 'AUTH_ACTION'
	];

	/**
	 * Get global Application.
	 * @return \CMain
	 */
	protected function getApplication()
	{
		$application = null;

		if ($application === null)
		{
			$application = $GLOBALS['APPLICATION'];
		}

		return $application;
	}

	/**
	 * Get global User.
	 * @return \CUser
	 */
	protected function getUser()
	{
		$user = null;

		if ($user === null)
		{
			$user = $GLOBALS['USER'];
		}

		return $user;
	}

	/**
	 * Return true if current user is authorized.
	 * @return boolean
	 */
	protected function isAuthorized()
	{
		return $this->getUser()->isAuthorized();
	}

	/**
	 * Gets some option of main module by code.
	 * @param string $code Option code.
	 * @param mixed $default Default value.
	 * @return mixed
	 */
	protected function getOption($code, $default = null)
	{
		return Option::get('main', $code, $default);
	}

	/**
	 * Check var in arParams. If no exists, create with default val.
	 * @param string|int $var Variable.
	 * @param mixed $default Default value.
	 * @return mixed
	 */
	protected function checkParam($var, $default)
	{
		if (!isset($this->arParams[$var]))
		{
			$this->arParams[$var] = $default;
		}
		return $this->arParams[$var];
	}

	/**
	 * Get URI without.
	 * @return string
	 */
	protected function getUri()
	{
		static $uri = null;

		if ($uri === null)
		{
			$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
			$curUri = new \Bitrix\Main\Web\Uri($request->getRequestUri());
			$uri = $curUri->getUri();
		}

		return $uri;
	}

	/**
	 * Get some var from request.
	 * @param string $fieldCode Code of $this->formFields.
	 * @return mixed
	 */
	protected function requestField($fieldCode)
	{
		static $request = null;

		if ($request === null)
		{
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$request = $context->getRequest();
		}

		$code = isset($this->formFields[$fieldCode])
				? $this->formFields[$fieldCode]
				: $fieldCode;

		return isset($request[$code]) ? $request[$code] : '';
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
	 * Add one more error.
	 * @param string $code Code of error.
	 * @param string $message Error message.
	 * @return void
	 */
	protected function addError($code, $message)
	{
		$this->errors[$code] = new Error($message, $code);
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
			$errors = [];
			foreach ($this->errors as $error)
			{
				$errors[$error->getCode()] = $error->getMessage();
			}
			return $errors;
		}
		else
		{
			return $this->errors;
		}
	}

	/**
	 * Get error from action.
	 * @param mixed $res Some resource.
	 * @return boolean
	 */
	protected function processingErrors($res)
	{
		$error = false;

		if (
			isset($res['TYPE']) &&
			$res['TYPE'] == 'ERROR'
		)
		{
			$error = true;
			if (
				isset($res['MESSAGE']) &&
				trim($res['MESSAGE'])
			)
			{
				$this->addError('ERROR_PROCESSING', $res['MESSAGE']);
			}
			else
			{
				$this->addError('ERROR_PROCESSING', 'Error occurred.');
			}
		}

		return $error;
	}

	/**
	 * Gets captcha code if need.
	 * @param string $login Login.
	 * @return string
	 */
	protected function getCaptchaCodeForUser($login)
	{
		$code = null;
		$application = $this->getApplication();

		if ($application->NeedCAPTHAForLogin($login))
		{
			$code = $application->CaptchaGetCode();
		}

		return $code;
	}

	/**
	 * Processing social services authorization, and return array of its.
	 * @return array
	 */
	protected function processingAuthServices()
	{
		$services = null;
		$intranet = false;

		if (
			ModuleManager::isModuleInstalled('intranet') ||
			ModuleManager::isModuleInstalled('rest')
		)
		{
			$intranet = true;
		}

		if (
			!$this->isAuthorized() &&
			\Bitrix\Main\Loader::includeModule('socialservices') &&
			$this->getOption('allow_socserv_authorization', 'Y') == 'Y'
		)
		{
			$oAuthManager = new \CSocServAuthManager();
			$authServices = $oAuthManager->GetActiveAuthServices([
			  	'BACKURL' => $this->getUri(),
				'FOR_INTRANET' => $intranet
			]);
			if (!empty($authServices))
			{
				$services = $authServices;
				// try authorization throw socialservices
				$requestAuthId = $this->request('auth_service_id');
				if (isset($services[$requestAuthId]))
				{
					if ($this->request('auth_service_error'))
					{
						$this->addError(
							'ERROR_SOCSERVICES',
							$oAuthManager->GetError(
								$requestAuthId,
								$this->request('auth_service_error')
							)
						);
					}
					elseif (!$oAuthManager->Authorize($requestAuthId))
					{
						$ex = $this->getApplication()->GetException();
						if ($ex)
						{
							$this->addError(
								'ERROR_SOCSERVICES',
								$ex->GetString()
							);
						}
					}
				}
			}
		}

		return $services;
	}

	/**
	 * Is secure auth connection?
	 * @return boolean
	 */
	protected function isSecureAuth()
	{
		$secure = false;
		$isHttps = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps();
		if (
			!$isHttps &&
			$this->getOption('use_encrypted_auth', 'N') == 'Y'
		)
		{
			$sec = new \CRsaSecurity();
			if (($keys = $sec->LoadKeys()))
			{
				$sec->SetKeys($keys);
				$sec->AddToForm($this->arResult['FORM_ID'], [
					$this->formFields['password'],
					$this->formFields['confirm_password']
				]);
				$secure = true;
			}
		}

		return $secure;
	}

	/**
	 * Processing login.
	 * @return void
	 */
	protected function actionLogin()
	{
		$password = null;

		// store pass checkbox
		$storePassword = $this->arResult['STORE_PASSWORD'];
		if ($storePassword == 'Y')
		{
			$storePassword = $this->requestField('remember');
			if (!$storePassword)
			{
				$storePassword = 'N';
			}
		}

		// check encrypt
		if ($this->getOption('use_encrypted_auth', 'N') == 'Y')
		{
			$sec = new \CRsaSecurity();
			if (($keys = $sec->LoadKeys()))
			{
				$sec->SetKeys($keys);
				$errno = $sec->AcceptFromForm([
					$this->formFields['password']
				]);
				if ($errno == CRsaSecurity::ERROR_SESS_CHECK)
				{
					$this->addError(
						'ERROR_SESSION',
						Loc::getMessage('MAIN_AUTH_FORM_SESS_EXPIRED')
					);
				}
				elseif ($errno < 0)
				{
					$this->addError(
						'ERROR_DECODE',
						Loc::getMessage('MAIN_AUTH_FORM_ERR_DECODE', [
							'#ERRCODE#' => $errno
						])
					);
				}
			}
			// replace password from global var
			if (isset($_REQUEST[$this->formFields['password']]))
			{
				$password = $_REQUEST[$this->formFields['password']];
			}
		}
		else
		{
			$password = $this->requestField('password');
		}

		// login and redirect on success
		$res = $this->getUser()->Login(
			$this->requestField('login'),
			$password,
			$storePassword
		);
		if (
			!$this->processingErrors($res)
		)
		{
			if ($this->isOtpRequired(true))
			{
				if ($this->request('auth') == 'yes')
				{
					$this->refresh([
						'auth'
			   		]);
				}
			}
			else
			{
				$this->successRedirect();
			}
		}
	}

	/**
	 * Processing OTP login.
	 * @return void
	 */
	protected function actionOtp()
	{
		// store code checkbox
		$storeCode = $this->arResult['REMEMBER_OTP'];
		if ($storeCode == 'Y')
		{
			$storeCode = $this->requestField('otp_remember');
		}
		if (!$storeCode)
		{
			$storeCode = 'N';
		}
		// login and redirect
		$res = $this->getUser()->LoginByOtp(
			$this->requestField('otp'),
			$storeCode,
			$this->request('captcha_word'),
			$this->request('captcha_sid')
		);
		if (!$this->processingErrors($res))
		{
			$this->successRedirect();
		}
	}

	/**
	 * Redirect to the success page.
	 * @return void
	 */
	protected function successRedirect()
	{
		if ($this->arParams['AUTH_SUCCESS_URL'])
		{
			\localRedirect(
				$this->arParams['AUTH_SUCCESS_URL']
			);
		}
	}

	/**
	 * Refresh current page.
	 * @param array $params Delete or add params.
	 * @return void
	 */
	protected function refresh(array $params = array())
	{
		$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
		$uriString = $request->getRequestUri();
		if (
			isset($params['add']) && is_array($params['add']) ||
			isset($params['delete']) && is_array($params['delete'])
		)
		{
			$uriSave = new \Bitrix\Main\Web\Uri($uriString);
			if (isset($params['add']))
			{
				$uriSave->addParams($params['add']);
			}
			if (isset($params['delete']))
			{
				$uriSave->deleteParams($params['delete']);
			}
			$uriString = $uriSave->getUri();
		}
		\localRedirect($uriString);
	}

	/**
	 * OTP required?
	 * @param boolean $ignoreReset Ignore reset otp by param.
	 * @return bool
	 */
	protected function isOtpRequired($ignoreReset = false)
	{
		return 	!$this->isAuthorized() &&
			  	($this->request('auth') != 'yes' || $ignoreReset) &&
			 	\Bitrix\Main\Loader::includeModule('security') &&
			   	\Bitrix\Security\Mfa\Otp::isOtpRequired();
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		// check authorization
		if ($this->isAuthorized())
		{
			$this->arResult['AUTHORIZED'] = true;
			$this->IncludeComponentTemplate();
			return;
		}

		// init vars
		$request = Application::getInstance()->getContext()->getRequest();

		// prepare params
		$this->checkParam('AUTH_SUCCESS_URL', '');

		// tpl vars
		$this->arResult['AUTHORIZED'] = false;
		$this->arResult['OTP_REQUIRED_BY_MANDATORY'] = false;
		$this->arResult['FORM_ID'] = 'form_auth';
		$this->arResult['FIELDS'] = $this->formFields;
		$this->arResult['CURR_URI'] = $this->getUri();
		$this->arResult['AUTH_SERVICES'] = $this->processingAuthServices();
		$this->arResult['LAST_LOGIN'] = $request->getCookie(
			'LOGIN'
		);
		$this->arResult['SECURE_AUTH'] = $this->isSecureAuth();
		$this->arResult['CAPTCHA_CODE'] = $this->getCaptchaCodeForUser(
			$this->arResult['LAST_LOGIN']
		);
		$this->arResult['STORE_PASSWORD'] = $this->getOption(
			'store_password',
			'Y'
		) == 'Y' ? 'Y' : 'N';
		$this->arResult['AUTH_FORGOT_PASSWORD_URL'] = $this->checkParam(
			'AUTH_FORGOT_PASSWORD_URL',
			''
		);
		$this->arResult['AUTH_REGISTER_URL'] = $this->checkParam(
			'AUTH_REGISTER_URL',
			''
		);
		$this->arResult['REMEMBER_OTP'] = Option::get('security', 'otp_allow_remember') == 'Y';

		// processing
		if ($this->requestField('action'))
		{
			if ($this->isOtpRequired())
			{
				$this->actionOtp();
			}
			else
			{
				$this->actionLogin();
			}
		}

		$this->arResult['ERRORS'] = $this->getErrors();
		$otp = $this->isOtpRequired();

		// otp required
		if ($otp)
		{
			if (Mfa\Otp::isOtpRequiredByMandatory())
			{
				$this->arResult['OTP_REQUIRED_BY_MANDATORY'] = true;
			}
			else if (Mfa\Otp::isCaptchaRequired())
			{
				$this->arResult['CAPTCHA_CODE'] = $this->getApplication()->CaptchaGetCode();
			}
			$uriString = $request->getRequestUri();
			$uriAuth = new \Bitrix\Main\Web\Uri($uriString);
			$uriAuth->addParams([
					'auth' => 'yes'
				]);
			$this->arResult['AUTH_AUTH_URL'] = $uriAuth->getUri();
		}

		$this->IncludeComponentTemplate($otp ? 'otp' : '');
	}
}