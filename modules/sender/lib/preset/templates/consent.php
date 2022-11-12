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
use Bitrix\UI;

Loc::loadMessages(__FILE__);

/**
 * Class Mail
 * @package Bitrix\Sender\Preset\Templates
 */
class Consent
{
	const LOCAL_DIR = '/modules/sender/preset/consent/';

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
				'%SUBJECT%',
				'%CONSENT_BODY%',
				'%APPROVE%',
				'%REJECT%',
				'%CONSENT_FOOTER%',
				'%FONTS_PROXY_DOMAIN%',
				'#SENDER_CONSENT_APPLY#',
				'#SENDER_CONSENT_REJECT#',
			),
			array(
				Loc::getMessage('SENDER_PRESET_TEMPLATE_CONSENT_SUBJECT'),
				$replace['CONSENT_BODY'],
				Loc::getMessage('SENDER_PRESET_TEMPLATE_CONSENT_APPROVE'),
				Loc::getMessage('SENDER_PRESET_TEMPLATE_CONSENT_REJECT'),
                $replace['CONSENT_FOOTER'],
				UI\Fonts\Proxy::resolveDomain(),
                $replace['APPLY_URL'],
                $replace['REJECT_URL'],
			),
			$content
		);

		return Texts::replace($content);
	}

	public static function getApproveBtnText($agreement)
	{
		return $agreement && $agreement->getLabelText()
			? $agreement->getLabelText() : Loc::getMessage('SENDER_PRESET_TEMPLATE_CONSENT_APPROVE');
	}

	public static function getRejectnBtnText()
	{
		return Loc::getMessage('SENDER_PRESET_TEMPLATE_CONSENT_REJECT');
	}

	/**
	 * Get template html.
	 *
	 * @return string|null
	 */
	public static function getTemplateHtml()
	{
		$fileContent = self::getFileContent('template');

		if (!$fileContent)
		{
			return null;
		}

		return $fileContent;
	}
}