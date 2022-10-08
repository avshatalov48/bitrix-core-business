<?php

namespace Bitrix\Calendar\Sync\Office365\Dto;

class ParticipantDto extends PersonDto
{
	/** @var string */
	public $type;

	/** @var ResponseStatusDto */
	public $status;

	/** @var EmailDto */
	public $emailAddress;

	/**
	 * @return array[]
	 */
	protected function getComplexPropertyMap(): array
	{
		return [
			'status' => [
				'class' => ResponseStatusDto::class,
				'isMandatory' => false,
			],
			'emailAddress' => [
				'class' => EmailDto::class,
				'isMandatory' => false,
			],
		];
	}
}
