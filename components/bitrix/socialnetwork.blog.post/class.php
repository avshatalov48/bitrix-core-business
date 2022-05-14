<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Page\Asset;
use Bitrix\Socialnetwork\ComponentHelper;

final class SocialnetworkBlogPost extends CBitrixComponent
{
	public function onPrepareComponentParams($params)
	{
		return $params;
	}

	public function convertRequestData(): void
	{
		ComponentHelper::convertSelectorRequestData($_POST);
	}

	protected function clearTextForColoredPost(string $text = '')
	{
		$text = preg_replace('/\[DISK\s+FILE\s+ID\s*\=\s*[n]?[0-9]+\]/is'.BX_UTF_PCRE_MODIFIER, '', $text);
		$text = preg_replace('/\[\/*QUOTE\]/is'.BX_UTF_PCRE_MODIFIER, '', $text);
		$text = preg_replace('/\[\/*CODE\]/is'.BX_UTF_PCRE_MODIFIER, '', $text);
		$text = preg_replace('/\[\/*LEFT\]/is'.BX_UTF_PCRE_MODIFIER, '', $text);
		$text = preg_replace('/\[\/*RIGHT\]/is'.BX_UTF_PCRE_MODIFIER, '', $text);
		$text = preg_replace('/\[\/*CENTER\]/is'.BX_UTF_PCRE_MODIFIER, '', $text);
		$text = preg_replace('/\[\/*JUSTIFY\]/is'.BX_UTF_PCRE_MODIFIER, '', $text);
		$text = preg_replace('/\[COLOR\s*=\s*[^\]]+\](.+?)\[\/COLOR\]/is'.BX_UTF_PCRE_MODIFIER, '\\1', $text);
		$text = preg_replace('/\[FONT\s+[^\]]+\](.+?)\[\/FONT\]/is'.BX_UTF_PCRE_MODIFIER, '\\1', $text);
		$text = preg_replace('/\[SIZE\s+[^\]]+\](.+?)\[\/SIZE\]/is'.BX_UTF_PCRE_MODIFIER, '\\1', $text);
		$text = preg_replace('/\[IMG[^\]]*\]/is'.BX_UTF_PCRE_MODIFIER, '', $text);
		$text = preg_replace('/\[LIST[^\]]*\](.+?)\[\/LIST\]/is'.BX_UTF_PCRE_MODIFIER, '\\1', $text);
		$text = preg_replace('/\[\*\]/is'.BX_UTF_PCRE_MODIFIER, '', $text);
		$text = preg_replace('/\[\/*VIDEO\]/is'.BX_UTF_PCRE_MODIFIER, '', $text);
		$text = preg_replace(
			[
				'/\[B\](.+?)\[\/B\]/is'.BX_UTF_PCRE_MODIFIER,
				'/\[I\](.+?)\[\/I\]/is'.BX_UTF_PCRE_MODIFIER,
				'/\[U\](.+?)\[\/U\]/is'.BX_UTF_PCRE_MODIFIER,
				'/\[S\](.+?)\[\/S\]/is'.BX_UTF_PCRE_MODIFIER,
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
		return $this->__includeComponent();
	}
}
