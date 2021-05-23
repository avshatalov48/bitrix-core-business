<?
	if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	if (!isset($arParams["WSDESCR"]))
	{
		echo GetMessage("BX_WSTMPL_ERROR_NOWSDESCR");
		die();
	}

	$bDesignMode = $GLOBALS["APPLICATION"]->GetShowIncludeAreas() && is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAdmin();
	if ($bDesignMode)
	{
		require_once($_SERVER["DOCUMENT_ROOT"].$templateFolder."/template.designer.php");
		return;
	}

	if (isset($_GET["op"]) and isset($_GET["class"]))
	{
		require_once($_SERVER["DOCUMENT_ROOT"].$templateFolder."/template.tester.php");
		die();
	}

	$templateDataSource = $arParams["WSDESCR"];
	$wsName = $arParams["WEBSERVICE_NAME"];
	$wsClass = $arParams["WEBSERVICE_CLASS"];
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
		hr { margin-top: 10px; margin-right: 20px; }
		.heading1 { color: #ffffff; font-family: Tahoma; font-size: 26px; font-weight: normal; background-color: #003366; margin-top: 0px; margin-bottom: 0px; margin-left: -30px; padding-top: 10px; padding-bottom: 3px; padding-left: 15px; width: 100%; }
</style>
</head>
<body>
<div id="content">

<p class="heading1"><?=GetMessage("BX_WSTMPL_WEBSERVICE").$wsName;?></p><br>

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

<span>
	<p class="intro">
		<?=GetMessage("BX_WSTMPL_WSDLDESCPRE");?>
		<a href="?wsdl"><?=GetMessage("BX_WSTMPL_WSDLDESC");?></a>
	</p>
</span>

<?
if (in_array("TestComponent", get_class_methods($wsClass)) or
	in_array("testcomponent", get_class_methods($wsClass)))
{
	echo "
	<span>
		<p class=\"intro\">";
			echo GetMessage("BX_WSTMPL_INNERTESTPRE");
			echo "<a href=\"?test\">";
			echo GetMessage("BX_WSTMPL_INNERTEST");
			echo "</a>";
	echo "
		</p>
	</span>
	";
}

?>

<span>
<?
	if (!is_object($arParams["WSDESCR"]) or
		!is_array($arParams["WSDESCR"]->classes))
	{
		echo GetMessage("BX_WSTMPL_ERROR_NOMETHODS");
	}
	else
	{
		foreach ($arParams["WSDESCR"]->classes as $class => $arClass)
		{
			echo "<b>".GetMessage("BX_WSTMPL_CLASS")."</b>";
			echo $class;

			foreach ($arClass as $method => $params)
			{
				//echo '<pre> ********** '; print_r($params); echo '</pre>';

				//$methodDeclared = "<u>";
				$methodDeclared = "";
				if (isset($params["output"]))
				{
					reset($params['output']);
					if (count($params['output']) > 1)
					{
						$first = true;
						$methodDeclared .= "{<br />";
						foreach ($params["output"] as $pname => $pparam)
						{
							if ($first)
								$first = false;
							else
								$methodDeclared .= ",<br />";

							$methodDeclared .= "&nbsp;&nbsp;&nbsp;&nbsp;";
							$methodDeclared .= $pname;
							if (isset($pparam["arrType"])) $methodDeclared .= "[]";
							$methodDeclared .= ": <i>{$pparam[varType]}</i>";
							//$methodDeclared .= " ";
						}
						$methodDeclared .= "<br />}<br />";
					}
					else
					{
						list($pname, $pparam) = each($params['output']);
						//foreach ($params["output"] as $pname => $pparam) break;
						$methodDeclared .= $pname;
						if (isset($pparam["arrType"])) $methodDeclared .= "[]";
						$methodDeclared .= ": <i>{$pparam[varType]}</i>";
						$methodDeclared .= " ";
					}
				}

				$methodDeclared .= "<a href=\"?class={$class}&op={$method}\">";
				$methodDeclared .= $method."(";
				if (isset($params["input"]) and count($params["input"]))
				{
					foreach ($params["input"] as $pname => $pparam)
					{
						$varType = "";
						if (isset($pparam["varType"]))
							$varType = $pparam["varType"];

						$methodDeclared .= "<i>{$varType}</i>&nbsp;{$pname}";
						if (isset($pparam["arrType"])) $methodDeclared .= "[]";
						$methodDeclared .= ", ";
					}
					$methodDeclared = substr($methodDeclared, 0, strlen($methodDeclared) - 2);
				}
				$methodDeclared .= ");</a>";
				//$methodDeclared .= "</u>";
				$methodDeclared .= "<br>".$paramsDeclared;

				echo "<hr />".$methodDeclared;
//				echo "<li>".$methodDeclared
//					."</li><p/>";

				if (isset($params["description"]))
				{
					echo "<br><b>".GetMessage("BX_WSTMPL_DOC")."</b>";
					echo $params["description"];
				}
			}
		}
	}

?>
</span>

</div>

</body>
</html>
