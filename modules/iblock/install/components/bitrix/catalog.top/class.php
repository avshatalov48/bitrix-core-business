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
			$this->arResult['RAW_ITEMS'] = $this->arResult['ITEMS'];
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
				$theme = Main\Config\Option::get('main', 'wizard_'.$templateId.'_theme_id', '', $siteId);
			}

			if ($theme != '')
			{
				$documentRoot = Main\Application::getDocumentRoot();
				$templateFolder = $this->getTemplate()->GetFolder();

				$themesFolder = new Main\IO\Directory($documentRoot.$templateFolder.'/themes/');

				if ($themesFolder->isExists())
				{
					$file = new Main\IO\File($documentRoot.$templateFolder.'/themes/'.$theme.'/style.css');

					if (!$file->isExists())
					{
						$theme = '';
					}
				}
			}
		}

		if ($theme == '')
		{
//			$theme = 'blue';
		}
	}

	public static function getTemplateVariantsMapForSlider()
	{
		return array(
			array(
				'VARIANT' => 0,
				'TYPE' => 'CARD',
				'COLS' => 1,
				'CLASS' => 'product-item-list-col-1',
				'CODE' => '1',
				'ENLARGED_POS' => false,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 1,
				'DEFAULT' => 'N'
			),
			array(
				'VARIANT' => 1,
				'TYPE' => 'CARD',
				'COLS' => 2,
				'CLASS' => 'product-item-list-col-2',
				'CODE' => '2',
				'ENLARGED_POS' => false,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 2,
				'DEFAULT' => 'N'
			),
			array(
				'VARIANT' => 2,
				'TYPE' => 'CARD',
				'COLS' => 3,
				'CLASS' => 'product-item-list-col-3',
				'CODE' => '3',
				'ENLARGED_POS' => false,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 3,
				'DEFAULT' => 'Y'
			),
			array(
				'VARIANT' => 3,
				'TYPE' => 'CARD',
				'COLS' => 4,
				'CLASS' => 'product-item-list-col-4',
				'CODE' => '4',
				'ENLARGED_POS' => false,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 4,
				'DEFAULT' => 'N'
			),
			array(
				'VARIANT' => 6,
				'TYPE' => 'CARD',
				'COLS' => 6,
				'CLASS' => 'product-item-list-col-6',
				'CODE' => '6',
				'ENLARGED_POS' => false,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 6,
				'DEFAULT' => 'N'
			),
			array(
				'VARIANT' => 9,
				'TYPE' => 'LINE',
				'COLS' => 1,
				'CLASS' => 'product-item-line-list',
				'CODE' => 'line',
				'ENLARGED_POS' => false,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 1,
				'DEFAULT' => 'N'
			)
		);
	}
}