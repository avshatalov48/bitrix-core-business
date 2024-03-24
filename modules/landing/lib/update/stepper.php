<?php
namespace Bitrix\Landing\Update;

class Stepper
{
	/**
	 * list of updaters classes, then can be show in progress bar.
	 * @return array
	 */
	private static function getUpdaterClasses(): array
	{
		return array(
			'Bitrix\Landing\Update\Block\NodeAttributes',
			'Bitrix\Landing\Update\Block\NodeImg',
			'Bitrix\Landing\Update\Block\FixSrcImg',
			'Bitrix\Landing\Update\Block\SearchContent',
			'Bitrix\Landing\Update\Block\LastUsed',
			'Bitrix\Landing\Update\Block',
			'Bitrix\Landing\Update\Landing\InitApp',
			'Bitrix\Landing\Update\Landing\SearchContent',
			'Bitrix\Landing\Update\Landing\FolderNew',
			'Bitrix\Landing\Update\Domain\Check',
			'Bitrix\Landing\Update\Assets\WebpackClear',
			'Bitrix\Landing\Update\Assets\FontFix',
			'Bitrix\Landing\Update\Assets\FixFontWeight',
			'Bitrix\Landing\Update\Block\DomainUa',
			'Bitrix\Landing\Update\Site\Publish',
		);
	}


	/**
	 * Show some stepper if needed.
	 * @deprecated since 21.800.0
	 * @return void
	 */
	public static function show(): void
	{
	}
}
