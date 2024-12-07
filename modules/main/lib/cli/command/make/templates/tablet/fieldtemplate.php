<?php

namespace Bitrix\Main\Cli\Command\Make\Templates\Tablet;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Cli\Helper\Renderer\Template;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use ReflectionClass;

final class FieldTemplate implements Template
{
	private const TYPE_TO_CLASS_MAP = [
		'integer' => IntegerField::class,
		'float' => FloatField::class,
		'boolean' => BooleanField::class,
		'string' => StringField::class,
		'text' => TextField::class,
		'enum' => EnumField::class,
		'date' => DateField::class,
		'datetime' => DatetimeField::class,
	];

	private const TYPE_SUPPORT_LENGTH_VALIDATOR = [
		'string',
		'text',
	];

	private array $usedClasses;

	/**
	 * @param string $columnName
	 * @param string $typeOrm
	 * @param bool $isPrimary
	 * @param bool $isUnique
	 * @param bool $isIncrement
	 * @param bool $isNullable
	 * @param mixed $defaultValue
	 * @param int|null $size is database type size
	 * @param int|null $maxLength is string max length
	 */
	public function __construct(
		private readonly string $columnName,
		private readonly string $typeOrm,
		private readonly bool $isPrimary,
		private readonly bool $isUnique,
		private readonly bool $isIncrement,
		private readonly bool $isNullable,
		private readonly mixed $defaultValue,
		private readonly ?int $size,
		private readonly ?int $maxLength,
	)
	{}

	/**
	 * @return array
	 * @throws \Error is run that method before `getContent`
	 */
	public function getUsedClasses(): array
	{
		return $this->usedClasses;
	}

	public function getContent(): string
	{
		[$className, $fullClassName] = $this->getFieldClassName();
		$this->usedClasses[] = $fullClassName;

		$configureCode = [];

		if ($this->isPrimary)
		{
			$configureCode[] = "->configurePrimary()";
		}
		elseif (!$this->isNullable)
		{
			$configureCode[] = "->configureRequired()";
		}
		else
		{
			$configureCode[] = "->configureNullable()";
		}

		if ($this->isIncrement)
		{
			$configureCode[] = "->configureAutocomplete()";
		}

		if ($this->isUnique)
		{
			$configureCode[] = "->configureUnique()";
		}

		if (isset($this->defaultValue))
		{
			$configureCode[] = "->configureDefaultValue({$this->getDefaultValueCode()})";
		}

		if (isset($this->size))
		{
			$configureCode[] = "->configureSize({$this->size})";
		}

		if (isset($this->maxLength) && in_array($this->typeOrm, self::TYPE_SUPPORT_LENGTH_VALIDATOR))
		{
			$min = $this->isNullable ? 0 : 1;
			$max = $this->maxLength;

			$configureCode[] = "->addValidator(new LengthValidator(min:{$min}, max:{$max}))";
			$this->usedClasses[] = LengthValidator::class;
		}

		if ($this->typeOrm === 'boolean')
		{
			if (in_array($this->defaultValue, ['Y', 'N']))
			{
				$configureCode[] = "->configureValues('N', 'Y')";
			}
		}

		$resultCode = [
			"\t\t\t" . "(new {$className}('{$this->columnName}'))",
			...array_map(
				static fn($line) => "\t\t\t\t" . $line,
				$configureCode,
			),
			"\t\t\t" . ",\n",
		];

		return join("\n", $resultCode);
	}

	private function getFieldClassName(): array
	{
		$className = self::TYPE_TO_CLASS_MAP[$this->typeOrm] ?? null;
		if (empty($className))
		{
			throw new ArgumentException("Invalid type '{$this->typeOrm}' for field '{$this->columnName}'");
		}

		$sortClassName = (new ReflectionClass($className))->getShortName();

		return [$sortClassName, $className];
	}

	private function getDefaultValueCode(): string
	{
		if ($this->typeOrm === 'datetime' || $this->typeOrm === 'date')
		{
			$dateFunctions = [
				'curdate' => true,
				'current_date' => true,
				'current_time' => true,
				'current_timestamp' => true,
				'curtime' => true,
				'localtime' => true,
				'localtimestamp' => true,
				'now' => true
			];

			if (!is_numeric($this->defaultValue))
			{
				$prepareValue = mb_strtolower($this->defaultValue);
				if (
					mb_strlen($prepareValue) > 2
					&& substr_compare($prepareValue, '()', -2, 2, true) === 0
				)
				{
					$prepareValue = mb_substr($prepareValue, 0, -2);
				}

				if (isset($dateFunctions[$prepareValue]))
				{
					if ($this->typeOrm == 'date')
					{
						$this->usedClasses[] = Date::class;

						return "static fn() => Date()";
					}
					else
					{
						$this->usedClasses[] = DateTime::class;

						return "static fn() => DateTime()";
					}
				}
			}
		}

		return "'" . addslashes($this->defaultValue) . "'";
	}
}
