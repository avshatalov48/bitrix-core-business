<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO;

class Project implements TolokaTransferObject
{
	public const ASSIGNMENTS_ISSUING_TYPE_AUTOMATED    = 'AUTOMATED';
	public const ASSIGNMENTS_ISSUING_TYPE_MAP_SELECTOR = 'MAP_SELECTOR';

	public const STATUS_ACTIVE  = 'ACTIVE';
	public const STATUS_ARCHIVE = 'ARCHIVE';

	/**
	 * @var string
	 */
	private $publicName;

	/**
	 * @var string
	 */
	private $publicDescription;

	/**
	 * @var string
	 */
	private $publicInstructions;

	/**
	 * @var string
	 */
	private $privateComment;

	/**
	 * @var TaskSpec
	 */
	private $taskSpec;

	/**
	 * @var string
	 */
	private $assignmentsIssuingType = self::ASSIGNMENTS_ISSUING_TYPE_AUTOMATED;

	/**
	 * @var ViewConfig
	 */
	private $assignmentsIssuingViewConfig;

	/**
	 * @var QualityControl
	 */
	private $qualityControl;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $status = self::STATUS_ACTIVE;

	/**
	 * @var string
	 */
	private $created;

	/**
	 * @return string
	 */
	public function getPublicInstructions(): string
	{
		return $this->publicInstructions;
	}

	/**
	 * @param string $publicInstructions
	 *
	 * @return Project
	 */
	public function setPublicInstructions(string $publicInstructions): Project
	{
		$this->publicInstructions = $publicInstructions;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPrivateComment(): string
	{
		return $this->privateComment;
	}

	/**
	 * @param string $privateComment
	 *
	 * @return Project
	 */
	public function setPrivateComment(string $privateComment): Project
	{
		$this->privateComment = $privateComment;

		return $this;
	}

	/**
	 * @return TaskSpec
	 */
	public function getTaskSpec(): TaskSpec
	{
		return $this->taskSpec;
	}

	/**
	 * @param TaskSpec $taskSpec
	 *
	 * @return Project
	 */
	public function setTaskSpec(TaskSpec $taskSpec): Project
	{
		$this->taskSpec = $taskSpec;

		return $this;
	}

	/**
	 * @return ViewConfig
	 */
	public function getAssignmentsIssuingViewConfig(): ViewConfig
	{
		return $this->assignmentsIssuingViewConfig;
	}

	/**
	 * @param ViewConfig $assignmentsIssuingViewConfig
	 *
	 * @return Project
	 */
	public function setAssignmentsIssuingViewConfig(ViewConfig $assignmentsIssuingViewConfig): Project
	{
		$this->assignmentsIssuingViewConfig = $assignmentsIssuingViewConfig;

		return $this;
	}

	/**
	 * @return QualityControl
	 */
	public function getQualityControl(): QualityControl
	{
		return $this->qualityControl;
	}

	/**
	 * @param QualityControl $qualityControl
	 *
	 * @return Project
	 */
	public function setQualityControl(QualityControl $qualityControl): Project
	{
		$this->qualityControl = $qualityControl;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 *
	 * @return Project
	 */
	public function setId(int $id): Project
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getStatus(): string
	{
		return $this->status;
	}

	/**
	 * @param string $status
	 *
	 * @return Project
	 */
	public function setStatus(string $status): Project
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCreated(): string
	{
		return $this->created;
	}

	/**
	 * @param string $created
	 *
	 * @return Project
	 */
	public function setCreated(string $created): Project
	{
		$this->created = $created;

		return $this;
	}

	public function toArray():array
	{
		return [
			'public_name'              => $this->publicName,
			'public_description'       => $this->publicDescription,
			'public_instruction'       => $this->publicInstructions,
			'task_spec'                => $this->taskSpec->toArray(),
			'assignments_issuing_type' => $this->assignmentsIssuingType
		];
	}

	/**
	 * @return string
	 */
	public function getPublicName(): string
	{
		return $this->publicName;
	}

	/**
	 * @param string $publicName
	 *
	 * @return Project
	 */
	public function setPublicName(string $publicName): Project
	{
		$this->publicName = $publicName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPublicDescription(): string
	{
		return $this->publicDescription;
	}

	/**
	 * @param string $publicDescription
	 *
	 * @return Project
	 */
	public function setPublicDescription(string $publicDescription): Project
	{
		$this->publicDescription = $publicDescription;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAssignmentsIssuingType(): string
	{
		return $this->assignmentsIssuingType;
	}

	/**
	 * @param string $assignmentsIssuingType
	 *
	 * @return Project
	 */
	public function setAssignmentsIssuingType(string $assignmentsIssuingType): Project
	{
		$this->assignmentsIssuingType = $assignmentsIssuingType;

		return $this;
	}
}