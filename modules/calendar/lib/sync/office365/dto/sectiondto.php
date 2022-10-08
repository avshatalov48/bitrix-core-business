<?php

namespace Bitrix\Calendar\Sync\Office365\Dto;

class SectionDto extends Dto
{
	/** @var string */
	public $id;
	/** @var string */
	public $name;
	/** @var string|int */
	public $color;
	/** @var string */
	public $hexColor;
	/** @var boolean */
	public $isDefaultCalendar;
	/** @var string */
	public $changeKey;
	/** @var boolean */
	public $canShare;
	/** @var boolean */
	public $canViewPrivateItems;
	/** @var boolean */
	public $canEdit;
	/** @var array */
	public $allowedOnlineMeetingProviders;
	/** @var string */
	public $defaultOnlineMeetingProvider;
	/** @var boolean */
	public $isTallyingResponses;
	/** @var boolean */
	public $isRemovable;
	/** @var EmailDto */
	public $owner;

	/**
	 * @return array[]
	 */
	protected function getComplexPropertyMap(): array
	{
		return [
			'owner' => [
				'class' => EmailDto::class,
				'isMandatory' => true,
			],
		];
	}
}
