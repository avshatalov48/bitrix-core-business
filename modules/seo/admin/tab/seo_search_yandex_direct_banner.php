<?php
/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global array $bannerInfo
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Converter;

$request = Bitrix\Main\Context::getCurrent()->getRequest();

if(!defined("YANDEX_DIRECT_BANNER_STYLES_INCLUDED"))
{
	define("YANDEX_DIRECT_BANNER_STYLES_INCLUDED", 1);
?>
<style>
	.yandex-adv-block
	{
		display: inline-block;
		width: 370px;
		overflow: hidden;
		background-color: #FFFFFF !important;
		color: #000000 !important;
		padding: 20px;
		margin-top: 4px;
		border: solid 1px #BBBBBB;
		font: 13px Arial, Helvetica, sans-serif !important;
		line-height: 16px !important;
	}

	.yandex-adv-block .yandex-title
	{
		font-size: 16px !important;
		line-height: 18px !important;
		margin: -5px 0 0 0;
		padding-bottom: 1px;
		font-weight: 400 !important;
	}

	.yandex-adv-block .yandex-title-link
	{
		text-decoration: underline !important;
		position: relative;
		z-index: 2;
		margin: -10px 0 0;
		padding: 10px 0 0;
		vertical-align: top;
		color: #33c !important;
	}

	.yandex-adv-block .yandex-title-link:hover
	{
		color: #d00 !important;
	}

	.yandex-adv-block .yandex-adv
	{
		color: #070 !important;
		margin: 2px 0;
		padding-bottom: 0;
		line-height: 16px !important;
	}

	.yandex-adv-block .yandex-adv-note
	{
		margin: 0 12px 0 0;
		background: none repeat scroll 0 0 #ffeba0 !important;
		border-radius: 3px;
		color: #332f1e !important;
		display: inline-block;
		font: 11px/15px Verdana !important;
		padding: 0 6px 1px;
		vertical-align: baseline;
	}

	.yandex-adv-block .yandex-adv-link
	{
		transition: color 0.15s ease-out 0s;
		margin-right: 20px;
		color: #070 !important;
		text-decoration: none !important;
	}

	.yandex-adv-block .yandex-adv-link:hover
	{
		color: #d00 !important;
		text-decoration: none !important;
	}
</style>
<?
}

if(!is_array($bannerInfo))
{
	$bannerInfo = array(
		'SETTINGS' => array(
			'Title' => Loc::getMessage('SEO_BANNER_DATA_TITLE'),
			'Text' => Loc::getMessage('SEO_BANNER_DATA_TEXT'),
			'Href' => ($request->isHttps() ? 'http' : 'https') . '://' . $request->getHttpHost()."/",
		)
	);
}

$host = parse_url($bannerInfo['SETTINGS']['Href'], PHP_URL_HOST);
?>
<div class="yandex-adv-block">
	<h2 class="yandex-title"><a href="/" class="yandex-title-link" id="yandex_link"><b id="yandex_title_content"><?=Converter::getHtmlConverter()->encode($bannerInfo['SETTINGS']['Title']);?></b> / <font id="yandex_link_content"><?=Converter::getHtmlConverter()->encode($host)?></font></a></h2>
<div class="yandex-adv"><div class="yandex-adv-note"><?=Loc::getMessage('SEO_BANNER_ADV_MARK')?></div><a class="yandex-adv-link" href="<?=Converter::getHtmlConverter()->encode($bannerInfo['SETTINGS']['Href'])?>" id="yandex_link_content_link"><?=Converter::getHtmlConverter()->encode($host)?></a></div>
<div class="yandex-text" id="yandex_text_content"><?=preg_replace("/\\n+/", ' ', Converter::getHtmlConverter()->encode($bannerInfo['SETTINGS']['Text']))?></div>
</div>
