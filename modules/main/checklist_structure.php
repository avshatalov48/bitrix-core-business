<?
$arCheckListStructure=Array(
	"CATEGORIES" =>Array(
		"QDESIGN" => Array(
		),
		"DESIGN"=>Array(
			"PARENT"=>"QDESIGN"
		),
		"MODEL"=>Array(
			"PARENT"=>"QDESIGN"
		),
		"STANDART"=>Array(
			"PARENT"=>"QDESIGN"
		),
		"CUSTOM"=>Array(
			"PARENT"=>"QDESIGN"
		),
		"EXTAND"=>Array(
			"PARENT"=>"QDESIGN"
		),
		"QSECURITY" => Array(
		),
		"QPERFORMANCE" => Array(
		),
		"QHOSTING" => Array(
		),
		"QPROJECT" => Array(
		),

	),
	"POINTS"=>Array(
		//DESIGN
		"QD0010" => Array(
			"PARENT"=>"DESIGN",
			"REQUIRE"=>"Y",
		),
		"QD0020" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"DESIGN",
			"AUTO" => "Y",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckTemplates"


		),
		"QD0030" => Array(
			"PARENT"=>"DESIGN",
			"REQUIRE"=>"Y",

		),
		"QD0040" => Array(
			"PARENT"=>"DESIGN",
			"REQUIRE"=>"Y",

		),
		"QD0050" => Array(

			"PARENT"=>"DESIGN",

		),
		"QD0060" => Array(
			"PARENT"=>"DESIGN"
		),
		"QD0070" => Array(
			"PARENT"=>"DESIGN"
		),
		"QD0080" => Array(
			"PARENT"=>"DESIGN",
		),
		"QD0090" => Array(
			"PARENT"=>"DESIGN",
		),
		"QD0100" => Array(
			"PARENT"=>"DESIGN",
		),
		"QD0110" => Array(
			"PARENT"=>"DESIGN",
		),
		"QD0120" => Array(
			"PARENT"=>"DESIGN",
		),
		//MODEL
		"QM0010" => Array(
			"PARENT"=>"MODEL",
			"REQUIRE"=>"Y",

		),
		"QM0020" => Array(
			"PARENT"=>"MODEL",
		),
		//STANDART
		"QS0010" => Array(
			"PARENT"=>"STANDART",
		),
		"QS0020" => Array(
			"PARENT"=>"STANDART",
			"REQUIRE"=>"Y"

		),
		"QS0030" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"STANDART",
		),

		"QS0040" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"STANDART",
		),
		//CUSTOM
		"QC0010" => Array(
			"PARENT"=>"CUSTOM",
			//"AUTO" =>"Y",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckCustomComponents",
			"PARAMS"=>Array(
				"ACTION"=>"FIND"
			)
		),
		"QC0020" => Array(
			"PARENT"=>"CUSTOM"

		),
		"QC0030" => Array(
			"PARENT"=>"CUSTOM",
			"AUTO" =>"Y",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckCustomComponents",
		),
		"QC0040" => Array(
			"PARENT"=>"CUSTOM"
		),
		"QC0050" => Array(
			"PARENT"=>"CUSTOM",
		),
		"QC0060" => Array(
			"PARENT"=>"CUSTOM"
		),
		"QC0070" => Array(
			"PARENT"=>"CUSTOM"
		),
		"QC0080" => Array(
			"PARENT"=>"CUSTOM"
		),
		"QC0090" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"CUSTOM",
			"AUTO" =>"Y",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckQueryString"
		),
		"QC0100" => Array(
			"PARENT"=>"CUSTOM"
		),
		"QC0110" => Array(
			"PARENT"=>"CUSTOM",
		),
		"QC0120" => Array(
			"PARENT"=>"CUSTOM",
		),
		"QC0130" => Array(
			"PARENT"=>"CUSTOM"
		),
		"QC0140" => Array(
			"PARENT"=>"CUSTOM",
		),
		"QC0150" => Array(
			"PARENT"=>"CUSTOM",
		),
		"QC0160" => Array(
			"PARENT"=>"CUSTOM",
		),
		//EXTENDED
		"QE0010" => Array(
			"PARENT"=>"EXTAND",
		),
		"QE0020" => Array(
			"PARENT"=>"EXTAND",
		),
		"QE0030" => Array(
			"PARENT"=>"EXTAND",
		),
		"QE0040" => Array(
			"PARENT"=>"EXTAND",
		),

		//SECURIRY
		"QSEC0010" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QSECURITY",
			"AUTO"=>"Y",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckSecurity",
			"PARAMS"=>Array(
				"ACTION"=>"SECURITY_LEVEL"
			)

		),
		"QSEC0020" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QSECURITY",
			"AUTO"=>"Y",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckSecurity",
			"PARAMS"=>Array(
				"ACTION"=>"ADMIN_POLICY"
			)

		),
		"QSEC0030" => Array(
			"PARENT"=>"QSECURITY"
		),
		"QSEC0040" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QSECURITY",

		),
		"QSEC0050" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QSECURITY",
			"AUTO"=>"Y",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckDBPassword"
		),
		"QSEC0060" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QSECURITY",
			"AUTO"=>"Y",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckErrorReport"
		),
		"QSEC0070" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QSECURITY"

		),
		"QSEC0080" => Array(
			"REQUIRE"=>"N",
			"PARENT"=>"QSECURITY",
			"AUTO"=>"Y",
			'CLASS_NAME' => "CQAACheckListTests",
            'METHOD_NAME' => "checkVulnerabilities",
            "FILE_PATH"=>"/bitrix/modules/main/classes/general/vuln_scanner.php"
		),
		//QPERFORMANCE
		"QP0010" => Array(
			"PARENT"=>"QPERFORMANCE",
		),
		"QP0020" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QPERFORMANCE",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckPerfomance",
			"AUTO"=>"Y",
			"PARAMS"=>Array(
				"ACTION"=>"PHPCONFIG"
			)

		),
		"QP0030" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QPERFORMANCE",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"IsCacheOn",
			"AUTO"=>"Y"
		),
		"QP0040" => Array(
			"PARENT"=>"QPERFORMANCE"
		),
		"QP0050" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QPERFORMANCE",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckPerfomance",
			"AUTO"=>"Y",
			"PARAMS"=>Array(
				"ACTION"=>"PERF_INDEX"
			)

		),
		"QP0060" => Array(
			"PARENT"=>"QPERFORMANCE"
		),
		"QP0070" => Array(
			"PARENT"=>"QPERFORMANCE"

		),
		"QP0080" => Array(
			"PARENT"=>"QPERFORMANCE"
		),
		"QP0100" => Array(
			"PARENT"=>"QPERFORMANCE"
		),

		//HOSTING
		"QH0010" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QHOSTING",
		),
		"QH0020" => Array(
			"PARENT"=>"QHOSTING",
		),
		"QH0030" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QHOSTING",
		),
		"QH0040" => Array(
			"PARENT"=>"QHOSTING",
		),
		"QH0050" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QHOSTING",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckBackup",
			"AUTO"=>"Y"
		),
		"QH0060" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QHOSTING",
		),
		//QPROJECT
		"QJ0010" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QPROJECT",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"KeyCheck",
			"AUTO"=>"Y"
		),
		"QJ0020" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QPROJECT",
			"AUTO"=>"Y",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckKernel"
		),
		"QJ0030" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QPROJECT",
		),
		"QJ0040" => Array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QPROJECT",
		),

	),
);

$bIntranet = CModule::IncludeModule('intranet');
if ($bIntranet)
{
	$wenvCategories = array(
		'WENV' => array(),
	);
	$arCheckListStructure['CATEGORIES'] = array_merge($wenvCategories, $arCheckListStructure['CATEGORIES']);

	$wenvPoints = array(
		//WENV
		"QWE0010" => Array(
			"PARENT"=>"WENV",
			"REQUIRE"=>"Y",
			"AUTO"=>"Y",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckVMBitrix",
		),
		"QWE0020" => Array(
			"PARENT"=>"WENV",
			"REQUIRE"=>"Y",
			"AUTO"=>"Y",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckSiteCheckerStatus",
		),
		"QWE0030" => Array(
			"PARENT"=>"WENV",
			"REQUIRE"=>"Y",
			"AUTO"=>"Y",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckSiteCheckerStatus",
		),
	);
	$arCheckListStructure['POINTS'] = array_merge($wenvPoints, $arCheckListStructure['POINTS']);

	$secPoints = array(
		"QSEC0090" => array(
			"REQUIRE"=>"Y",
			"PARENT"=>"QSECURITY",
			"AUTO"=>"Y",
			"CLASS_NAME"=>"CAutoCheck",
			"METHOD_NAME"=>"CheckSecurityScannerStatus",
		)
	);

	$arCheckListStructure['POINTS'] = array_merge($arCheckListStructure['POINTS'], $secPoints);

	unset($arCheckListStructure['POINTS']['QH0030']);
}

return $arCheckListStructure;
?>