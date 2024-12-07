<?php

namespace Bitrix\Landing\Subtype;

use Bitrix\Landing\Assets\Manager;
use Bitrix\Landing\Block;
use Bitrix\Landing\Repo;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\DOM;
use Bitrix\Main\Web\Json;

/**
 * Block with Vue library
 */
class WidgetVue
{
	/**
	 * Prepare manifest.
	 * @param array $manifest Block's manifest.
	 * @param Block|null $block Block instance.
	 * @param array $params Additional params.
	 * @return array
	 */
	public static function prepareManifest(array $manifest, ?Block $block = null, array $params = []): array
	{
		if (!self::checkParams($block, $params))
		{
			return $manifest;
		}

		$manifest['nodes'] = [];
		$manifest['assets'] = [
			'ext' => ['landing.widgetvue'],
		];

		$manifest['style'] = [
			'block' => [
				'type' => ['widget'],
			],
		];

		$assets = Manager::getInstance();

		$vueScript = self::getVueScript($block, $params);
		$assets->addString(
			"<script>{$vueScript}</script>",
		);

		Extension::load('main.loader');
		$loaderScript = self::getLoaderScript($block, $params);
		$assets->addString(
			"<script>{$loaderScript}</script>",
		);

		$loaderStyle = self::getLoaderStyle($block);
		$assets->addString(
			"<style>{$loaderStyle}</style>",
		);

		$widgetStyle = $params['style'] ?? null;
		if ($widgetStyle)
		{
			$assets->addAsset($widgetStyle);
		}

		// add callbacks
		$manifest['callbacks'] = [
			'afterAdd' => function (Block $block) use ($params)
			{
				$content = $block->getContent();
				$protected = self::protectVueContentBeforeParse($content);

				$doc = new DOM\Document();
				try
				{
					$doc->loadHTML($content);
				}
				catch (\Exception $e) {}

				$rootNode = $doc->querySelector($params['rootNode']);
				if (!$rootNode)
				{
					return;
				}

				$newId = WidgetVue::getRootNodeId($block);
				$rootNode->setAttribute('id', $newId);
				$rootNode->setInnerHTML('');

				$parentNode = $rootNode->getParentNode();
				$parentNode->setChildNodesArray([$rootNode]);

				$contentAfter = $doc->saveHTML();
				self::returnVueContentAfterParse($contentAfter, $protected);

				$block->saveContent($contentAfter);
				$block->save();
			},
		];

		return $manifest;
	}

	private static function getRootNodeId(Block $block, bool $loader = false): string
	{
		$id = $loader ? 'mp_widget_loader' : 'mp_widget';
		$id .= $block->getId();

		return $id;
	}

	private static function checkParams (?Block $block, array $params): bool
	{
		if (!$block)
		{
			return false;
		}

		// todo: check exist node by dom
		if (!isset($params['rootNode']) || !is_string($params['rootNode']))
		{
			return false;
		}

		return true;
	}

	private static function getVueScript(Block $block, array $params): string
	{
		$vueParams = [
			'blockId' => $block->getId(),
			'rootNode' => '#' . WidgetVue::getRootNodeId($block),
			'lang' => self::getLangPhrases($params),
		];

		if ($block->getRepoId())
		{
			$app = Repo::getAppInfo($block->getRepoId());
			if ($app['ID'])
			{
				$vueParams['appId'] = (int)$app['ID'];

				$vueParams['appAllowedByTariff'] = true;
				if ($app['PAYMENT_ALLOW'] !== 'Y')
				{
					$vueParams['appAllowedByTariff'] = false;
				}
			}
		}

		$content = Block::getContentFromRepository($block->getCode());
		$vueParams['template'] = $content ?? '';

		if (
			is_array($params['data'])
			&& !empty($params['data'])
		)
		{
			$vueParams['data'] = $params['data'];
		}

		if (
			isset($params['handler'])
			&& is_string($params['handler'])
			&& mb_strlen($params['handler']) > 0
		)
		{
			$vueParams['fetchable'] = true;
		}

		$vueParams = Json::encode($vueParams);

		return "
			BX.ready(() => {
				(new BX.Landing.WidgetVue(
					{$vueParams}
				)).mount();
			});
		";
	}

	private static function getLangPhrases(array $params): array
	{
		$phrases = '{}';

		$lang = Loc::getCurrentLang();
		$defaultLang = 'en';

		if (is_array($params['lang']))
		{
			if (
				isset($params['lang'][$lang])
				|| isset($params['lang'][$defaultLang])
			)
			{
				$lang = isset($params['lang'][$lang]) ? $lang : $defaultLang;
				$phrases = $params['lang'][$lang];
			}
		}

		return $phrases;
	}

	private static function getLoaderScript(Block $block): string
	{
		$rootNodeId = WidgetVue::getRootNodeId($block);
		$loaderNodeId = WidgetVue::getRootNodeId($block, true);

		return "
			BX.ready(() => {
				const rootNode = BX('$rootNodeId');
				if (rootNode)
				{
					const loaderNode = document.createElement('div');
					loaderNode.id = '{$loaderNodeId}';
					BX.Dom.append(loaderNode, rootNode.parentElement);
				
					(new BX.Loader({
						target: loaderNode,
					})).show();
				}
			});
		";
	}

	protected static function getLoaderStyle(Block $block): string
	{
		$id = '#' . self::getRootNodeId($block, true);

		// todo: need loader style?
		return "
			$id {
				height: 200px;
				position: relative;
			}
		";
	}

	/**
	 * HTML-parser broke some vue-constructions. Replace them before parse
	 * @param string $content
	 * @return array - array of replaced values
	 */
	private static function protectVueContentBeforeParse(string &$content): array
	{
		$protected = [];
		$replaces = [
			'/@click/' => 0,
			'/v-if="([^"]+)"/' => 1,
			'/v-for="([^"]+)"/' => 1,
		];

		foreach ($replaces as $pattern => $position)
		{
			$callback = function($matches) use (&$protected, $position) {
				$replace = '__bxreplace' . count($protected);
				$protected[$replace] = $matches[$position];

				return ($position === 0)
					? $replace
					: str_replace($matches[$position], $replace, $matches[0]);
			};

			$content = preg_replace_callback($pattern, $callback, $content);
		}

		return $protected;
	}

	/**
	 * Return replaced vue-constructions after html-parse
	 * @param string $content
	 * @param array $protected
	 * @return void
	 */
	private static function returnVueContentAfterParse(string &$content, array $protected): void
	{
		$content = str_replace(array_keys($protected), array_values($protected), $content);
	}
}
