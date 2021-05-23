<?
namespace Bitrix\UI\EntitySelector;

class TextNode implements \JsonSerializable
{
	protected $text;
	protected $type;

	public function __construct($options)
	{
		if (is_array($options))
		{
			if (is_string($options['text']))
			{
				$this->text = $options['text'];
			}

			if (TextNodeType::isValid($options['type']))
			{
				$this->type = $options['type'];
			}
		}
		else if (is_string($options))
		{
			$this->text = $options;
		}
	}

	public function getType(): ?string
	{
		return $this->type;
	}

	public function getText(): ?string
	{
		return $this->text;
	}

	public function isNullable(): bool
	{
		return $this->getText() === null;
	}

	public function jsonSerialize()
	{
		if ($this->getType() === null)
		{
			return $this->getText();
		}
		else
		{
			return [
				'text' => $this->getText(),
				'type' => $this->getType()
			];
		}
	}
}