<?php

namespace Bitrix\Calendar\Sync\Office365\Dto;

class PersonDto extends Dto
{
	/** @var EmailDto */
	public $emailAddress;

	/**
	 * @return array[]
	 */
	protected function getComplexPropertyMap(): array
	{
		return [
			'emailAddress' => [
				'class' => EmailDto::class,
				'isMandatory' => true,
			],
		];
	}
}
