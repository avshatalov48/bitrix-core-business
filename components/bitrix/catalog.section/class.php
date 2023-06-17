<?

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Error;
use \Bitrix\Main\Type\DateTime;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Iblock;
use \Bitrix\Iblock\Component\ElementList;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CIntranetToolbar $INTRANET_TOOLBAR
 */

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

class CatalogSectionComponent extends ElementList
{
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->setExtendedMode(false)->setMultiIblockMode(false)->setPaginationMode(true);
		$this->setSeparateLoading(true);
	}

	public function onPrepareComponentParams($params)
	{
		$params = parent::onPrepareComponentParams($params);

		$params['IBLOCK_TYPE'] = trim((string)($params['IBLOCK_TYPE'] ?? ''));

		if ((int)$params['SECTION_ID'] > 0 && (int)$params['SECTION_ID'].'' != $params['SECTION_ID'] && Loader::includeModule('iblock'))
		{
			$this->errorCollection->setError(new Error(Loc::getMessage('CATALOG_SECTION_NOT_FOUND'), self::ERROR_404));
			return $params;
		}

		$params['SECTION_ID_VARIABLE'] = trim((string)($params['SECTION_ID_VARIABLE'] ?? ''));
		if (
			$params['SECTION_ID_VARIABLE'] === ''
			|| !preg_match(self::PARAM_TITLE_MASK, $params['SECTION_ID_VARIABLE'])
		)
		{
			$params['SECTION_ID_VARIABLE'] = 'SECTION_ID';
		}

		$params['SHOW_ALL_WO_SECTION'] = isset($params['SHOW_ALL_WO_SECTION']) && $params['SHOW_ALL_WO_SECTION'] === 'Y';
		$params['USE_MAIN_ELEMENT_SECTION'] = isset($params['USE_MAIN_ELEMENT_SECTION']) && $params['USE_MAIN_ELEMENT_SECTION'] === 'Y';
		$params['SECTIONS_CHAIN_START_FROM'] = (int)($params['SECTIONS_CHAIN_START_FROM'] ?? 0);

		$params['BACKGROUND_IMAGE'] = trim((string)($params['BACKGROUND_IMAGE'] ?? ''));
		if ($params['BACKGROUND_IMAGE'] === '-')
		{
			$params['BACKGROUND_IMAGE'] = '';
		}

		// compatibility for bigData case with zero initial elements
		if ($params['PAGE_ELEMENT_COUNT'] <= 0 && !isset($params['PRODUCT_ROW_VARIANTS']))
		{
			$params['PAGE_ELEMENT_COUNT'] = 20;
		}

		$params['CUSTOM_CURRENT_PAGE'] = trim((string)($params['CUSTOM_CURRENT_PAGE'] ?? ''));

		$params['COMPATIBLE_MODE'] = ($params['COMPATIBLE_MODE'] ?? 'N') === 'Y' ? 'Y' : 'N';
		if ($params['COMPATIBLE_MODE'] === 'N')
		{
			$params['DISABLE_INIT_JS_IN_COMPONENT'] = 'Y';
			$params['OFFERS_LIMIT'] = 0;
		}

		$this->setCompatibleMode($params['COMPATIBLE_MODE'] === 'Y');

		$params['DISABLE_INIT_JS_IN_COMPONENT'] = isset($params['DISABLE_INIT_JS_IN_COMPONENT']) && $params['DISABLE_INIT_JS_IN_COMPONENT'] === 'Y' ? 'Y' : 'N';

		if ($params['DISABLE_INIT_JS_IN_COMPONENT'] !== 'Y')
		{
			CJSCore::Init(array('popup'));
		}

		$params['HIDE_SECTION_DESCRIPTION'] = (string)($params['HIDE_SECTION_DESCRIPTION'] ?? 'N');
		if ($params['HIDE_SECTION_DESCRIPTION'] !== 'Y')
		{
			$params['HIDE_SECTION_DESCRIPTION'] = 'N';
		}

		$params['META_KEYWORDS'] = trim((string)($params['META_KEYWORDS'] ?? ''));
		$params['META_DESCRIPTION'] = trim((string)($params['META_DESCRIPTION'] ?? ''));
		$params['BROWSER_TITLE'] = trim((string)($params['BROWSER_TITLE'] ?? ''));

		return $params;
	}

	protected function processResultData()
	{
		if ($this->initSectionResult())
		{
			$this->initSectionProperties();
			parent::processResultData();
		}
	}

	protected function initSectionResult()
	{
		$success = true;
		$selectFields = array();

		if ($this->arParams['IBLOCK_ID'] > 0)
		{
			if (!empty($this->arParams['SECTION_USER_FIELDS']) && is_array($this->arParams['SECTION_USER_FIELDS']))
			{
				foreach ($this->arParams['SECTION_USER_FIELDS'] as $field)
				{
					if (is_string($field) && preg_match('/^UF_/', $field))
					{
						$selectFields[] = $field;
					}
				}
			}

			if (preg_match('/^UF_/', $this->arParams['META_KEYWORDS']))
			{
				$selectFields[] = $this->arParams['META_KEYWORDS'];
			}

			if (preg_match('/^UF_/', $this->arParams['META_DESCRIPTION']))
			{
				$selectFields[] = $this->arParams['META_DESCRIPTION'];
			}

			if (preg_match('/^UF_/', $this->arParams['BROWSER_TITLE']))
			{
				$selectFields[] = $this->arParams['BROWSER_TITLE'];
			}

			if (preg_match('/^UF_/', $this->arParams['BACKGROUND_IMAGE']))
			{
				$selectFields[] = $this->arParams['BACKGROUND_IMAGE'];
			}
		}

		$filterFields = array(
			'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
			'IBLOCK_ACTIVE' => 'Y',
			'ACTIVE' => 'Y',
			'GLOBAL_ACTIVE' => 'Y',
		);

		// Hidden tricky parameter USED to display linked
		// by default it is not set
		if (isset($this->arParams['BY_LINK']) && $this->arParams['BY_LINK'] === 'Y')
		{
			$sectionResult = array(
				'ID' => 0,
				'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
			);
		}
		elseif ($this->arParams['SECTION_ID'] > 0)
		{
			$filterFields['ID'] = $this->arParams['SECTION_ID'];
			$sectionIterator = CIBlockSection::GetList(array(), $filterFields, false, $selectFields);
			$sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
			$sectionResult = $sectionIterator->GetNext();
		}
		elseif ($this->arParams['SECTION_CODE'] <> '')
		{
			$filterFields['=CODE'] = $this->arParams['SECTION_CODE'];
			$sectionIterator = CIBlockSection::GetList(array(), $filterFields, false, $selectFields);
			$sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
			$sectionResult = $sectionIterator->GetNext();
		}
		elseif (isset($this->arParams['SECTION_CODE_PATH']) && $this->arParams['SECTION_CODE_PATH'] <> '')
		{
			$sectionId = CIBlockFindTools::GetSectionIDByCodePath($this->arParams['IBLOCK_ID'], $this->arParams['SECTION_CODE_PATH']);
			if ($sectionId)
			{
				$filterFields['ID'] = $sectionId;
				$sectionIterator = CIBlockSection::GetList(array(), $filterFields, false, $selectFields);
				$sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
				$sectionResult = $sectionIterator->GetNext();
			}
		}
		else	// Root section (no section filter)
		{
			$sectionResult = array(
				'ID' => 0,
				'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
			);
		}

		if (empty($sectionResult))
		{
			$success = false;
			$this->abortResultCache();
			$this->errorCollection->setError(new Error(Loc::getMessage('CATALOG_SECTION_NOT_FOUND'), self::ERROR_404));
		}
		else
		{
			$this->arResult = array_merge($this->arResult, $sectionResult);
			if ($this->arResult['ID'] > 0 && $this->arParams['ADD_SECTIONS_CHAIN'])
			{
				$this->arResult['PATH'] = array();
				$pathIterator = CIBlockSection::GetNavChain(
					$this->arResult['IBLOCK_ID'],
					$this->arResult['ID'],
					array(
						'ID', 'CODE', 'XML_ID', 'EXTERNAL_ID', 'IBLOCK_ID',
						'IBLOCK_SECTION_ID', 'SORT', 'NAME', 'ACTIVE',
						'DEPTH_LEVEL', 'SECTION_PAGE_URL'
					)
				);
				$pathIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
				while ($path = $pathIterator->GetNext())
				{
					$ipropValues = new Iblock\InheritedProperty\SectionValues($this->arParams['IBLOCK_ID'], $path['ID']);
					$path['IPROPERTY_VALUES'] = $ipropValues->getValues();
					$this->arResult['PATH'][] = $path;
				}

				if ($this->arParams['SECTIONS_CHAIN_START_FROM'] > 0)
				{
					$this->arResult['PATH'] = array_slice($this->arResult['PATH'], $this->arParams['SECTIONS_CHAIN_START_FROM']);
				}
			}
		}

		return $success;
	}

	protected function initSectionProperties()
	{
		$arResult =& $this->arResult;

		$arResult['IPROPERTY_VALUES'] = array();
		if ($arResult['ID'] > 0)
		{
			$ipropValues = new Iblock\InheritedProperty\SectionValues($arResult['IBLOCK_ID'], $arResult['ID']);
			$arResult['IPROPERTY_VALUES'] = $ipropValues->getValues();
		}

		Iblock\Component\Tools::getFieldImageData(
			$arResult,
			array('PICTURE', 'DETAIL_PICTURE'),
			Iblock\Component\Tools::IPROPERTY_ENTITY_SECTION,
			'IPROPERTY_VALUES'
		);

		$arResult['BACKGROUND_IMAGE'] = false;
		if ($this->arParams['BACKGROUND_IMAGE'] != '' && !empty($arResult[$this->arParams['BACKGROUND_IMAGE']]))
		{
			$arResult['BACKGROUND_IMAGE'] = CFile::GetFileArray($arResult[$this->arParams['BACKGROUND_IMAGE']]);
		}
	}

	protected function initCatalogInfo()
	{
		parent::initCatalogInfo();
		$useCatalogButtons = array();
		if (
			!empty($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
			&& is_array($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
		)
		{
			$catalogType = $this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']]['CATALOG_TYPE'];
			if ($catalogType == CCatalogSku::TYPE_CATALOG || $catalogType == CCatalogSku::TYPE_FULL)
			{
				$useCatalogButtons['add_product'] = true;
			}

			if ($catalogType == CCatalogSku::TYPE_PRODUCT || $catalogType == CCatalogSku::TYPE_FULL)
			{
				$useCatalogButtons['add_sku'] = true;
			}
			unset($catalogType);
		}

		$this->storage['USE_CATALOG_BUTTONS'] = $useCatalogButtons;
	}

	protected function getCacheKeys()
	{
		return array(
			'ID',
			'NAV_CACHED_DATA',
			'NAV_STRING',
			$this->arParams['META_KEYWORDS'],
			$this->arParams['META_DESCRIPTION'],
			$this->arParams['BROWSER_TITLE'],
			$this->arParams['BACKGROUND_IMAGE'],
			'NAME',
			'PATH',
			'IBLOCK_SECTION_ID',
			'IPROPERTY_VALUES',
			'ITEMS_TIMESTAMP_X',
			'BACKGROUND_IMAGE',
			'USE_CATALOG_BUTTONS'
		);
	}

	protected function getFilter()
	{
		$filterFields = parent::getFilter();

		if ($this->getAction() === 'bigDataLoad')
		{
			return $filterFields;
		}

		$filterFields['INCLUDE_SUBSECTIONS'] = $this->arParams['INCLUDE_SUBSECTIONS'] === 'N' ? 'N' : 'Y';

		if ($this->arParams['INCLUDE_SUBSECTIONS'] === 'A')
		{
			$filterFields['SECTION_GLOBAL_ACTIVE'] = 'Y';
		}

		if (!isset($this->arParams['BY_LINK']) || $this->arParams['BY_LINK'] !== 'Y')
		{
			if ($this->arResult['ID'])
			{
				$filterFields['SECTION_ID'] = $this->arResult['ID'];
			}
			elseif (!$this->arParams['SHOW_ALL_WO_SECTION'])
			{
				$filterFields['SECTION_ID'] = 0;
			}
			else
			{
				unset($filterFields['INCLUDE_SUBSECTIONS']);
				unset($filterFields['SECTION_GLOBAL_ACTIVE']);
			}
		}

		return $filterFields;
	}

	protected function makeOutputResult()
	{
		parent::makeOutputResult();
		$this->arResult['USE_CATALOG_BUTTONS'] = $this->storage['USE_CATALOG_BUTTONS'];
	}

	protected function initialLoadAction()
	{
		parent::initialLoadAction();

		if (!$this->hasErrors())
		{
			$this->initAdminIconsPanel();
			$this->setTemplateCachedData($this->arResult['NAV_CACHED_DATA'] ?? '');
			$this->initMetaData();
		}
	}

	protected function initAdminIconsPanel()
	{
		global $APPLICATION, $INTRANET_TOOLBAR, $USER;

		$this->storage['TITLE_OPTIONS'] = null;

		if (!$USER->IsAuthorized())
		{
			return;
		}

		$arResult =& $this->arResult;

		$intranetToolbarEnable =
			($this->arParams['INTRANET_TOOLBAR'] ?? '') !== 'N'
			&& isset($INTRANET_TOOLBAR)
			&& is_object($INTRANET_TOOLBAR)
		;

		if (
			$APPLICATION->GetShowIncludeAreas()
			|| $intranetToolbarEnable
			|| $this->arParams['SET_TITLE']
			|| isset($arResult[$this->arParams['BROWSER_TITLE']])
		)
		{
			if (Loader::includeModule('iblock'))
			{
				$urlDeleteSectionButton = '';

				if (isset($arResult['IBLOCK_SECTION_ID']) && $arResult['IBLOCK_SECTION_ID'] > 0)
				{
					$sectionIterator = CIBlockSection::GetList(
						array(),
						array('=ID' => $arResult['IBLOCK_SECTION_ID']),
						false,
						array('SECTION_PAGE_URL')
					);
					$sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
					$section = $sectionIterator->GetNext();
					$urlDeleteSectionButton = $section['SECTION_PAGE_URL'];
				}

				if (empty($urlDeleteSectionButton))
				{
					$urlTemplate = CIBlock::GetArrayByID($this->arParams['IBLOCK_ID'], 'LIST_PAGE_URL');
					$iblock = CIBlock::GetArrayByID($this->arParams['IBLOCK_ID']);
					$iblock['IBLOCK_CODE'] = $iblock['CODE'];
					$urlDeleteSectionButton = CIBlock::ReplaceDetailUrl($urlTemplate, $iblock, true, false);
				}

				$returnUrl = array(
					'add_section' => (
					$this->arParams['SECTION_URL'] <> ''? $this->arParams['SECTION_URL'] : CIBlock::GetArrayByID($this->arParams['IBLOCK_ID'], 'SECTION_PAGE_URL')
					),
					'delete_section' => $urlDeleteSectionButton,
				);
				$buttonParams = array(
					'RETURN_URL' => $returnUrl,
					'CATALOG' => true
				);

				if (isset($arResult['USE_CATALOG_BUTTONS']))
				{
					$buttonParams['USE_CATALOG_BUTTONS'] = $arResult['USE_CATALOG_BUTTONS'];
				}

				$buttons = CIBlock::GetPanelButtons(
					$this->arParams['IBLOCK_ID'],
					0,
					$arResult['ID'],
					$buttonParams
				);
				unset($buttonParams);

				if ($APPLICATION->GetShowIncludeAreas())
				{
					$this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $buttons));
				}

				if (
					isset($buttons['intranet'])
					&& is_array($buttons['intranet'])
					&& $intranetToolbarEnable
				)
				{
					Main\Page\Asset::getInstance()->addJs('/bitrix/js/main/utils.js');

					foreach ($buttons['intranet'] as $button)
					{
						$INTRANET_TOOLBAR->AddButton($button);
					}
				}

				if ($this->arParams['SET_TITLE'] || isset($arResult[$this->arParams['BROWSER_TITLE']]))
				{
					if (isset($buttons['submenu']['edit_section']))
					{
						$this->storage['TITLE_OPTIONS'] = [
							'ADMIN_EDIT_LINK' => $buttons['submenu']['edit_section']['ACTION'],
							'PUBLIC_EDIT_LINK' => $buttons['edit']['edit_section']['ACTION'],
							'COMPONENT_NAME' => $this->getName(),
						];
					}
				}
			}
		}
	}

	protected function initMetaData()
	{
		global $APPLICATION;

		if ($this->arParams['SET_TITLE'])
		{
			if (
				isset($this->arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'])
				&& $this->arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'] !== ''
			)
			{
				$APPLICATION->SetTitle($this->arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'], $this->storage['TITLE_OPTIONS']);
			}
			elseif (isset($this->arResult['NAME']))
			{
				$APPLICATION->SetTitle($this->arResult['NAME'], $this->storage['TITLE_OPTIONS']);
			}
		}

		if ($this->arParams['SET_BROWSER_TITLE'] === 'Y')
		{
			$browserTitle = Main\Type\Collection::firstNotEmpty(
				$this->arResult, $this->arParams['BROWSER_TITLE'],
				$this->arResult['IPROPERTY_VALUES'], 'SECTION_META_TITLE'
			);
			if (is_array($browserTitle))
			{
				$APPLICATION->SetPageProperty('title', implode(' ', $browserTitle), $this->storage['TITLE_OPTIONS']);
			}
			elseif ($browserTitle != '')
			{
				$APPLICATION->SetPageProperty('title', $browserTitle, $this->storage['TITLE_OPTIONS']);
			}
		}

		if ($this->arParams['SET_META_KEYWORDS'] === 'Y')
		{
			$metaKeywords = Main\Type\Collection::firstNotEmpty(
				$this->arResult, $this->arParams['META_KEYWORDS'],
				$this->arResult['IPROPERTY_VALUES'], 'SECTION_META_KEYWORDS'
			);
			if (is_array($metaKeywords))
			{
				$APPLICATION->SetPageProperty('keywords', implode(' ', $metaKeywords), $this->storage['TITLE_OPTIONS']);
			}
			elseif ($metaKeywords != '')
			{
				$APPLICATION->SetPageProperty('keywords', $metaKeywords, $this->storage['TITLE_OPTIONS']);
			}
		}

		if ($this->arParams['SET_META_DESCRIPTION'] === 'Y')
		{
			$metaDescription = Main\Type\Collection::firstNotEmpty(
				$this->arResult, $this->arParams['META_DESCRIPTION'],
				$this->arResult['IPROPERTY_VALUES'], 'SECTION_META_DESCRIPTION'
			);
			if (is_array($metaDescription))
			{
				$APPLICATION->SetPageProperty('description', implode(' ', $metaDescription), $this->storage['TITLE_OPTIONS']);
			}
			elseif ($metaDescription != '')
			{
				$APPLICATION->SetPageProperty('description', $metaDescription, $this->storage['TITLE_OPTIONS']);
			}
		}

		if (!empty($this->arResult['BACKGROUND_IMAGE']) && is_array($this->arResult['BACKGROUND_IMAGE']))
		{
			$APPLICATION->SetPageProperty(
				'backgroundImage',
				'style="background-image: url(\''.\CHTTP::urnEncode($this->arResult['BACKGROUND_IMAGE']['SRC'], 'UTF-8').'\')"'
			);
		}

		if (
			$this->arParams['ADD_SECTIONS_CHAIN']
			&& isset($this->arResult['PATH'])
			&& is_array($this->arResult['PATH'])
		)
		{
			foreach ($this->arResult['PATH'] as $path)
			{
				if (
					isset($path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'])
					&& $path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'] !== ''
				)
				{
					$APPLICATION->AddChainItem($path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'], $path['~SECTION_PAGE_URL']);
				}
				else
				{
					$APPLICATION->AddChainItem($path['NAME'], $path['~SECTION_PAGE_URL']);
				}
			}
		}

		if ($this->arParams['SET_LAST_MODIFIED'] && $this->arResult['ITEMS_TIMESTAMP_X'])
		{
			Main\Context::getCurrent()->getResponse()->setLastModified($this->arResult['ITEMS_TIMESTAMP_X']);
		}
	}

	protected function getElementList($iblockId, $products)
	{
		$elementIterator = parent::getElementList($iblockId, $products);

		if (
			!empty($elementIterator)
			&& (!isset($this->arParams['BY_LINK']) || $this->arParams['BY_LINK'] !== 'Y')
			&& !$this->arParams['SHOW_ALL_WO_SECTION']
			&& !$this->arParams['USE_MAIN_ELEMENT_SECTION']
		)
		{
			$elementIterator->SetSectionContext($this->arResult);
		}

		return $elementIterator;
	}

	protected function processElement(array &$element)
	{
		if ($this->arResult['ID'])
		{
			$element['IBLOCK_SECTION_ID'] = $this->arResult['ID'];
		}

		parent::processElement($element);
		$this->checkLastModified($element);
	}

	protected function checkLastModified($element)
	{
		if ($this->arParams['SET_LAST_MODIFIED'])
		{
			$time = DateTime::createFromUserTime($element['TIMESTAMP_X']);
			if (
				!isset($this->arResult['ITEMS_TIMESTAMP_X'])
				|| $time->getTimestamp() > $this->arResult['ITEMS_TIMESTAMP_X']->getTimestamp()
			)
			{
				$this->arResult['ITEMS_TIMESTAMP_X'] = $time;
			}
		}
	}

	protected function initElementList()
	{
		parent::initElementList();

		// compatibility for old components
		if ($this->isEnableCompatible() && empty($this->arResult['NAV_RESULT']))
		{
			$this->initNavString(\CIBlockElement::GetList(
				array(),
				array_merge($this->globalFilter, $this->filterFields + array('IBLOCK_ID' => $this->arParams['IBLOCK_ID'])),
				false,
				array('nTopCount' => 1),
				array('ID')
			));
			$this->arResult['NAV_RESULT']->NavNum = Main\Security\Random::getString(6);
		}

		$this->storage['sections'] = array();

		if (!empty($this->elements) && is_array($this->elements))
		{
			foreach ($this->elements as &$element)
			{
				$this->modifyItemPath($element);
			}
		}
	}

	protected function modifyItemPath(&$element)
	{
		$sections =& $this->storage['sections'];

		if (isset($this->arParams['BY_LINK']) && $this->arParams['BY_LINK'] === 'Y')
		{
			if (!isset($sections[$element['IBLOCK_SECTION_ID']]))
			{
				$sections[$element['IBLOCK_SECTION_ID']] = array();
				$pathIterator = CIBlockSection::GetNavChain(
					$element['IBLOCK_ID'],
					$element['IBLOCK_SECTION_ID'],
					array(
						'ID', 'CODE', 'XML_ID', 'EXTERNAL_ID', 'IBLOCK_ID',
						'IBLOCK_SECTION_ID', 'SORT', 'NAME', 'ACTIVE',
						'DEPTH_LEVEL', 'SECTION_PAGE_URL'
					)
				);
				$pathIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
				while ($path = $pathIterator->GetNext())
				{
					$sections[$element['IBLOCK_SECTION_ID']][] = $path;
				}
			}

			$element['SECTION']['PATH'] = $sections[$element['IBLOCK_SECTION_ID']];
		}
		else
		{
			$element['SECTION']['PATH'] = array();
		}
	}
}
