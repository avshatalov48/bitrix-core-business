<?php

namespace Bitrix\Landing\Subtype;

use Bitrix\Landing\Block;
use Bitrix\Landing\Hook;
use Bitrix\Landing\Manager;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
Loc::loadMessages(Manager::getDocRoot() . '/bitrix/modules/landing/lib/subtype/map_ru.php');

/**
 * Subtype for blocks with map
 */
class Map
{
	/**
	 * Map provider names
	 */
	protected const PROVIDER_GOOGLE = 'google';
	protected const PROVIDER_YANDEX = 'yandex';
	protected const PROVIDER_DEFAULT = Map::PROVIDER_GOOGLE;

	/**
	 * Regexp for find data-attribute with provider name. If not find - set default
	 */
	protected const PROVIDER_REGEXP = '/data-map-provider=[\'"]([\w-]+)[\'"]/i';

	/**
	 * Temporary save hooks for maps
	 * @var array
	 */
	protected static $settings = [];

	/**
	 * Temporary save provider of current block
	 * @var string
	 */
	protected static $provider = Map::PROVIDER_DEFAULT;

	/**
	 * Default selector for map node
	 */
	protected const MAP_SELECTOR = '.landing-block-node-map';

	/**
	 * Asset for default provider. For other - must be replaced 'google'
	 */
	protected const ASSET_NAME = 'map_init';

	protected static $manifestStore = [];

	/**
	 * Prepare manifest.
	 * @param array $manifest Block's manifest.
	 * @param Block|null $block Block instance.
	 * @param array $params Additional params.
	 * @return array
	 */
	public static function prepareManifest(array $manifest, Block $block = null, array $params = []): array
	{
		if ($block === null)
		{
			return $manifest;
		}

		if (
			isset(self::$manifestStore[$block->getId()])
			&& !empty(self::$manifestStore[$block->getId()])
		)
		{
			return self::$manifestStore[$block->getId()];
		}

		self::readSettings(Hook::getForSite($block->getSiteId()));
		self::readProviderFromBlock($block);

		// save provider in content
		$manifest['callbacks'] = [
			'afterAdd' => function (Block &$block)
			{
				$isUseYandex = self::$settings['YMAP']['USE'] && !empty(self::$settings['YMAP']['CODE']);
				$isNotSettings =
					(!self::$settings['YMAP']['USE'] || empty(self::$settings['YMAP']['CODE']))
					&& (!self::$settings['GMAP']['USE'] || empty(self::$settings['GMAP']['CODE']));

				$providerForNewBlock = self::PROVIDER_DEFAULT;
				if (
					($isUseYandex || $isNotSettings)
					&& self::canUseYandex()
				)
				{
					$providerForNewBlock = self::PROVIDER_YANDEX;
				}

				$block->setAttributes([
					self::MAP_SELECTOR => [
						'data-map-provider' => $providerForNewBlock,
					],
				]);
				$block->save();

				unset(self::$manifestStore[$block->getId()]);
			},
		];

		$manifest = self::addRequiredUserAction($manifest);
		$manifest = self::addSettings($manifest);
		$manifest = self::addVisualSettings($manifest);
		$manifest = self::addAssets($manifest);

		// save local, but disable common cache
		$manifest['disableCache'] = true;
		self::$manifestStore[$block->getId()] = $manifest;

		return $manifest;
	}

	/**
	 * @param Hook\Page[] $hooks - array of page setting hooks
	 * @return void
	 */
	protected static function readSettings(array $hooks): void
	{
		$readHook = static function (string $hook) use ($hooks)
		{
			$fields = $hooks[$hook]->getFields();
			if ($fields)
			{
				self::$settings[$hook] = [
					'USE' => isset($fields['USE']) && $fields['USE']->getValue() === 'Y',
					'CODE' => isset($fields['CODE']) ? $fields['CODE']->getValue() : '',
				];
			}
		};

		$readHook('GMAP');
		if (self::canUseYandex())
		{
			$readHook('YMAP');
		}
	}

	/**
	 * Try to find data-attribute with provider name and save them in static var. Save default if not found.
	 * @param Block $block
	 * @return void
	 */
	protected static function readProviderFromBlock(Block $block): void
	{
		self::$provider = self::PROVIDER_DEFAULT;
		$content = $block->getContent();

		if (preg_match(
			self::PROVIDER_REGEXP,
			$content,
			$matches
		))
		{
			$provider = $matches[1];
			if ($provider === self::PROVIDER_GOOGLE || $provider === self::PROVIDER_YANDEX)
			{
				self::$provider = $provider;
			}
		}
	}

	protected static function canUseYandex(): bool
	{
		return Manager::availableOnlyForZone('ru');
	}

	/**
	 * Set alert actions if needed
	 * @param array $manifest
	 * @return array
	 */
	protected static function addRequiredUserAction(array $manifest): array
	{
		$isGoogleFail =
			self::$provider === self::PROVIDER_GOOGLE
			&& (!self::$settings['GMAP']['USE'] || empty(self::$settings['GMAP']['CODE']));
		$isYandexFail =
			self::$provider === self::PROVIDER_YANDEX
			&& (!self::$settings['YMAP']['USE'] || empty(self::$settings['YMAP']['CODE']));

		$error = '';
		$description = '';

		if ($isYandexFail && self::canUseYandex())
			{
				$error = Loc::getMessage('LANDING_BLOCK_EMPTY_YMAP_TITLE');
				$description = Loc::getMessage('LANDING_BLOCK_EMPTY_YMAP_DESC');
			}
		elseif ($isGoogleFail)
		{
			$error = Loc::getMessage('LANDING_BLOCK_EMPTY_GMAP_TITLE');
			$description = Loc::getMessage('LANDING_BLOCK_EMPTY_GMAP_DESC');
		}

		if ($error && !is_array($manifest['requiredUserAction']))
		{
			$manifest['requiredUserAction'] = [
				'header' => $error,
				'description' => $description,
				'text' => Loc::getMessage('LANDING_BLOCK_EMPTY_GMAP_SETTINGS'),
				'href' => '#page_url_site_edit@map_required_key',
				'className' => 'landing-required-link',
			];
		}

		return $manifest;
	}

	/**
	 * Add settings for map
	 * @param array $manifest
	 * @return array
	 */
	protected static function addSettings(array $manifest): array
	{
		if (self::canUseYandex())
		{
			$attrs = [
				[
					'name' => Loc::getMessage('LANDING_GOOGLE_MAP-PROVIDER'),
					'attribute' => 'data-map-provider',
					'type' => 'list',
					'items' => [
						['name' => Loc::getMessage('LANDING_GOOGLE_MAP-PROVIDER-G'), 'value' => self::PROVIDER_GOOGLE],
						['name' => Loc::getMessage('LANDING_GOOGLE_MAP-PROVIDER-Y'), 'value' => self::PROVIDER_YANDEX],
					],
					'requireReload' => true,
				],
			];

			if (!is_array($manifest['attrs'][self::MAP_SELECTOR]))
			{
				$manifest['attrs'][self::MAP_SELECTOR] = [];
			}
			$manifest['attrs'][self::MAP_SELECTOR] = array_merge($manifest['attrs'][self::MAP_SELECTOR], $attrs);
		}

		return $manifest;
	}

	/**
	 * Add some design settings for map.
	 * @param array $manifest
	 * @return array
	 */
	protected static function addVisualSettings(array $manifest): array
	{
		$additional = [];
		if (self::$provider === self::PROVIDER_GOOGLE)
		{
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
		}

		// check block/nodes style notation
		if (!is_array($manifest['style']['block']) && !is_array($manifest['style']['nodes']))
		{
			$manifest['style'] = [
				'block' => Block::DEFAULT_WRAPPER_STYLE,
				'nodes' => $manifest['style'],
			];
		}
		if (!empty($additional))
		{
			if (!is_array($manifest['style']['nodes'][self::MAP_SELECTOR]['additional']))
			{
				$manifest['style']['nodes'][self::MAP_SELECTOR]['additional'] = [];
			}
			$manifest['style']['nodes'][self::MAP_SELECTOR]['additional'][] = $additional;
		}
		else
		{
			unset($manifest['style']['nodes'][self::MAP_SELECTOR]['additional']);
		}

		return $manifest;
	}

	/**
	 * Load extensions
	 * @param array $manifest
	 * @return array
	 */
	protected static function addAssets(array $manifest): array
	{
		if (
			!is_array($manifest['assets']['ext'])
			|| !in_array(self::ASSET_NAME, $manifest['assets']['ext'], true)
		)
		{
			$manifest['assets']['ext'][] = self::ASSET_NAME;
		}

		return $manifest;
	}
}