<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO;

class ViewSpecSettings implements TolokaTransferObject
{

	/**
	 * @var boolean
	 */
	private $showTimer = true;

	/**
	 * @var boolean
	 */
	private $showTitle = true;

	/**
	 * @var boolean
	 */
	private $showInstructions = true;

	/**
	 * @var boolean
	 */
	private $showFullscreen = true;

	/**
	 * @var boolean
	 */
	private $showSubmit = true;

	/**
	 * @var boolean
	 */
	private $showSkip = true;

	/**
	 * @var boolean
	 */
	private $showFinish = true;

	/**
	 * @var boolean
	 */
	private $showMessage = true;

	/**
	 * @return bool
	 */
	public function isShowTimer(): bool
	{
		return $this->showTimer;
	}

	/**
	 * @param bool $showTimer
	 *
	 * @return ViewSpecSettings
	 */
	public function setShowTimer(bool $showTimer): ViewSpecSettings
	{
		$this->showTimer = $showTimer;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isShowTitle(): bool
	{
		return $this->showTitle;
	}

	/**
	 * @param bool $showTitle
	 *
	 * @return ViewSpecSettings
	 */
	public function setShowTitle(bool $showTitle): ViewSpecSettings
	{
		$this->showTitle = $showTitle;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isShowInstructions(): bool
	{
		return $this->showInstructions;
	}

	/**
	 * @param bool $showInstructions
	 *
	 * @return ViewSpecSettings
	 */
	public function setShowInstructions(bool $showInstructions): ViewSpecSettings
	{
		$this->showInstructions = $showInstructions;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isShowFullscreen(): bool
	{
		return $this->showFullscreen;
	}

	/**
	 * @param bool $showFullscreen
	 *
	 * @return ViewSpecSettings
	 */
	public function setShowFullscreen(bool $showFullscreen): ViewSpecSettings
	{
		$this->showFullscreen = $showFullscreen;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isShowSubmit(): bool
	{
		return $this->showSubmit;
	}

	/**
	 * @param bool $showSubmit
	 *
	 * @return ViewSpecSettings
	 */
	public function setShowSubmit(bool $showSubmit): ViewSpecSettings
	{
		$this->showSubmit = $showSubmit;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isShowSkip(): bool
	{
		return $this->showSkip;
	}

	/**
	 * @param bool $showSkip
	 *
	 * @return ViewSpecSettings
	 */
	public function setShowSkip(bool $showSkip): ViewSpecSettings
	{
		$this->showSkip = $showSkip;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isShowFinish(): bool
	{
		return $this->showFinish;
	}

	/**
	 * @param bool $showFinish
	 *
	 * @return ViewSpecSettings
	 */
	public function setShowFinish(bool $showFinish): ViewSpecSettings
	{
		$this->showFinish = $showFinish;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isShowMessage(): bool
	{
		return $this->showMessage;
	}

	/**
	 * @param bool $showMessage
	 *
	 * @return ViewSpecSettings
	 */
	public function setShowMessage(bool $showMessage): ViewSpecSettings
	{
		$this->showMessage = $showMessage;

		return $this;
	}

	public function toArray():array
	{
		return [
			'show_timer'        => $this->showTimer,
			'show_title'        => $this->showTitle,
			'show_instructions' => $this->showInstructions,
			'show_fullscreen'   => $this->showFullscreen,
			'show_submit'       => $this->showSubmit,
			'show_skip'         => $this->showSkip,
			'show_finish'       => $this->showFinish,
			'show_message'      => $this->showMessage,
		];
	}
}