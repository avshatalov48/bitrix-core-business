<?
namespace Bitrix\UI\EntitySelector;

class TextNode implements \JsonSerializable
{
	protected ?string $text = null;
	protected ?string $type = null;

	public function __construct($options)
	{
		if (is_array($options))
		{
			if (isset($options['text']) && (is_string($options['text']) || is_int($options['text'])))
			{
				$this->text = (string)$options['text'];
			}

			if (isset($options['type']) && TextNodeType::isValid($options['type']))
			{
				$this->type = $options['type'];
			}
		}
		else if (is_string($options) || is_int($options))
		{
			$this->text = (string)$options;
		}
	}

	public static function isValidText($text): bool
	{
		return is_string($text) || is_int($text) || is_array($text);
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