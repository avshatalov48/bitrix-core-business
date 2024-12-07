<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Converters;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\TradingPlatform\Vk;

/**
 * Class Album
 * Convert album data from source.
 *
 * @package Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Converters
 */
class Album extends DataConverter
{
	const TITLE_LENGHT_MAX = 128;
	private $result;

	/**
	 * Album constructor.
	 *
	 * @param $exportId - int ID of export
	 */
	public function __construct($exportId)
	{
		if (!isset($exportId) || $exportId == '')
			throw new ArgumentNullException("EXPORT_ID");

		$this->exportId = $exportId;
	}


	/**
	 * Main method for convert
	 *
	 * @param $data - Array of albums data from source.
	 * @return array
	 */
	public function convert($data)
	{
		$this->result = array();
		$logger = new Vk\Logger($this->exportId);
		if ($data["ELEMENT_CNT"] == 0)
			$logger->addError("ALBUM_EMPTY", $data["ID"]);

		$this->result["SECTION_ID"] = $data["ID"];
		$this->result["IBLOCK_ID"] = $data["IBLOCK_ID"];
		$this->result["TITLE"] = $data["TO_ALBUM_ALIAS"] ? $data["TO_ALBUM_ALIAS"] : $data["NAME"];
		$this->result["TITLE"] = $this->validateTitle($this->result['TITLE'], $logger);
		$this->result["TITLE"] = $this->result["TITLE"];
//		add only checked photos
		$sortedPhotos = Vk\PhotoResizer::sortPhotoArray(
			array($data["PICTURE"], $data["DETAIL_PICTURE"]),
			'ALBUM'
		);
		$checkedPhotos = Vk\PhotoResizer::checkPhotos($sortedPhotos, 'ALBUM');
		if ($checkedPhotos)
			foreach ($checkedPhotos["PHOTOS"] as $photo)
			{
				$this->result["PHOTO_BX_ID"] = $photo["PHOTO_BX_ID"];
				$this->result["PHOTO_URL"] = $photo["PHOTO_URL"];
			}
		else
			$logger->addError("ALBUM_EMPTY_PHOTOS", $data["ID"]);

//		add item to log, if image was be resized
		if ($checkedPhotos['RESIZE'])
			$logger->addError('ALBUM_PHOTOS_'.$checkedPhotos['RESIZE_TYPE'], $data["ID"]);

		return array($data["ID"] => $this->result);
	}


	/**
	 * Valid length of TITLE
	 *
	 * @param string $title
	 * @param Vk\Logger|NULL $logger
	 * @return string
	 */
	private function validateTitle($title, Vk\Logger $logger = NULL)
	{
		$newTitle = $title;

		if (mb_strlen($title) > self::TITLE_LENGHT_MAX)
		{
			$newTitle = mb_substr($title, 0, self::TITLE_LENGHT_MAX - 1);
			if ($logger)
				$logger->addError('ALBUM_LONG_TITLE', $this->result["ID"]);
		}

		return $newTitle;
	}
}