<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$psTitle = "Payflow Pro";
$psDescription = "Accept payments on your Web site with Verisign scalable, fully customizable payment gateway solution. Payflow Pro gives you immediate connectivity to all major payment processors. <a href=\"http://verisign.com\" target=\"_blank\">http://verisign.com</a>";

$arPSCorrespondence = array(
		"PAYFLOW_URL" => array(
				"NAME" => "Pay system web url",
				"DESCR" => "Pay system web url (e.g. test-payflow.verisign.com)",
				"VALUE" => "test-payflow.verisign.com",
				"TYPE" => ""
			),
		"PAYFLOW_PORT" => array(
				"NAME" => "Pay system port",
				"DESCR" => "Pay system port (e.g. 443)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYFLOW_USER" => array(
				"NAME" => "User code",
				"DESCR" => "The user code obtained from Verisign.com",
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYFLOW_PASSWORD" => array(
				"NAME" => "Password",
				"DESCR" => "The password obtained from Verisign.com",
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYFLOW_PARTNER" => array(
				"NAME" => "Partner",
				"DESCR" => "Verisign.com partnet (e.g. VeriSign)",
				"VALUE" => "VeriSign",
				"TYPE" => ""
			),
		"PAYFLOW_EXE_PATH" => array(
				"NAME" => "Path to SDK",
				"DESCR" => "Path to file pfpro.exe (e.g. /verisign/win32/bin/pfpro.exe)",
				"VALUE" => "/verisign/win32/bin/pfpro.exe",
				"TYPE" => ""
			),
		"PAYFLOW_CERT_PATH" => array(
				"NAME" => "Path to certification (e.g. /verisign/win32/certs/)",
				"DESCR" => "Path to certification",
				"VALUE" => "/verisign/win32/certs/",
				"TYPE" => ""
			),
		"NOC" => array(
				"NAME" => "Buyer name",
				"DESCR" => "Buyer name",
				"VALUE" => "NAME",
				"TYPE" => "PROPERTY"
			),
		"ADDRESS" => array(
				"NAME" => "Buyer address",
				"DESCR" => "Buyer address",
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			),
		"ZIP" => array(
				"NAME" => "Buyer zip",
				"DESCR" => "Buyer zip",
				"VALUE" => "ZIP",
				"TYPE" => "PROPERTY"
			)
	);
?>