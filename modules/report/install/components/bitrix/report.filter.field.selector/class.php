<?php

use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
	die();

Loc::loadMessages(__FILE__);

class CReportComponent extends \CBitrixComponent
{
	protected $selectorItems;
	protected $moduleIncluded;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->selectorItems = array();
		$this->moduleIncluded = array();
	}

	public function executeComponent()
	{
		if (is_array($this->arParams['ufInfo']))
		{
			if ($this->prepareResult())
				$this->IncludeComponentTemplate();
		}
	}

	public function ensureModuleIncluded($moduleName)
	{
		$moduleName = trim(strval($moduleName));
		if (empty($moduleName))
			return false;

		if (isset($this->moduleIncluded[$moduleName]))
			return $this->moduleIncluded[$moduleName];

		$this->moduleIncluded[$moduleName] = CModule::IncludeModule($moduleName);

		return $this->moduleIncluded[$moduleName];
	}

	protected function prepareResult()
	{
		foreach ($this->arParams['ufInfo'] as $arUfInfo)
		{
			foreach ($arUfInfo as $ufInfo)
			{
				if (is_array($ufInfo['USER_TYPE'])
					&& is_callable(array($ufInfo['USER_TYPE']['CLASS_NAME'], 'getlist')))
				{
					$enum = array();
					$rsEnum = call_user_func_array(
						array($ufInfo['USER_TYPE']['CLASS_NAME'], 'getlist'),
						array($ufInfo)
					);
					while($arEnum = $rsEnum->GetNext())
						$enum[$arEnum['ID']] = $arEnum['VALUE'];
					$ufInfo['USER_TYPE']['FIELDS'] = $enum;
				}

				switch ($ufInfo['USER_TYPE_ID'])
				{
					case 'crm':
						if($this->ensureModuleIncluded('crm'))
						{
							$selectorItem = $this->prepareCrmSelectorItem($ufInfo);
							if ($selectorItem)
								$this->selectorItems[] = $selectorItem;
						}
						break;
					case 'crm_status':
						if($this->ensureModuleIncluded('crm'))
						{
							$selectorItem = $this->prepareCrmStatusSelectorItem($ufInfo);
							if ($selectorItem)
								$this->selectorItems[] = $selectorItem;
						}
						break;
					case 'iblock_element':
						if($this->ensureModuleIncluded('iblock'))
						{
							$selectorItem = $this->prepareIblockElementSelectorItem($ufInfo);
							if ($selectorItem)
								$this->selectorItems[] = $selectorItem;
						}
						break;
					case 'iblock_section':
						if($this->ensureModuleIncluded('iblock'))
						{
							$selectorItem = $this->prepareIblockSectionSelectorItem($ufInfo);
							if ($selectorItem)
								$this->selectorItems[] = $selectorItem;
						}
						break;
				}
			}
		}

		$this->arResult['SELECTOR_ITEMS'] = $this->selectorItems;

		return !empty($this->arResult['SELECTOR_ITEMS']);
	}
	
	protected function prepareCrmSelectorItem($ufInfo)
	{
		/** @global CUser $USER */
		global $USER;

		$result = false;
		$selectorItem = array();
		
		if(!CModule::IncludeModule('crm'))
			return $result;

		$CCrmPerms = new CCrmPerms($USER->GetID());
		$nPermittedEntityTypes = 0;
		if ($ufInfo['SETTINGS']['LEAD'] == 'Y' && !$CCrmPerms->HavePerm('LEAD', BX_CRM_PERM_NONE, 'READ'))
			$nPermittedEntityTypes++;
		if ($ufInfo['SETTINGS']['CONTACT'] == 'Y' && !$CCrmPerms->HavePerm('CONTACT', BX_CRM_PERM_NONE, 'READ'))
			$nPermittedEntityTypes++;
		if ($ufInfo['SETTINGS']['COMPANY'] == 'Y' && !$CCrmPerms->HavePerm('COMPANY', BX_CRM_PERM_NONE, 'READ'))
			$nPermittedEntityTypes++;
		if ($ufInfo['SETTINGS']['DEAL'] == 'Y' && !$CCrmPerms->HavePerm('DEAL', BX_CRM_PERM_NONE, 'READ'))
			$nPermittedEntityTypes++;
		if ($ufInfo['SETTINGS']['QUOTE'] == 'Y' && !$CCrmPerms->HavePerm('QUOTE', BX_CRM_PERM_NONE, 'READ'))
			$nPermittedEntityTypes++;
		if ($ufInfo['SETTINGS']['PRODUCT'] == 'Y' && $CCrmPerms->HavePerm('CONFIG', BX_CRM_PERM_CONFIG, 'READ'))
			$nPermittedEntityTypes++;
		$usePrefix = $nPermittedEntityTypes > 1;

		// last 50 entity
		$entityTypes = array();
		$elements = array();
		$arSettings = $ufInfo['SETTINGS'];
		if (isset($arSettings['LEAD']) && $arSettings['LEAD'] == 'Y')
		{
			$entityTypes[] = 'lead';

			$arSelect = array('ID', 'TITLE', 'FULL_NAME', 'STATUS_ID');
			$obRes = CCrmLead::GetList(array('ID' => 'DESC'), array(), $arSelect, 50);
			$arFiles = array();
			while ($arRes = $obRes->Fetch())
			{
				$arRes['SID'] = $usePrefix ? 'L_'.$arRes['ID']: $arRes['ID'];

				$elements[] = array(
					'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
					'desc' => $arRes['FULL_NAME'],
					'id' => $arRes['SID'],
					'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_lead_show'),
						array(
							'lead_id' => $arRes['ID']
						)
					),
					'type'  => 'lead',
					'selected' => 'N'
				);
			}
		}
		if (isset($arSettings['CONTACT']) && $arSettings['CONTACT'] == 'Y')
		{
			$entityTypes[] = 'contact';

			$arSelect = array('ID', 'FULL_NAME', 'COMPANY_TITLE', 'PHOTO');
			$obRes = CCrmContact::GetList(array('ID' => 'DESC'), array(), $arSelect, 50);
			while ($arRes = $obRes->Fetch())
			{
				$strImg = '';
				if (!empty($arRes['PHOTO']) && !isset($arFiles[$arRes['PHOTO']]))
				{
					if ($arFile = CFile::GetFileArray($arRes['PHOTO']))
					{
						$arImg =  CFile::ResizeImageGet($arFile, array('width' => 25, 'height' => 25), BX_RESIZE_IMAGE_EXACT);
						if(is_array($arImg) && isset($arImg['src']))
						{
							$strImg = CHTTP::URN2URI($arImg['src'], '', true);
						}
					}
				}

				$arRes['SID'] = $usePrefix ? 'C_'.$arRes['ID']: $arRes['ID'];

				$elements[] = array(
					'title' => (str_replace(array(';', ','), ' ', $arRes['FULL_NAME'])),
					'desc'  => empty($arRes['COMPANY_TITLE'])? '': $arRes['COMPANY_TITLE'],
					'id' => $arRes['SID'],
					'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_contact_show'),
						array(
							'contact_id' => $arRes['ID']
						)
					),
					'image' => $strImg,
					'type'  => 'contact',
					'selected' => 'N'
				);
			}
		}
		if (isset($arSettings['COMPANY']) && $arSettings['COMPANY'] == 'Y')
		{
			$entityTypes[] = 'company';

			$arCompanyTypeList = CCrmStatus::GetStatusListEx('COMPANY_TYPE');
			$arCompanyIndustryList = CCrmStatus::GetStatusListEx('INDUSTRY');
			$arSelect = array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY',  'LOGO');
			$obRes = CCrmCompany::GetList(array('ID' => 'DESC'), array(), $arSelect, 50);
			$arFiles = array();
			while ($arRes = $obRes->Fetch())
			{
				$strImg = '';
				if (!empty($arRes['LOGO']) && !isset($arFiles[$arRes['LOGO']]))
				{
					if ($arFile = CFile::GetFileArray($arRes['LOGO']))
					{
						$arImg =  CFile::ResizeImageGet($arFile, array('width' => 25, 'height' => 25), BX_RESIZE_IMAGE_EXACT);
						if(is_array($arImg) && isset($arImg['src']))
						{
							$strImg = CHTTP::URN2URI($arImg['src'], '', true);
						}
					}

					$arFiles[$arRes['LOGO']] = $strImg;
				}

				$arRes['SID'] = $usePrefix ? 'CO_'.$arRes['ID']: $arRes['ID'];

				$arDesc = array();
				if (isset($arCompanyTypeList[$arRes['COMPANY_TYPE']]))
					$arDesc[] = $arCompanyTypeList[$arRes['COMPANY_TYPE']];
				if (isset($arCompanyIndustryList[$arRes['INDUSTRY']]))
					$arDesc[] = $arCompanyIndustryList[$arRes['INDUSTRY']];

				$elements[] = array(
					'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
					'desc' => implode(', ', $arDesc),
					'id' => $arRes['SID'],
					'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_company_show'),
						array(
							'company_id' => $arRes['ID']
						)
					),
					'image' => $strImg,
					'type'  => 'company',
					'selected' => 'N'
				);
			}
		}
		if (isset($arSettings['DEAL']) && $arSettings['DEAL'] == 'Y')
		{
			$entityTypes[] = 'deal';

			$arDealStageList = CCrmStatus::GetStatusListEx('DEAL_STAGE');
			$arSelect = array('ID', 'TITLE', 'STAGE_ID', 'COMPANY_TITLE', 'CONTACT_FULL_NAME');
			$obRes = CCrmDeal::GetList(array('ID' => 'DESC'), array(), $arSelect, 50);
			while ($arRes = $obRes->Fetch())
			{
				$arRes['SID'] = $usePrefix ? 'D_'.$arRes['ID']: $arRes['ID'];

				$clientTitle = (!empty($arRes['COMPANY_TITLE'])) ? $arRes['COMPANY_TITLE'] : '';
				$clientTitle .= (($clientTitle !== '' && !empty($arRes['CONTACT_FULL_NAME'])) ? ', ' : '').$arRes['CONTACT_FULL_NAME'];

				$elements[] = array(
					'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
					'desc' => $clientTitle,
					'id' => $arRes['SID'],
					'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_deal_show'),
						array(
							'deal_id' => $arRes['ID']
						)
					),
					'type'  => 'deal',
					'selected' => 'N'
				);
			}
		}
		if (isset($arSettings['QUOTE']) && $arSettings['QUOTE'] == 'Y')
		{
			$entityTypes[] = 'quote';
			$arSelect = array('ID', 'QUOTE_NUMBER', 'TITLE', 'COMPANY_TITLE', 'CONTACT_FULL_NAME');
			$obRes = CCrmQuote::GetList(
				array('ID' => 'DESC'),
				array(),
				false,
				array('nTopCount' => 50),
				array('ID', 'QUOTE_NUMBER', 'TITLE', 'COMPANY_TITLE', 'CONTACT_FULL_NAME')
			);
			while ($arRes = $obRes->Fetch())
			{
				$arRes['SID'] = $usePrefix ? 'Q_'.$arRes['ID']: $arRes['ID'];

				$clientTitle = (!empty($arRes['COMPANY_TITLE'])) ? $arRes['COMPANY_TITLE'] : '';
				$clientTitle .= (($clientTitle !== '' && !empty($arRes['CONTACT_FULL_NAME'])) ? ', ' : '').$arRes['CONTACT_FULL_NAME'];

				$quoteTitle = empty($arRes['TITLE']) ? $arRes['QUOTE_NUMBER'] : $arRes['QUOTE_NUMBER'].' - '.$arRes['TITLE'];

				$elements[] = array(
					'title' => empty($quoteTitle) ? '' : str_replace(array(';', ','), ' ', $quoteTitle),
					'desc' => $clientTitle,
					'id' => $arRes['SID'],
					'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_quote_show'),
						array(
							'quote_id' => $arRes['ID']
						)
					),
					'type'  => 'quote',
					'selected' => 'N'
				);
			}
		}
		if (isset($arSettings['PRODUCT']) && $arSettings['PRODUCT'] == 'Y')
		{
			$entityTypes[] = 'product';

			$arSelect = array('ID', 'NAME', 'PRICE', 'CURRENCY_ID');
			$arPricesSelect = $arVatsSelect = array();
			$arSelect = CCrmProduct::DistributeProductSelect($arSelect, $arPricesSelect, $arVatsSelect);
			$obRes = CCrmProduct::GetList(array('ID' => 'DESC'), array('ACTIVE' => 'Y'), $arSelect, 50);

			$arProducts = $arProductId = array();
			while ($arRes = $obRes->Fetch())
			{
				foreach ($arPricesSelect as $fieldName)
					$arRes[$fieldName] = null;
				foreach ($arVatsSelect as $fieldName)
					$arRes[$fieldName] = null;
				$arProductId[] = $arRes['ID'];
				$arProducts[$arRes['ID']] = $arRes;
			}
			CCrmProduct::ObtainPricesVats($arProducts, $arProductId, $arPricesSelect, $arVatsSelect);
			unset($arProductId, $arPricesSelect, $arVatsSelect);

			foreach ($arProducts as $arRes)
			{
				$arRes['SID'] = $usePrefix ? 'PROD_'.$arRes['ID']: $arRes['ID'];

				$elements[] = array(
					'title' => $arRes['NAME'],
					'desc' => CCrmProduct::FormatPrice($arRes),
					'id' => $arRes['SID'],
					'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_product_show'),
						array(
							'product_id' => $arRes['ID']
						)
					),
					'type'  => 'product',
					'selected' => 'N'
				);
			}
			unset($arProducts);
		}

		$selectorItem['USER_TYPE_ID'] = $ufInfo['USER_TYPE_ID'];
		$selectorItem['ENTITY_ID'] = $ufInfo['ENTITY_ID'];
		$selectorItem['FIELD_NAME'] = $ufInfo['FIELD_NAME'];

		$selectorItem['PREFIX'] = $usePrefix ? 'Y' : 'N';
		$selectorItem['MULTIPLE'] = 'Y';//$ufInfo['MULTIPLE'];
		$selectorItem['ENTITY_TYPE'] = $entityTypes;
		$selectorItem['ELEMENT'] = $elements;

		$result = $selectorItem;

		return $result;
	}

	protected function prepareCrmStatusSelectorItem($ufInfo)
	{
		$result = false;
		$selectorItem = array();

		$selectorItem['USER_TYPE_ID'] = $ufInfo['USER_TYPE_ID'];
		$selectorItem['ENTITY_ID'] = $ufInfo['ENTITY_ID'];
		$selectorItem['FIELD_NAME'] = $ufInfo['FIELD_NAME'];

		$isMultiple = (isset($ufInfo['MULTIPLE']) && $ufInfo['MULTIPLE'] === 'Y');
		$selectorItem['LIST_HEIGHT'] =
			isset($ufInfo['SETTINGS']['LIST_HEIGHT']) ? intval($ufInfo['SETTINGS']['LIST_HEIGHT']) : 5;
		if (!$isMultiple && $selectorItem['LIST_HEIGHT'] < 3)
			$selectorItem['LIST_HEIGHT'] = 5;
		else if ($selectorItem['LIST_HEIGHT'] <= 0)
			$selectorItem['LIST_HEIGHT'] = 1;
		$selectorItem['ITEMS'] = array();
		$enum = is_array($ufInfo['USER_TYPE']['FIELDS']) ? $ufInfo['USER_TYPE']['FIELDS'] : array();
		$selectorItem['ITEMS'][] = array('id' => '', 'title' => GetMessage('REPORT_IGNORE_FILTER_VALUE'));
		foreach ($enum as $k => $v)
			$selectorItem['ITEMS'][] = array('id' => $k, 'title' => $v);

		$result = $selectorItem;

		return $result;

	}

	protected function prepareIblockElementSelectorItem($ufInfo)
	{
		$result = false;
		$selectorItem = array();

		$selectorItem['USER_TYPE_ID'] = $ufInfo['USER_TYPE_ID'];
		$selectorItem['ENTITY_ID'] = $ufInfo['ENTITY_ID'];
		$selectorItem['FIELD_NAME'] = $ufInfo['FIELD_NAME'];

		$isMultiple = (isset($ufInfo['MULTIPLE']) && $ufInfo['MULTIPLE'] === 'Y');
		$selectorItem['LIST_HEIGHT'] =
			isset($ufInfo['SETTINGS']['LIST_HEIGHT']) ? intval($ufInfo['SETTINGS']['LIST_HEIGHT']) : 5;
		if (!$isMultiple && $selectorItem['LIST_HEIGHT'] < 3)
			$selectorItem['LIST_HEIGHT'] = 5;
		else if ($selectorItem['LIST_HEIGHT'] <= 0)
			$selectorItem['LIST_HEIGHT'] = 1;
		$selectorItem['ITEMS'] = array();
		$enum = is_array($ufInfo['USER_TYPE']['FIELDS']) ? $ufInfo['USER_TYPE']['FIELDS'] : array();
		$selectorItem['ITEMS'][] = array('id' => '', 'title' => GetMessage('REPORT_IGNORE_FILTER_VALUE'));
		foreach ($enum as $k => $v)
			$selectorItem['ITEMS'][] = array('id' => $k, 'title' => $v);

		$result = $selectorItem;

		return $result;

	}

	protected function prepareIblockSectionSelectorItem($ufInfo)
	{
		$result = false;
		$selectorItem = array();

		$selectorItem['USER_TYPE_ID'] = $ufInfo['USER_TYPE_ID'];
		$selectorItem['ENTITY_ID'] = $ufInfo['ENTITY_ID'];
		$selectorItem['FIELD_NAME'] = $ufInfo['FIELD_NAME'];

		$isMultiple = (isset($ufInfo['MULTIPLE']) && $ufInfo['MULTIPLE'] === 'Y');
		$selectorItem['LIST_HEIGHT'] =
			isset($ufInfo['SETTINGS']['LIST_HEIGHT']) ? intval($ufInfo['SETTINGS']['LIST_HEIGHT']) : 5;
		if (!$isMultiple && $selectorItem['LIST_HEIGHT'] < 3)
			$selectorItem['LIST_HEIGHT'] = 5;
		else if ($selectorItem['LIST_HEIGHT'] <= 0)
			$selectorItem['LIST_HEIGHT'] = 1;
		$selectorItem['ITEMS'] = array();
		$enum = is_array($ufInfo['USER_TYPE']['FIELDS']) ? $ufInfo['USER_TYPE']['FIELDS'] : array();
		$selectorItem['ITEMS'][] = array('id' => '', 'title' => GetMessage('REPORT_IGNORE_FILTER_VALUE'));
		foreach ($enum as $k => $v)
			$selectorItem['ITEMS'][] = array('id' => $k, 'title' => $v);

		$result = $selectorItem;

		return $result;

	}
}
