<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

final class SocialnetworkBlogPost extends CBitrixComponent
{
	public function convertRequestData(): void
	{
		\Bitrix\Socialnetwork\ComponentHelper::convertSelectorRequestData($_POST);
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

	public function executeComponent()
	{
		return $this->__includeComponent();
	}
}
