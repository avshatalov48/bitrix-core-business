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
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\InputOutputSpec;
use Bitrix\Sender\Message;
use Bitrix\Sender\Templates\Category;
use Bitrix\Sender\Templates\Type;

Loc::loadMessages(__FILE__);

/**
 * Class Rc
 * @package Bitrix\Sender\Preset\Templates
 */
class Toloka
{
	const IMAGE_DIR = '/images/sender/preset/events/';
	const LOCAL_DIR = '/modules/sender/preset/template_toloka/';

	private static function getFileContent($fileName)
	{
		$pathTemplate = Loader::getLocal(self::LOCAL_DIR . bx_basename($fileName) . '.php');
		$pathCSS = Loader::getLocal(self::LOCAL_DIR . bx_basename($fileName) . '.css');
		$pathJS = Loader::getLocal(self::LOCAL_DIR . bx_basename($fileName) . '.js');

		if (
			self::fileExists($pathTemplate) &&
			self::fileExists($pathCSS) &&
			self::fileExists($pathJS)
		)
		{
			return [
				'template' => File::getFileContents($pathTemplate),
				'css' => File::getFileContents($pathCSS),
				'js' => File::getFileContents($pathJS),
			];
		}

		return [];
	}
	private static function fileExists($path):bool
	{
		if($path && File::isFileExists($path))
		{
			return true;
		}

		return false;
	}
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
		if($messageCode && $messageCode !== Message\iBase::CODE_TOLOKA)
		{
			return array();
		}

		return self::getTemplates($templateId, $messageCode);
	}

	private static function getListByType()
	{
		$list = [
			[
				'CODE' => 'video',
				'SEGMENT_CODES' => [],
				'NAME' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_TITLE_VIDEO'),
				'HINT' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_HINT_VIDEO'),
				'FIELDS' => [
					'INSTRUCTION' =>  [
						'CODE' => 'INSTRUCTION',
						'VALUE' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_VIDEO_INSTRUCTION')
					],
					'DESCRIPTION' =>  [
						'CODE' => 'DESCRIPTION',
						'VALUE' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_VIDEO_DESCRIPTION')
					],
					'TASKS' =>  [
						'CODE' => 'TASKS',
						'VALUE' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_VIDEO_TASKS')
					],
					'INPUT_VALUE' => [
						'CODE' => 'INPUT_VALUE',
						'VALUE' => ['video' => InputOutputSpec::TYPES['URL']]
					],
					'OUTPUT_VALUE' => [
						'CODE' => 'OUTPUT_VALUE',
						'VALUE' => ['result' => InputOutputSpec::TYPES['STRING']]
					],
					'PRESET' => [
						'CODE' => 'PRESET',
						'VALUE' => self::getFileContent('video')
					]
				]
			],
			[
				'CODE' => 'leaflets',
				'SEGMENT_CODES' => [],
				'NAME' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_TITLE_LEAFLETS'),
				'HINT' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_HINT_LEAFLETS'),
				'FIELDS' => [
					'INSTRUCTION' =>  [
						'CODE' => 'INSTRUCTION',
						'VALUE' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_LEAFLETS_INSTRUCTION')
					],
					'DESCRIPTION' =>  [
						'CODE' => 'DESCRIPTION',
						'VALUE' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_LEAFLETS_DESCRIPTION')
					],
					'TASKS' =>  [
						'CODE' => 'TASKS',
						'VALUE' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_LEAFLETS_TASKS')
					],
					'INPUT_VALUE' => [
						'CODE' => 'INPUT_VALUE',
						'VALUE' => ['url' => InputOutputSpec::TYPES['URL']]
					],
					'OUTPUT_VALUE' => [
						'CODE' => 'OUTPUT_VALUE',
						'VALUE' => ['result' => InputOutputSpec::TYPES['STRING']]
					],
					'PRESET' => [
						'CODE' => 'PRESET',
						'VALUE' => self::getFileContent('video')
					]
				]			],
			[
				'CODE' => 'mystery_shopper',
				'SEGMENT_CODES' => [],
				'NAME' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_TITLE_MYSTERY'),
				'HINT' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_HINT_MYSTERY'),
				'FIELDS' => [
					'INSTRUCTION' =>  [
						'CODE' => 'INSTRUCTION',
						'VALUE' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_MYSTERY_INSTRUCTION')
					],
					'DESCRIPTION' =>  [
						'CODE' => 'DESCRIPTION',
						'VALUE' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_MYSTERY_DESCRIPTION')
					],
					'TASKS' =>  [
						'CODE' => 'TASKS',
						'VALUE' => Loc::getMessage('SENDER_PRESET_TEMPLATE_TOLOKA_MYSTERY_TASKS')
					],
					'INPUT_VALUE' => [
						'CODE' => 'INPUT_VALUE',
						'VALUE' => ['url' => InputOutputSpec::TYPES['URL']]
					],
					'OUTPUT_VALUE' => [
						'CODE' => 'OUTPUT_VALUE',
						'VALUE' => ['result' => InputOutputSpec::TYPES['STRING']]
					],
					'PRESET' => [
						'CODE' => 'PRESET',
						'VALUE' => self::getFileContent('video')
					]
				]			],
		];
		return $list;
	}

	private static function getTemplates($templateId = null, $messageCode = null)
	{
		$messageCodes = $messageCode ? array($messageCode) : [];

		$result = [];
		foreach (self::getListByType() as $item)
		{
			$originalCode = strtolower($item['CODE']);
			$code = 'toloka_' . strtolower($item['CODE']);
			if($templateId && $code !== $templateId)
			{
				continue;
			}

			$result[] = array(
				'ID' => $code,
				'TYPE' => Type::getCode(Type::BASE),
				'CATEGORY' => Category::getCode(Category::CASES),
				'MESSAGE_CODE' => $messageCodes,
				'VERSION' => 2,
				'HOT' => $item['HOT'],
				'ICON' => BX_ROOT . self::IMAGE_DIR . "$originalCode.png",
				'NAME' => $item['NAME'],
				'DESC' => $item['DESC'],
				'HINT' => $item['HINT'],
				'FIELDS' => array_merge(
					$item['FIELDS'],
					[
					'TITLE' => [
						'CODE' => 'TITLE',
						'VALUE' => $item['TITLE'],
					],
					'COMMENT' => [
						'CODE' => 'COMMENT',
						'VALUE' => $item['TEXT'],
					],
					'ALWAYS_ADD' => [
						'CODE' => 'ALWAYS_ADD',
						'VALUE' => 'Y',
					],
				]),
				'SEGMENTS' => [],
				'DISPATCH' => $item['DISPATCH'],
			);
		}

		return $result;
	}
}