<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Preset\Templates;

use Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Message;
use Bitrix\Sender\Templates\Type;
use Bitrix\Sender\Integration;

Loc::loadMessages(__FILE__);

/**
 * Class Mail
 * @package Bitrix\Sender\Preset\Templates
 */
class Mail
{
	const LOCAL_DIR = '/modules/sender/preset/template_v2/';

	/**
	 * Return base templates.
	 *
	 * @param string|null $templateType Template type.
	 * @param string|null $templateId Template ID.
	 * @param string|null $messageCode Message code.
	 * @return array
	 */
	public static function onPresetTemplateList($templateType = null, $templateId = null, $messageCode = null)
	{
		if($templateType && $templateType !== 'BASE')
		{
			return array();
		}
		if($messageCode && $messageCode !== Message\iBase::CODE_MAIL)
		{
			return array();
		}

		return self::getTemplates($templateId);
	}

	private static function getFileContent($fileName)
	{
		$path = Loader::getLocal(self::LOCAL_DIR . bx_basename($fileName) . '.php');
		if ($path && File::isFileExists($path))
		{
			return File::getFileContents($path);
		}

		return '';
	}

	/**
	 * Get template html.
	 *
	 * @param array $replace Replace data.
	 * @return mixed|null
	 */
	public static function replaceTemplateHtml($content, $replace = [])
	{
		$content = str_replace(
			array(
				'%TEXT%',
				'%IMAGE_PATH%',
				'%BUTTON_TEXT%',

				'%UNSUB_LINK%',
			),
			array(
				$replace['TEXT'],
				'/bitrix/images/sender/preset/template_v2/banner.png?1',
				Loc::getMessage('SENDER_PRESET_TEMPLATE_MAIL_BUTTON_GO'),
				Loc::getMessage(
					'SENDER_PRESET_TEMPLATE_MAIL_UNSUBSCRIBE',
					array(
						'%btn_start%' => '<a style="color: #0054a5;" href="#' . 'UNSUBSCRIBE_LINK' . '#">',
						'%btn_end%' => '</a>',
					)
				)
			),
			$content
		);

		return  Texts::replace($content);
	}

	/**
	 * Get template html.
	 *
	 * @return string|null
	 */
	public static function getTemplateHtml()
	{
		$fileTheme = self::getFileContent('theme');
		$fileSimple = self::getFileContent('image_text_button');

		if (!$fileTheme || !$fileSimple)
		{
			return null;
		}

		$fileSocial = self::getFileContent('social');
		$fileSocialRu = self::getFileContent('social_ru');
		$fileSocialEn = '';
		if (Integration\Bitrix24\Service::isCloud() && !Integration\Bitrix24\Service::isCloudRegionRussian())
		{
			$fileSocialRu = '';
			$fileSocialEn = self::getFileContent('social_en');
		}

		$fileSocial = str_replace(
			['%SOCIAL_RU%', '%SOCIAL_EN%'],
			[$fileSocialRu, $fileSocialEn],
			$fileSocial
		);
		$fileContent = str_replace(
			['%TEMPLATE_CONTENT%', '%TEMPLATE_SOCIAL%'],
			[$fileSimple, $fileSocial],
			$fileTheme
		);

		return $fileContent;
	}

	private static function getTemplates($templateId = null)
	{
		$fileContent = self::getTemplateHtml();
		if (!$fileContent)
		{
			return [];
		}

		$result = [
			[
				'ID' => 'empty',
				'TYPE' => Type::getCode(Type::BASE),
				'MESSAGE_CODE' => Message\iBase::CODE_MAIL,
				'VERSION' => 2,
				'HOT' => false,
				'ICON' => '',

				'NAME' => Loc::getMessage('SENDER_PRESET_TEMPLATE_MAIL_HTML_NAME'),
				'DESC' => Loc::getMessage('SENDER_PRESET_TEMPLATE_MAIL_HTML_DESC'),
				'FIELDS' => array(
					'SUBJECT' => array(
						'CODE' => 'SUBJECT',
						'VALUE' => Loc::getMessage('SENDER_PRESET_TEMPLATE_MAIL_HTML_SUBJECT'),
					),
					'MESSAGE' => array(
						'CODE' => 'MESSAGE',
						'VALUE' => '<html><body></body></html>',
						'ON_DEMAND' => false
					),
				),
			]
		];

		$result = array_merge(
			$result,
			Integration\EventHandler::onTemplateList(Message\iBase::CODE_MAIL)
		);

		foreach (Texts::getListByType(Message\iBase::CODE_MAIL) as $item)
		{
			$code = mb_strtolower("mail_".$item['CODE']);
			if($templateId && $code !== $templateId)
			{
				continue;
			}

			$fileContent = self::replaceTemplateHtml(
				$fileContent,
				[
					'TEXT' => "<br><h2>{$item['TEXT_HEAD']}</h2><br>{$item['TEXT_BODY']}<br><br>"
				]
			);

			$result[] = array(
				'ID' => $code,
				'TYPE' => Type::getCode(Type::BASE),
				'MESSAGE_CODE' => Message\iBase::CODE_MAIL,
				'VERSION' => 2,
				'HOT' => $item['HOT'],
				'ICON' => $item['ICON'],

				'NAME' => $item['NAME'],
				'DESC' => $item['DESC'],
				'FIELDS' => array(
					'SUBJECT' => array(
						'CODE' => 'SUBJECT',
						'VALUE' => $item['SUBJECT'],
					),
					'MESSAGE' => array(
						'CODE' => 'MESSAGE',
						'VALUE' => $fileContent,
						'ON_DEMAND' => true
					),
				),
			);
		}

		return $result;
	}
}