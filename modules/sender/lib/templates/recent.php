<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Templates;

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\ExpressionField;

use Bitrix\Sender\Entity;
use Bitrix\Sender\Security;

Loc::loadMessages(__FILE__);

/**
 * Class Recent
 * @package Bitrix\Sender\Templates
 */
class Recent
{
	private static $maxCount = 4;
	private static $cacheTtl = 3600;

	/**
	 * Return recent templates.
	 *
	 * @param string|null $templateType Template type.
	 * @param string|null $templateId Template ID.
	 * @param string|null $messageCode Message code.
	 * @return array
	 */
	public static function onPresetTemplateList($templateType = null, $templateId = null, $messageCode = null)
	{
		if($templateType)
		{
			return array();
		}

		if (!$templateId)
		{
			$templateId = null;
		}

		return self::getTemplates($messageCode);
	}

	private static function getTemplates($messageCode = null)
	{
		static $result = null;
		if ($result !== null)
		{
			return $result;
		}
		$result = array();

		$filter = array(
			'!=TEMPLATE_TYPE' => null,
			'!=TEMPLATE_ID' => null,
		);
		$userId = Security\User::current()->getId();
		if ($userId)
		{
			$filter['=CREATED_BY'] = $userId;
		}
		if ($messageCode)
		{
			$filter['=MESSAGE_CODE'] = $messageCode;
		}

		$chains = Entity\Letter::getList(array(
			'select' => array('TEMPLATE_TYPE', 'TEMPLATE_ID'),
			'filter' => $filter,
			'runtime' => array(new ExpressionField('MAX_ID', 'MAX(%s)', 'ID')),
			'limit' => self::$maxCount + 1,
			'cache' => array('ttl' => self::$cacheTtl),
			'group' => array('TEMPLATE_TYPE', 'TEMPLATE_ID'),
			'order' => array('MAX_ID' => 'DESC'),
		));
		foreach ($chains as $chain)
		{
			$template = Selector::create()
				->withTypeId($chain['TEMPLATE_TYPE'])
				->withId($chain['TEMPLATE_ID'])
				->get();

			if (!$template)
			{
				continue;
			}

			if ($template['TYPE'] === Type::getCode(Type::BASE) && $template['ID'] === 'empty')
			{
				continue;
			}

			$template['CATEGORY'] = Category::getCode(Category::RECENT);
			$result[] = $template;

			if (count($result) >= self::$maxCount)
			{
				break;
			}
		}

		return $result;
	}
}