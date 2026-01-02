<?php
declare(strict_types=1);

namespace Bitrix\Landing\Metrika;

class FieldsDto
{
	public function __construct(
		public ?Events $event = null,
		public ?Types $type = null,
		public ?Sections $section = null,
		public ?string $subSection = null,
		public ?string $element = null,
		/**
		 * @var array|null - array of arrays [position, name, value]
		 * F.e.
		 * [
		 *     [1, foo, bar],
		 *     [2, baz, bat],
		 * ]
		 */
		public ?array $params = null,
		public ?string $error = null,
	)
	{}

	public function toArray(): array
	{
		return [
			'event' => $this->event,
			'type' => $this->type,
			'section' => $this->section,
			'subSection' => $this->subSection,
			'element' => $this->element,
			'params' => $this->params,
			'error' => $this->error,
		];
	}

	public static function fromArray(array $data): self
	{
		if (isset($data['event']))
		{
			$event = Events::tryFrom($data['event']);
		}
		if (isset($data['type']))
		{
			$type = Types::tryFrom($data['type']);
		}
		if (isset($data['section']))
		{
			$section = Sections::tryFrom($data['section']);
		}
		$subSection = $data['subSection'] ?? null;
		$element = $data['element'] ?? null;
		$params = $data['params'] ?? null;
		$error = $data['error'] ?? null;

		return new self(
			$event,
			$type,
			$section,
			$subSection,
			$element,
			$params,
			$error,
		);
	}

	public function addFields(?FieldsDto $addFields): self
	{
		if ($addFields === null)
		{
			return $this;
		}

		$this->event = $addFields->event ?? $this->event;
		$this->type = $addFields->type ?? $this->type;
		$this->section = $addFields->section ?? $this->section;
		$this->subSection = $addFields->subSection ?? $this->subSection;
		$this->element = $addFields->element ?? $this->element;
		$this->params = $addFields->params ?? $this->params;
		$this->error = $addFields->error ?? $this->error;

		return $this;
	}
}
