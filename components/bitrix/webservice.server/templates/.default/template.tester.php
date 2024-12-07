<?
	if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	
	if (!isset($arParams["WSDESCR"]))
	{
		echo GetMessage("BX_WSTMPL_ERROR_NOWSDESCR");
		return;		
	}
	
	$templateDataSource = $arParams["WSDESCR"];
	$wsName = $arParams["WEBSERVICE_NAME"];
	$wsClass = $arParams["WEBSERVICE_CLASS"];
	
	$getclass = htmlspecialcharsEx($_GET["class"]);
	$getop = htmlspecialcharsEx($_GET["op"]);
	
	if (!isset($templateDataSource->classes[$getclass][$getop]))
	{
		ShowError(GetMessage("BX_WSTMPL_ERRSEC_NOOP"));
		return;
	}
	
	$opdecl = $templateDataSource->classes[$getclass][$getop];
	
	$methodDeclared = "<h2>";	
	if (isset($opdecl["output"]))
	{
		foreach ($opdecl["output"] as $pname => $pparam) break;
		$methodDeclared .= "<i>{$pname}";
		if (isset($pparam["arrType"])) $methodDeclared .= "[]";
		$methodDeclared .= ":{$pparam['varType']}</i>";
		$methodDeclared .= " ";
	}
				
		$methodDeclared .= $getop."(";
		if (isset($opdecl["input"]) and count($opdecl["input"]))
		{
			foreach ($opdecl["input"] as $pname => $pparam) 
			{
				$varType = "";
				if (isset($pparam["varType"]))
					$varType = $pparam["varType"];

				$methodDeclared .= "<i>{$varType}&nbsp;{$pname}</i>";
				if (isset($pparam["arrType"])) $methodDeclared .= "[]";
					$methodDeclared .= ", ";
			}
			$methodDeclared = mb_substr($methodDeclared, 0, mb_strlen($methodDeclared) - 2);
		}
		$methodDeclared .= ");</h2>";
		$methodDeclared .= "<br>";
		$methodDescription = "";	
				
		if (isset($opdecl["description"]))
		{
			$methodDescription .= "<br><b>".GetMessage("BX_WSTMPL_DOC")."</b><br>";
			$methodDescription .= $opdecl["description"]."<br>";
		}
?>
<html>
<head>
<title>
<?=
	GetMessage("BX_WSTMPL_TITLE_PREFIX").$wsName;
?>
</title>
<style type="text/css">
		BODY { color: #000000; background-color: white; font-family: Verdana; margin-left: 0px; margin-top: 0px; }
		#content { margin-left: 30px; font-size: .70em; padding-bottom: 2em; }
		A:link { color: #336699; font-weight: bold; text-decoration: underline; }
		A:visited { color: #6699cc; font-weight: bold; text-decoration: underline; }
		A:active { color: #336699; font-weight: bold; text-decoration: underline; }
		A:hover { color: cc3300; font-weight: bold; text-decoration: underline; }
		P { color: #000000; margin-top: 0px; margin-bottom: 12px; font-family: Verdana; }
		pre { background-color: #e5e5cc; padding: 5px; font-family: Courier New; font-size: x-small; margin-top: -5px; border: 1px #f0f0e0 solid; }
		td { color: #000000; font-family: Verdana; font-size: .7em; }
		h2 { font-size: 1.5em; font-weight: bold; margin-top: 25px; margin-bottom: 10px; border-top: 1px solid #003366; margin-left: -15px; color: #003366; }
		h3 { font-size: 1.1em; color: #000000; margin-left: -15px; margin-top: 10px; margin-bottom: 10px; }
		ul { margin-top: 10px; margin-left: 20px; }
		ol { margin-top: 10px; margin-left: 20px; }
		li { margin-top: 10px; color: #000000; }
		font.value { color: darkblue; font: bold; }
		font.key { color: darkgreen; font: bold; }
		font.error { color: darkred; font: bold; }
		.heading1 { color: #ffffff; font-family: Tahoma; font-size: 26px; font-weight: normal; background-color: #003366; margin-top: 0px; margin-bottom: 0px; margin-left: -30px; padding-top: 10px; padding-bottom: 3px; padding-left: 15px; width: 100%; }
		.button { background-color: #dcdcdc; font-family: Verdana; font-size: 1em; border-top: #cccccc 1px solid; border-bottom: #666666 1px solid; border-left: #cccccc 1px solid; border-right: #666666 1px solid; }
		.frmheader { color: #000000; background: #dcdcdc; font-family: Verdana; font-size: .7em; font-weight: normal; border-bottom: 1px solid #dcdcdc; padding-top: 2px; padding-bottom: 2px; }
		.frmtext { font-family: Verdana; font-size: .7em; margin-top: 8px; margin-bottom: 0px; margin-left: 32px; }
		.frmInput { font-family: Verdana; font-size: 1em; }
		.intro { margin-left: -15px; }
</style>
</head>
<body>
<div id="content">

<p class="heading1"><?=GetMessage("BX_WSTMPL_WEBSERVICE").$wsName;?></p><br>

<span>
<p class="intro">
		<?=GetMessage("BX_WSTMPL_BACKPRE");?>
		<a href="?"><?=GetMessage("BX_WSTMPL_BACK");?></a>
</p>
</span>

<table width="600px" border=1>

<tr>
	<td><?=GetMessage("BX_WSTMPL_NAMESPACE");?></td>
	<td><?=$templateDataSource->wstargetns;?></td>
</tr>
<tr>
	<td><?=GetMessage("BX_WSTMPL_ENDPOINT");?></td>
	<td><?=$arParams["WSDESCR"]->wsendpoint;?></td>
</tr>
<tr>
	<td><?=GetMessage("BX_WSTMPL_BSTYLE");?></td>
	<td>document/literal only</td>
</tr>

</table><br>

<?
	echo $methodDeclared;
	echo $methodDescription;
	echo "<br><b>".GetMessage("BX_WSTMPL_TEST")."</b><br>";
	
	$input = array();
	$request = array();
	if (isset($opdecl["input"])) $input = $opdecl["input"];
	if (!$input and !isset($opdecl["input"]))
	{
		echo GetMessage("BX_WSTMPL_ERROR_NOFORCOMPLEX");
		echo "</body></hmtl>";
		return;
	}

	$xsd_simple_type = array(
		"string"=>"string", "bool"=>"boolean", "boolean"=>"boolean",
		"int"=>"integer", "integer"=>"integer", "double"=>"double", "float"=>"float", "number"=>"float",
		"base64"=>"string", "base64Binary"=>"string"
		);
			
	foreach ($input as $pname => $param)
	{
		if (!isset($param["varType"]) 
			or !isset($xsd_simple_type[$param["varType"]]))
		{
			echo GetMessage("BX_WSTMPL_ERROR_NOFORCOMPLEX");
			echo "</body></hmtl>";
			return;
		}
		
		if (isset($_POST[$pname]))
			$request[] = htmlspecialcharsEx($_POST[$pname]);
	}
	
	echo "
		<form action=\"?class={$getclass}&op={$getop}&directcall=1\" method=\"POST\">
			<table cellspacing=\"0\" cellpadding=\"4\" frame=\"box\" bordercolor=\"#dcdcdc\" rules=\"none\" style=\"border-collapse: collapse;\">
				";
	
	if (count($input))
	echo "
		<tr>
			<td class=\"frmHeader\" background=\"#dcdcdc\" style=\"border-right: 2px solid white;\">".GetMessage("BX_WSTMPL_ERROR_PARAMETER")."</td>
			<td class=\"frmHeader\" background=\"#dcdcdc\">".GetMessage("BX_WSTMPL_ERROR_VALUE")."</td>
		</tr>
		";
	
	foreach ($input as $pname => $param)
	{
		$value = "";
		if (isset($_POST[$pname]))
			$value = htmlspecialcharsEx($_POST[$pname]);
		echo " 
			<tr>
				<td class=\"frmText\" style=\"color: #000000; font-weight: normal;\">{$pname}:</td>
				<td><input value=\"$value\" class=\"frmInput\" type=\"text\" size=\"50\" name=\"{$pname}\"></td>
			</tr>
			";
	}
	
	echo "
		<tr>
			<td></td>
			<td align=\"right\"><input type=\"submit\" value=\"".GetMessage("BX_WSTMPL_ERROR_SUBMIT")."\" class=\"button\"></td>
		</tr>
		";
	
	echo "</table></form>";
	
	if (isset($_GET["directcall"]) and count($request)<count($input))
	{
		$request = array();
		echo GetMessage("BX_WSTMPL_ERROR_NOTENOUGHTPARAMS");
	}
	
	if (isset($_GET["directcall"])) 
	{
		echo "<b>".GetMessage("BX_WSTMPL_RESULT")."</b><br>";
		if (count($request)==count($input) and class_exists($getclass))
		{
			$object = new $getclass;
			$result = call_user_func_array(array($object, $getop), $request);
			
			$xml = CXMLCreator::encodeValueLight("result", $result);			
			$xml_str = $xml->getXML();			
			global $APPLICATION;
			$APPLICATION->RestartBuffer();
			
			$payload = CXMLCreator::getXMLHeader().$xml_str;
			header("Pragma: no-cache");
			header( "SOAPServer: BITRIX SOAP" );
	        header( "Content-Type: text/xml; charset=\"UTF-8\"" );
	        header( "Content-Length: " . strlen($payload));
	        echo $payload;
	        die();
		} 
		else
		{
			echo GetMessage("BX_WSTMPL_ERROR_UNKNOWN");
		}
	}
?>

</div>

</body>
</html>
