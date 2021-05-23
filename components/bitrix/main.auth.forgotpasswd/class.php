<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Application;

\CBitrixComponent::includeComponentClass('bitrix:main.auth.form');

class MainForgotPasswdComponent extends MainAuthFormComponent
{
	/**
	 * Processing request new pass.
	 * @return void
	 */
	protected function actionRequest()
	{
		if (!defined('ADMIN_SECTION') || ADMIN_SECTION !== true)
		{
			$lid = LANG;
		}
		else
		{
			$lid = false;
		}

		$res = \CUser::SendPassword(
			$this->requestField('login'),
			$this->requestField('email'),
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
		// check authorization
		if ($this->isAuthorized())
		{
			$this->arResult['AUTHORIZED'] = true;
			$this->IncludeComponentTemplate();
			return;
		}

		// init vars
		$request = Application::getInstance()->getContext()->getRequest();

		// tpl vars
		$this->arResult['SUCCESS'] = null;
		$this->arResult['FIELDS'] = $this->formFields;
		$this->arResult['LAST_LOGIN'] = $request->getCookie(
			'LOGIN'
		);
		$this->arResult['AUTH_AUTH_URL'] = $this->checkParam(
			'AUTH_AUTH_URL',
			''
		);
		$this->arResult['AUTH_REGISTER_URL'] = $this->checkParam(
			'AUTH_REGISTER_URL',
			''
		);
		if ($this->getOption('captcha_restoring_password', 'N') == 'Y')
		{
			$this->arResult['CAPTCHA_CODE'] = $this->getApplication()->CaptchaGetCode();
		}
		else
		{
			$this->arResult['CAPTCHA_CODE'] = '';
		}

		// processing
		if ($this->requestField('action'))
		{
			$this->actionRequest();
		}

		$this->arResult['ERRORS'] = $this->getErrors();

		if ($applyTemplate)
		{
			$this->IncludeComponentTemplate();
		}
	}
}