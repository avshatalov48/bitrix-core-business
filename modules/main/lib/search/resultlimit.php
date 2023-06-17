<?php
namespace Bitrix\Main\Search;

final class ResultLimit implements \JsonSerializable
{
	/** @var string */
	private $type;

	/** @var string */
	private $title;

	/** @var string */
	private $description;

	/** @var array */
	private $buttons = [];

	public function __construct($type, $title, $description = null)
	{
		$this
			->setType($type)
			->setTitle($title)
			->setDescription($description)
		;
	}

	/**
	 * Returns the element type. For example: lead, deal, file, folder, etc.
	 *
	 * @return string|null
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Sets the element type. For example: lead, deal, file, folder, etc.
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Returns the limit title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Sets the limit title
	 *
	 * @param $title
	 *
	 * @return $this
	 */
	public function setTitle($title)
	{
		if (is_string($title))
		{
			$this->title = $title;
		}

		return $this;
	}

	/**
	 * Return the limit description
	 *
	 * @return string|null
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Sets the limit description
	 *
	 * @param $description
	 *
	 * @return $this
	 */
	public function setDescription($description)
	{
		if (is_string($description))
		{
			$this->description = $description;
		}

		return $this;
	}

	/**
	 * Gets the limit buttons
	 *
	 * @return array
	 */
	public function getButtons()
	{
		return $this->buttons;
	}

	/**
	 * Sets the limit buttons
	 *
	 * @param $buttons
	 *
	 * @return $this
	 */
	public function setButtons($buttons)
	{
		if (is_array($buttons))
		{
			foreach ($buttons as $button)
			{
				if (is_string($button))
				{
					$this->buttons[] = $button;
				}
			}
		}

		return $this;
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize(): array
	{
		return [
			"type" => $this->getType(),
			"title" => $this->getTitle(),
			"description" => $this->getDescription(),
			"buttons" => $this->getButtons(),
		];
	}
}