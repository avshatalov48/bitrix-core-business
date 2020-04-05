<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;

\CBitrixComponent::includeComponentClass('bitrix:landing.demo');

class LandingSiteDemoPreviewComponent extends LandingSiteDemoComponent
{
	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if ($init)
		{
			$this->checkParam('SITE_ID', 0);
			$this->checkParam('CODE', '');
			$this->checkParam('TYPE', '');
			$this->checkParam('SITE_WORK_MODE', 'N');
			$this->checkParam('DONT_LEAVE_FRAME', 'N');
			$this->checkParam('BINDING_TYPE', '');
			$this->checkParam('BINDING_ID', '');

			\Bitrix\Landing\Site\Type::setScope(
				$this->arParams['TYPE']
			);

			$code = $this->arParams['CODE'];
			$demo = $this->getDemoPage($code);

			$this->instagramUrlRegister();//@tmp

			if (isset($demo[$code]))
			{
				// check if SITE GROUP
				if (
					isset($demo[$code]['DATA']['site_group']) &&
					$demo[$code]['DATA']['site_group'] == 'Y'
				)
				{
					$this->arResult['SITE_GROUP'] = $demo[$code]['DATA']['site_group_items'];
					
					$code = $this->arResult['SITE_GROUP'][0]['page']
							? $this->arResult['SITE_GROUP'][0]['code'] . '/' . $this->arResult['SITE_GROUP'][0]['page']
							: $this->arResult['SITE_GROUP'][0]['code'];
					
					foreach ($this->arResult['SITE_GROUP'] as $i => $site)
					{
						$this->arResult['SITE_GROUP'][$i]['url'] = $this->getUrlPreview(
							$site['page'] ? $site['code'] . '/' . $site['page'] : $site['code']
						);
					}
				}
				
				if ($demo[$code]['REST'] > 0)
				{
					$demo[$code]['DATA'] = $this->getTemplateManifest(
						$demo[$code]['REST']
					);
				}

				$this->arResult['EXTERNAL_IMPORT'] = [];
				$this->arResult['COLORS'] = \Bitrix\Landing\Hook\Page\Theme::getColorCodes();
				$this->arResult['TEMPLATE'] = $demo[$code];
				$this->arResult['TEMPLATE']['URL_PREVIEW'] = $this->getUrlPreview($code);
				// first color by default
				$this->arResult['THEME_CURRENT'] = array_shift(array_keys($this->arResult['COLORS']));

				// check external import (additional step after submit create)
				$event = new Event('landing', 'onBuildTemplateCreateUrl', array(
					'code' => $code,
					'uri' => $this->getUri()
				));
				$event->send();
				foreach ($event->getResults() as $result)
				{
					if ($result->getType() != EventResult::ERROR)
					{
						if (($modified = $result->getModified()))
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
				}
				unset($event, $result);

				// for NEW PAGE IN EXIST SITE - add option for inherit color
				if ($this->arParams['SITE_ID'])
				{
					$classFull = $this->getValidClass('Site');
					if ($classFull && method_exists($classFull, 'getHooks'))
					{
						\Bitrix\Landing\Hook::setEditMode();
						$hooks = $classFull::getHooks($this->arParams['SITE_ID']);
					}
					
					if (isset($hooks['THEME']) && isset($hooks['THEME']->getPageFields()['THEME_CODE']))
					{
						$this->arResult['THEME_SITE'] = $hooks['THEME']->getPageFields()['THEME_CODE']->getValue();
					}
					else
					{
						$this->arResult['THEME_SITE'] = $this->arResult['THEME_CURRENT'];
					}

					$this->checkColorExists($this->arResult['THEME_SITE']);
					$this->addColorToPallete($this->arResult['THEME_SITE']);

					// use color from template or use_site_theme
					$this->arResult['THEME_CURRENT'] =
						(isset($this->arResult['TEMPLATE']['DATA']['fields']['ADDITIONAL_FIELDS']['THEME_CODE']))
							? $this->arResult['TEMPLATE']['DATA']['fields']['ADDITIONAL_FIELDS']['THEME_CODE']
							: 'USE_SITE';
				}
				// NEW SITE - get theme from template (or default)
				else
				{
					if (isset($this->arResult['TEMPLATE']['DATA']['fields']['ADDITIONAL_FIELDS']['THEME_CODE']))
					{
						$this->arResult['THEME_CURRENT'] = $this->arResult['TEMPLATE']['DATA']['fields']['ADDITIONAL_FIELDS']['THEME_CODE'];
					}
				}
				
				$this->checkColorExists($this->arResult['THEME_CURRENT']);
				$this->addColorToPallete($this->arResult['THEME_CURRENT']);

				// disable import
				if (isset($demo[$code]['DATA']['disable_import']) &&
					$demo[$code]['DATA']['disable_import'] == 'Y')
				{
					$this->arResult['DISABLE_IMPORT'] = true;
				}
				else
				{
					$this->arResult['DISABLE_IMPORT'] = false;
				}
			}
			else
			{
				$this->arResult['COLORS'] = array();
				$this->arResult['TEMPLATE'] = array();
			}
		}

		parent::executeComponent();
	}

	/**
	 * Temp function for register external instagram import.
	 * @return void
	 */
	private function instagramUrlRegister()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->addEventHandler('landing', 'onBuildTemplateCreateUrl',
			function(\Bitrix\Main\Event $event)
			{
				$result = new \Bitrix\Main\Entity\EventResult;
				$uri = $event->getParameter('uri');
				$code = $event->getParameter('code');

				if (
					($code == 'store-instagram/mainpage') &&
					\Bitrix\Main\Loader::includeModule('crm')
				)
				{
					// build url for create site
					$uriSelect = new \Bitrix\Main\Web\Uri($uri);
					$uriSelect->addParams([
						'action' => 'select',
						'param' => $code,
						'sessid' => bitrix_sessid(),
						'additional' => [
							//TODO: change to method from \Bitrix\Crm\Order\Import\Instagram - get section XML_ID
							'section' => 'instagram'
						]
					]);
					// removed dependency from crm instagram feature
					// @see \Bitrix\Crm\Order\Import\Instagram::isSiteTemplateImportable
					$externalImportPath = (string) \Bitrix\Main\Config\Option::get(
						'crm', 'path_to_order_import_instagram'
					);
					$uriCreate = new \Bitrix\Main\Web\Uri($externalImportPath);
					$params = [
						'create_url' => $uriSelect->getUri(),
					];

					if ($this->request->get('IFRAME') === 'Y')
					{
						$params['IFRAME'] = 'Y';
						$params['IFRAME_TYPE'] = 'SIDE_SLIDER';
					}

					$uriCreate->addParams($params);
					// set new url for create
					$result->modifyFields([
						'href' => $uriCreate->getUri()
					]);
				}

				return $result;
			}
		);
		unset($eventManager);
	}

	/**
	 * Mark some color for default set.
	 * @param string $color Color code.
	 * @return void
	 */
	private function addColorToPallete($color)
	{
		if (isset($this->arResult['COLORS'][$color]))
		{
			$this->arResult['COLORS'][$color]['base'] = true;
		}
	}
	
	/**
	 * If try to using unknown color - set default from pallete
	 * @param $color
	 */
	private function checkColorExists(&$color)
	{
		if (!isset($this->arResult['COLORS'][$color]))
		{
			$color = array_shift(array_keys($this->arResult['COLORS']));
		}
	}
}