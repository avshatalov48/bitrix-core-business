<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

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

			$code = $this->arParams['CODE'];
			$demo = $this->getDemoPage($code);
			if (isset($demo[$code]))
			{
				if ($demo[$code]['REST'] > 0)
				{
					$demo[$code]['DATA'] = $this->getTemplateManifest(
						$demo[$code]['REST']
					);
				}
				$this->arResult['COLORS'] = \Bitrix\Landing\Hook\Page\Theme::getColorCodes();
				$this->arResult['TEMPLATE'] = $demo[$code];
				$this->arResult['TEMPLATE']['URL_PREVIEW'] = $this->getUrlPreview($code);
				// first color by default
				$this->arResult['THEME_CURRENT'] = array_shift(array_keys($this->arResult['COLORS']));
				
				
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