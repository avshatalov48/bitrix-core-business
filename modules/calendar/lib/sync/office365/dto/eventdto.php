<?php

namespace Bitrix\Calendar\Sync\Office365\Dto;

class EventDto extends Dto
{
	// "@odata.etag": "W/\"IiLKjG2I7E+Xv0+ys6MD0wAEHja7TQ==\"",
	/** @var string */
	public $etag;
	/** @var string */
	public $id;
	/** @var string */
	public $changeKey;
	/** @var string[] like tags */
	public $categories;
	/** @var string */
	public $transactionId;
	/** @var string|null */
	public $originalStart;
	/** @var string */
	public $originalStartTimeZone;
	/** @var string */
	public $originalEndTimeZone;
	/** @var string */
	public $iCalUId;
	/** @var integer */
	public $reminderMinutesBeforeStart;
	/** @var boolean */
	public $isReminderOn;
	/** @var boolean */
	public $hasAttachments;
	/** @var string */
	public $subject;
	/** @var string */
	public $bodyPreview;
	/** @var string enum */
	public $importance;
	/** @var string */
	public $sensitivity;
	/** @var boolean */
	public $isAllDay;
	/** @var boolean */
	public $isCancelled;
	/** @var boolean */
	public $isOrganizer;
	/** @var boolean */
	public $responseRequested;
	/** @var string */
	public $seriesMasterId;
	/** @var string  "tentative" */
	public $showAs;
	/** @var string "occurrence" */
	public $type;
	/** @var string */
	public $webLink;
	/** @var string */
	public $onlineMeetingUrl;
	/** @var boolean */
	public $isOnlineMeeting;
	/** @var string */
	public $onlineMeetingProvider;
	/** @var boolean */
	public $allowNewTimeProposals;
	/** @var string */
	public $occurrenceId;
	/** @var boolean */
	public $isDraft;
	/** @var boolean */
	public $hideAttendees;
	/** @var ResponseStatusDto */
	public $responseStatus;
	/** @var RichTextDto */
	public $body;
	/** @var DateTimeDto */
	public $start;
	/** @var DateTimeDto */
	public $end;
	/** @var string */
	public $createdDateTime;
	/** @var string */
	public $lastModifiedDateTime;
	/** @var LocationDto */
	public $location;
	/** @var LocationDto[] */
	public $locations;
	/** @var RecurrenceDto*/
	public $recurrence;
	/** @var ParticipantDto[] */
	public $attendees;
	/** @var EmailDto */
	public $organizer;
	/** @var string ?? */
	public $onlineMeeting;

	/**
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		$this->etag = $data['@odata.etag'] ?? '';
		unset($data['@odata.etag']);
		parent::__construct($data);
	}

	/**
	 * @return array[]
	 */
	protected function getComplexPropertyMap(): array
	{
		return [
			'responseStatus' => [
				'class' => ResponseStatusDto::class,
				'isMandatory' => false,
			],
			'body' => [
				'class' => RichTextDto::class,
				'isMandatory' => true,
			],
			'start' => [
				'class' => DateTimeDto::class,
				'isMandatory' => true,
			],
			'end' => [
				'class' => DateTimeDto::class,
				'isMandatory' => true,
			],
			'location' => [
				'class' => LocationDto::class,
				'isMandatory' => true,
			],
			'organizer' => [
				'class' => PersonDto::class,
				'isMandatory' => true,
			],
			'recurrence' => [
				'class' => RecurrenceDto::class,
				'isMandatory' => false,
			],
			'locations' => [
				'class' => LocationDto::class,
				'isMandatory' => false,
				'isArray' => true
			],
			'attendees' => [
				'class' => ParticipantDto::class,
				'isMandatory' => true,
				'isArray' => true,
			],
		];
	}
}
