<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arWizardDescription = Array(
	"NAME" => GetMessage("WD_TITLE"), 
	"DESCRIPTION" => GetMessage("WD_TITLE_DESCR"), 
	"ICON" => "",
	"COPYRIGHT" => "Bitrix",
	"VERSION" => "1.0.0",
	"DEPENDENCIES" => Array( 
		"main" => "6.5.0",
		"sale" => "6.0.0",
		"currency" => "6.0.0",
	),
	"STEPS" => Array("Step0", "Step1", "Step2", "Step3", "Step4", "Step5", "Step6", "Step7", "Step8", "Install", "FinalStep", "CancelStep")
);

?>