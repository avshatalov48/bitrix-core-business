<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Preset;

use Bitrix\Main\Entity;
use Bitrix\Main\EventResult;
use Bitrix\Main\Event;
use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class MailBlock
 * @package Bitrix\Sender\Preset
 * @deprecated
 * @internal
 */
class MailBlock
{
	/**
	 * @return array
	 */
	public static function getListByType()
	{
		$resultTemplateList = array();
		$arTemplateList = static::getList();
		foreach($arTemplateList as $template)
			$resultTemplateList[$template['TYPE']][] = $template;

		return $resultTemplateList;
	}

	/**
	 * @return array
	 */
	public static function getList()
	{
		$resultList = array();
		$event = new Event('sender', 'OnPresetMailBlockList');
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() == EventResult::ERROR)
			{
				continue;
			}

			$eventResultParameters = $eventResult->getParameters();

			if (!empty($eventResultParameters))
			{
				$resultList = array_merge($resultList, $eventResultParameters);
			}
		}

		return $resultList;
	}

	/**
	 * @return array
	 */
	public static function getBlockForVisualEditor()
	{
		$arResult = array(
			'items' => array(),
			'groups' => array(),
			'rootDefaultFilename' => ''
		);

		$arGroupExists = array();
		$arBlocksByType = static::getListByType();
		foreach($arBlocksByType as $type => $arBlockList)
		{
			foreach($arBlockList as $blockNum => $arBlock)
			{
				$name = 'mailblock'.str_pad($blockNum+1, 4, '0', STR_PAD_LEFT);
				$key = $arBlock['TYPE'].'/'.$name;
				$arResult['items'][$key] = array(
					'name' => $name,
					'path' => $arBlock['TYPE'],
					'title' => $arBlock['NAME'],
					'thumb' => '',
					'code' => $arBlock['HTML'],
					'description' => empty($arBlock['DESC']) ? '' : $arBlock['DESC'],
					'template' => '',
					'level' => '',
					'parent' => $arBlock['TYPE'],
				);

				if(!in_array($arBlock['TYPE'], $arGroupExists))
				{
					$arResult['groups'][] = array(
						'path' => '',
						'name' => $arBlock['TYPE'],
						'level' => '0',
						'default_name' => 'mailblockgroup' . (count($arGroupExists) + 1)
					);
					$arGroupExists[] = $arBlock['TYPE'];
				}

			} // foreach $arBlockList

		} // foreach $arBlocksByType

		if(isset($arResult['groups'][0]))
			$arResult['rootDefaultFilename'] = $arResult['groups'][0]['default_name'];

		return $arResult;
	}
}