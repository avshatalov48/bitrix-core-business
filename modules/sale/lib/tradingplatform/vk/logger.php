<?php

namespace Bitrix\Sale\TradingPlatform\Vk;

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class Logger
 * Manage errors in Log Table.
 *
 * @package Bitrix\Sale\TradingPlatform\Vk
 */
class Logger
{
	private $exportId;
	
	const MAX_SHOWING_ERRORS_ITEMS = 5;
	
	/**
	 * Logger constructor.
	 * @param $exportId - int, ID of export profile
	 */
	public function __construct($exportId)
	{
		$this->exportId = $exportId;
	}
	
	/**
	 * Return list of saved on log errors
	 *
	 * @param null $errCode
	 * @param null $itemId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getExistingErrors($errCode = NULL, $itemId = NULL)
	{
		$result = array();
		
		$filter = array('=EXPORT_ID' => $this->exportId,);
		if ($errCode)
			$filter['=ERROR_CODE'] = $errCode;
		if ($itemId)
			$filter['=ITEM_ID'] = $itemId;
		
		$resExistErrors = LogTable::getList(array(
			'filter' => $filter,
			'order' => array('ERROR_CODE' => 'ASC'),
		));
		
		while ($err = $resExistErrors->fetch())
		{
			$result[$err['ID']] = $err;
		}
		
		return $result;
	}
	
	
	/**
	 * Log is like error, but not error.
	 * It is equal entities, but we always set ErrorCode in "log".
	 */
	public function addLog($itemId = NULL, $params = NULL)
	{
		return $this->addError("LOG", $itemId, print_r($params, true));	//print_r to preserve multilevel array error in mysql
	}
	
	/**
	 * Add new error in log.
	 * If error by this code already exist - match time. Use newer error,
	 * If set Item - adding in items list, if not - set only text error.
	 *
	 * @param $errCode - string of error code, from predetermined list
	 * @param null $itemId
	 * @return bool
	 * @throws ExecuteException
	 * @throws \Exception
	 */
	public function addError($errCode, $itemId = NULL, $errParams = NULL)
	{
		$errorDescription = $this->getErrorsDescriptions($errCode);
		$errCode = $errorDescription['CODE'] ? $errorDescription['CODE'] : $errCode;
//		add new error
		if (!$this->addErrorToTable($errCode, $itemId, $errParams))
			return false;

//		show notify only for critical errors, other message - show only on page
		if ($errorDescription['CRITICAL'])
		{
			throw new ExecuteException($errorDescription["MESSAGE"]);
		}
		
		return true;
	}
	
	
	/**
	 * Write new error to table
	 *
	 * @param $errCode
	 * @param null $itemId
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	private function addErrorToTable($errCode, $itemId = NULL, $errParams = NULL)
	{
		$fields = array(
			"EXPORT_ID" => $this->exportId,
			"ERROR_CODE" => $errCode,
		);
//		add item if not null
		if ($itemId)
			$fields["ITEM_ID"] = $itemId;

//		add params for rich log
		if ($errParams)
			$fields["ERROR_PARAMS"] = $errParams;
		
		$resExistError = LogTable::getList(array("filter" => $fields));
		
		if ($existError = $resExistError->fetch())
//			UPDATE
			$resDb = LogTable::update($existError["ID"], $fields);
		
		else
//			ADD
			$resDb = LogTable::add($fields);
		
		return $resDb->isSuccess();
	}
	
	
	/**
	 * @param $errCode - string of error code, from predetermined list
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	public function clearOneError($errCode)
	{
//		prepare code from errors list
		$errorDescription = $this->getErrorsDescriptions($errCode);

//		clear all code format: number and string. Need for errors code aliases
		$errCodes = array();
		$errCodes[$errorDescription['CODE']] = $errorDescription['CODE'];
		$errCodes[$errCode] = $errCode;
		
//		remove error from log table
		$resErrId = LogTable::getList(array('filter' => array("EXPORT_ID" => $this->exportId, "ERROR_CODE" => $errCodes)));
		while ($err = $resErrId->fetch())
		{
			$errId = $err["ID"];
			LogTable::delete($errId);
		}
	}
	
	
	/**
	 * Delete all errors from log (for one export ID)
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function clearLog()
	{
//		clear errors
		$existingErrors = $this->getExistingErrors();
		foreach ($existingErrors as $key => $err)
			$resDelete = LogTable::delete($err['ID']);
		
		return true;
	}
	
	
	/**
	 * Return existing errors in text format for view on page
	 *
	 * @return array|string
	 */
	public function getErrorsList($flagCritical = false)
	{
		$errorsConverted = array();
		$errorsConvertedStrings = array();
		$result = '';
		$errorsExistings = $this->getExistingErrors();
		$errorsDescriptions = $this->getErrorsDescriptions();
		
		if ($errorsExistings)
		{
			foreach ($errorsExistings as $err)
			{
//				only described and not ignored errors
				if (
					array_key_exists($err['ERROR_CODE'], $errorsDescriptions) &&
					!$errorsDescriptions[$err['ERROR_CODE']]['IGNORE']
				)
				{
//					if first error with this code - create new item
					$errorsConverted[$err['ERROR_CODE']]['MESSAGE'] = $errorsDescriptions[$err['ERROR_CODE']]['MESSAGE'];
					if ($err['ITEM_ID'])
						$errorsConverted[$err['ERROR_CODE']]['ITEMS'][$err['ITEM_ID']] = $err['ITEM_ID'];
				}

//				unknown errors (if not ignored)
				elseif (!$errorsDescriptions[$err['ERROR_CODE']]['IGNORE'])
				{
					$errorsConverted['UNKNOWN']['MESSAGE'] = Loc::getMessage("SALE_VK_ERRORS__UNKNOWN") . '. ' . Loc::getMessage("SALE_VK_ERRORS__ERRORS_CODES");
//					add codes as items to show in future
					if ($err['ERROR_CODE'])
						$errorsConverted['UNKNOWN']['ITEMS'][$err['ERROR_CODE']] = $err['ERROR_CODE'];
				}
			}

//			implode converted errors to one string for showing
			foreach ($errorsConverted as $errCode => $err)
			{
				$errCritical = $errorsDescriptions[$errCode]['CRITICAL'];
//				get errors by CRITICAL flag
				
				if ($errCritical == $flagCritical)
				{
					$itemsConvertedString = '';
					$itemsConverted = array();
					$itemsConvertedHide = array();
//					create strings for every item
					if (array_key_exists('ITEMS', $err) && !empty($err['ITEMS']))
					{
						$itemsCount = 0;
						foreach ($err['ITEMS'] as $item)
						{
							$itemConverted = $this->createItemErrorString(
								$item,
								$errCode
							);
							$itemsCount++;
							
							if (strlen($itemConverted) > 0)
								if ($itemsCount <= self::MAX_SHOWING_ERRORS_ITEMS)
									$itemsConverted[] = $itemConverted;
								else
									$itemsConvertedHide[] = $itemConverted;
						}
						if (!empty($itemsConverted))
							$itemsConvertedString = ': ' . implode(', ', $itemsConverted);
						if (!empty($itemsConvertedHide))
						{
							$itemsConvertedString .= ', 
							<span class="vk_export_notify__error_normal__hide_elements" style="display:none"
								id="vk_export_notify__error_normal__hide_elements--' . $errCode . '">' .
								implode(', ', $itemsConvertedHide) .
								'.</span>';
							$itemsConvertedString .= '
							<span style="text-align: center;">
								<span style="cursor:pointer; border: 1px dashed; border-width: 0 0 1px 0" 
									onclick = "expandElements(\'' . $errCode . '\');"
									class="vk_export_notify__error_normal__more_elements" id="vk_export_notify__error_normal__more_elements--' . $errCode . '">' .
								Loc::getMessage('SALE_VK_ERRORS__MORE_ELEMENTS', array('#C1' => $itemsCount - self::MAX_SHOWING_ERRORS_ITEMS)) .
								'</span>
								
								<span style="cursor:pointer; border: 1px dashed; border-width: 0 0 1px 0; display:none;" 
									onclick = "narrowElement(\'' . $errCode . '\');"
									class="vk_export_notify__error_normal__less_elements" id="vk_export_notify__error_normal__less_elements--' . $errCode . '">' .
								Loc::getMessage('SALE_VK_ERRORS__LESS_ELEMENTS') .
								'</span>
							</span>
						';
						}
					}

//					create common string for one error
					$errorsConvertedStrings[$errCode] =
						'<p style="font-weight: normal !important;">' .
						$err['MESSAGE'] . $itemsConvertedString .
						'</p>';
				}


//				for critical errors show message just once - clean after get message
				if ($errCritical && $flagCritical)
				{
					$criticalErrors = $this->getExistingErrors($errCode);
					foreach ($criticalErrors as $error)
						$resDelete = LogTable::delete($error['ID']);
				}
			}
			
			$errorsConvertedString = implode('', $errorsConvertedStrings);

//			add intro before errors
			if (strlen($errorsConvertedString) > 0)
			{
				$result = $flagCritical ? Loc::getMessage("SALE_VK_ERRORS__INTRO_CRITICAL") : Loc::getMessage("SALE_VK_ERRORS__INTRO_NORMAL");
				$result .= ':<br>' . $errorsConvertedString;
			}

//			check if need download log file
			if (strlen($result) > 0)
			{
				$vk = Vk::getInstance();
				if ($richLog = $vk->getRichLog($this->exportId))
				{
					$href = '/bitrix/admin/sale_vk_export_edit.php' . '?ID=' . $this->exportId . '&lang=' . LANG . '&download_log=Y';
					$result .= '<br><p>' . Loc::getMessage("SALE_VK_ERRORS__LOG_TITLE") . ': <a href="' . $href . '">' . Loc::getMessage("SALE_VK_ERRORS__LOG_DOWNLOAD") . '</a>.</p>';
				}
			}
			
			return $result;
		}

//		not errors
		else
		{
			return '';
		}
	}
	
	
	/**
	 * Return string with js-script for expand not critical errors
	 */
	public function getErrorExpandScript()
	{
		return "
			<script>
				function expandElements(code) {
					BX.adjust(BX('vk_export_notify__error_normal__less_elements--'+code), {style:{display:'inline'}});
					BX.adjust(BX('vk_export_notify__error_normal__more_elements--'+code), {style:{display:'none'}});
					BX.adjust(BX('vk_export_notify__error_normal__hide_elements--'+code), {style:{display:'inline'}});
				}
				
				function narrowElement(code) {
					BX.adjust(BX('vk_export_notify__error_normal__more_elements--'+code), {style:{display:'inline'}});
					BX.adjust(BX('vk_export_notify__error_normal__less_elements--'+code), {style:{display:'none'}});
					BX.adjust(BX('vk_export_notify__error_normal__hide_elements--'+code), {style:{display:'none'}});
				}
			</script>
		";
	}
	
	
	public static function createLogFileContent($exportId)
	{
		$resExistLogs = LogTable::getList(array(
			'select' => array("ITEM_ID", "ERROR_PARAMS", "TIME"),
			'filter' => array(
				'=EXPORT_ID' => $exportId,
				'=ERROR_CODE' => "LOG",
			),
			'order' => array('TIME' => 'ASC', 'ID' => 'ASC'),
		));
		
		$log = "";
		while ($record = $resExistLogs->fetch())
		{
			$log .= $record["TIME"] . ' - ' . $record["ITEM_ID"] . ".";
			if (!empty($record["ERROR_PARAMS"]))
				$log .= " Params: " . print_r($record["ERROR_PARAMS"], true);
			$log .= "\r\n";
		}
		
		return $log;
	}
	
	
	/**
	 * Format error items to string (wrapper). If unknown type of error - just return item ID
	 *
	 * @param $item
	 * @param $errCode
	 * @return string
	 */
	private function createItemErrorString($item, $errCode)
	{
		$errorsDescriptions = $this->getErrorsDescriptions();
		
		if (array_key_exists($errCode, $errorsDescriptions) && array_key_exists('ITEMS_TYPE', $errorsDescriptions[$errCode]))
			return self::createItemErrorStringByType($item, $errorsDescriptions[$errCode]['ITEMS_TYPE']);

//		if error have format without items - just item ID - for unknown errors
		else
			return $item;
	}
	
	
	/**
	 * Format error items to string
	 *
	 * @param $item
	 * @param $type
	 * @return string
	 */
	private static function createItemErrorStringByType($item, $type)
	{
		switch ($type)
		{
			case 'PRODUCT':
//				get iblock id fore create link to edit
				$resItem = \CIBlockElement::GetList(
					array(),
					array("ID" => $item),
					false,
					false,
					array('IBLOCK_ID', 'NAME')
				);
				$resItem = $resItem->Fetch();
				$href = "/bitrix/admin/cat_product_edit.php";
				
				break;
			
			case 'ALBUM':
//				get iblock id fore create link to edit
				$resItem = \CIBlockSection::GetList(
					array(),
					array("ID" => $item),
					false,
					array('IBLOCK_ID', 'NAME')
				);
				$resItem = $resItem->Fetch();
				$href = "/bitrix/admin/cat_section_edit.php";
				
				break;
			
			case 'METHODS':
//				do nothing - just print method name
				break;
			
			case 'NONE':
//				not need items string
				return '';
				break;
			
			default:
				$href = '';
				break;
		}

//		create RESULTING string
		if (isset($resItem) && $resItem)
		{
			$query = array(
				"IBLOCK_ID" => $resItem["IBLOCK_ID"],
				"type" => "catalog",
				"ID" => $item,
				"lang" => LANGUAGE_ID,
			);
			
			return '<a href="' . $href . '?' . http_build_query($query) . '">' . $resItem['NAME'] . '</a>';
		}

//		if we have't IBLOCK ID - return only item ID
		else
		{
			return $item;
		}
	}
	
	
	/**
	 * Preset known errors and descriptions.
	 * Unknown type of errors will be added in OTHER.
	 * Number codes - errors from api-methods, other errors catch in runtime.
	 *
	 *
	 * @param null $key
	 * @return array|mixed|null
	 */
	private function getErrorsDescriptions($key = NULL)
	{
		$errorsDescriptions = array(
//			"LOG" using just for write to log some data - f.e. to debug
			"LOG" => array(
				"MESSAGE" => 'log',
				"CODE" => "LOG",
				'IGNORE' => true,
			),
			"100" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__CODE_100"),
				"CODE" => "100",
				"ITEMS_TYPE" => 'METHODS',
//				'IGNORE' => true,
				"CRITICAL" => true,
			),
			"1" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__UNKNOWN_VK_ERROR") . ' ' . Loc::getMessage('SALE_VK_ERROR__CODE_1'),
				"CODE" => "1",
				"CRITICAL" => true,
				"ITEMS_TYPE" => 'NONE',
			),
			"7" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__GROUP_NOT_ACCESS_ERROR"),
				"CODE" => "7",
				"CRITICAL" => true,
				"ITEMS_TYPE" => 'NONE',
			),
			"10" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__UNKNOWN_VK_ERROR") . ' ' . Loc::getMessage('SALE_VK_ERROR__CODE_10'),
				"CODE" => "10",
				"CRITICAL" => true,
				"ITEMS_TYPE" => 'NONE',
			),
			"13" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__EXECUTE_ERROR"),
				"CODE" => "13",
				"CRITICAL" => true,
				"ITEMS_TYPE" => 'NONE',
			),
			"15" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__ACCESS_DENIED"),
				"CODE" => "15",
				"CRITICAL" => true,
				"ITEMS_TYPE" => 'NONE',
			),
			"VK_NOT_AVAILABLE" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__VK_NOT_AVAILABLE", array(
					'#A1' => '<a href="http://vk.com">http://vk.com</a>',
				)),
				"CRITICAL" => true,
				"CODE" => "VK_NOT_AVAILABLE",
				"ITEMS_TYPE" => 'NONE',
			),
			"PRODUCT_SHORT_NAME" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__PRODUCT_SHORT_NAME"),
				"CRITICAL" => false,
				"CODE" => "PRODUCT_SHORT_NAME",
				"ITEMS_TYPE" => 'PRODUCT',
			),
			"PRODUCT_SHORT_DESCRIPTION" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__PRODUCT_SHORT_DESCRIPTION"),
				"CRITICAL" => false,
				"CODE" => "PRODUCT_SHORT_DESCRIPTION",
				"ITEMS_TYPE" => 'PRODUCT',
			),
			"PRODUCT_LONG_NAME" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__PRODUCT_LONG_NAME"),
				"CRITICAL" => false,
				"CODE" => "PRODUCT_LONG_NAME",
				"ITEMS_TYPE" => 'PRODUCT',
			),
			"ALBUM_LONG_TITLE" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__ALBUM_LONG_TITLE"),
				"CRITICAL" => false,
				"CODE" => "ALBUM_LONG_TITLE",
				"ITEMS_TYPE" => 'ALBUM',
			),
			"ALBUM_EMPTY" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__ALBUM_EMPTY"),
				"CRITICAL" => false,
				"CODE" => "ALBUM_EMPTY",
				"ITEMS_TYPE" => 'ALBUM',
			),
			"PRODUCT_EMPTY_PRICE" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__PRODUCT_EMPTY_PRICE"),
				"CRITICAL" => false,
				"CODE" => "PRODUCT_EMPTY_PRICE",
				"ITEMS_TYPE" => 'PRODUCT',
			),
			"5" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__WRONG_ACCESS_TOKEN", array(
					'#A1' => '<a href="/bitrix/admin/sale_vk_export_edit.php?ID=' . $this->exportId . '&lang=' . LANG . '&tabControl_active_tab=vk_settings">',
					'#A2' => '</a>',
				)),
				"CRITICAL" => true,
				"CODE" => "5",
				"ITEMS_TYPE" => 'NONE',
			),
			"WRONG_ACCESS_TOKEN" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__WRONG_ACCESS_TOKEN"),
				"CRITICAL" => true,
				"CODE" => "5",
				"ITEMS_TYPE" => 'NONE',
			),
			"CLIENT_SECRET_IS_INCORRECT" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__CLIENT_SECRET_IS_INCORRECT"),
				"CRITICAL" => true,
				"CODE" => "CLIENT_SECRET_IS_INCORRECT",
				"ITEMS_TYPE" => 'NONE',
			),
			"205" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__ACCESS_DENIED"),
				"CRITICAL" => true,
				"CODE" => "205",
				"ITEMS_TYPE" => 'NONE',
			),
//			todo: maybe we can recreate elements if 1402 and 1403 errors
			"1402" => array(
				'IGNORE' => true,
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__SECTION_NOT_FOUND"),
				"CRITICAL" => false,
				"CODE" => "1403",
				"ITEMS_TYPE" => 'NONE',
			),
			"1403" => array(
				'IGNORE' => true,
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__PRODUCTS_NOT_FOUND"),
				"CRITICAL" => false,
				"CODE" => "1403",
				"ITEMS_TYPE" => 'NONE',
			),
			'1404' => array(
				'IGNORE' => true,
				'MESSAGE' => 'Product already in album',
				'CODE' => '1404',
				"ITEMS_TYPE" => 'NONE',
			),
			"1405" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__PRODUCTS_LIMIT_EXCEED"),
				"CRITICAL" => true,
				"CODE" => "1405",
				"ITEMS_TYPE" => 'NONE',
			),
			"1406" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__PRODUCTS_IN_ALBUM_LIMIT_EXCEED"),
				"CRITICAL" => false,
				"CODE" => "1406",
				"ITEMS_TYPE" => 'NONE',
			),
			"1407" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERROR__ALBUMS_LIMIT_EXCEED"),
				"CRITICAL" => false,
				"CODE" => "1407",
				"ITEMS_TYPE" => 'NONE',
			),
			"PRODUCT_WRONG_PHOTOS" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERRORS__PRODUCT_WRONG_PHOTOS"),
				"CRITICAL" => false,
				"CODE" => "PRODUCT_WRONG_PHOTOS",
				"ITEMS_TYPE" => 'PRODUCT',
			),
			"PRODUCT_PHOTOS_RESIZE_UP" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERRORS__PRODUCT_PHOTOS_RESIZE_UP"),
				"CRITICAL" => false,
				"CODE" => "PRODUCT_PHOTOS_RESIZE_UP",
				"ITEMS_TYPE" => 'PRODUCT',
			),
			"PRODUCT_PHOTOS_RESIZE_DOWN" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERRORS__PRODUCT_PHOTOS_RESIZE_DOWN"),
				"CRITICAL" => false,
				"CODE" => "PRODUCT_PHOTOS_RESIZE_DOWN",
				"ITEMS_TYPE" => 'PRODUCT',
			),
			"ALBUM_EMPTY_PHOTOS" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERRORS__ALBUM_EMPTY_PHOTOS"),
				"CRITICAL" => false,
				"CODE" => "ALBUM_EMPTY_PHOTOS",
				"ITEMS_TYPE" => 'ALBUM',
			),
			"ALBUM_PHOTOS_RESIZE_UP" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERRORS__ALBUM_PHOTOS_RESIZE_UP"),
				"CRITICAL" => false,
				"CODE" => "ALBUM_PHOTOS_RESIZE_UP",
				"ITEMS_TYPE" => 'ALBUM',
			),
			"ALBUM_PHOTOS_RESIZE_DOWN" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERRORS__ALBUM_PHOTOS_RESIZE_DOWN"),
				"CRITICAL" => false,
				"CODE" => "ALBUM_PHOTOS_RESIZE_DOWN",
				"ITEMS_TYPE" => 'ALBUM',
			),
			"ALBUM_PHOTOS_RESIZE_UP_CROP" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERRORS__ALBUM_PHOTOS_RESIZE_CROP"),
				"CRITICAL" => false,
				"CODE" => "ALBUM_PHOTOS_RESIZE_UP_CROP",
				"ITEMS_TYPE" => 'ALBUM',
			),
			"ALBUM_PHOTOS_RESIZE_DOWN_CROP" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERRORS__ALBUM_PHOTOS_RESIZE_CROP"),
				"CRITICAL" => false,
				"CODE" => "ALBUM_PHOTOS_RESIZE_DOWN_CROP",
				"ITEMS_TYPE" => 'ALBUM',
			),
			
			"EMPTY_SECTIONS_LIST" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERRORS__EMPTY_SECTIONS_LIST"),
				"CRITICAL" => true,
				"CODE" => "EMPTY_SECTIONS_LIST",
				"ITEMS_TYPE" => 'NONE',
			),
			"EMPTY_SECTION_PRODUCTS" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERRORS__EMPTY_SECTION_PRODUCTS"),
				"CRITICAL" => false,
				"CODE" => "EMPTY_SECTION_PRODUCTS",
				"ITEMS_TYPE" => 'NONE',
			),
			"TOO_MANY_SECTIONS_TO_EXPORT" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERRORS__TOO_MANY_SECTIONS_TO_EXPORT") . ' ' . Vk::MAX_EXECUTION_ITEMS,
				"CRITICAL" => false,
				"CODE" => "TOO_MANY_SECTIONS_TO_EXPORT",
				"ITEMS_TYPE" => 'NONE',
			),
			"TOO_MANY_PRODUCTS_TO_EXPORT" => array(
				"MESSAGE" => Loc::getMessage("SALE_VK_ERRORS__TOO_MANY_PRODUCTS_TO_EXPORT") . ' ' . Vk::MAX_EXECUTION_ITEMS,
				"CRITICAL" => false,
				"CODE" => "TOO_MANY_PRODUCTS_TO_EXPORT",
				"ITEMS_TYPE" => 'NONE',
			),
		);

//		if set key - return one element, else return all array
		if ($key)
		{
			return array_key_exists($key, $errorsDescriptions) ? $errorsDescriptions[$key] : NULL;
		}
		else
		{
			return $errorsDescriptions;
		}
	}
}