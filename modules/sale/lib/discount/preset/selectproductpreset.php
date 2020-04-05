<?php

namespace Bitrix\Sale\Discount\Preset;


use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

abstract class SelectProductPreset extends BasePreset
{
	protected function init()
	{
		parent::init();

		if(!Loader::includeModule('iblock'))
		{
			throw new SystemException('Could not include iblock module');
		}

		\CJSCore::RegisterExt('select_product_preset', array(
			'js' => '/bitrix/js/sale/admin/discountpreset/select_product_preset.js',
			'lang' => '/bitrix/modules/sale/lang/' . LANGUAGE_ID . '/admin/js/discountpreset/select_product_preset.php',
			'rel' => array('core'),
		));

		\CUtil::InitJSCore(array('select_product_preset'));
	}

	protected function renderElementBlock(State $state, $inputName = 'discount_product', $multi = true)
	{
		if($multi)
		{
			$inputName .= '[]';
		}

		return '
			<div class="sale-discount-container-box">
				<div class="sale-discount-title-container">
					<div class="sale-discount-title-text">' . Loc::getMessage('SALE_BASE_PRESET_SUB_TITLE_PRODUCTS') . '</div>
					<div class="clb"></div>
				</div>
				<div class="adm-sl-content-container">
					<div class="sale-discount-btn-container">
						<a href="#" class="adm-btn adm-btn-save adm-btn-add" title="" id="sale_discount_preset_product_add">' . Loc::getMessage('SALE_BASE_PRESET_ADD_ELEMENTS') . '</a>
					</div>
				</div>				
				<div class="sale-discount-content-container">
					<input type="hidden" name="' . $inputName . '" value="">
					<div class="adm-s-order-table-ddi" style="margin-top:20px">
					    <table class="adm-s-order-table-ddi-table" style="width: 100%;" id="sale_discount_preset_product_table">
					        <thead style="text-align: left;">
					        <tr>
					            <td></td>
					            <td>' . Loc::getMessage('SALE_BASE_PRESET_HEAD_IMAGE') . '</td>
					            <td>' . Loc::getMessage('SALE_BASE_PRESET_HEAD_NAME') . '</td>
					            <td>' . Loc::getMessage('SALE_BASE_PRESET_HEAD_PROPS') . '</td>
					        </tr>
					        </thead>
							<tbody style="border: 1px solid rgb(221, 221, 221);" id="sale_discount_preset_product_table_empty_row">
								<tr>
									<td colspan="4" style="padding: 20px;">
										' . Loc::getMessage('SALE_BASE_PRESET_EMPTY_PRODUCT_NOTICE') . '
									</td>
								</tr>
							</tbody>      
					    </table>
					</div>
				</div>
			</div>		
		';
	}
	
	protected function renderSectionBlock(State $state, $inputName = 'discount_section', $multi = true)
	{
		$fromInputName = $inputName;
		if($multi)
		{
			$fromInputName .= '[]';
		}

		return '
			<div class="sale-discount-container-box">
				<div class="sale-discount-title-container">
					<div class="sale-discount-title-text">' . Loc::getMessage('SALE_BASE_PRESET_SUB_TITLE_SECTION') . '</div>
					<div class="clb"></div>
				</div>
				<div class="adm-sl-content-container">
					<div class="sale-discount-btn-container">
						<a href="#" class="adm-btn adm-btn-save adm-btn-add" title="" id="sale_discount_preset_section_add">' . Loc::getMessage('SALE_BASE_PRESET_ADD_ELEMENTS') . '</a>
					</div>
				</div>				
				<div class="sale-discount-content-container" style="margin-top:20px;">
					<table class="sale-discount-detail-content-sale-table bdrb" style="margin: 0 auto;" border="0" cellspacing="7" cellpadding="0">
						<tbody id="sale_discount_preset_section_tbody">
							<input type="hidden" name="' . $fromInputName . '" value="">								
							' . $this->renderSections($state, $inputName) . '
						</tbody>
					</table>
				</div>
			</div>		
		';
	}

	protected function renderSections(State $state, $inputName, $multi = true)
	{
		global $APPLICATION;

		$currentValue = array();

		$sectionIds = $state->get($inputName);
		if($sectionIds)
		{
			$sectionsIterator = SectionTable::getList(array(
				'select' => array('ID', 'NAME'),
				'filter' => array('@ID' => $sectionIds),
			));

			while($row = $sectionsIterator->fetch())
			{
				$currentValue[] = "{$row['NAME']} [{$row['ID']}]";
			}
		}

		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:main.lookup.input',
			'iblockedit',
			array(
				'CONTROL_ID' => 'select_section',
				'INPUT_NAME' => $inputName .'[]',
				'INPUT_NAME_STRING' => 'inp_'. $inputName,
				'INPUT_VALUE_STRING' => implode("\n", $currentValue),
				'START_TEXT' => Loc::getMessage('SALE_BASE_PRESET_TRY_TO_TEXT'),
				'MULTIPLE' => $multi? 'Y' : 'N',
				'MAX_WIDTH' => '200',
				'MIN_HEIGHT' => '100',
				'WITHOUT_IBLOCK' => 'Y',
				'FILTER' => 'Y',
				'TYPE' => 'SECTION',
			), null, array('HIDE_ICONS' => 'Y')
		);
		$htmlResult = ob_get_contents();
		ob_end_clean();

		return $htmlResult;
	}

	protected function getSectionsFromConditions(array $conditions = null)
	{
		if(!$conditions)
		{
			return array();
		}

		$sectionIds = array();
		foreach($conditions as $condition)
		{
			if($condition['CLASS_ID'] !== 'CondIBSection')
			{
				continue;
			}
			$sectionIds[] = $condition['DATA']['value'];
		}

		return array_unique($sectionIds);
	}

	protected function getProductsFromConditions(array $conditions = null)
	{
		if(!$conditions)
		{
			return array();
		}

		$productIds = array();
		foreach($conditions as $condition)
		{
			if($condition['CLASS_ID'] !== 'CondIBElement')
			{
				continue;
			}

			$value = $condition['DATA']['value'];
			if(!is_array($value))
			{
				$value = array($value);
			}

			$productIds = array_merge($productIds, $value);
		}

		return array_unique($productIds);
	}

	protected function generateSectionConditions($sectionIds, $logic = 'Equal')
	{
		$sectionIds = $this->cleanIds($sectionIds);
		if(empty($sectionIds))
		{
			return array();
		}

		$data = array();
		foreach($sectionIds as $sectionId)
		{
			if(empty($sectionId))
			{
				continue;
			}

			$data[] = array(
				'CLASS_ID' => 'CondIBSection',
				'DATA' => array(
					'logic' => $logic,
					'value' => (int)$sectionId,
				),
			);
		}
		unset($sectionId);

		return $data;
	}

	protected function generateProductConditions($productIds, $logic = 'Equal')
	{
		$productIds = $this->cleanIds($productIds);
		if(empty($productIds))
		{
			return array();
		}

		return array(
			array(
				'CLASS_ID' => 'CondIBElement',
				'DATA' => array(
					'logic' => $logic,
					'value' => array_filter(array_map('intval', $productIds)),
				)
			)
		);
	}

	protected function generateSectionActions($sectionIds)
	{
		$sectionIds = $this->cleanIds($sectionIds);
		if(empty($sectionIds))
		{
			return array();
		}

		return array(
			'CLASS_ID' => 'ActSaleSubGrp',
			'DATA' => array(
				'All' => 'OR',
				'True' => 'True',
			),
			'CHILDREN' => $this->generateSectionConditions($sectionIds),
		);
	}

	protected function generateProductActions($productIds)
	{
		$productIds = $this->cleanIds($productIds);
		if(empty($productIds))
		{
			return array();
		}

		return array(
			'CLASS_ID' => 'ActSaleSubGrp',
			'DATA' => array(
				'All' => 'AND',
				'True' => 'True',
			),
			'CHILDREN' => array(
				array(
					'CLASS_ID' => 'CondIBElement',
					'DATA' => array(
						'logic' => 'Equal',
						'value' => array_map('intval', $productIds),
					)
				),
			),
		);
	}
	
	protected function generateProductsData($productIds, $siteId)
	{
		$productData = array();

		$productIds = $this->cleanIds($productIds);
		if(empty($productIds))
		{
			return array();
		}

		foreach($productIds as $productId)
		{
			$productData[] = $this->processAjaxActionGetProductDetails(array(
				'productId' => $productId,
				'quantity' => 1,
				'siteId' => $siteId,
			));
		}

		return $productData;
	}

	protected function validateSectionsAndProductsState(State $state, ErrorCollection $errorCollection)
	{
		if(!is_array($state->get('discount_section', array())))
		{
			$errorCollection[] = new Error(Loc::getMessage('SALE_BASE_PRESET_ERROR_SECTION_NON_ARRAY'));
		}

		if(!is_array($state->get('discount_product', array())))
		{
			$errorCollection[] = new Error(Loc::getMessage('SALE_BASE_PRESET_ERROR_PRODUCT_NON_ARRAY'));
		}
	}

	private function cleanIds($ids)
	{
		if(empty($ids))
		{
			return array();
		}

		if (!is_array($ids))
		{
			return array();
		}

		return array_filter($ids);
	}
}