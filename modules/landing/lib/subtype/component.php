<?php
namespace Bitrix\Landing\Subtype;

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Component
{
	/**
	 * Prepare manifest.
	 * @param array $manifest Block's manifest.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param array $params Additional params.
	 * @return array
	 */
	public static function prepareManifest(array $manifest, \Bitrix\Landing\Block $block = null, array $params = array())
	{
		$settings = \Bitrix\Landing\Hook\Page\Settings::getDataForSite(
			$block->getSiteId()
		);

		// set predefined
		\Bitrix\Landing\Node\Component::setPredefineForDynamicProps(array(
			'IBLOCK_ID' => $settings['IBLOCK_ID'],
			'USE_ENHANCED_ECOMMERCE' => 'Y',
			'SHOW_DISCOUNT_PERCENT' => 'Y',
			'LABEL_PROP' => array(
				'NEWPRODUCT',
				'SALELEADER',
				'SPECIALOFFER'
			),
			'CONVERT_CURRENCY' => 'Y'
		));

		if (
			isset($params['required']) &&
			$params['required'] == 'catalog'
		)
		{
			// check catalog
			$settings = \Bitrix\Landing\Hook\Page\Settings::getDataForSite(
				$block->getSiteId()
			);
			if (!$settings['IBLOCK_ID'])
			{
				$manifest['requiredUserAction'] = array(
					'header' => Loc::getMessage('LANDING_BLOCK_EMPTY_CATLOG_TITLE'),
					'description' => Loc::getMessage('LANDING_BLOCK_EMPTY_CATLOG_DESC'),
					'text' => Loc::getMessage('LANDING_BLOCK_EMPTY_CATLOG_LINK'),
					'href' => '#page_url_catalog_edit',
					'className' => 'landing-required-link'
				);
			}
			// add settings link
			if ($settings['IBLOCK_ID'])
			{
				if (
					!isset($manifest['block']) ||
					!is_array($manifest['block'])
				)
				{
					$manifest['block'] = array();
				}
				if (Manager::isB24())
				{
					$link = '/shop/settings/menu_catalog_' . $settings['IBLOCK_ID'] . '/';
				}
				else if (\Bitrix\Main\Loader::includeModule('iblock'))
				{
					if ($iblock = \CIBlock::getById($settings['IBLOCK_ID'])->fetch())
					{
						$link = '/bitrix/admin/cat_product_list.php?IBLOCK_ID=' . $iblock['ID'] .
								'&type=' . $iblock['IBLOCK_TYPE_ID'] . '&lang=' . LANGUAGE_ID .
								'&find_section_section=-1';
					}
				}
				if (isset($link))
				{
					$manifest['block']['attrsFormDescription'] = '<a href="' . $link . '" target="_blank">' .
																 	Loc::getMessage('LANDING_BLOCK_CATALOG_CONFIG') .
																 '</a>';
				}
			}
		}
		
		return $manifest;
	}
}