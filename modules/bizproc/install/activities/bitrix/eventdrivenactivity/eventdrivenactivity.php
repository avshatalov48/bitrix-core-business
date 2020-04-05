<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$runtime = CBPRuntime::GetRuntime();
$runtime->IncludeActivityFile("SequenceActivity");

class CBPEventDrivenActivity
	extends CBPSequenceActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array("Title" => "");
	}

	public function GetEventActivity()
	{
		if (count($this->arActivities) == 0)
			return null;

		return $this->arActivities[0];
	}

	public static function ValidateChild($childActivity, $bFirstChild = false)
	{
		$arErrors = array();
		$messageSuffix = CBPHelper::getDistrName() == CBPHelper::DISTR_B24 ? '_B24' : '';

		if ($bFirstChild)
		{
			self::IncludeActivityFile($childActivity);
			$child = self::CreateInstance($childActivity, "XXX");
			if (!($child instanceof IBPEventDrivenActivity))
				$arErrors[] = array("code" => "WrongChildType", "message" => GetMessage("BPEDA_INVALID_CHILD".$messageSuffix));
		}

		return array_merge($arErrors, parent::ValidateChild($childActivity, $bFirstChild));
	}
}
?>