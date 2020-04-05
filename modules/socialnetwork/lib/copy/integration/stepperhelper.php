<?php
namespace Bitrix\Socialnetwork\Copy\Integration;

class StepperHelper implements Helper
{
	private $stepper = "";

	private $moduleId = "";

	private $queueOption = "";
	private $checkerOption = "";
	private $stepperOption = "";
	private $errorOption = "";

	private $title = "";
	private $error = "";

	/**
	 * Returns a module id for work with options.
	 * @return string
	 */
	public function getModuleId()
	{
		return $this->moduleId;
	}

	/**
	 * Returns a map of option names.
	 *
	 * @return array
	 */
	public function getOptionNames()
	{
		return [
			"queue" => $this->queueOption,
			"checker" => $this->checkerOption,
			"stepper" => $this->stepperOption,
			"error" => $this->errorOption
		];
	}

	/**
	 * Returns a link to stepper class.
	 * @return string
	 */
	public function getLinkToStepperClass()
	{
		return $this->stepper;
	}

	/**
	 * Returns a text map.
	 * @return array
	 */
	public function getTextMap()
	{
		return [
			"title" => $this->title,
			"error" => $this->error,
		];
	}

	/**
	 * @param string $stepper
	 */
	public function setStepper(string $stepper): void
	{
		$this->stepper = $stepper;
	}

	/**
	 * @param string $moduleId
	 */
	public function setModuleId(string $moduleId): void
	{
		$this->moduleId = $moduleId;
	}

	/**
	 * @param string $queueOption
	 */
	public function setQueueOption(string $queueOption): void
	{
		$this->queueOption = $queueOption;
	}

	/**
	 * @param string $checkerOption
	 */
	public function setCheckerOption(string $checkerOption): void
	{
		$this->checkerOption = $checkerOption;
	}

	/**
	 * @param string $stepperOption
	 */
	public function setStepperOption(string $stepperOption): void
	{
		$this->stepperOption = $stepperOption;
	}

	/**
	 * @param string $errorOption
	 */
	public function setErrorOption(string $errorOption): void
	{
		$this->errorOption = $errorOption;
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title): void
	{
		$this->title = $title;
	}

	/**
	 * @param string $error
	 */
	public function setError(string $error): void
	{
		$this->error = $error;
	}
}