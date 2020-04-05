<?php
namespace Bitrix\Landing\Subtype;

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
		\Bitrix\Landing\Node\Component::setPredefineForDynamicProps(array(
			'IBLOCK_ID' => \Bitrix\Landing\Node\Component::getIblockParams('id'),
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
			$settings = \Bitrix\Landing\Hook\Page\Settings::getDataForSite(
				$block->getSiteId()
			);
			if (!$settings['IBLOCK_ID'])
			{
				$manifest['requiredUserAction'] = array(
					'header' => Loc::getMessage('LANDING_BLOCK_EMPTY_CATLOG_TITLE'),
					'description' => Loc::getMessage('LANDING_BLOCK_EMPTY_CATLOG_DESC')
				);
			}
		}
		
		return $manifest;
	}
}