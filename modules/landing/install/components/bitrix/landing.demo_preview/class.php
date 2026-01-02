<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Hook;
use Bitrix\Landing\Hook\Page\Theme;
use Bitrix\Landing\Site\Type;
use Bitrix\Landing\Rights;
use \Bitrix\Landing\Mainpage;
use Bitrix\Main\Config\Option;
use \Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use \Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Uri;

CBitrixComponent::includeComponentClass('bitrix:landing.demo');

class LandingSiteDemoPreviewComponent extends LandingSiteDemoComponent
{
	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$init = $this->init();

		if ($init)
		{
			$this->checkParam('SITE_ID', 0);
			$this->checkParam('LANG_ID', '');
			$this->checkParam('ADMIN_SECTION', 'N');
			$this->checkParam('CODE', '');
			$this->checkParam('TYPE', '');
			$this->checkParam('SITE_WORK_MODE', 'N');
			$this->checkParam('DONT_LEAVE_FRAME', 'N');
			$this->checkParam('DISABLE_REDIRECT', 'N');
			$this->checkParam('BINDING_TYPE', '');
			$this->checkParam('BINDING_ID', '');
			$this->checkParam('ACTION_FOLDER', 'folderId');

			Type::setScope(
				$this->arParams['TYPE']
			);

			$code = $this->arParams['CODE'];
			$this->getRemoteTemplates = true;
			$demo = $this->getDemoPage($code);

			if (isset($demo[$code]))
			{
				if (isset($demo[$code]['LABELS']))
				{
					$labels = $demo[$code]['LABELS'];
					$bySubscription = array_reduce($labels, static function($lastRes, $label) {
						return $lastRes || $label['CODE'] === 'subscription';
					}, false);
				}

				if ($demo[$code]['REST'] > 0)
				{
					$demo[$code]['DATA'] = $this->getTemplateManifest(
						$demo[$code]['REST']
					);
				}

				$this->arResult['EXTERNAL_IMPORT'] = [];
				$this->arResult['TEMPLATE'] = $demo[$code];
				$this->arResult['TEMPLATE']['URL_PREVIEW'] = $this->getUrlPreview($code, $demo[$code]);
				$this->arResult['RIGHTS_CREATE'] = Rights::hasAdditionalRight(
					Rights::ADDITIONAL_RIGHTS['create']
				);
				$this->arResult['NEEDED_SUBSCRIPTION'] = $bySubscription ?? false;

				// check external import (additional step after submit create)
				$event = new Event('landing', 'onBuildTemplateCreateUrl', array(
					'code' => $code,
					'uri' => $this->getUri()
				));
				$event->send();
				foreach ($event->getResults() as $result)
				{
					if (($result->getType() != EventResult::ERROR) && ($modified = $result->getModified()))
					{
						if (isset($modified['onclick']))
						{
							$this->arResult['EXTERNAL_IMPORT']['onclick'] = $modified['onclick'];
						}
						if (isset($modified['href']))
						{
							$this->arResult['EXTERNAL_IMPORT']['href'] = $modified['href'];
						}
					}
				}
				unset($event, $result);

				// disable import
				if (isset($demo[$code]['DATA']['disable_import']) &&
					$demo[$code]['DATA']['disable_import'] === 'Y')
				{
					$this->arResult['DISABLE_IMPORT'] = true;
				}
				else
				{
					$this->arResult['DISABLE_IMPORT'] = false;
				}

				// folder
				if ($this->request($this->arParams['ACTION_FOLDER']))
				{
					$this->arResult['FOLDER_ID'] = (int)$this->request($this->arParams['ACTION_FOLDER']);
				}

				// replace landing instead create new
				if ((int)$this->request('replaceLid') > 0)
				{
					$this->arParams['REPLACE_LID'] = (int)$this->request('replaceLid');
				}

				if ($this->request('specType') === Type::PSEUDO_SCOPE_CODE_FORMS)
				{
					$this->arParams['IS_CRM_FORM'] = 'Y';
				}

				if ($this->arParams['TYPE'] === Type::SCOPE_CODE_MAINPAGE)
				{
					$manager = new Mainpage\Manager();
					$this->arParams['MAINPAGE_EXISTS'] = (bool)$manager->getConnectedPageId();
				}
			}
			else
			{
				$this->arResult['TEMPLATE'] = array();
			}
		}

		parent::executeComponent();
	}
}