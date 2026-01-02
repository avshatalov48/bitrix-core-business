<?php
declare(strict_types=1);

namespace Bitrix\UI\Form;

class FeedbackFormParams
{
	public int $id;
	public string $sec;
	public array $zones;
	public ?string $lang = null;

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'sec' => $this->sec,
			'zones' => $this->zones,
			'lang' => $this->lang,
		];
	}
}
