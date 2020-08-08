<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Preset;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\Loc;
use Bitrix\Fileman\Block\Editor as BlockEditor;

use Bitrix\Sender\Integration;

Loc::loadMessages(__FILE__);

/**
 * Class TemplateBase
 * @package Bitrix\Sender\Preset
 * @deprecated
 * @internal
 */
class TemplateBase
{
	const LOCAL_DIR_TMPL = '/modules/sender/preset/template/';
	const LOCAL_DIR_IMG = '/images/sender/preset/template/';

	/**
	 * Return base templates.
	 *
	 * @param string|null $templateType Template type.
	 * @param string|null $templateId Template ID.
	 * @return array
	 */
	public static function onPresetTemplateList($templateType = null, $templateId = null)
	{
		$resultList = array();

		$templateList = static::getListName();


		foreach ($templateList as $templateName)
		{
			if($templateName !== $templateId && $templateId)
			{
				continue;
			}

			$template = static::getById($templateName);
			if($template)
			{
				$template['VERSION'] = 1;
				if($template['TYPE'] === $templateType || !$templateType)
				{
					$resultList[] = $template;
				}
			}
		}

		return $resultList;
	}

	/**
	 * Return site templates.
	 *
	 * @param string|null $templateType Template type.
	 * @param string|null $templateId Template ID.
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function onPresetTemplateListSite($templateType = null, $templateId = null)
	{
		$resultList = array();

		if (Integration\Bitrix24\Service::isPortal())
		{
			return $resultList;
		}

		if($templateType && $templateType !== 'SITE_TMPL')
		{
			return $resultList;
		}

		$by = 'SORT';
		$order = 'ASC';
		$filter = array('TYPE' => 'mail');
		if($templateId)
		{
			$filter['ID'] = $templateId;
		}

		$templateDb = \CSiteTemplate::GetList(array($by => $order), $filter, array("ID", "NAME", "CONTENT", "SCREENSHOT"));
		Loader::includeModule('fileman');
		$replaceAttr = BlockEditor::BLOCK_PLACE_ATTR . '="' . BlockEditor::BLOCK_PLACE_ATTR_DEF_VALUE . '"';
		$replaceText = '<div style="padding: 20px; border: 2px dashed #868686;"><span style="color: #868686; font-size: 20px;">' . Loc::getMessage('PRESET_TEMPLATE_LIST_SITE_DEF_TEXT') . '</span></div>';
		while($template = $templateDb->Fetch())
		{
			if($template['ID'] == 'mail_user')
			{
				continue;
			}

			$replaceTo = $replaceText;
			$html = $template['CONTENT'];

			$html = preg_replace('/<\?[\w\w].*?B_PROLOG_INCLUDED[^>].*?\?>/is', '', $html);
			if(mb_stripos($html, $replaceAttr) === false)
			{
				$replaceTo = '<div id="bxStylistBody" ' . $replaceAttr . '>' . $replaceText . '</div>';
			}

			$html = str_replace(
				'#WORK_AREA#',
				$replaceTo,
				$html
			);

			$resultList[] = array(
				'TYPE' => 'SITE_TMPL',
				'ID' => $template['ID'],
				'NAME' => $template['NAME'],
				'HTML' => $html
			);
		}

		return $resultList;
	}

	/**
	 * @return array
	 */
	public static function getListName()
	{
		$templateNameList = array(
			'empty',
			'1column1',
			'1column2',
			'2column1',
			'2column2',
			'2column3',
			'2column4',
			'2column5',
			'2column6',
			'2column7',
			'dynamic1',
			'dynamic2',
		);

		return $templateNameList;
	}

	/**
	 * @param string $templateName
	 * @return array|null
	 */
	public static function getById($templateName)
	{
		$result = null;

		$localPathOfIcon = static::LOCAL_DIR_IMG . bx_basename($templateName) . '.png';
		$fullPathOfIcon = Loader::getLocal($localPathOfIcon);

		$fullPathOfFile = Loader::getLocal(static::LOCAL_DIR_TMPL . bx_basename($templateName) . '.php');
		if ($fullPathOfFile && File::isFileExists($fullPathOfFile))
		{
			$fileContent = File::getFileContents($fullPathOfFile);
		}
		else
		{
			$fileContent = '';
		}

		if (!empty($fileContent) || $templateName == 'empty')
		{
			Loader::includeModule('fileman');
			if(BlockEditor::isContentSupported($fileContent))
			{
				$fileContent = static::replaceTemplateByDefaultData($fileContent);
			}

			$fileContent = str_replace(
				array('%TEXT_UNSUB_TEXT%', '%TEXT_UNSUB_LINK%'),
				array(
					Loc::getMessage('PRESET_MAILBLOCK_unsub_TEXT_UNSUB_TEXT'),
					Loc::getMessage('PRESET_MAILBLOCK_unsub_TEXT_UNSUB_LINK')
				),
				$fileContent
			);

			$result = array(
				'TYPE' => 'BASE',
				'ID' => $templateName,
				'NAME' => Loc::getMessage('PRESET_TEMPLATE_' . $templateName),
				'HTML' => $fileContent,
			);
		}

		return $result;
	}

	/**
	 * @param string $template
	 * @return string
	 */
	protected static function replaceTemplateByDefaultData($template)
	{
		$phone = '8 495 212-85-06';
		$phonePath = Application::getDocumentRoot() . '/include/telephone.php';
		$logoHeader = '/include/logo.png';
		$logoFooter = '/include/logo_mobile.png';
		if(!File::isFileExists(Application::getDocumentRoot() . $logoHeader))
		{
			$logoHeader = '/bitrix/images/sender/preset/blocked1/logo.png';
		}
		if(!File::isFileExists(Application::getDocumentRoot() . $logoFooter))
		{
			$logoFooter = '/bitrix/images/sender/preset/blocked1/logo_m.png';;
		}

		if(File::isFileExists($phonePath))
		{
			$phone = File::getFileContents($phonePath);
		}

		$themeContent = File::getFileContents(Loader::getLocal(static::LOCAL_DIR_TMPL . 'theme.php'));
		return str_replace(
			array(
				'%TEMPLATE_CONTENT%', '%LOGO_PATH_HEADER%', '%LOGO_PATH_FOOTER%', '%PHONE%',
				'%UNSUB_LINK%', '%MENU_CONTACTS%',
				'%MENU_HOWTO%', '%MENU_DELIVERY%',
				'%MENU_ABOUT%', '%MENU_GUARANTEE%',
				'%SCHEDULE_NAME%', '%SCHEDULE_DETAIL%',

				'%BUTTON%', '%HEADER%',
				'%TEXT1%', '%TEXT2%',
				'%TEXT3%', '%TEXT4%',
				'%TEXT5%', '%TEXT6%',
			),
			array(
				$template, $logoHeader, $logoFooter, $phone,
				Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_UNSUB_LINK'), Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_MENU_CONTACTS'),
				Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_MENU_HOWTO'), Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_MENU_DELIVERY'),
				Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_MENU_ABOUT'), Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_MENU_GUARANTEE'),
				Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_SCHEDULE_NAME'), Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_SCHEDULE_DETAIL'),

				Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_BUTTON'), Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_HEADER'),
				Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_TEXT1'), Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_TEXT2'),
				Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_TEXT3'), Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_TEXT4'),
				Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_TEXT5'), Loc::getMessage('PRESET_TEMPLATE_LIST_BLANK_TEXT6'),
			),
			$themeContent
		);
	}

	/**
	 * @param $templateName
	 * @param $html
	 * @return bool|int
	 */
	public static function update($templateName, $html)
	{
		$result = false;
		$fullPathOfFile = Loader::getLocal(static::LOCAL_DIR_TMPL . bx_basename($templateName) . '.php');
		if ($fullPathOfFile)
			$result = File::putFileContents($fullPathOfFile, $html);

		return $result;
	}
}