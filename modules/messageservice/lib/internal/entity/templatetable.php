<?php
namespace Bitrix\MessageService\Internal\Entity;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Data\Internal\MergeTrait;

class TemplateTable extends DataManager
{
	use MergeTrait;
	use DeleteByFilterTrait;

	/**
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_messageservice_template';
	}

	/**
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' =>
				(new IntegerField('ID'))
					->configurePrimary()
					->configureAutocomplete()
			,
			'NAME' =>
				(new StringField('NAME'))
					->configureRequired()
					->configureSize(500)
					->addValidator(new LengthValidator(1, 500))
			,
			'TITLE' =>
				(new StringField('TITLE'))
					->configureRequired()
					->configureSize(500)
					->addValidator(new LengthValidator(1, 500))
			,
			'DATE_CREATE' =>
				(new DatetimeField('DATE_CREATE'))
					->configureDefaultValue(function()
					{
						return new DateTime();
					})
			,
			'ACTIVE' =>
				(new StringField('ACTIVE'))
					->configureRequired()
					->configureSize(1)
					->addValidator(new LengthValidator(1, 1))
					->configureDefaultValue('Y')
			,
		];
	}

	public static function refreshTemplates(array $templates = []): bool
	{
		$names = [];
		$titles = [];
		$outdatedTemplates = [];
		$allTemplates = self::getList()->fetchAll();
		foreach ($templates as $template)
		{
			$names[$template['NAME']] = $template['NAME'];
			$titles[$template['NAME']] = $template['TITLE'];
		}

		foreach ($allTemplates as $template)
		{
			if (!in_array($template['NAME'], $names, true))
			{
				$outdatedTemplates[] = $template['ID'];
			}

			unset($names[$template['NAME']]);
		}

		if (count($outdatedTemplates))
		{
			self::updateMulti(
				$outdatedTemplates,
				['ACTIVE' => 'N'],
				true
			);
		}

		if (count($names))
		{
			$newTemplates = [];
			foreach ($names as $name)
			{
				$newTemplates[] = [
					'NAME' => $name,
					'TITLE' => $titles[$name] ?? $name,
				];
			}
			self::addMulti($newTemplates, true);
		}

		\Bitrix\MessageService\Providers\Edna\WhatsApp\Utils::cleanTemplatesCache();

		return true;
	}
}
