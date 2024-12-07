<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass('bitrix:main.auth.forgotpasswd');

class MainChangePasswdComponent extends MainForgotPasswdComponent
{
	/**
	 * Gets group policy of current user.
	 * @return array
	 */
	protected function getGroupPolicy()
	{
		$userId = 0;

		if ($this->arResult['LAST_LOGIN'] <> '')
		{
			$res = \CUser::GetByLogin($this->arResult['LAST_LOGIN']);
			if ($profile = $res->Fetch())
			{
				$userId = $profile['ID'];
			}
		}

		return \CUser::GetGroupPolicy($userId);
	}

	/**
	 * Replace some params from GET.
	 * @return void
	 */
	protected function requestForTpl()
	{
		$requestParams = array(
			'checkword',
			'password',
			'confirm_password',
		);

		foreach ($requestParams as $param)
		{
			$code = $this->formFields[$param];
			if ($this->request($code))
			{
				$this->arResult[$code] = $this->request($code);
			}
		}
	}

	/**
	 * Processing request set new pass.
	 * @return void
	 */
	protected function actionRequest()
	{
		$password = $password2 = null;

		// check encrypt
		if ($this->getOption('use_encrypted_auth', 'N') == 'Y')
		{
			$sec = new \CRsaSecurity();
			if (($keys = $sec->LoadKeys()))
			{
				$sec->SetKeys($keys);
				$errno = $sec->AcceptFromForm([
					$this->formFields['password'],
					$this->formFields['confirm_password']
				]);
				if ($errno == CRsaSecurity::ERROR_SESS_CHECK)
				{
					$this->addError(
						'ERROR_SESSION',
						Loc::getMessage('MAIN_AUTH_CHD_SESS_EXPIRED')
					);
				}
				elseif ($errno < 0)
				{
					$this->addError(
						'ERROR_DECODE',
						Loc::getMessage('MAIN_AUTH_CHD_ERR_DECODE', [
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
			if (isset($_REQUEST[$this->formFields['confirm_password']]))
			{
				$password2 = $_REQUEST[$this->formFields['confirm_password']];
			}
		}
		else
		{
			$password = $this->requestField('password');
			$password2 = $this->requestField('confirm_password');
		}

		if (!defined('ADMIN_SECTION') || ADMIN_SECTION !== true)
		{
			$lid = LANG;
		}
		else
		{
			$lid = false;
		}

		// change pass
		$res = $this->getUser()->ChangePassword(
			$this->requestField('login'),
			$this->requestField('checkword'),
			$password,
			$password2,
			$lid,
			$this->request('captcha_word'),
			$this->request('captcha_sid')
		);

		if (
			!$this->processingErrors($res) &&
			isset($res['MESSAGE'])
		)
		{
			$this->arResult['SUCCESS'] = $res['MESSAGE'];
		}
	}

	/**
	 * Base executable method.
	 * @param boolean $applyTemplate Apply template or not.
	 * @return void
	 */
	public function executeComponent($applyTemplate = true)
	{
		parent::executeComponent(false);

		// replace last_login with request data
		$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
		if (
			!$request->isPost() &&
			$request->get('USER_LOGIN')
		)
		{
			$this->arResult['LAST_LOGIN'] = $request->get('USER_LOGIN');
		}
		else if ($request->getPost('USER_LOGIN'))
		{
			$this->arResult['LAST_LOGIN'] = $request->getPost('USER_LOGIN');
		}

		// tpl vars
		$this->arResult['GROUP_POLICY'] = $this->getGroupPolicy();
		$this->arResult['SECURE_AUTH'] = $this->isSecureAuth();
		$this->requestForTpl();

		$this->IncludeComponentTemplate();
	}
}