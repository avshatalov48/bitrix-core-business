<?php
define('SITE_TEMPLATE_ID', 'landing24');
define('LANDING_PREVIEW_MODE', true);
define('LANDING_DEVELOPER_MODE', false);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

use \Bitrix\Landing\Block;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Assets;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Config;
use \Bitrix\Landing\Template;
use \Bitrix\Main\EventManager;

/**
 * Gets hooks data from common fields.
 * @param array $fields Data fields.
 * @return array
 */
$getHooksData = function($fields)
{
	$data = [];

	foreach ((array)$fields as $code => $value)
	{
		if (!$value)
		{
			continue;
		}
		list($hookCode, $hookFieldCode) = explode('_', $code, 2);
		if (!isset($data[$hookCode]))
		{
			$data[$hookCode] = [];
		}
		$data[$hookCode][$hookFieldCode] = $value;
		unset($hookCode, $hookFieldCode);
	}
	unset($code, $value);

	return $data;
};

/**
 * Builds pseudo page from blocks and returns it.
 * @param array $blocks Array of Blocks data.
 * @param int $version Version number.
 * @return string
 */
$getBlocksContent = function($blocks, $version)
{
	$blocks = (array) $blocks;
	$content = '';

	// new type of template, with rules in manifest
	if ($version == 2)
	{
		foreach ($blocks as $blockItem)
		{
			if (!isset($blockItem['code']))
			{
				continue;
			}
			$blockGhost = new Block(-1*rand(1, 1000), [
				'ACTIVE' => 'Y',
				'PUBLIC' => 'Y',
				'CODE' => $blockItem['code'],
				'CONTENT' => Block::getContentFromRepository($blockItem['code'])
			]);
			// adjust cards
			if (isset($blockItem['cards']) && is_array($blockItem['cards']))
			{
				foreach ($blockItem['cards'] as $selector => $count)
				{
					$blockGhost->adjustCards($selector, $count);
				}
				unset($selector, $count);
			}
			// update style
			if (isset($blockItem['style']) && is_array($blockItem['style']) && !empty($blockItem['style']))
			{
				foreach ($blockItem['style'] as $selector => $classes)
				{
					if ($selector == '#wrapper')
					{
						$selector = '#' . $blockGhost->getAnchor($blockGhost->getId());
					}
					$blockGhost->setClasses([
						$selector => [
							'classList' => $classes
						]
					]);
				}
				unset($selector, $classes);
			}
			// update nodes
			if (isset($blockItem['nodes']) && !empty($blockItem['nodes']))
			{
				$blockGhost->updateNodes($blockItem['nodes']);
			}
			// update attrs
			if (isset($blockItem['attrs']) && !empty($blockItem['attrs']))
			{
				$blockGhost->setAttributes($blockItem['attrs']);
			}
			ob_start();
			$blockGhost->view();
			$content .= ob_get_contents();
			ob_end_clean();
			unset($blockGhost);
		}
	}
	// old type of template, just view the content/58.1.catalog_list_dynami
	else
	{
		uasort($blocks, function($a, $b)
		{
			if ($a['SORT'] == $b['SORT'])
			{
				return 0;
			}
			else
			{
				return $a['SORT'] > $b['SORT'] ? 1 : -1;
			}
		});
		foreach ($blocks as $blockItem)
		{
			if (!isset($blockItem['CODE']))
			{
				continue;
			}
			$blockGhost = new Block(-1*rand(1, 1000), [
				'ACTIVE' => 'Y',
				'PUBLIC' => 'Y',
				'CODE' => $blockItem['CODE'],
				'CONTENT' => $blockItem['CONTENT']
			]);
			ob_start();
			$blockGhost->view();
			$content .= ob_get_contents();
			ob_end_clean();
			unset($blockGhost);
		}
	}
	unset($blocks, $blockItem, $version);

	return $content;
};

/**
 * Gets full content for output.
 * @param array $content Content of page's part.
 * @param array $layout Layout data.
 * @return string
 */
$applyLayout = function(array $content, array $layout)
{
	$output = '';

	if (!isset($layout['code']))
	{
		$layout['code'] = 'empty';
	}
	if (
		!isset($layout['ref']) ||
		!is_array($layout['ref'])
	)
	{
		$layout['ref'] = [];
	}

	$res = Template::getList([
		'filter' => [
			'=XML_ID' => $layout['code']
		]
	]);
	if ($row = $res->fetch())
	{
		$output = str_replace(
			'#CONTENT#',
			$content[$layout['content']],
			$row['CONTENT']
		);
		foreach ($layout['ref'] as $num => $code)
		{
			if (isset($content[$code]))
			{
				$output = str_replace(
					'#AREA_' . $num . '#',
					$content[$code],
					$output
				);
			}
		}
		unset($num, $code);
	}
	unset($res, $row);

	return $output;
};

if (\Bitrix\Main\Loader::includeModule('landing'))
{
	$buttons = array_keys(\Bitrix\Landing\Hook\Page\B24button::getButtons());
	$context = \Bitrix\Main\Application::getInstance()->getContext();
	$request = $context->getRequest();
	$code = $request->get('code');
	$type = $request->get('type') ?: 'page';
	$hookData = [
		'site' => [],
		'page' => []
	];

	if ($code)
	{
		// include component for getting sites and pages
		$componentName = 'bitrix:landing.demo';
		$className = \CBitrixComponent::includeComponentClass($componentName);
		$demoCmp = new $className;/** @var $demoCmp LandingSiteDemoComponent */
		$demoCmp->initComponent($componentName);
		$demoCmp->arParams = [
			'TYPE' => mb_strtoupper($type)
		];
		$sites = $demoCmp->getDemoSite();

		// if template are exist
		if (isset($sites[$code]))
		{
			$pages = $demoCmp->getDemoPage();
			$content = [];
			$layout = [
				'code' => 'empty',
				'ref' => []
			];
			// set title
			if (isset($sites[$code]['DATA']['name']))
			{
				$APPLICATION->setTitle($sites[$code]['DATA']['name']);
			}
			// collect hooks data for general site
			if (isset($sites[$code]['DATA']['fields']['ADDITIONAL_FIELDS']))
			{
				$hookData['site'] = $getHooksData(
					$sites[$code]['DATA']['fields']['ADDITIONAL_FIELDS']
				);
			}
			// layout for site
			if (isset($sites[$code]['DATA']['layout']))
			{
				$layout = $sites[$code]['DATA']['layout'];
			}

			// going to the page

			// get the first page, it will be the main page
			if (isset($sites[$code]['DATA']['items'][0]))
			{
				$mpCode = $sites[$code]['DATA']['items'][0];
			}
			// or get the code of site, it will be the main
			else
			{
				$mpCode = $sites[$code]['ID'];
			}
			// collect hooks data for page
			if (isset($pages[$mpCode]['DATA']['fields']['ADDITIONAL_FIELDS']))
			{
				$hookData['page'] = $getHooksData(
					$pages[$mpCode]['DATA']['fields']['ADDITIONAL_FIELDS']
				);
			}
			// layout for page
			if (isset($pages[$mpCode]['DATA']['layout']))
			{
				$layout = $pages[$mpCode]['DATA']['layout'];
			}
			// get content for every part of page
			$content[$mpCode] = '';
			if (isset($layout['ref']))
			{
				foreach ((array)$layout['ref'] as $code)
				{
					$content[$code] = '';
				}
			}
			unset($code);
			foreach ($content as $key => $value)
			{
				if (
					isset($pages[$key]['DATA']['items']) &&
					is_array($pages[$key]['DATA']['items'])
				)
				{
					$content[$key] = $getBlocksContent(
						$pages[$key]['DATA']['items'],
						$pages[$key]['DATA']['version']
					);
				}
			}
			unset($key, $pages);
			// output
			$layout['content'] = $mpCode;
			echo $applyLayout($content, $layout);
			unset($content, $mpCode);
		}
		unset($sites);
	}

	unset($context, $request);

	// exec hooks
	$hooksExec = [];
	$hookData['page']['B24BUTTON'] = [
		'CODE' => $buttons[0]
	];
	foreach (Hook::getForSite(0) as $code => $hook)
	{
		foreach (['site', 'page'] as $hookType)
		{
			if (isset($hookData[$hookType][$code]))
			{
				$hook->setData(
					$hookData[$hookType][$code]
				);
				if ($hook->enabled())
				{
					$hooksExec[$code] = $hook;
				}
			}
		}
	}
	unset($code, $hook);

	foreach ($hooksExec as $hook)
	{
		$hook->exec();
	}
	unset($hookData, $hooksExec, $hook);

	// general assets
	$assets = Assets\Manager::getInstance();
	$assets->addAsset(
		'landing_public',
		Assets\Location::LOCATION_AFTER_TEMPLATE
	);
	$assets->addAsset(
		Config::get('js_core_public'),
		Assets\Location::LOCATION_KERNEL
	);
	$assets->addAsset(
		'landing_critical_grid',
		Assets\Location::LOCATION_BEFORE_ALL
	);
	unset($assets);

	// final init assets
	Manager::setPageView('MainClass', 'landing-public-mode');
	$eventManager = EventManager::getInstance();
	$eventManager->addEventHandler('main', 'OnEpilog',
		function()
		{
			Manager::initAssets();
		}
	);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');