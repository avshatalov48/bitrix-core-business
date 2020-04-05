<?php

namespace Bitrix\Iblock\Component;
/**
 * Class Tools
 * Provides various useful methods.
 *
 * @package Bitrix\Iblock\Component
 */
class Tools
{
	const IPROPERTY_ENTITY_ELEMENT = 'ELEMENT';
	const IPROPERTY_ENTITY_SECTION = 'SECTION';
	/**
	 * Performs actions enabled by its parameters.
	 *
	 * @param string $message Message to show with bitrix:system.show_message component.
	 * @param bool $defineConstant If true then ERROR_404 constant defined.
	 * @param bool $setStatus If true sets http response status.
	 * @param bool $showPage If true then work area will be cleaned and /404.php will be included.
	 * @param string $pageFile Alternative file to /404.php.
	 *
	 * @return void
	 */
	public static function process404($message = "", $defineConstant = true, $setStatus = true, $showPage = false, $pageFile = "")
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		if($message <> "")
		{
			$APPLICATION->includeComponent(
				"bitrix:system.show_message",
				".default",
				array(
					"MESSAGE"=> $message,
					"STYLE" => "errortext",
				),
				null,
				array(
					"HIDE_ICONS" => "Y",
				)
			);
		}

		if ($defineConstant && !defined("ERROR_404"))
		{
			define("ERROR_404", "Y");
		}

		if ($setStatus)
		{
			\CHTTP::setStatus("404 Not Found");
		}

		if ($showPage)
		{
			if ($APPLICATION->RestartWorkarea())
			{
				if (!defined("BX_URLREWRITE"))
					define("BX_URLREWRITE", true);
				\Bitrix\Main\Page\Frame::setEnable(false);
				if ($pageFile)
					require(\Bitrix\Main\Application::getDocumentRoot().rel2abs("/", "/".$pageFile));
				else
					require(\Bitrix\Main\Application::getDocumentRoot()."/404.php");
				die();
			}
		}
	}

	/**
	 * Get image data for element fields.
	 *
	 * @param array &$item				Result CIBlockResult::GetNext/Fetch or _CIBElement::GetFields.
	 * @param array $keys				Field keys.
	 * @param string $entity			Entity id.
	 * @param string $ipropertyKey		Key with seo data.
	 *
	 * @return void
	 */
	public static function getFieldImageData(array &$item, array $keys, $entity, $ipropertyKey = 'IPROPERTY_VALUES')
	{
		if (empty($item) || empty($keys))
			return;

		$entity = (string)$entity;
		$ipropertyKey = (string)$ipropertyKey;
		foreach ($keys as $fieldName)
		{
			if (!isset($item[$fieldName]) || is_array($item[$fieldName]))
				continue;
			$imageData = false;
			$imageId = (int)$item[$fieldName];
			if ($imageId > 0)
				$imageData = \CFile::getFileArray($imageId);
			unset($imageId);
			if (is_array($imageData))
			{
				if (isset($imageData['SAFE_SRC']))
				{
					$imageData['UNSAFE_SRC'] = $imageData['SRC'];
					$imageData['SRC'] = $imageData['SAFE_SRC'];
				}
				else
				{
					if (!preg_match('/^(ftp|ftps|http|https):\/\//', $imageData['SRC']))
					{
						$imageData['UNSAFE_SRC'] = $imageData['SRC'];
						$imageData['SAFE_SRC'] = \CHTTP::urnEncode($imageData['SRC'], 'UTF-8');
						$imageData['SRC'] = $imageData['SAFE_SRC'];
					}
				}
				$imageData['ALT'] = '';
				$imageData['TITLE'] = '';
				if ($ipropertyKey != '' && isset($item[$ipropertyKey]) && is_array($item[$ipropertyKey]))
				{
					$entityPrefix = $entity.'_'.$fieldName;
					if (isset($item[$ipropertyKey][$entityPrefix.'_FILE_ALT']))
						$imageData['ALT'] = $item[$ipropertyKey][$entityPrefix.'_FILE_ALT'];
					if (isset($item[$ipropertyKey][$entityPrefix.'_FILE_TITLE']))
						$imageData['TITLE'] = $item[$ipropertyKey][$entityPrefix.'_FILE_TITLE'];
					unset($entityPrefix);
				}
				if ($imageData['ALT'] == '' && isset($item['NAME']))
					$imageData['ALT'] = $item['NAME'];
				if ($imageData['TITLE'] == '' && isset($item['NAME']))
					$imageData['TITLE'] = $item['NAME'];
			}
			$item[$fieldName] = $imageData;
			unset($imageData);
		}
		unset($fieldName);
	}

	/**
	 * Get absolute path to image.
	 *
	 * @param array $image			Image array from CFile::GetFileArray or Tools::getImageData.
	 * @param bool $safe			Get encode path or unsafe.
	 * @return string
	 */
	public static function getImageSrc(array $image, $safe = true)
	{
		$result = '';
		if (empty($image) || !isset($image['SRC']))
			return $result;
		$safe = ($safe === true);

		if ($safe)
		{
			if (isset($image['SAFE_SRC']))
				$result = $image['SAFE_SRC'];
			elseif (preg_match('/^(ftp|ftps|http|https):\/\//', $image['SRC']))
				$result = $image['SRC'];
			else
				$result = \CHTTP::urnEncode($image['SRC'], 'UTF-8');
		}
		else
		{
			$result = (isset($image['UNSAFE_SRC']) ? $image['UNSAFE_SRC'] : $image['SRC']);
		}

		return $result;
	}
}