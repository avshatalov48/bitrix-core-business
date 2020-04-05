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
			$this->checkParam('PAGE_URL_BACK', '');
			$this->checkParam('SITE_WORK_MODE', 'N');

			$code = $this->arParams['CODE'];
			$demo = $this->getDemoPage();
			if (isset($demo[$code]))
			{
				$this->arResult['COLORS'] = \Bitrix\Landing\Hook\Page\Theme::getColorCodes();
				$this->arResult['TEMPLATE'] = $demo[$code];
				$this->arResult['TEMPLATE']['URL_PREVIEW'] = $this->getUrlPreview($code);
			}
			else
			{
				$this->arResult['TEMPLATE'] = array();
			}
		}

		parent::executeComponent();
	}
}