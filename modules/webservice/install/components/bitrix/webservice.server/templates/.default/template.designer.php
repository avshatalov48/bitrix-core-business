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
?>
<style type="text/css">
		#WSContent { margin-left: 30px; font-size: 9pt; padding-bottom: 2em; }
		.heading1 { font-family: Tahoma; font-size: 20px; font-weight: normal; margin-top: 0px; margin-bottom: 0px; margin-left: -30px; padding-top: 10px; padding-bottom: 3px; padding-left: 15px; width: 100%; }
</style>

<div id="WSContent">

<p class="heading1"><?=GetMessage("BX_WSTMPL_WEBSERVICE").$wsName;?></p><br>

<table width="80%" border=1>

<tr>
	<td><?=GetMessage("BX_WSTMPL_NAMESPACE");?></td>
	<td><?=$templateDataSource->wstargetns;?>&nbsp;</td>
</tr>
<tr>
	<td><?=GetMessage("BX_WSTMPL_ENDPOINT");?></td>
	<td><?=$arParams["WSDESCR"]->wsendpoint;?>&nbsp;</td>
</tr>
<tr>
	<td><?=GetMessage("BX_WSTMPL_BSTYLE");?></td>
	<td>document/literal only</td>
</tr>

</table><br>

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
							$methodDeclared .= "{$pname}";
							if (isset($pparam["arrType"])) $methodDeclared .= "[]";
							$methodDeclared .= ": <i>{$pparam['varType']}</i>";
							//$methodDeclared .= " ";
						}
						$methodDeclared .= "<br />}<br />";
					}
					else
					{
						$pname = key($params['output']);
						$pparam = current($params['output']);
						$methodDeclared .= "{$pname}";
						if (isset($pparam["arrType"])) $methodDeclared .= "[]";
						$methodDeclared .= ": <i>{$pparam['varType']}</i>";
						$methodDeclared .= " ";
					}
				}

				//$methodDeclared .= "<a href=\"?class={$class}&op={$method}\">";
				$methodDeclared .= "<b>".$method."</b> (";
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
					$methodDeclared = mb_substr($methodDeclared, 0, mb_strlen($methodDeclared) - 2);
				}
				//$methodDeclared .= ");</a>";
				$methodDeclared .= ");";
				//$methodDeclared .= "</u>";
				$methodDeclared .= "<br />".$paramsDeclared;

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