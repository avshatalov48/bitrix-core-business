<?php
namespace Bitrix\Seo\Sitemap\Type;

class Step
{
	public const STEP_INIT = 'init';
	public const STEP_FILES = 'files';
	public const STEP_IBLOCK_INDEX = 'iblock_index';
	public const STEP_IBLOCK = 'iblock';
	public const STEP_FORUM_INDEX = 'forum_index';
	public const STEP_FORUM = 'forum';
	public const STEP_INDEX = 'index';

	public const STEPS = [
		self::STEP_INIT => 0,
		self::STEP_FILES => 40,
		self::STEP_IBLOCK_INDEX => 50,
		self::STEP_IBLOCK => 60,
		self::STEP_FORUM_INDEX => 70,
		self::STEP_FORUM => 80,
		self::STEP_INDEX => 100,
	];

	/**
	 * Return first step value
	 * @return int
	 */
	public static function getFirstStep(): int
	{
		$stepValues = array_values(self::STEPS);

		return array_shift($stepValues);
	}

	/**
	 * Return first step name
	 * @return string
	 */
	public static function getFirstStepName(): string
	{
		$stepValues = array_keys(self::STEPS);

		return array_shift($stepValues);
	}

	/**
	 * Return last step value
	 * @return int
	 */
	public static function getLastStep(): int
	{
		$stepValues = array_values(self::STEPS);

		return array_pop($stepValues);
	}

	/**
	 * Return last step name
	 * @return string
	 */
	public static function getLastStepName(): string
	{
		$stepValues = array_keys(self::STEPS);

		return array_pop($stepValues);
	}
}
