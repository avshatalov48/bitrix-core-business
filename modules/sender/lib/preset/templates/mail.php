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
use Bitrix\Sender\Integration\Bitrix24;

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

	private static function getTemplates($templateId = null)
	{
		$fileTheme = self::getFileContent('theme');
		$fileSimple = self::getFileContent('image_text_button');

		if (!$fileTheme || !$fileSimple)
		{
			return array();
		}

		$fileSocial = self::getFileContent('social');
		$fileSocialRu = self::getFileContent('social_ru');
		$fileSocialEn = '';
		if (Bitrix24\Service::isCloud() && !Bitrix24\Service::isCloudRegionRussian())
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
		foreach (Texts::getListByType(Message\iBase::CODE_MAIL) as $item)
		{
			$code = strtolower("mail_" . $item['CODE']);
			if($templateId && $code !== $templateId)
			{
				continue;
			}

			$textHead = $item['TEXT_HEAD'];
			$textBody = $item['TEXT_BODY'];
			$fileContent = str_replace(
				array(
					'%TEXT%',
					'%IMAGE_PATH%',
					'%BUTTON_TEXT%',

					'%UNSUB_LINK%',
				),
				array(
					"<br><h2>$textHead</h2><br>$textBody<br><br>",
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
				$fileContent
			);
			$fileContent = Texts::replace($fileContent);

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