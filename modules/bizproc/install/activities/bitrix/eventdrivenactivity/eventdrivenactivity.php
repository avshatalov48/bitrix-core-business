<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$runtime = CBPRuntime::GetRuntime();
$runtime->IncludeActivityFile('SequenceActivity');

class CBPEventDrivenActivity extends CBPSequenceActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = ['Title' => ''];
	}

	public function GetEventActivity()
	{
		if (count($this->arActivities) === 0)
		{
			return null;
		}

		return $this->arActivities[0];
	}

	public static function validateChild($childActivity, $bFirstChild = false)
	{
		$arErrors = [];
		$messageSuffix = CBPHelper::getDistrName() == CBPHelper::DISTR_B24 ? '_B24' : '';

		if ($bFirstChild)
		{
			self::includeActivityFile($childActivity);
			$child = self::createInstance($childActivity, 'XXX');
			if (!($child instanceof IBPEventDrivenActivity))
			{
				$arErrors[] = [
					'code' => 'WrongChildType',
					'message' => Loc::getMessage('BPEDA_INVALID_CHILD_1' . $messageSuffix),
				];
			}
		}

		return array_merge($arErrors, parent::validateChild($childActivity, $bFirstChild));
	}
}
