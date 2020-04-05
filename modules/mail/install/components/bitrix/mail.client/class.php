<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule('mail');

class CMailClientComponent extends CBitrixComponent
{

	public function executeComponent()
	{
		global $USER, $APPLICATION;

		if (empty($this->arParams['PATH_TO_USER_TASKS_TASK']))
		{
			$this->arParams['PATH_TO_USER_TASKS_TASK'] = \Bitrix\Main\Config\Option::get(
				'tasks',
				'paths_task_user_action',
				'/company/personal/user/#user_id#/tasks/task/#action#/#task_id#/',
				SITE_ID
			);
		}

		$componentPage = '';
		$variables = array();

		if ($this->arParams['SEF_MODE'] == 'Y')
		{
			$defaultUrlTemplates = array(
				'home'      => '',
				'config'    => 'config/#act#',
				'msg_new'   => 'message/new',
				'blacklist' => 'blacklist',
				'signatures' => 'signatures',
				'signature' => 'signature/#id#',
				'msg_view'  => 'message/#id#',
				'msg_list'  => 'list/#id#',
			);
		}
		else
		{
			$defaultUrlTemplates = array(
				'home'      => '',
				'config'    => 'page=config&act=#act#',
				'msg_new'   => 'page=msg_new',
				'blacklist' => 'page=blacklist',
				'signatures' => 'page=signatures',
				'signature' => 'page=signature&id=#id#',
				'msg_view'  => 'page=msg_view&id=#id#',
				'msg_list'  => 'page=msg_list&id=#id#',
			);
		}

		if ($this->arParams['SEF_MODE'] == 'Y')
		{
			$urlTemplates  = \CComponentEngine::makeComponentUrlTemplates($defaultUrlTemplates, $this->arParams['SEF_URL_TEMPLATES']);
			$componentPage = \CComponentEngine::parseComponentPath($this->arParams['SEF_FOLDER'], $urlTemplates, $variables);

			foreach ($urlTemplates as $page => $path)
			{
				$this->arResult['PATH_TO_MAIL_'.strtoupper($page)] = $this->arParams['SEF_FOLDER'] . $path;
			}
		}
		else
		{
			if (!empty($_REQUEST['page']))
				$componentPage = $_REQUEST['page'];

			\CComponentEngine::initComponentVariables(false, array('id'), array(), $variables);

			foreach ($defaultUrlTemplates as $page => $path)
			{
				$this->arResult['PATH_TO_MAIL_'.strtoupper($page)] = $APPLICATION->getCurPage() . '?' . $path;
			}
		}

		if (empty($componentPage) || !array_key_exists($componentPage, $defaultUrlTemplates))
			$componentPage = 'home';

		$this->arResult['VARIABLES'] = $variables;

		$this->arResult['PATH_TO_USER_TASKS_TASK'] = \CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_USER_TASKS_TASK'],
			array('user_id' => $USER->getId())
		);

		$APPLICATION->setAdditionalCSS('/bitrix/components/bitrix/mail.client.sidepanel/templates/.default/style.css');
		$APPLICATION->setAdditionalCSS('/bitrix/components/bitrix/mail.client.config/templates/.default/style.css');
		$APPLICATION->setAdditionalCSS('/bitrix/components/bitrix/mail.contact.avatar/templates/.default/style.css');

		$APPLICATION->setAdditionalCSS('/bitrix/components/bitrix/main.interface.buttons/templates/.default/style.css');

		$this->includeComponentTemplate($componentPage);
	}

	public function includePageComponent($name, $template, &$params)
	{
		global $APPLICATION;

		if (isset($_REQUEST['IFRAME']) && $_REQUEST['IFRAME'] == 'Y')
		{
			$APPLICATION->includeComponent(
				'bitrix:mail.client.sidepanel',
				'',
				array(
					'COMPONENT_ARGUMENTS' => array($name, $template, $params, $this),
				)
			);
		}
		else
		{
			$APPLICATION->includeComponent($name, $template, $params, $this);
		}
	}

}
