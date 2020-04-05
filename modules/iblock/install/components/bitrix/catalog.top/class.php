<?
use \Bitrix\Iblock\Component\ElementList;
use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

class CatalogTopComponent extends ElementList
{
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->setExtendedMode(false)->setMultiIblockMode(false)->setPaginationMode(false);
	}

	public function onPrepareComponentParams($params)
	{
		$params = parent::onPrepareComponentParams($params);

		if ($params['ELEMENT_COUNT'] <= 0)
		{
			$params['ELEMENT_COUNT'] = $params['PAGE_ELEMENT_COUNT'] = 9;
		}

		if ($params['LINE_ELEMENT_COUNT'] <= 0)
		{
			$params['LINE_ELEMENT_COUNT'] = 3;
		}

		$params['COMPATIBLE_MODE'] = (isset($params['COMPATIBLE_MODE']) && $params['COMPATIBLE_MODE'] === 'N' ? 'N' : 'Y');
		if ($params['COMPATIBLE_MODE'] === 'N')
		{
			$params['OFFERS_LIMIT'] = 0;
		}

		$this->setCompatibleMode($params['COMPATIBLE_MODE'] === 'Y');

		return $params;
	}

	protected function checkIblock()
	{
		if (!array_key_exists(0, $this->iblockProducts))
		{
			parent::checkIblock();
		}
	}

	public function prepareTemplateParams()
	{
		parent::prepareTemplateParams();
		$params =& $this->arParams;

		$viewModeList = array('BANNER', 'SLIDER', 'SECTION');
		if (!in_array($params['VIEW_MODE'], $viewModeList))
		{
			$params['VIEW_MODE'] = 'SECTION';
		}

		$this->arResult['VIEW_MODE_LIST'] = $viewModeList;

		if ($params['VIEW_MODE'] == 'BANNER')
		{
			$params['PRODUCT_DISPLAY_MODE'] = 'N';
		}

		$params['ROTATE_TIMER'] = (int)$params['ROTATE_TIMER'];
		if ($params['ROTATE_TIMER'] < 0)
		{
			$params['ROTATE_TIMER'] = 30;
		}

		$params['ROTATE_TIMER'] *= 1000;
		$params['SHOW_PAGINATION'] = isset($params['SHOW_PAGINATION']) && $params['SHOW_PAGINATION'] === 'Y' ? 'Y' : 'N';
	}

	public function getTemplateDefaultParams()
	{
		$defaultParams = parent::getTemplateDefaultParams();
		$defaultParams['VIEW_MODE'] = 'SECTION';
		$defaultParams['ROTATE_TIMER'] = 30;
		$defaultParams['SHOW_PAGINATION'] = 'Y';

		return $defaultParams;
	}

	protected function editTemplateData()
	{
		parent::editTemplateData();

		if (isset($this->arParams['VIEW_MODE']) && $this->arParams['VIEW_MODE'] === 'SLIDER')
		{
			$this->sliceItemsForSlider($this->arResult['ITEMS']);
		}
	}

	protected function checkTemplateTheme()
	{
		$theme =& $this->arParams['TEMPLATE_THEME'];
		$theme = (string)$theme;

		if ($theme != '')
		{
			$theme = preg_replace('/[^a-zA-Z0-9_\-\(\)\!]/', '', $theme);
			if ($theme === 'site')
			{
				$siteId = $this->getSiteId();
				$templateId = Main\Config\Option::get('main', 'wizard_template_id', 'eshop_bootstrap', $siteId);
				$templateId = preg_match('/^eshop_adapt/', $templateId) ? 'eshop_adapt' : $templateId;
				$theme = Main\Config\Option::get('main', 'wizard_'.$templateId.'_theme_id', 'blue', $siteId);
			}

			if ($theme != '')
			{
				$documentRoot = Main\Application::getDocumentRoot();
				$templateFolder = $this->getTemplate()->GetFolder();

				if (!is_file($documentRoot.$templateFolder.'/'.ToLower($this->arParams['VIEW_MODE']).'/themes/'.$theme.'/style.css'))
				{
					$theme = '';
				}
			}
		}

		if ($theme == '')
		{
			$theme = 'blue';
		}
	}
}