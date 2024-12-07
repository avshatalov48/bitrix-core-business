<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CSaleExportCML2 extends CSaleExport
{
	/**
	 * @param array $arFilter
	 * @return array
	 */
	protected static function prepareFilter($arFilter=array())
	{
		return $arFilter;
	}

	/**
	 * @param array $arOrder
	 */
	protected static function saveExportParams(array $arOrder)
	{
	}

	protected static function outputXmlUnit($arBasket)
	{
		$measures = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBasket::getCatalogMeasures();
		?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_BASE_UNIT")?> <?=CSaleExport::getTagName("SALE_EXPORT_CODE")?>="<?=$arBasket["MEASURE_CODE"]?>" <?=CSaleExport::getTagName("SALE_EXPORT_FULL_NAME_UNIT")?>="<?=htmlspecialcharsbx(self::$measures[$arBasket["MEASURE_CODE"]]["MEASURE_TITLE"])?>" <?=CSaleExport::getTagName("SALE_EXPORT_INTERNATIONAL_ABR")?>="<?=CSaleExport::getTagName("SALE_EXPORT_RCE")?>"><?=$measures[$arBasket["MEASURE_CODE"]]?></<?=CSaleExport::getTagName("SALE_EXPORT_BASE_UNIT")?>>
		<?
	}

	/**
	 * @param array $fields
	 * @return \Bitrix\Main\Entity\AddResult
	 * @deprecated
	 */
	public static function log(array $fields)
	{
		return new \Bitrix\Main\Entity\AddResult();
	}

	protected static function getLastOrderExported($timeUpdate)
	{
		return array();
	}
}

ob_start();

$options = array();

if (!empty($runtimeFields) && is_array($runtimeFields))
{
	$options['RUNTIME'] = $runtimeFields;
}
CSaleExportCML2::ExportOrders2Xml($arFilter, 0, "", false, 0, false, $options);

$contents = ob_get_contents();
ob_end_clean();

if(mb_strtoupper(LANG_CHARSET) != "WINDOWS-1251")
	$contents = \Bitrix\Main\Text\Encoding::convertEncoding($contents, LANG_CHARSET, "windows-1251");

$str = strlen($contents);

header('Pragma: public');
header('Cache-control: private');
header('Accept-Ranges: bytes');
header("Content-Type: application/xml; charset=windows-1251");
header("Content-Length: ".$str);
header("Content-Disposition: attachment; filename=orders.xml");

echo $contents;
die();
