<?php


namespace Bitrix\Calendar\ICal\Parser;


class ParserPropertyType
{
	/**
	 * @var string
	 */
	private $value;
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var array
	 */
	private $parameters;

	/**
	 * @param string $name
	 * @return ParserPropertyType
	 */
	public static function createInstance(string $name): ParserPropertyType
	{
		return new self($name);
	}

	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string|null
	 */
	public function getValue(): ?string
	{
		return $this->value;
	}

	/**
	 * @return string|null
	 */
	public function getOriginalValue(): ?string
	{
		return $this->value;
	}

	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string|null $value
	 * @return $this
	 */
	public function setValue(?string $value): ParserPropertyType
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * @param array $parameters
	 * @return $this
	 */
	public function addParameters(array $parameters): ParserPropertyType
	{
		foreach ($parameters as $key => $parameter)
		{
			$this->addParameter($key, $parameter);
		}

		return $this;
	}

	/**
	 * @param string $key
	 * @param string $parameter
	 * @return $this
	 */
	public function addParameter(string $key, string $parameter): ParserPropertyType
	{
		$this->parameters[$key] = $parameter;

		return $this;
	}

	public function getParameterValueByName(string $name): ?string
	{
		return $this->parameters[$name] ?? null;
	}
}