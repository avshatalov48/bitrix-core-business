<?php

namespace Bitrix\Seo\LeadAds;

/**
 * Class Field.
 * Metadata of question field in form.
 *
 * @package Bitrix\Seo\LeadAds
 */
class Field
{
	public const TYPE_INPUT = 'input';
	public const TYPE_TEXT_AREA = 'textarea';
	public const TYPE_RADIO = 'radio';
	public const TYPE_CHECKBOX = 'checkbox';
	public const TYPE_SELECT = 'select';
	public const TYPE_DATE_TIME = 'date';
	public const TYPE_CONDITION_QUESTION = 'condition';

	//This constants represent pre-fill forms types:
	//personal data:
	public const TYPE_NAME = 'NAME';
	public const TYPE_LAST_NAME = 'LAST_NAME';
	public const TYPE_FULL_NAME = 'FULL_NAME';
	public const TYPE_PATRONYMIC_NAME = 'PATRONYMIC_NAME';
	public const TYPE_GENDER = 'GENDER';
	public const TYPE_AGE = 'AGE';
	public const TYPE_BIRTHDAY = 'BIRTHDAY';

	//contact data:
	public const TYPE_PHONE = 'PHONE';
	public const TYPE_EMAIL = 'EMAIL';
	public const TYPE_LOCATION_FULL = 'LOCATION'; //Country, state, city
	public const TYPE_LOCATION_COUNTRY = 'COUNTRY';
	public const TYPE_LOCATION_STATE = 'STATE';
	public const TYPE_LOCATION_CITY = 'CITY';
	public const TYPE_LOCATION_STREET_ADDRESS = 'ADDRESS';
	public const TYPE_LOCATION_ZIP = 'ZIP'; //ZIP-CODE: https://en.wikipedia.org/wiki/ZIP_Code

	//demographic data
	public const TYPE_MILITARY_STATUS = 'MILITARY_STATUS';
	public const TYPE_MARITIAL_STATUS = 'MARITIAL_STATUS';
	public const TYPE_RELATIONSHIP_STATUS = 'RELATIONSHIP_STATUS';

	//job data
	public const TYPE_COMPANY_NAME = 'COMPANY_NAME';
	public const TYPE_JOB_TITLE = 'JOB_TITLE';
	public const TYPE_WORK_EMAIL = 'WORK_EMAIL';
	public const TYPE_WORK_PHONE = 'WORK_PHONE';


	// National Ids Types
	public const TYPE_CPF = 'CPF'; // https://brasil-russia.ru/cpf/
	public const TYPE_DNI_ARGENTINA = 'DNI_AR'; //
	public const TYPE_DNI_PERU = 'DNI_PE';
	public const TYPE_RUT = 'RUT';
	public const TYPE_CC = 'CC';
	public const TYPE_CI = 'CI';


	/**
	 * @return string[]
	 */
	public static function getTypes(): array
	{
		static $list;
		return $list = $list ?? (new \ReflectionClass(static::class))->getConstants();
	}


	/**@var string*/
	private $type;

	/**@var string|null $name*/
	private $name;

	/**@var string|null*/
	private $label;

	/**@var string|null*/
	private $key;

	/**@var array<string,mixed>[] */
	private $options = [];

	/**
	 * Convert to array.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		$result = [];

		foreach ($this as $key => $value)
		{
			if (isset($value))
			{
				$result[$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * Field constructor.
	 *
	 * @param string $type Type.
	 * @param string|null $label Label.
	 * @param string|null $key Key.
	 * @param array<string,string>[] $options
	 */
	public function __construct(
		string $type = self::TYPE_INPUT,
		?string $name = null,
		?string $label = null,
		?string $key = null,
		array $options = []
	)
	{
		$this->type = $type;
		$this->name = $name;
		$this->label = $label;
		$this->key = $key;
		$this->setOptions($options);
	}

	/**
	 * Add option.
	 *
	 * @param string $key Key.
	 * @param string $label Label.
	 * @return $this
	 */
	public function addOption(string $key, string $label): Field
	{
		$this->options[] = [
			'key' => $key,
			'label' => $label
		];

		return $this;
	}

	/**
	 * Set options.
	 *
	 * @param array<string,string>[] $options Options.
	 *
	 * @return $this
	 */
	public function setOptions(array $options): Field
	{
		$this->options = [];

		foreach ($options as $option)
		{
			$this->addOption(
				$option['key'],
				$option['label']
			);
		}

		return $this;
	}

	/**
	 * Get type.
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Get name.
	 *
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Get label.
	 *
	 * @return null|string
	 */
	public function getLabel(): ?string
	{
		return $this->label;
	}

	/**
	 * Get key.
	 *
	 * @return null|string
	 */
	public function getKey(): ?string
	{
		return $this->key;
	}

	/**
	 * Get options.
	 *
	 * @return array
	 */
	public function getOptions(): array
	{
		return $this->options;
	}
}