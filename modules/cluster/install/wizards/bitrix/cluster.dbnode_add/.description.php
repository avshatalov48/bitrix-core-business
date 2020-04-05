<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arWizardDescription = Array(
	"NAME" => GetMessage('CLUWIZ_DESCR_NAME'),
	"DESCRIPTION" => GetMessage('CLUWIZ_DESCR_DESCRIPTION'),
	"ICON" => "",
	"COPYRIGHT" => GetMessage('CLUWIZ_DESCR_COPYRIGHT'),
	"VERSION" => "1.0.0",
	"STEPS" => Array(
		"Step1", //Check master
		"Step3", //Node connect credentials
		"Step4", //Node connection check + params
		"FinalStep", //Name and description input
		"CancelStep",
	),
);

?>