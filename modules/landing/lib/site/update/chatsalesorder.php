<?php

namespace Bitrix\Landing\Site\Update;

use Bitrix\Landing\Internals;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Manager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loader::includeModule('landing');
Loader::includeModule('salescenter');

Loc::loadMessages(__FILE__);
Loc::loadMessages(Manager::getDocRoot() . '/bitrix/components/bitrix/landing.demo/data/page/store-chats-dark/catalog/.description.php');

class ChatSalesOrder extends Update
{
	/**
	 * This updater expects only this code.
	 */
	private const ONLY_CODES = [
		'store-chats-dark',
		'store-chats-light',
		'store-chats',
	];

	private const PAGE_CODE = 'store-chats-dark/order';

	private static function getExistsPageId(int $siteId, string $code): ?int
	{
		$res = Landing::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'SITE_ID' => $siteId,
				'=TPL_CODE' => $code,
				'=PUBLIC' => 'Y',
			],
		]);
		if ($page = $res->fetch())
		{
			return $page['ID'];
		}
		return null;
	}
	private static function getLandingHooks(string $pageId): ?array
	{
		$res = Internals\HookDataTable::getList([
			'select' => [
				'ID', 'HOOK', 'CODE', 'VALUE', 'PUBLIC'
			],
			'filter' => [
				'=ENTITY_TYPE' => 'L',
				'ENTITY_ID' => $pageId,
			],
		]);
		if ($rows = $res->fetchAll())
		{
			return $rows;
		}
		return null;
	}

	private static function getLandingBlocks(string $pageId): ?array
	{
		$res = Internals\BlockTable::getList([
			'select' => [
				'ID', 'CODE', 'CONTENT'
			],
			'filter' => [
				'LID' => $pageId,
			],
		 ]);
		if ($rows = $res->fetchAll())
		{
			return $rows;
		}
		return null;
	}

	private static function getNewLandingBlocksData(): array
	{
		return [
			'store.salescenter.order.details' => [
				'COMPONENT_PARAMS' => [
					'TEMPLATE_MODE' => 'graymode',
				],
			],
			'61.1.phone_w_btn_rght' => [
				'COMPONENT_PARAMS' => [
					'TEMPLATE_MODE' => 'graymode',
					'BUTTON_CLASSES' => 'btn g-rounded-50 g-btn-type-outline g-btn-px-l g-btn-size-md g-btn-darkgray text-uppercase',
				],
				'CLASSES' => [
					'landing-block g-pt-20 g-pb-0 g-bg-transparent u-block-border-none',
				],
			],
			'26.separator' => [
				'CONTENT' => '<section class="landing-block g-bg-transparent g-pt-20 g-pb-10" style="">'
					. PHP_EOL . '<hr class="landing-block-line g-brd-gray-dark-v2 my-0" style="" />'
					. PHP_EOL . '</section>',
			],
		];
	}

	private static function prepareLandingBlocks($currentBlocksData, $newBlocksData): void
	{
		$newBlocksDataFiltered = array_filter($newBlocksData, static function($newBlockData) {
			return isset($newBlockData['CONTENT']) || isset($newBlockData['COMPONENT_PARAMS']) || isset($newBlockData['CLASSES']);
		});

		$currentBlocksDataProcessed = array_map(static function($currentBlockData) use ($newBlocksDataFiltered) {
			foreach ($newBlocksDataFiltered as $codeBlock => $newBlockData)
			{
				if ($codeBlock === $currentBlockData['CODE'])
				{
					if (isset($newBlockData['CONTENT']))
					{
						$currentBlockData['CONTENT'] = $newBlockData['CONTENT'];
					}
					if (isset($newBlockData['COMPONENT_PARAMS']))
					{
						foreach ($newBlockData['COMPONENT_PARAMS'] as $nameParam => $valueParam)
						{
							$newParamsString = PHP_EOL . '"' . $nameParam . '" => "' . $valueParam . '",';
							$pattern = '/["\']' . $nameParam . '["\'][\s=>]*[^,]*[,?]/';
							if (preg_match($pattern, $currentBlockData['CONTENT']) === 1)
							{
								$currentBlockData['CONTENT'] = preg_replace($pattern, $newParamsString, $currentBlockData['CONTENT']);
							}
							else
							{
								$pattern = '/(["\'].*["\']\s*=>\s*[^,]*[,?])/';
								$currentBlockData['CONTENT'] = preg_replace($pattern, '${1}' . $newParamsString, $currentBlockData['CONTENT'], 1);
							}
						}
					}
					if (isset($newBlockData['CLASSES']))
					{
						$pos = strpos($newBlockData['CLASSES'][0], ' ');
						$string = substr($newBlockData['CLASSES'][0], 0, $pos + 1);
						$pattern = '/class="'. $string . '.*"/';
						$replacement = 'class="' . $newBlockData['CLASSES'][0] . '"';
						$currentBlockData['CONTENT'] = preg_replace($pattern, $replacement, $currentBlockData['CONTENT']);
					}
					return $currentBlockData;
				}
			}
			return $currentBlockData;
		}, $currentBlocksData);

		$preparedBlocksData = array_filter($currentBlocksDataProcessed);

		//update
		foreach ($preparedBlocksData as $preparedBlockData)
		{
			Internals\BlockTable::update(
				$preparedBlockData['ID'],
				['CONTENT' => $preparedBlockData['CONTENT']]
			);
		}
	}

	private static function getNewLandingHooks(): array
	{
		return [
			[
				'HOOK' => 'BACKGROUND',
				'CODE' => 'USE',
				'VALUE' => 'Y',
			],
			[
				'HOOK' => 'BACKGROUND',
				'CODE' => 'COLOR',
				'VALUE' => '#1c1c22',
			],
			[
				'HOOK' => 'BACKGROUND',
				'CODE' => 'PICTURE',
				'VALUE' => 'https://cdn.bitrix24.site/bitrix/images/landing/bg/store-chat-gray.jpg',
			],
			[
				'HOOK' => 'BACKGROUND',
				'CODE' => 'POSITION',
				'VALUE' => 'no_repeat',
			],
			[
				'HOOK' => 'CSSBLOCK',
				'CODE' => 'USE',
				'VALUE' => 'Y',
			],
			[
				'HOOK' => 'CSSBLOCK',
				'CODE' => 'CODE',
				'VALUE' => '.landing-viewtype--mobile .landing-public-mode {outline: none;}',
			],
		];
	}

	private static function prepareLandingHooks($currentHooks, $newHooks, $pageId): void
	{
		$isUpdateBgHooks = true;
		foreach ($currentHooks as $currentHookData)
		{
			if (
				$currentHookData['HOOK'] === 'BACKGROUND'
				&& $currentHookData['CODE'] === 'USE'
				&& $currentHookData['VALUE'] === 'Y'
			)
			{
				$isUpdateBgHooks = false;
			}
		}

		$updateHooksData = [];
		$createHooksData = [];
		foreach ($newHooks as $hookData)
		{
			if ($hookData['HOOK'] === 'BACKGROUND' && $isUpdateBgHooks === false)
			{
				continue;
			}

			$isExistPublicHook = false;
			$isExistUnPublicHook = false;
			foreach ($currentHooks as $currentHookData)
			{
				if (
					$hookData['HOOK'] === $currentHookData['HOOK']
					&& $hookData['CODE'] === $currentHookData['CODE']
				)
				{
					if ($hookData['VALUE'] !== $currentHookData['VALUE'])
					{
						$updateHooksData[] = [
							'ID' => $currentHookData['ID'],
							'VALUE' => $hookData['VALUE'],
						];
					}
					$isPublicHook = $currentHookData['PUBLIC'];
					if ($isPublicHook === 'Y')
					{
						$isExistPublicHook = true;
					}
					if ($isPublicHook === 'N')
					{
						$isExistUnPublicHook = true;
					}
				}
			}
			$isNeedCreateHook = false;
			if (!$isExistPublicHook)
			{
				$isNeedCreateHook = true;
				$isPublic = 'Y';
			}
			if (!$isExistUnPublicHook)
			{
				$isNeedCreateHook = true;
				$isPublic = 'N';
			}
			if ($isNeedCreateHook === true && isset($isPublic))
			{
				$createHooksData[] = [
					'ENTITY_ID' => $pageId,
					'ENTITY_TYPE' => 'L',
					'HOOK' => $hookData['HOOK'],
					'CODE' => $hookData['CODE'],
					'VALUE' => $hookData['VALUE'],
					'PUBLIC' => $isPublic,
				];
			}
		}

		//update
		foreach ($updateHooksData as $updateHookData)
		{
			Internals\HookDataTable::update(
				$updateHookData['ID'],
				['VALUE' => $updateHookData['VALUE']]
			);
		}
		//create
		foreach ($createHooksData as $createHookData)
		{
			Internals\HookDataTable::add([
				'ENTITY_ID' => $createHookData['ENTITY_ID'],
				'ENTITY_TYPE' => $createHookData['ENTITY_TYPE'],
				'HOOK' => $createHookData['HOOK'],
				'CODE' => $createHookData['CODE'],
				'VALUE' => $createHookData['VALUE'],
				'PUBLIC' => $createHookData['PUBLIC'],
			]);
		}
	}

	/**
	 * Entry point. Returns true on success.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	public static function update(int $siteId): bool
	{
		$site = self::getId($siteId);

		if (!$site || !in_array($site['TPL_CODE'], self::ONLY_CODES, true))
		{
			return true;
		}

		$orderPageId = self::getExistsPageId($siteId, self::PAGE_CODE);
		if ($orderPageId)
		{
			$currentHooks = self::getLandingHooks($orderPageId);
			$newHooks = self::getNewLandingHooks();
			self::prepareLandingHooks($currentHooks, $newHooks, $orderPageId);

			$currentBlocks = self::getLandingBlocks($orderPageId);
			$newBlocksData = self::getNewLandingBlocksData();
			self::prepareLandingBlocks($currentBlocks, $newBlocksData);
		}

		return true;
	}
}
