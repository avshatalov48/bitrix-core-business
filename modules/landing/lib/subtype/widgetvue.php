<?php

namespace Bitrix\Landing\Subtype;

use Bitrix\Landing\Assets\Manager;
use Bitrix\Landing\Block;
use Bitrix\Landing\Repo;
use Bitrix\Landing\Mainpage;
use Bitrix\Landing;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\DOM;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\UsageStatTable;

/**
 * Block with Vue library
 */
class WidgetVue
{
	private const APP_DEBUG_OPTION_NAME = 'widgetvue_debug';

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
			'ext' => [
				'landing.widgetvue',
				'main.loader',
			],
		];

		$manifest['style'] = [
			'block' => [
				'type' => ['widget'],
			],
		];

		$assets = Manager::getInstance();
		$assets->addString(self::getVueScript($block, $params));

		if (
			!Landing\Manager::isAjaxRequest()
			&& Loader::includeModule('rest')
		)
		{
			$app = self::getAppInfo($block);
			if (isset($app['CLIENT_ID']))
			{
				UsageStatTable::logLandingWidget($app['CLIENT_ID'], 'render');
				UsageStatTable::finalize();
			}
		}

		// add callbacks
		$manifest['callbacks'] = [
			'afterAdd' => function (Block $block) use ($params)
			{
				$content = $block->getContent();
				$doc = new DOM\Document();
				try
				{
					$doc->loadHTML($content);
				}
				catch (\Exception $e)
				{}

				$rootNode = $doc->querySelector($params['rootNode']);
				if (!$rootNode)
				{
					return;
				}

				$newId = self::getRootNodeId($block);
				$rootNode->setAttribute('id', $newId);
				$rootNode->setInnerHTML(
					self::getLoaderString($block)
					. self::getInitScript($block)
				);

				$parentNode = $rootNode->getParentNode();

				$wrapperNode = $doc->createElement('div');
				$wrapperNode->setClassName('landing-block');

				$wrapperNode->setChildNodesArray([$rootNode]);
				$parentNode->setChildNodesArray([$wrapperNode]);

				$block->saveContent($doc->saveHTML());
				$block->save();
			},
		];

		return $manifest;
	}

	private static function getAppInfo(Block $block): ?array
	{
		static $apps = [];

		$repoId = $block->getRepoId();
		if ($repoId)
		{
			if (!isset($apps[$repoId]))
			{
				$apps[$repoId] = Repo::getAppInfo($repoId);
			}

			return $apps[$repoId];
		}

		return null;
	}

	private static function getRootNodeId(Block $block, bool $loader = false): string
	{
		$id = $loader ? 'mp_widget_loader' : 'mp_widget';
		$id .= $block->getId();

		return $id;
	}

	private static function checkParams(?Block $block, array $params): bool
	{
		if (!$block)
		{
			return false;
		}

		if (!isset($params['rootNode']) || !is_string($params['rootNode']))
		{
			return false;
		}

		return true;
	}

	private static function getVueScript(Block $block, array $params): string
	{
		$rootNodeId = self::getRootNodeId($block);
		$vueParams = [
			'blockId' => $block->getId(),
			'rootNode' => '#' . $rootNodeId,
			'lang' => self::getLangPhrases($params),
		];

		$app = self::getAppInfo($block);
		if ($app && isset($app['ID']))
		{
			$vueParams['appId'] = (int)$app['ID'];

			$vueParams['appAllowedByTariff'] = true;
			if ($app['PAYMENT_ALLOW'] !== 'Y')
			{
				$vueParams['appAllowedByTariff'] = false;
			}
		}

		$content = Block::getContentFromRepository($block->getCode());
		$vueParams['template'] = $content ?? '';

		if (
			isset($params['style'])
			&& is_string($params['style'])
		)
		{
			$vueParams['style'] = $params['style'];
		}

		$vueParams['useDemoData'] = Mainpage\Manager::isUseDemoData();
		if (
			is_array($params['demoData'])
			&& !empty($params['demoData'])
		)
		{
			$vueParams['demoData'] = $params['demoData'];
		}

		if (
			isset($params['handler'])
			&& is_string($params['handler'])
			&& mb_strlen($params['handler']) > 0
		)
		{
			$vueParams['fetchable'] = true;
		}

		$vueParams['debug'] = false;
		if (isset($app['CODE']))
		{
			$vueParams['debug'] = self::isAppDebugEnabled($app['CODE']);
		}

		$vueParams = Json::encode($vueParams);
		$type = Landing\Site\Scope::getCurrentScopeId();

		return "
			<script>
				(() => {
					if (BX.Landing.Env)
					{
						BX.Landing.Env.getInstance().setType('{$type}');
					}
						
					const init = () => {
						(new BX.Landing.WidgetVue(
							{$vueParams}
						)).mount();
					};
					
					if (BX('{$rootNodeId}'))
					{
						init();
					}
					else 
					{
						BX.addCustomEvent('BX.Landing.WidgetVue:initNode', blockId => {
							if (blockId === '$rootNodeId')
							{
								init();
							}
						});
					}
				})();
			</script>
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

	private static function getLoaderString(Block $block): string
	{
		$loaderNodeId = self::getRootNodeId($block, true);

		return "
			<div id=\"$loaderNodeId\"></div>
			<script>
				(() => {
					const loaderNode = BX('$loaderNodeId');
					(new BX.Loader({
						target: loaderNode,
					})).show();
				})();
			</script>
			<style>
				#{$loaderNodeId} {
					height: 200px;
					position: relative;
				}
			</style>
		";
	}

	private static function getInitScript(Block $block): string
	{
		$rootNodeId = self::getRootNodeId($block);

		return "
			<script>
				BX.onCustomEvent('BX.Landing.WidgetVue:initNode', ['$rootNodeId']);
			</script>
		";
	}

	/**
	 * Enable or disable widgets debug logging
	 * @param string $appCode - code of repo application
	 * @param bool $enable
	 * @return void
	 */
	public static function setAppDebug(string $appCode, bool $enable): void
	{
		$data = Landing\Manager::getOption(self::APP_DEBUG_OPTION_NAME, '{}');
		try
		{
			$data = Json::decode($data);
			$data[$appCode] = $enable;
			Landing\Manager::setOption(self::APP_DEBUG_OPTION_NAME, Json::encode($data));
		}
		catch (\Exception $exception)
		{
			return;
		}
	}

	/**
	 * Check is widget debug logging enabled
	 * @param string $appCode - code of repo application
	 * @return boolean
	 */
	private static function isAppDebugEnabled(string $appCode): bool
	{
		$data = Landing\Manager::getOption(self::APP_DEBUG_OPTION_NAME, '');
		try
		{
			$data = Json::decode($data);

			return $data[$appCode] ?? false;
		}
		catch (\Exception $exception)
		{
			return false;
		}
	}
}
