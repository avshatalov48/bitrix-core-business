<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Preset\Templates;

use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Message;
use Bitrix\Sender\Templates\Type;

Loc::loadMessages(__FILE__);

/**
 * Class Sms
 * @package Bitrix\Sender\Preset\Templates
 */
class Sms
{
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
		if($messageCode && !in_array($messageCode, self::getProvidedMessageCodes()))
		{
			return array();
		}

		return self::getTemplates($templateId, $messageCode = null);
	}

	private static function getProvidedMessageCodes()
	{
		return array(
			Message\iBase::CODE_CALL,
			Message\iBase::CODE_SMS,
			Message\iBase::CODE_IM,
		);
	}

	private static function getTemplates($templateId = null, $messageCode = null)
	{
		$messageCodes = $messageCode ? array($messageCode) : self::getProvidedMessageCodes();

		$result = array();
		foreach ($messageCodes as $messageCode)
		{
			$textMessageCode = $messageCode;
			if ($messageCode === Message\iBase::CODE_CALL)
			{
				$textMessageCode = Message\iBase::CODE_SMS;
			}

			foreach (Texts::getListByType($textMessageCode) as $item)
			{
				$code = mb_strtolower($messageCode."_".$item['CODE']);
				if($templateId && $code !== $templateId)
				{
					continue;
				}

				$result[] = array(
					'ID' => $code,
					'TYPE' => Type::getCode(Type::BASE),
					'MESSAGE_CODE' => array($messageCode),
					'VERSION' => 2,
					'HOT' => $item['HOT'],
					'ICON' => $item['ICON'],

					'NAME' => $item['NAME'],
					'DESC' => $item['DESC'],
					'FIELDS' => array(
						'MESSAGE_TEXT' => array(
							'CODE' => 'MESSAGE_TEXT',
							'VALUE' => $item['TEXT'],
						)
					),
				);
			}
		}

		return $result;
	}
}