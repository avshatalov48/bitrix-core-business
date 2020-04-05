<?php
namespace Bitrix\Landing\Subtype;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Map
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
		if (
			isset($params['required']) &&
			$params['required'] == 'google'
		)
		{
			$hooks = \Bitrix\Landing\Hook::getForSite(
				$block->getSiteId()
			);
			if ($hooks['GMAP']->getFields()['USE']->getValue() !== 'Y')
			{
				$manifest['requiredUserAction'] = array(
					'header' => Loc::getMessage('LANDING_BLOCK_EMPTY_GMAP_TITLE'),
					'description' => Loc::getMessage('LANDING_BLOCK_EMPTY_GMAP_DESC'),
					'text' => Loc::getMessage('LANDING_BLOCK_EMPTY_GMAP_SETTINGS'),
					'href' => '#page_url_site_edit',
					'className' => 'landing-required-link'
				);
				
				return $manifest;
			}
		}
		
		$manifest = self::addVisualSettings($manifest);

		// add ASSETS
		if (
			!is_array($manifest['assets']['ext']) ||
			!in_array('landing_google_maps_new', $manifest['assets']['ext'])
		)
		{
			$manifest['assets']['ext'][] = 'landing_google_maps_new';
		}
		
		return $manifest;
	}

	/**
	 * Add some settings for map.
	 * @param array $manifest
	 * @return array
	 */
	private static function addVisualSettings($manifest)
	{
		// add STYLES
		$additional = [
			'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_TITLE'),
			'attrs' => [
				[
					'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_THEME_TITLE'),
					'type' => 'dropdown',
					'attribute' => 'data-map-theme',
					'items' => [
						[
							'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_THEME_DEFAULT'),
							'value' => '',
						],
						[
							'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_THEME_SILVER'),
							'value' => 'SILVER',
						],
						[
							'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_THEME_RETRO'),
							'value' => 'RETRO',
						],
						[
							'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_THEME_DARK'),
							'value' => 'DARK',
						],
						[
							'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_THEME_NIGHT'),
							'value' => 'NIGHT',
						],
						[
							'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_THEME_AUBERGINE'),
							'value' => 'AUBERGINE',
						],
					],
				],
				
				[
					'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_ROADS_TITLE'),
					'type' => 'dropdown',
					'attribute' => 'data-map-roads',
					'items' => [
						[
							'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_ON'),
							'value' => '',
						],
						[
							'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_OFF'),
							'value' => 'off',
						],
					],
				],
				
				[
					'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_LANDMARKS_TITLE'),
					'type' => 'dropdown',
					'attribute' => 'data-map-landmarks',
					'items' => [
						[
							'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_ON'),
							'value' => '',
						],
						[
							'name' => Loc::getMessage('LANDING_GOOGLE_MAP--STYLE_OFF'),
							'value' => 'off',
						],
					],
				],
			],
		];

		// check block/nodes style notation
		if (!is_array($manifest['style']['block']) && !is_array($manifest['style']['nodes']))
		{
			$manifest['style'] = [
				'block' => [],
				'nodes' => $manifest['style'],
			];
		}
		
		if (!is_array($manifest['style']['nodes']['.landing-block-node-map']['additional']))
		{
			$manifest['style']['nodes']['.landing-block-node-map']['additional'] = [];
		}
		$manifest['style']['nodes']['.landing-block-node-map']['additional'][] = $additional;
		
		
		// add ATTRS
		$attrs = [
			[
				'hidden' => true,
				'attribute' => 'data-map-theme',
			],
			[
				'hidden' => true,
				'attribute' => 'data-map-roads',
			],
			[
				'hidden' => true,
				'attribute' => 'data-map-landmarks',
			],
		];
		
		if (!is_array($manifest['attrs']['.landing-block-node-map']))
		{
			$manifest['attrs']['.landing-block-node-map'] = [];
		}
		$manifest['attrs']['.landing-block-node-map'] = array_merge($manifest['attrs']['.landing-block-node-map'], $attrs);
		
		
		return $manifest;
	}
}