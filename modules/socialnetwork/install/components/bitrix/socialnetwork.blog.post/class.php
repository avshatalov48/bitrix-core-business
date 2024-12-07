<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\AI;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Socialnetwork\ComponentHelper;

final class SocialnetworkBlogPost extends CBitrixComponent
{
	public function onPrepareComponentParams($params)
	{
		$params['CONTEXT'] = (is_string($params['CONTEXT'] ?? null) ? $params['CONTEXT'] : '');

		return $params;
	}

	public function convertRequestData(): void
	{
		ComponentHelper::convertSelectorRequestData($_POST);
	}

	protected function clearTextForColoredPost(string $text = '')
	{
		$text = preg_replace('/\[DISK\s+FILE\s+ID\s*\=\s*[n]?[0-9]+\]/isu', '', $text);
		$text = preg_replace('/\[\/*QUOTE\]/isu', '', $text);
		$text = preg_replace('/\[\/*CODE\]/isu', '', $text);
		$text = preg_replace('/\[\/*LEFT\]/isu', '', $text);
		$text = preg_replace('/\[\/*RIGHT\]/isu', '', $text);
		$text = preg_replace('/\[\/*CENTER\]/isu', '', $text);
		$text = preg_replace('/\[\/*JUSTIFY\]/isu', '', $text);
		$text = preg_replace('/\[COLOR\s*=\s*[^\]]+\](.+?)\[\/COLOR\]/isu', '\\1', $text);
		$text = preg_replace('/\[FONT\s+[^\]]+\](.+?)\[\/FONT\]/isu', '\\1', $text);
		$text = preg_replace('/\[SIZE\s+[^\]]+\](.+?)\[\/SIZE\]/isu', '\\1', $text);
		$text = preg_replace('/\[IMG[^\]]*\]/isu', '', $text);
		$text = preg_replace('/\[LIST[^\]]*\](.+?)\[\/LIST\]/isu', '\\1', $text);
		$text = preg_replace('/\[\*\]/isu', '', $text);
		$text = preg_replace('/\[\/*VIDEO\]/isu', '', $text);
		$text = preg_replace(
			[
				'/\[B\](.+?)\[\/B\]/isu',
				'/\[I\](.+?)\[\/I\]/isu',
				'/\[U\](.+?)\[\/U\]/isu',
				'/\[S\](.+?)\[\/S\]/isu',
			],
			'\\1',
			$text
		);

		return $text;
	}

	protected function getUrlMetadataExpireDate(array $params = [])
	{
		$result = false;

		$id = (int)($params['id'] ?? 0);
		if ($id <= 0)
		{
			return $result;
		}

		$res = \Bitrix\Main\UrlPreview\UrlMetadataTable::getList([
			'filter' => [
				'ID' => $id,
			],
			'select' => [ 'DATE_EXPIRE' ],
		]);

		if ($urlMetadataFields = $res->fetch())
		{
			$result = $urlMetadataFields['DATE_EXPIRE'];
		}

		return $result;
	}

	protected function processUrlPreview(array $params = []): void
	{
		global $APPLICATION;

		$fieldData = ($params['fieldData'] && is_array($params['fieldData']) ? $params['fieldData'] : []);

		if (
			!isset($fieldData['VALUE'])
			|| (int)$fieldData['VALUE'] <= 0
		)
		{
			return;
		}

		$value = (int)$fieldData['VALUE'];
		$postId = (int)($params['postId'] ?? 0);

		$cacheTime = $this->arParams['CACHE_TIME'];

		$expireDate = $this->getUrlMetadataExpireDate([
			'id' => $value,
		]);
		if ($expireDate)
		{
			$lifetime = MakeTimeStamp($expireDate) - time();
			if ($lifetime < 0)
			{
				$lifetime = 0;
			}
			$cacheTime = $lifetime;
		}

		$cache = new CPHPCache;

		$cacheIdList = [];
		$cacheKeysList = [
			'MOBILE',
			'LAZYLOAD',
			'NAME_TEMPLATE',
			'PATH_TO_USER',
		];

		foreach ($cacheKeysList as $paramKey)
		{
			$cacheIdList[$paramKey] = ($this->arParams[$paramKey] ?? false);
		}

		$cacheId = implode('_', [
			$value,
			'blog_socnet_urlpreview',
			md5(serialize($cacheIdList)),
			LANGUAGE_ID,
		]);

		$cachePath = ComponentHelper::getBlogPostCacheDir([
			'TYPE' => 'post_urlpreview',
			'POST_ID' => $postId,
		]);

		if (
			$cacheTime > 0
			&& $cache->initCache($cacheTime, $cacheId, $cachePath)
		)
		{
			$vars = $cache->getVars();
			$this->arResult['URL_PREVIEW'] = ($vars['URL_PREVIEW'] ?? '');
			$urlPreviewAssets = ($vars['Assets'] ?? []);

			if (!empty($urlPreviewAssets))
			{
				if (!empty($urlPreviewAssets['CSS']))
				{
					foreach ($urlPreviewAssets['CSS'] as $cssFile)
					{
						Asset::getInstance()->addCss($cssFile);
					}
				}

				if (!empty($urlPreviewAssets['JS']))
				{
					foreach ($urlPreviewAssets['JS'] as $jsFile)
					{
						Asset::getInstance()->addJs($jsFile);
					}
				}
			}

			$cache->Output();
		}
		else
		{
			$urlPreviewAssets = [
				'CSS' => [],
				'JS' => [],
			];

			if ($cacheTime > 0)
			{
				$cache->startDataCache($cacheTime, $cacheId, $cachePath);
			}

			$cssList = $APPLICATION->sPath2css;
			$jsList = $APPLICATION->arHeadScripts;

			$this->arResult['URL_PREVIEW'] = ComponentHelper::getUrlPreviewContent($fieldData, [
				'LAZYLOAD' => $this->arParams['LAZYLOAD'],
				'MOBILE' => (isset($this->arParams['MOBILE']) && $this->arParams['MOBILE'] === 'Y' ? 'Y' : 'N'),
				'NAME_TEMPLATE' => $this->arParams['NAME_TEMPLATE'],
				'PATH_TO_USER' => $this->arParams['~PATH_TO_USER'],
			]);


			$urlPreviewAssets['CSS'] = array_diff($APPLICATION->sPath2css, $cssList);
			$urlPreviewAssets['JS'] = array_diff($APPLICATION->arHeadScripts, $jsList);

			if ($cacheTime > 0)
			{
				$cache->endDataCache([
					'URL_PREVIEW' => $this->arResult['URL_PREVIEW'],
					'Assets' => $urlPreviewAssets,
				]);
			}
		}
	}

	public function executeComponent()
	{
		$this->arResult['IS_COPILOT_READONLY_ENABLED'] = $this->isCopilotEnabled();
		$this->arResult['IS_COPILOT_READONLY_ENABLED_BY_SETTINGS'] = $this->isCopilotEnabledBySettings();

		$userId = \Bitrix\Main\Engine\CurrentUser::get()->getId();
		$pathToPostEdit = $this->arParams['PATH_TO_POST_EDIT'];
		$this->arResult['PATH_TO_CREATE_NEW_POST'] = str_replace(['#user_id#', '#post_id#'], [$userId, 0], $pathToPostEdit);

		return $this->__includeComponent();
	}

	private function isCopilotEnabled(): bool
	{
		if (!Loader::includeModule('ai'))
		{
			return false;
		}

		$engine = AI\Engine::getByCategory(AI\Engine::CATEGORIES['text'], AI\Context::getFake());

		return !is_null($engine);
	}

	private function isCopilotEnabledBySettings(): bool
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return false;
		}

		return \Bitrix\Socialnetwork\Integration\AI\Settings::isTextAvailable();
	}
}
