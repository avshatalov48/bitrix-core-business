<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO;

class TaskSpec implements TolokaTransferObject
{
	/**
	 * @var InputOutputSpec
	 */
	private $inputSpec;

	/**
	 * @var InputOutputSpec
	 */
	private $outputSpec;

	/**
	 * @var ViewSpec
	 */
	private $viewSpec;

	/**
	 * @return InputOutputSpec
	 */
	public function getInputSpec(): InputOutputSpec
	{
		return $this->inputSpec;
	}

	/**
	 * @param InputOutputSpec $inputSpec
	 *
	 * @return TaskSpec
	 */
	public function setInputSpec(InputOutputSpec $inputSpec): TaskSpec
	{
		$this->inputSpec = $inputSpec;

		return $this;
	}

	/**
	 * @return InputOutputSpec
	 */
	public function getOutputSpec(): InputOutputSpec
	{
		return $this->outputSpec;
	}

	/**
	 * @param InputOutputSpec $outputSpec
	 *
	 * @return TaskSpec
	 */
	public function setOutputSpec(InputOutputSpec $outputSpec): TaskSpec
	{
		$this->outputSpec = $outputSpec;

		return $this;
	}

	/**
	 * @return ViewSpec
	 */
	public function getViewSpec(): ViewSpec
	{
		return $this->viewSpec;
	}

	/**
	 * @param ViewSpec $viewSpec
	 *
	 * @return TaskSpec
	 */
	public function setViewSpec(ViewSpec $viewSpec): TaskSpec
	{
		$this->viewSpec = $viewSpec;

		return $this;
	}

	public function toArray():array
	{
		return [
			'input_spec'  => $this->inputSpec->toArray(),
			'output_spec' => $this->outputSpec->toArray(),
			'view_spec'   => $this->viewSpec->toArray()
		];
	}
}