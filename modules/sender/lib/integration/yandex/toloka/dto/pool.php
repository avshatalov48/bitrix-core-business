<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO;

class Pool
{
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $projectId;

	/**
	 * @var string
	 */
	private $privateName;

	/**
	 * @var string
	 */
	private $publicDescription;

	/**
	 * @var boolean
	 */
	private $mayContainAdultContent;

	/**
	 * @var string
	 */
	private $willExpire;

	/**
	 * @var float
	 */
	private $rewardPerAssignment;

	/**
	 * @var PricingConfig
	 */
	private $dynamicPricingConfig;

	/**
	 * @var int
	 */
	private $assignmentMaxDurationSeconds = 300;

	/**
	 * @var QualityControl
	 */
	private $qualityControl;

	/**
	 * @var PoolDefaults
	 */
	private $defaults;

	private $autoAcceptSolutions = true;

	/**
	 * @var Filter[]
	 */
	private $filter = [];

	/**
	 * @return Filter[]
	 */
	public function getFilter(): array
	{
		return $this->filter;
	}

	/**
	 * @param Filter[] $filter
	 *
	 * @return Pool
	 */
	public function setFilter(array $filter): Pool
	{
		$this->filter = $filter;

		return $this;
	}

	/**
	 * @param Filter $filter
	 *
	 * @return Pool
	 */
	public function addFilter(Filter $filter): Pool
	{
		$this->filter[] = $filter;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getProjectId(): string
	{
		return $this->projectId;
	}

	/**
	 * @param string $projectId
	 *
	 * @return Pool
	 */
	public function setProjectId(string $projectId): Pool
	{
		$this->projectId = $projectId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPrivateName(): string
	{
		return $this->privateName;
	}

	/**
	 * @param string $privateName
	 *
	 * @return Pool
	 */
	public function setPrivateName(string $privateName): Pool
	{
		$this->privateName = $privateName;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isMayContainAdultContent(): bool
	{
		return $this->mayContainAdultContent;
	}

	/**
	 * @param bool $mayContainAdultContent
	 *
	 * @return Pool
	 */
	public function setMayContainAdultContent(bool $mayContainAdultContent): Pool
	{
		$this->mayContainAdultContent = $mayContainAdultContent;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getWillExpire(): string
	{
		return $this->willExpire;
	}

	/**
	 * @param string $willExpire
	 *
	 * @return Pool
	 */
	public function setWillExpire(string $willExpire): Pool
	{
		$this->willExpire = $willExpire;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getRewardPerAssignment(): float
	{
		return $this->rewardPerAssignment;
	}

	/**
	 * @param float $rewardPerAssignment
	 *
	 * @return Pool
	 */
	public function setRewardPerAssignment(float $rewardPerAssignment): Pool
	{
		$rewardPerAssignment = number_format(
			(float)round(
				$rewardPerAssignment,
				2,
				PHP_ROUND_HALF_DOWN
			),
			2,
			'.',
			''
		);
		$this->rewardPerAssignment = $rewardPerAssignment;

		return $this;
	}

	/**
	 * @return PricingConfig
	 */
	public function getDynamicPricingConfig(): PricingConfig
	{
		return $this->dynamicPricingConfig;
	}

	/**
	 * @param PricingConfig $dynamicPricingConfig
	 *
	 * @return Pool
	 */
	public function setDynamicPricingConfig(PricingConfig $dynamicPricingConfig): Pool
	{
		$this->dynamicPricingConfig = $dynamicPricingConfig;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getAssignmentMaxDurationSeconds(): int
	{
		return $this->assignmentMaxDurationSeconds;
	}

	/**
	 * @param int $assignmentMaxDurationSeconds
	 *
	 * @return Pool
	 */
	public function setAssignmentMaxDurationSeconds(int $assignmentMaxDurationSeconds): Pool
	{
		$this->assignmentMaxDurationSeconds = $assignmentMaxDurationSeconds;

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
	 * @return Pool
	 */
	public function setQualityControl(QualityControl $qualityControl): Pool
	{
		$this->qualityControl = $qualityControl;

		return $this;
	}

	/**
	 * @return PoolDefaults
	 */
	public function getDefaults(): PoolDefaults
	{
		return $this->defaults;
	}

	/**
	 * @param PoolDefaults $defaults
	 *
	 * @return Pool
	 */
	public function setDefaults(PoolDefaults $defaults): Pool
	{
		$this->defaults = $defaults;

		return $this;
	}

	/**
	 * @return null|int
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 *
	 * @return Pool
	 */
	public function setId(int $id): Pool
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAutoAcceptSolutions()
	{
		return $this->autoAcceptSolutions;
	}

	/**
	 * @param mixed $autoAcceptSolutions
	 *
	 * @return Pool
	 */
	public function setAutoAcceptSolutions($autoAcceptSolutions)
	{
		$this->autoAcceptSolutions = $autoAcceptSolutions;

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
	 * @return Pool
	 */
	public function setPublicDescription(string $publicDescription): Pool
	{
		$this->publicDescription = $publicDescription;

		return $this;
	}

	public function toArray():array
	{
		$resultArray = [
			'project_id'                      => $this->projectId,
			'private_name'                    => $this->privateName,
			'public_description'              => $this->publicDescription,
			'may_contain_adult_content'       => $this->mayContainAdultContent,
			'will_expire'                     => $this->willExpire,
			'reward_per_assignment'           => $this->rewardPerAssignment,
			'assignment_max_duration_seconds' => $this->assignmentMaxDurationSeconds,
			'auto_accept_solutions'           => $this->autoAcceptSolutions,
			'defaults'                        => $this->defaults->toArray(),
		];

		if(!empty($this->filter))
		{
			$resultArray['filter'] = [];
			$resultArray['filter']['and'] = [];
			foreach ($this->filter as $filter)
			{
				$resultArray['filter']['and'][] = $filter->toArray();
			}
		}

		return $resultArray;
	}
}