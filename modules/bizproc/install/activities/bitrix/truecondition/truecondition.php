<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPTrueCondition
	extends CBPActivityCondition
{
	public $condition = true;

	public function __construct($condition)
	{
		$this->condition = $condition;
	}

	public function Evaluate(CBPActivity $ownerActivity)
	{
		return true; //$this->condition;
	}

	public static function ValidateProperties($value = null, CBPWorkflowTemplateUser $user = null)
	{
		return array();
	}

	public static function GetPropertiesDialog($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $defaultValue, $arCurrentValues = null)
	{
		return "&nbsp;";
	}

	public static function GetPropertiesDialogValues($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();
		return true;
	}
}
?>