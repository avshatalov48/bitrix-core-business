<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Converters;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\TradingPlatform\Vk;
use Bitrix\Highloadblock\HighloadBlockTable;

Loc::loadMessages(__FILE__);

class Product extends DataConverter
{
	protected $selectOfferProps;
	protected $sectionsList;
	private $result;

	const DESCRIPTION_LENGHT_MIN = 10;
	const DESCRIPTION_LENGHT_MAX = 3300;	// it is not entirely accurate value, but in doc i can't find true info
	const NAME_LENGHT_MIN = 4;
	const NAME_LENGHT_MAX = 100;

	/**
	 * Product constructor.
	 * @param $exportId - int ID of export
	 */
	public function __construct($exportId)
	{
		if (!isset($exportId) || $exportId == '')
			throw new ArgumentNullException("EXPORT_ID");

		$this->exportId = $exportId;
		$this->sectionsList = new Vk\SectionsList($this->exportId);
	}


	/**
	 * Main method for convert
	 *
	 * @param $data - Array of albums data from source.
	 * @return array
	 */
	public function convert($data)
	{
		$logger = new Vk\Logger($this->exportId);
		$this->result = array();

//		get common SKU and notSKU data
		$this->result = $this->getNotSkuItemData($data);

//		product WITH SKU
		$offersDescription = '';
		if (isset($data["OFFERS"]) && is_array($data["OFFERS"]) && !empty($data["OFFERS"]))
		{
			//adding desc and additional photos from SKU
			$this->selectOfferProps = $data["SELECT_OFFER_PROPS"];
			$this->result["PHOTOS_OFFERS"] = array();
			$this->result["PHOTOS_OFFERS_FOR_VK"] = array();

			$offersConverted = array();
			foreach ($data["OFFERS"] as $offer)
			{
				$resultOffer = $this->getItemDataOffersOffer($offer);

				if (!empty($resultOffer["PHOTOS"]))
					$this->result["PHOTOS_OFFERS"] += $resultOffer["PHOTOS"];

				if (!empty($resultOffer["PHOTOS_FOR_VK"]))
					$this->result["PHOTOS_OFFERS_FOR_VK"] += $resultOffer["PHOTOS_FOR_VK"];

				$offersConverted[] = $resultOffer;
			}

			$offersDescription = $this->createOffersDescriptionByPrices($offersConverted);
		}

//		check price. After offers convertions price may be changed
		if (!$this->result["PRICE"])
		{
			$logger->addError('PRODUCT_EMPTY_PRICE', $data["ID"]);

			return NULL;
		}

//		if exist offers descriptions - add title for them
		if ($offersDescription <> '')
			$this->result["description"] .= "\n\n" . Loc::getMessage("SALE_VK_PRODUCT_VARIANTS") . "\n" . $offersDescription;

//		sorted photos array in right order
//		todo: move this operation in Photoresizer
		$photosSorted = $this->sortPhotosArray();

//		CHECK photo sizes and count
		$photosChecked = Vk\PhotoResizer::checkPhotos($photosSorted, 'PRODUCT');
		if (empty($photosChecked))
		{
			$logger->addError("PRODUCT_WRONG_PHOTOS", $data["ID"]);

			return NULL;
		}

		$this->result["PHOTO_MAIN_BX_ID"] = $photosChecked["PHOTO_MAIN_BX_ID"];
		$this->result["PHOTO_MAIN_URL"] = $photosChecked["PHOTO_MAIN_URL"];
		$this->result["PHOTOS"] = $photosChecked["PHOTOS"];

//		add item to log, if image was be resized
		if ($photosChecked['RESIZE_UP'])
			$logger->addError('PRODUCT_PHOTOS_RESIZE_UP', $data["ID"]);
		if ($photosChecked['RESIZE_DOWN'])
			$logger->addError('PRODUCT_PHOTOS_RESIZE_DOWN', $data["ID"]);


//		cleaing DESCRIPTION
		$this->result["description"] = html_entity_decode($this->result["description"]);
		$this->result["description"] = preg_replace('/\t*/', '', $this->result["description"]);
		$this->result["description"] = strip_tags($this->result["description"]);

		$this->result['description'] = $this->validateDescription($this->result['description'], $logger);
		$this->result["description"] = self::convertToUtf8($this->result["description"]);

		$this->result['NAME'] = $this->validateName($this->result['NAME'], $logger);
		$this->result['NAME'] = self::convertToUtf8($this->result['NAME']);

		return array($data["ID"] => $this->result);
	}


	/**
	 * Valid length of name
	 *
	 * @param $name
	 * @param Vk\Logger|NULL $logger
	 * @return string
	 */
	private function validateName($name, Vk\Logger $logger = NULL)
	{
		$newName = $name;

		if (($length = self::matchLength($name)) < self::NAME_LENGHT_MIN)
		{
			$newName = self::extendString($name, $length, self::NAME_LENGHT_MIN);
			if ($logger)
			{
				$logger->addError('PRODUCT_SHORT_NAME', $this->result["BX_ID"]);
			}
		}

		if (($length = self::matchLength($name)) > self::NAME_LENGHT_MAX)
		{
			$newName = self::reduceString($name, $length, self::NAME_LENGHT_MAX);
			if ($logger)
			{
				$logger->addError('PRODUCT_LONG_NAME', $this->result["BX_ID"]);
			}
		}

		return $newName;
	}


	/**
	 * Valid length of description
	 *
	 * @param $name
	 * @param Vk\Logger|NULL $logger
	 * @return string
	 */
	private function validateDescription($desc, Vk\Logger $logger = NULL)
	{
		$newDesc = $desc;

		if (mb_strlen($desc) < self::DESCRIPTION_LENGHT_MIN)
		{
			$newDesc = $this->result['NAME'] . ': ' . $desc;
			if (mb_strlen($newDesc) < self::DESCRIPTION_LENGHT_MIN)
			{
				$newDesc = self::mb_str_pad($newDesc, self::DESCRIPTION_LENGHT_MIN, self::PAD_STRING);
//				ending space trim fix
				if ($newDesc[mb_strlen($newDesc) - 1] == ' ')
				{
					$newDesc .= self::PAD_STRING;
				}
				if ($logger)
				{
					$logger->addError('PRODUCT_SHORT_DESCRIPTION', $this->result["BX_ID"]);
				}
			}
		}

		if (mb_strlen($newDesc) > self::DESCRIPTION_LENGHT_MAX)
		{
			$newDesc = mb_substr($newDesc, 0, self::DESCRIPTION_LENGHT_MAX).'...';
		}

		return $newDesc;
	}


	/**
	 * Create description of SKU depending of prices.
	 * If all SKU prices equal main price - hide them.
	 * If prices a different - add them to description
	 *
	 * @param $offers
	 * @return string - string of SKUs description
	 */
	private function createOffersDescriptionByPrices($offers)
	{
		$mainPrice = isset($this->result['PRICE']) && $this->result['PRICE'] ? $this->result['PRICE'] : 0;
		$needSkuPriceDescription = false;

//		compare main price and SKU prices. Find minimum, check difference
		foreach ($offers as $offer)
		{
			if ($offer['PRICE'])
			{
//				if not set main price - get them from SKU prices
				if ($mainPrice == 0)
					$mainPrice = $offer['PRICE'];

//				add price to SKU descriptions only of prices is different
				if ($offer['PRICE'] != $mainPrice)
					$needSkuPriceDescription = true;

				$mainPrice = ($mainPrice != 0) ? min($offer['PRICE'], $mainPrice) : $offer['PRICE'];
			}
		}

//		update SKU DESRIPTIONS if needed
		$offersDescription = '';
		if ($needSkuPriceDescription)
		{
			foreach ($offers as $offer)
			{
				$offersDescription .= $offer["DESCRIPTION_PROPERTIES"] . " - " . Loc::getMessage("SALE_VK_PRODUCT_PRICE") . " " . $offer['PRICE'] . " " . Loc::getMessage("SALE_VK_PRODUCT_CURRENCY") . "\n";
			}
		}

		else
		{
			foreach ($offers as $offer)
			{
				$offersDescription .= $offer["DESCRIPTION_PROPERTIES"] . "\n";
			}
		}

		$this->result['PRICE'] = $mainPrice;

		return $offersDescription;
	}


	/**
	 * Sorted different photos types by priority
	 *
	 * @return array - Array of sorted photos
	 */
	private function sortPhotosArray()
	{
		$newPhotos = array();
		if (isset($this->result['PHOTOS_FOR_VK']) && !empty($this->result['PHOTOS_FOR_VK']))
			$newPhotos += $this->result['PHOTOS_FOR_VK'];

		if (isset($this->result['PHOTOS_OFFERS_FOR_VK']) && !empty($this->result['PHOTOS_OFFERS_FOR_VK']))
			$newPhotos += $this->result['PHOTOS_OFFERS_FOR_VK'];

		if (isset($this->result['PHOTO_MAIN']) && !empty($this->result['PHOTO_MAIN']))
			$newPhotos += $this->result['PHOTO_MAIN'];

		if (isset($this->result['PHOTOS']) && !empty($this->result['PHOTOS']))
			$newPhotos += $this->result['PHOTOS'];

		if (isset($this->result['PHOTOS_OFFERS']) && !empty($this->result['PHOTOS_OFFERS']))
			$newPhotos += $this->result['PHOTOS_OFFERS'];

//		delete wasted photos
		unset(
			$this->result['PHOTOS_FOR_VK'],
			$this->result['PHOTOS_OFFERS_FOR_VK'],
			$this->result['PHOTOS'],
			$this->result['PHOTO_MAIN'],
			$this->result['PHOTOS_OFFERS']
		);

		return $newPhotos;
	}


	/**
	 * Get description, prices and photos by SKUs
	 *
	 * @param $data
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getItemDataOffersOffer($data)
	{
		$result = array("DESCRIPTION" => "");

//		create description for SKU PROPERTIES
		$propertyDescriptions = array();
		foreach ($this->selectOfferProps as $prop)
		{
			if ($propValue = $data["PROPERTIES"][$prop]["VALUE"])
			{
//				check if HIGLOADBLOCKS
				if ($data["PROPERTIES"][$prop]["USER_TYPE"] == 'directory')
				{
					if (\CModule::IncludeModule('highloadblock'))
					{
//						get ID for hl-block
						$resHlBlocks = HighloadBlockTable::getList(array(
							'filter' => array('=TABLE_NAME' => $data["PROPERTIES"][$prop]["USER_TYPE_SETTINGS"]["TABLE_NAME"]),
						));
						$hlBlockItemId = $resHlBlocks->fetch();
						$hlBlockItemId = $hlBlockItemId['ID'];
//						HL directory may not exist in some strange situations
						if(!$hlBlockItemId)
							continue;

//						get entity class for current hl
						$hlBlock = HighloadBlockTable::getById($hlBlockItemId)->fetch();
						$hlEntity = HighloadBlockTable::compileEntity($hlBlock);
						$strEntityDataClass = $hlEntity->getDataClass();

//						get value for current hl
						$resData = $strEntityDataClass::getList(array(
							'select' => array('ID', 'UF_NAME'),
							'filter' => array('=UF_XML_ID' => $propValue),
						));
						$propValue = $resData->fetch();
						$propValue = $propValue['UF_NAME'];
					}
				}

				if(is_array($propValue))
					$propValue = implode(', ', $propValue);

				$propertyDescriptions[] = $data["PROPERTIES"][$prop]["NAME"] . ": " . $propValue;
			}
		}
		if (!empty($propertyDescriptions))
			$result["DESCRIPTION_PROPERTIES"] = implode("; ", $propertyDescriptions);

//		adding MAIN DESCRIPTION
		$description = strip_tags($data["~DETAIL_TEXT"] <> '' ? $data["~DETAIL_TEXT"] : $data["~PREVIEW_TEXT"]);
		if ($description)
			$result["DESCRIPTION"] .= $description;

//		adding PRICE. Ib desc we adding prices later
		$result['PRICE'] = $data["PRICES"]["MIN_RUB"];

//		adding PHOTOS
		$photoId = ($data["DETAIL_PICTURE"] <> '') ? $data["DETAIL_PICTURE"] : $data["PREVIEW_PICTURE"];
		if ($photoId)
			$result["PHOTOS"] = array($photoId => array("PHOTO_BX_ID" => $photoId));

//		adding special VK photos
		$vkPhotosKey = 'PHOTOS_FOR_VK_' . $data["IBLOCK_ID"];
		$resOfferProps = new \_CIBElement();
		$resOfferProps->fields = array("IBLOCK_ID" => $data["IBLOCK_ID"], "ID" => $data["ID"]);
		$resOfferProps = $resOfferProps->GetProperties(array(), array("CODE" => $vkPhotosKey));
		if (!empty($resOfferProps[$vkPhotosKey]["VALUE"]))
		{
			foreach ($resOfferProps[$vkPhotosKey]["VALUE"] as $ph)
			{
				$result["PHOTOS_FOR_VK"][$ph] = array(
					"PHOTO_BX_ID" => $ph,
				);
			}
		}

		return $result;
	}


	/**
	 * Get main (not SKU) data.
	 *
	 * @param $data
	 * @return array
	 */
	private function getNotSkuItemData($data)
	{
		$result = array();
		$result["BX_ID"] = $data["ID"];
		$result["IBLOCK_ID"] = $data["IBLOCK_ID"];
		$result["NAME"] = $data["~NAME"];
		$result["SECTION_ID"] = $data["IBLOCK_SECTION_ID"];
		$result["CATEGORY_VK"] = $this->sectionsList->getVkCategory($data["IBLOCK_SECTION_ID"]);

//		todo: DELETED should depended by AVAILABLE
		$result["deleted"] = 0;
		$result["PRICE"] = $data["PRICES"]["MIN_RUB"];    // price converted in roubles
		$result["description"] = $data["~DETAIL_TEXT"] <> '' ? $data["~DETAIL_TEXT"] : $data["~PREVIEW_TEXT"];
		$result["description"] = trim(preg_replace('/\s{2,}/', "\n", $result["description"]));
//		get main photo from preview or detail
		$photoMainBxId = $data["DETAIL_PICTURE"] <> '' ? $data["DETAIL_PICTURE"] : $data["PREVIEW_PICTURE"];
		$photoMainUrl = $data["DETAIL_PICTURE_URL"] <> '' ? $data["DETAIL_PICTURE_URL"] : $data["PREVIEW_PICTURE_URL"];
		if ($photoMainBxId && $photoMainUrl)
			$result["PHOTO_MAIN"] = array(
				$photoMainBxId => array(
					"PHOTO_BX_ID" => $photoMainBxId,
					"PHOTO_URL" => $photoMainUrl,
				),
			);

//		adding MORE PHOTOS to the all_photos array/ Later we will checked sizes
		if (isset($data["PROPERTIES"]["MORE_PHOTO"]["VALUE"]) &&
			is_array($data["PROPERTIES"]["MORE_PHOTO"]["VALUE"]) &&
			!empty($data["PROPERTIES"]["MORE_PHOTO"]["VALUE"])
		)
		{
			foreach ($data["PROPERTIES"]["MORE_PHOTO"]["VALUE"] as $ph)
			{
				$result["PHOTOS"][$ph] = array("PHOTO_BX_ID" => $ph);
			}
		}

//		take special VK photos
		$vkPhotosKey = 'PHOTOS_FOR_VK_' . $data["IBLOCK_ID"];
		if (isset($data["PROPERTIES"][$vkPhotosKey]["VALUE"]) &&
			is_array($data["PROPERTIES"][$vkPhotosKey]["VALUE"]) &&
			!empty($data["PROPERTIES"][$vkPhotosKey]["VALUE"])
		)
		{
			foreach ($data["PROPERTIES"][$vkPhotosKey]["VALUE"] as $ph)
			{
				$result["PHOTOS_FOR_VK"][$ph] = array(
					"PHOTO_BX_ID" => $ph,
				);
			}
		}

		return $result;
	}
}