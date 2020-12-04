<?php


namespace Bitrix\Calendar\ICal\Builder;


use Bitrix\Calendar\ICal\Basic\Content;
use Bitrix\Calendar\ICal\Basic\PropertyCreator;

class ComponentCreator
{
	private $content;

	public function __construct(Content $content)
	{
		$this->content = $content;
	}

	public function build(): string
	{
		$lines = [];

		foreach ($this->buildComponent() as $line) {
			$lines = array_merge($lines, $this->chipLine($line));
		}

		return implode("\r\n", $lines)."\r\n";
	}

	public function buildComponent(): array
	{
		$lines[] = "BEGIN:{$this->content->getType()}";

		$lines = array_merge(
			$lines,
			$this->buildProperties(),
			$this->buildSubComponents()
		);

		$lines[] = "END:{$this->content->getType()}";

		return $lines;
	}

	private function buildProperties(): array
	{
		$lines = [];

		foreach ($this->content->getProperties() as $key => $property)
		{
			$builder = new PropertyCreator($property);

			$lines = array_merge(
				$lines,
				$builder->build()
			);
		}

		return $lines;
	}

	private function buildSubComponents(): array
	{
		$lines = [];

		foreach ($this->content->getSubComponents() as $component) {
			$builder = new ComponentCreator($component->accessContent());

			$lines = array_merge(
				$lines,
				$builder->buildComponent()
			);
		}

		return $lines;
	}

	private function chipLine(string $line): array
	{
		$chippedLines = [];

		while (strlen($line) > 0)
		{
			if (strlen($line) > 75)
			{
				$chippedLines[] = mb_strcut($line, 0, 75, 'utf-8');
				$line = ' '.mb_strcut($line, 75, strlen($line), 'utf-8');
			}
			else
			{
				$chippedLines[] = $line;

				break;
			}
		}

		return $chippedLines;
	}
}