<?php
use \Bitrix\Main\SystemException;

//<title>Ebay</title>

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/export_yandex.php');
set_time_limit(0);

global $USER;

$bTmpUserCreated = false;

if (!CCatalog::IsUserExists())
{
	$bTmpUserCreated = true;

	if (isset($USER))
		$USER_TMP = $USER;
	$USER = new CUser();
}

CCatalogDiscountSave::Disable();

$arRunErrors = array();

$IBLOCK_ID = (int)$IBLOCK_ID;

if ($XML_DATA && CheckSerializedData($XML_DATA))
{
	$XML_DATA = unserialize(stripslashes($XML_DATA), ['allowed_classes' => false]);

	if (!is_array($XML_DATA))
		$XML_DATA = array();
}

if (!empty($XML_DATA['PRICE']))
{
	if ((int)$XML_DATA['PRICE'] > 0)
	{
		$rsCatalogGroups = CCatalogGroup::GetGroupsList(array('CATALOG_GROUP_ID' => $XML_DATA['PRICE'],'GROUP_ID' => 2));

		if (!($arCatalogGroup = $rsCatalogGroups->Fetch()))
			$arRunErrors[] = GetMessage('EBAY_ERR_BAD_PRICE_TYPE');
	}
	else
	{
		$arRunErrors[] = GetMessage('EBAY_ERR_BAD_PRICE_TYPE');
	}
}

if ($SETUP_FILE_NAME == '')
	$arRunErrors[] = GetMessage("CATI_NO_SAVE_FILE");
elseif (preg_match(BX_CATALOG_FILENAME_REG,$SETUP_FILE_NAME))
	$arRunErrors[] = GetMessage("CES_ERROR_BAD_EXPORT_FILENAME");
else
	$SETUP_FILE_NAME = Rel2Abs("/", $SETUP_FILE_NAME);

if (empty($arRunErrors))
{
	CheckDirPath($_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME);

	if (!$fp = @fopen($_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME, "wb"))
	{
		$arRunErrors[] = str_replace('#FILE#', $_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME, GetMessage('EBAY_ERR_FILE_OPEN_WRITING'));
	}
	else
	{
		if (!@fwrite($fp, '<?xml version="1.0" encoding="utf-8"?>'))
		{
			$arRunErrors[] = str_replace('#FILE#', $_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME, GetMessage('EBAY_ERR_SETUP_FILE_WRITE'));
			@fclose($fp);
		}
		else
		{
			@fwrite($fp, "\n<ListingArray>\n");
		}
	}
}

if (empty($arRunErrors))
{
	try
	{
		$offers = \Bitrix\Catalog\Ebay\ExportOfferCreator::getOfferObject(
			array(
				"IBLOCK_ID" => $IBLOCK_ID,
				"PRODUCT_GROUPS" => $V,
				"XML_DATA" => $XML_DATA,
				"SETUP_SERVER_NAME" => $SETUP_SERVER_NAME
			)
		);
	}
	catch(SystemException $e)
	{
		$arRunErrors[] = $e->getMessage();
	}

	foreach($offers as $offerId => $offer)
	{
		$strXmlProduct = "\t<Listing>\n";
		$strXmlProduct .= "\t\t<Product>\n";
		$strXmlProduct .= "\t\t\t<SKU>".$offer["PROPERTIES"]["ARTNUMBER"]["VALUE"]."</SKU>\n";
		$strXmlProduct .= "\t\t\t<ProductInformation>\n";
		$strXmlProduct .= "\t\t\t\t<Title>".$offer["NAME"]."</Title>\n";
		$strXmlProduct .= "\t\t\t\t<Description>\n";
		$strXmlProduct .= "\t\t\t\t\t<ProductDescription><![CDATA[".$offer["DESCRIPTION"]."!]]</ProductDescription>\n";
		$strXmlProduct .= "\t\t\t\t</Description>\n";
		$strXmlProduct .= "\t\t\t\t<PictureUrls>\n";
		$strXmlProduct .= "\t\t\t\t<PictureUrl>".($offer["DETAIL_PICTURE"] <> '' ? $offer["DETAIL_PICTURE"] : $offer["PREVIEW_PICTURE"] )."</PictureUrl>\n";
		$strXmlProduct .= "\t\t\t\t\t</PictureUrls>\n";
		$strXmlProduct .= "\t\t\t\t<Categories>\n";
		$strXmlProduct .= "\t\t\t\t<Category>".($offer["DETAIL_PICTURE"] <> '' ? $offer["DETAIL_PICTURE"] : $offer["PREVIEW_PICTURE"] )."</Category>\n";
		$strXmlProduct .= "\t\t\t\t\t</Categories>\n";
		$strXmlProduct .= "\t\t\t</ProductInformation>\n";
		$strXmlProduct .= "\t\t</Product>\n";
		$strXmlProduct .= "\t</Listing>\n";

		@fwrite($fp, $strXmlProduct);
	}

	@fwrite($fp, '</ListingArray>');
	@fclose($fp);
}

CCatalogDiscountSave::Enable();

if (!empty($arRunErrors))
	$strExportErrorMessage = implode('<br />',$arRunErrors);

if ($bTmpUserCreated)
{
	if (isset($USER_TMP))
	{
		$USER = $USER_TMP;
		unset($USER_TMP);
	}
}

die();
