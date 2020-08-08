<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
class Step0 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage("WW_STEP0"));
		$this->SetNextStep("step1");
		$this->SetStepID("step0");
		$this->SetFinishStep("install");
		$this->SetCancelStep("cancel");
	}

	function ShowStep()
	{
		$this->content = GetMessage("WW_STEP_DESCR");
	}
}

class Step1 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage("WW_STEP1"));
		$this->SetNextStep("step2");
		$this->SetStepID("step1");
		$this->SetFinishStep("install");
		$this->SetCancelStep("cancel");
		$dbSite = CSite::GetDefList();
		if($arSite = $dbSite->Fetch())
		{
			CModule::IncludeModule("sale");
			$arCurr = CSaleLang::GetByID($arSite["LID"]);
			$wizard = &$this->GetWizard();
			$wizard->SetDefaultVars(
				Array(
					"siteID" => $arSite["ID"],
					"orderEmail" => "order@".$arSite["SERVER_NAME"],
					"saveBasket" => 30,
					"currencyID" => $arCurr["CURRENCY"],
				)
			);
		}
	}

	function ShowStep()
	{
		CModule::IncludeModule("currency");
		$arSites = Array();
		//$dbSite = CSite::GetList($b="SORT", $o="ASC", Array("ACTIVE"=>"Y", "ID"=>"ru"));
		$dbSite = CSite::GetList($b="SORT", $o="ASC", Array("ACTIVE"=>"Y"));
		while($arSite = $dbSite -> Fetch())
		{
			$arSites[$arSite["ID"]] = $arSite["NAME"];
			if($arSite["DEF"]=="Y")
				$defSite = $arSite["ID"];
		}
		/*if(empty($arSites))
		{
			$dbSite = CSite::GetList($b="SORT", $o="ASC", Array("ACTIVE"=>"Y", "ID"=>"s1"));
			while($arSite = $dbSite -> Fetch())
			{
				$arSites[$arSite["ID"]] = $arSite["NAME"];
				if($arSite["DEF"]=="Y")
					$defSite = $arSite["ID"];
			}
		}*/

		$this->content = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/bitrix/wizards/bitrix/sale.install/styles.css\">";
		$this->content .= GetMessage("WW_STEP1_1").'<br /><table class="data-table">';
		$this->content .= "<tr><th>".GetMessage("WW_STEP1_2").":</th><td>".$this->ShowSelectField("siteID", $arSites)."</td></tr>";
		$this->content .= "<tr><th>".GetMessage("WW_STEP1_3")."</th><td>".$this->ShowInputField("text", "orderEmail", Array("size" => "20"))."</td></tr>";
		$this->content .= "<tr><th>".GetMessage("WW_STEP1_4")."</th><td>".$this->ShowInputField("text", "saveBasket", Array("size" => "10"))."</td></tr>";
		$dbCurrency = CCurrency::GetList($b="SORT", $o="ASC");
		while($arCurrency = $dbCurrency -> Fetch())
			$arCurrencies[$arCurrency["CURRENCY"]] = $arCurrency["CURRENCY"] ." (".$arCurrency["FULL_NAME"].")";
		$this->content .= "<tr><th>".GetMessage("WW_STEP1_5")."</th><td>".$this->ShowSelectField("currencyID", $arCurrencies)."</td></tr>";
		$this->content .= "</table>";
	}
}


class Step2 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage("WW_STEP2"));
		$this->SetPrevStep("step1");
		$this->SetNextStep("step3");
		$this->SetStepID("step2");
		$this->SetFinishStep("install");
		$this->SetCancelStep("cancel");
		CModule::IncludeModule("sale");
		$wizard = &$this->GetWizard();
		$siteID = $wizard->GetVar("siteID", true);
		$dbPersonType = CSalePersonType::GetList(Array("SORT"=>"ASC"), Array("ACTIVE" => "Y", "LID" => $siteID));
		while($arPersonType = $dbPersonType->Fetch())
			$arPersons[] = $arPersonType["ID"];

		$wizard->SetDefaultVars(
			Array(
				"personType" => $arPersons,
			)
		);

	}

	function OnPostForm()
	{
		$wizard = &$this->GetWizard();
		if ($wizard->IsNextButtonClick() || $wizard->IsFinishButtonClick())
		{
			$personType = $wizard->GetVar("personType");
			if (empty($personType))
				$this->SetError(GetMessage("WW_STEP2_1"), "personType[]");
		}
	}

	function ShowStep()
	{
		$wizard = &$this->GetWizard();
		$siteID = $wizard->GetVar("siteID");
		$personType = $wizard->GetVar("personType");
		CModule::IncludeModule("sale");

		$dbPersonType = CSalePersonType::GetList(Array("SORT"=>"ASC"), Array("LID" => $siteID));
		while($arPersonType = $dbPersonType->Fetch())
		{
			$arPersons[$arPersonType["ID"]] = $arPersonType["NAME"];
		}

		$this->content .= GetMessage("WW_STEP2_2")."<br />";
		foreach($arPersons as $k => $v)
		{
			$this->content .= $this->ShowCheckboxField("personType[]", $k, Array("id" => $k))." <label for=\"".$k."\">".htmlspecialcharsEx($v)."</label><br />";
		}

	}
}

class Step3 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage("WW_STEP3"));
		$this->SetPrevStep("step2");
		$this->SetNextStep("step4");
		$this->SetStepID("step3");
		$this->SetFinishStep("install");
		$this->SetCancelStep("cancel");

		CModule::IncludeModule("sale");
		$wizard = &$this->GetWizard();
		$siteID = $wizard->GetVar("siteID", true);
		$personType = $wizard->GetVar("personType", true);
		$dbPaySystem = CSalePaySystem::GetList(Array("SORT"=>"ASC"), Array("ACTIVE" => "Y", "LID" => $siteID));
		$i=-1;
		$arTmp = Array();
		if(empty($personType))
			$personType = Array();
		while($arPaySystem = $dbPaySystem->Fetch())
		{
			$dbPaySystemAction = CSalePaySystemAction::GetList(
				array("NAME" => "ASC"),
				array("PAY_SYSTEM_ID" => $arPaySystem["ID"])
			);
			while($arPaySystemAction = $dbPaySystemAction -> Fetch())
			{
				$i++;
				if(in_array($arPaySystemAction["PERSON_TYPE_ID"], $personType))
					$arTmp[$arPaySystemAction["PERSON_TYPE_ID"]][] = $arPaySystem["ID"];
			}
		}

		$wizard->SetDefaultVars(Array("paySystem" => $arTmp));
	}

	function OnPostForm()
	{

		$wizard = &$this->GetWizard();
		if ($wizard->IsNextButtonClick())
		{
			$paySystem = $wizard->GetVar("paySystem");
			$personType = $wizard->GetVar("personType");

			$bFound = false;
			foreach($personType as $v)
			{
				if(!empty($paySystem[$v]))
					$bFound = true;
			}
			if(!$bFound)
				$wizard->SetCurrentStep("step5");
		}
	}

	function ShowStep()
	{
		$personType = Array();
		$wizard = &$this->GetWizard();
		$siteID = $wizard->GetVar("siteID");
		$personType = $wizard->GetVar("personType");
		CModule::IncludeModule("sale");
		$arPaySystems = Array();
		$arPersons = Array();

		$dbPaySystem = CSalePaySystem::GetList(Array("SORT"=>"ASC"), Array("ACTIVE" => "Y", "LID" => $siteID));
		while($arPaySystem = $dbPaySystem->Fetch())
		{
			$arPaySystems[$arPaySystem["ID"]] = $arPaySystem["NAME"];
		}

		$dbPersonType = CSalePersonType::GetList(Array("SORT"=>"ASC"), Array("ACTIVE" => "Y", "LID" => $siteID));
		while($arPersonType = $dbPersonType->GetNext())
		{
			$arPersons[$arPersonType["ID"]] = $arPersonType["NAME"];
		}

		$this->content .= GetMessage("WW_STEP3_1")."<br />";
		foreach($personType as $v1)
		{
			$this->content .= "<b>".$arPersons[$v1]."</b><br />";
			foreach($arPaySystems as $k => $v)
			{

				$this->content .= $this->ShowCheckboxField("paySystem[".$v1."][]", $k, Array("id" => $v1."_".$k))." <label for=\"".$v1."_".$k."\">".htmlspecialcharsEx($v)."</label><br />";

			}
			$this->content  .= "<br />";
		}

	}
}

class Step4 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage("WW_STEP4"));
		$this->SetPrevStep("step3");
		$this->SetNextStep("step5");
		$this->SetStepID("step4");
		$this->SetFinishStep("install");
		$this->SetCancelStep("cancel");

		$paySystem = Array();
		$personType = Array();
		$wizard = &$this->GetWizard();
		$paySystem = $wizard->GetVar("paySystem");
		$personType = $wizard->GetVar("personType");
		CModule::IncludeModule("sale");
		if(empty($paySystem))
			$paySystem = Array();
		foreach($paySystem as $k => $v)
		{
			if(!empty($v))
			{
				foreach($v as $v1)
				{
					$dbPaySystemAction = CSalePaySystemAction::GetList(
						array("NAME" => "ASC"),
						array("PAY_SYSTEM_ID" => $v1, "PERSON_TYPE_ID" => $k)
					);
					if($arPaySystemAction = $dbPaySystemAction -> Fetch())
					{
						$defVars["paySystemPopup"][$v1][$k] = $arPaySystemAction["NEW_WINDOW"];
						$arCorrespondence = CSalePaySystemAction::UnSerializeParams($arPaySystemAction["PARAMS"]);
						foreach ($arCorrespondence as $key => $value)
						{
							$defVars[$v1."_".$key."_".$k]  = $value["TYPE"];
							if($value["TYPE"] <> '')
								$defVars["VALUE1_".$v1."_".$key."_".$k] = str_replace("'", "\'", $value["VALUE"]);
							else
								$defVars["VALUE2_".$v1."_".$key."_".$k] = str_replace("'", "\'", $value["VALUE"]);
						}
					}
				}
			}
		}

		$wizard->SetDefaultVars($defVars);
	}

	function OnPostForm()
	{
		$wizard = &$this->GetWizard();
		$varNames = $wizard->GetVar("stamp_img");
		if (is_array($varNames))
		{
			foreach($varNames as $varName)
			{
				$imgID = $this->SaveFile($varName."_img");
				if(intval($imgID)>0)
					$wizard->SetVar($varName, CFile::GetPath($imgID));
				else
					$wizard->SetVar($varName, $wizard->GetDefaultVar($varName));
			}
		}
	}

	function ShowStep()
	{
		$wizard = &$this->GetWizard();
		$paySystem = $wizard->GetVar("paySystem");
		$siteID = $wizard->GetVar("siteID");
		$personType = $wizard->GetVar("personType");
		CModule::IncludeModule("sale");
		function LocalGetPSActionParams($fileName)
		{
			$arPSCorrespondence = array();

			if (file_exists($fileName) && is_file($fileName))
				include($fileName);

			return $arPSCorrespondence;
		}

		$dbPaySystem = CSalePaySystem::GetList(Array("SORT"=>"ASC"), Array("ACTIVE" => "Y", "LID" => $siteID));
		while($arPaySystem = $dbPaySystem->GetNext())
		{
			$arPaySystems[$arPaySystem["ID"]] = $arPaySystem["NAME"];
		}

		$dbPersonType = CSalePersonType::GetList(Array("SORT"=>"ASC"), Array("ACTIVE" => "Y", "LID" => $siteID));
		while($arPersonType = $dbPersonType->GetNext())
		{
			$arPersons[$arPersonType["ID"]] = $arPersonType["NAME"];
		}
		$arFieldsList["USER"] = Array(
				"ID" => GetMessage("SPS_USER_ID"),
				"LOGIN" => GetMessage("SPS_USER_LOGIN"),
				"NAME" => GetMessage("SPS_USER_NAME"),
				"LAST_NAME" => GetMessage("SPS_USER_LAST_NAME"),
				"EMAIL" => "EMail",
				"LID" => GetMessage("SPS_USER_SITE"),
				"PERSONAL_PROFESSION" => GetMessage("SPS_USER_PROF"),
				"PERSONAL_WWW" => GetMessage("SPS_USER_WEB"),
				"PERSONAL_ICQ" => GetMessage("SPS_USER_ICQ"),
				"PERSONAL_GENDER" => GetMessage("SPS_USER_SEX"),
				"PERSONAL_FAX" => GetMessage("SPS_USER_FAX"),
				"PERSONAL_MOBILE" => GetMessage("SPS_USER_PHONE"),
				"PERSONAL_STREET" => GetMessage("SPS_USER_ADDRESS"),
				"PERSONAL_MAILBOX" => GetMessage("SPS_USER_POST"),
				"PERSONAL_CITY" => GetMessage("SPS_USER_CITY"),
				"PERSONAL_STATE" => GetMessage("SPS_USER_STATE"),
				"PERSONAL_ZIP" => GetMessage("SPS_USER_ZIP"),
				"PERSONAL_COUNTRY" => GetMessage("SPS_USER_COUNTRY"),
				"WORK_COMPANY" => GetMessage("SPS_USER_COMPANY"),
				"WORK_DEPARTMENT" => GetMessage("SPS_USER_DEPT"),
				"WORK_POSITION" => GetMessage("SPS_USER_DOL"),
				"WORK_WWW" => GetMessage("SPS_USER_COM_WEB"),
				"WORK_PHONE" => GetMessage("SPS_USER_COM_PHONE"),
				"WORK_FAX" => GetMessage("SPS_USER_COM_FAX"),
				"WORK_STREET" => GetMessage("SPS_USER_COM_ADDRESS"),
				"WORK_MAILBOX" => GetMessage("SPS_USER_COM_POST"),
				"WORK_CITY" => GetMessage("SPS_USER_COM_CITY"),
				"WORK_STATE" => GetMessage("SPS_USER_COM_STATE"),
				"WORK_ZIP" => GetMessage("SPS_USER_COM_ZIP"),
				"WORK_COUNTRY" => GetMessage("SPS_USER_COM_COUNTRY"),
			);

		$arFieldsList["ORDER"] = Array(
				"ID" => GetMessage("SPS_ORDER_ID"),
				"DATE_INSERT" => GetMessage("SPS_ORDER_DATETIME"),
				"DATE_INSERT_DATE" => GetMessage("SPS_ORDER_DATE"),
				"SHOULD_PAY" => GetMessage("SPS_ORDER_PRICE"),
				"CURRENCY" => GetMessage("SPS_ORDER_CURRENCY"),
				"PRICE" => GetMessage("SPS_ORDER_SUM"),
				"LID" => GetMessage("SPS_ORDER_SITE"),
				"PRICE_DELIVERY" => GetMessage("SPS_ORDER_PRICE_DELIV"),
				"DISCOUNT_VALUE" => GetMessage("SPS_ORDER_DESCOUNT"),
				"USER_ID" => GetMessage("SPS_ORDER_USER_ID"),
				"PAY_SYSTEM_ID" => GetMessage("SPS_ORDER_PS"),
				"DELIVERY_ID" => GetMessage("SPS_ORDER_DELIV"),
				"TAX_VALUE" => GetMessage("SPS_ORDER_TAX"),
			);

		foreach($personType as $personID)
		{
			$dbOrderProps = CSaleOrderProps::GetList(
					array("SORT" => "ASC", "NAME" => "ASC"),
					array("PERSON_TYPE_ID" => $personID),
					false,
					false,
					array("ID", "CODE", "NAME", "TYPE", "SORT", "PERSON_TYPE_ID")
				);
			while ($arOrderProps = $dbOrderProps->GetNext())
			{
				$arFieldsList["PROPERTY"][$arOrderProps["PERSON_TYPE_ID"]][(($arOrderProps["CODE"] <> '') ? $arOrderProps["CODE"] : $arOrderProps["ID"])] = $arOrderProps["NAME"];

				if ($arOrderProps["TYPE"] == "LOCATION")
				{
					$arFieldsList["PROPERTY"][$arOrderProps["PERSON_TYPE_ID"]][(($arOrderProps["CODE"] <> '') ? $arOrderProps["CODE"] : $arOrderProps["ID"])."_COUNTRY"] = $arOrderProps["NAME"]." (".GetMessage("SPS_JCOUNTRY").")";
					$arFieldsList["PROPERTY"][$arOrderProps["PERSON_TYPE_ID"]][(($arOrderProps["CODE"] <> '') ? $arOrderProps["CODE"] : $arOrderProps["ID"])."_CITY"] = $arOrderProps["NAME"]." (".GetMessage("SPS_JCITY").")";
				}
			}
		}
		?>
		<script type="text/javascript">
		var arUserFieldsList = new Array();
		var arUserFieldsNameList = new Array();
		var arOrderFieldsList = new Array();
		var arOrderFieldsNameList = new Array();
		var arPropFieldsList = new Array();
		var arPropFieldsNameList = new Array();

		<?
		$i = -1;
		foreach($arFieldsList["USER"] as $k => $v)
		{
			$i++;
			?>
			arUserFieldsList[<?=$i?>] = "<?=$k?>";
			arUserFieldsNameList[<?=$i?>] = "<?=$v?>";
			<?
		}
		$i = -1;
		foreach($arFieldsList["ORDER"] as $k => $v)
		{
			$i++;
			?>
			arOrderFieldsList[<?=$i?>] = "<?=$k?>";
			arOrderFieldsNameList[<?=$i?>] = "<?=$v?>";
			<?
		}
		$i = -1;
		foreach($arFieldsList["PROPERTY"] as $k => $v)
		{
			?>
			arPropFieldsList[<?=$k?>] = new Array();
			arPropFieldsNameList[<?=$k?>] = new Array();
			<?
			foreach($v as $k1 => $v1)
			{
				$i++;
				?>
				arPropFieldsList[<?=$k?>][<?=$i?>] = "<?=$k1?>";
				arPropFieldsNameList[<?=$k?>][<?=$i?>] = "<?=$v1?>";
				<?
			}
		}
		?>
		function changeVariantList(id, value, ind)
		{
			var oValue1 = document.getElementById("VALUE1_" + id);
			var oValue2 = document.getElementById("VALUE2_" + id);

			var value1_length = oValue1.length;
			while (value1_length > 0)
			{
				value1_length--;
				oValue1.options[value1_length] = null;
			}
			value1_length = 0;

			if (value == "USER")
			{
				oValue2.style["display"] = "none";
				oValue1.style["display"] = "block";

				for (i = 0; i < arUserFieldsList.length; i++)
				{
					var newoption = new Option(arUserFieldsNameList[i], arUserFieldsList[i], false, false);
					oValue1.options[value1_length] = newoption;
					value1_length++;
				}
			}
			else
			{
				if (value == "ORDER")
				{
					oValue2.style["display"] = "none";
					oValue1.style["display"] = "block";

					for (i = 0; i < arOrderFieldsList.length; i++)
					{
						var newoption = new Option(arOrderFieldsNameList[i], arOrderFieldsList[i], false, false);
						oValue1.options[value1_length] = newoption;
						value1_length++;
					}
				}
				else
				{
					if (value == "PROPERTY")
					{
						oValue2.style["display"] = "none";
						oValue1.style["display"] = "block";
						for (i = 0; i < arPropFieldsList[ind].length; i++)
						{
							var newoption = new Option(arPropFieldsNameList[ind][i], arPropFieldsList[ind][i], false, false);
							oValue1.options[value1_length] = newoption;
							value1_length++;
						}
					}
					else
					{
						oValue1.style["display"] = "none";
						oValue2.style["display"] = "block";
						oValue2.value = "";
					}
				}
			}
		}

		function ShowSet(id, action)
		{
			if(action == "show")
			{
				document.getElementById(id).style['display'] = "block";
				document.getElementById(id+"-set").style['display'] = "none";
				document.getElementById(id+"-unset").style['display'] = "block";
			}
			else if(action == "hide")
			{
				document.getElementById(id).style['display'] = "none";
				document.getElementById(id+"-set").style['display'] = "block";
				document.getElementById(id+"-unset").style['display'] = "none";
			}
		}
		</script>
		<?

		$this->content .= GetMessage("WW_STEP4_1")."<br /><br />";
		$wizard = &$this->GetWizard();
		foreach($paySystem as $k => $v)
		{
			if(in_array($k, $personType))
			{
				foreach($v as $v1)
				{
					$dbPaySystemAction = CSalePaySystemAction::GetList(
						array("NAME" => "ASC"),
						array("PAY_SYSTEM_ID" => $v1)
					);
					if($arPaySystemAction = $dbPaySystemAction -> Fetch())
					{
						$adit = Array();
						$this->content .= "<b>".$arPaySystems[$v1]."</b> - ".$arPersons[$k]."<br />";

						$arPSCorrespondence = LocalGetPSActionParams($_SERVER["DOCUMENT_ROOT"].$arPaySystemAction["ACTION_FILE"]."/.description.php");

						$this->content .= "<div id=\"".$v1."-".$k."-set\"><a href=\"javascript:ShowSet('".$v1."-".$k."', 'show')\">".GetMessage("WW_STEP4_2")."</a></div>";
						$this->content .= "<div id=\"".$v1."-".$k."-unset\" style=\"display:none;\"><a href=\"javascript:ShowSet('".$v1."-".$k."', 'hide')\">".GetMessage("WW_STEP4_3")."</a></div>";
						$this->content .= "<div id=\"".$v1."-".$k."\" style=\"display: none;\">";
						$this->content .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"/bitrix/wizards/bitrix/sale.install/styles.css\">";
						$this->content .= "<table class=\"data-table\">";
						$this->content .= "<tr><th>".GetMessage("WW_STEP4_4")."</th><td>".$this->ShowCheckboxField("paySystemPopup[".$v1."][".$k."]", "Y")."</td></tr>";
						foreach($arPSCorrespondence as $k2 => $v2)
						{
							$arFList = Array();
							$this->content .= "<tr><th>";
							$this->content .= $v2["NAME"]."<br /><small>".$v2["DESCR"]."</small>";
							$this->content .= "</th><td>";
							$arTypes = Array("PROPERTY" => GetMessage("WW_STEP4_5"), "ORDER" => GetMessage("WW_STEP4_6"), "USER" => GetMessage("WW_STEP4_7"), ""=> GetMessage("WW_STEP4_8"));
							$this->content .= $this->ShowSelectField($v1."_".$k2."_".$k, $arTypes, Array("onChange" => "changeVariantList('".$v1."_".$k2."_".$k."', this.value, '".$k."')", "id" => $v1."_".$k2."_".$k));
							${$v1."_".$k2."_".$k} = $wizard->GetVar($v1."_".$k2."_".$k, true);

							$this->content .= "<br />";
							if(${$v1."_".$k2."_".$k} <> '')
							{
								if(${$v1."_".$k2."_".$k} == "PROPERTY")
									$arFList = $arFieldsList["PROPERTY"][$k];
								else
									$arFList = $arFieldsList[${$v1."_".$k2."_".$k}];
								$this->content .= $this->ShowSelectField("VALUE1_".$v1."_".$k2."_".$k, $arFList, Array("id" => "VALUE1_".$v1."_".$k2."_".$k));
								$this->content .= $this->ShowInputField("text", "VALUE2_".$v1."_".$k2."_".$k, Array("id" => "VALUE2_".$v1."_".$k2."_".$k, "style" => "display:none;", "size" => "20"));
							}
							else
							{
								$this->content .= $this->ShowSelectField("VALUE1_".$v1."_".$k2."_".$k, Array(), Array("id" => "VALUE1_".$v1."_".$k2."_".$k, "style" => "display:none;"));
								if($k2 == "PATH_TO_STAMP")
								{
									$this->content .= $this->ShowHiddenField("stamp_img[]", "VALUE2_".$v1."_".$k2."_".$k);
									$this->content .= $this->ShowFileField("VALUE2_".$v1."_".$k2."_".$k."_img", Array("id" => "VALUE2_".$v1."_".$k2."_".$k, "size" => "20"));
									$img = $wizard->GetVar("VALUE2_".$v1."_".$k2."_".$k, true);
									if($img <> '')
									{
										$this->content .= "<br />".CFile::ShowImage($img, 50, 50, "border=\"0\"", "", true)."<br />";
									}
								}
								else
									$this->content .= $this->ShowInputField("text", "VALUE2_".$v1."_".$k2."_".$k, Array("id" => "VALUE2_".$v1."_".$k2."_".$k, "size" => "20"));
							}
							$this->content .= "</td></tr>";
						}
						$this->content .= "</table></div><br />";
					}
				}
			}
		}
	}
}

class Step5 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage("WW_STEP5"));
		$this->SetSubTitle("");
		$this->SetPrevStep("step4");
		$this->SetNextStep("step6");
		$this->SetStepID("step5");
		$this->SetFinishStep("install");
		$this->SetCancelStep("cancel");

		$wizard = &$this->GetWizard();
		$dbSaleManagerGroups = $GLOBALS["APPLICATION"]->GetGroupRightList(array("MODULE_ID" => "sale", "G_ACCESS" => "U"));
		while ($arSaleManagerGroup = $dbSaleManagerGroups->Fetch())
			$defVars["groupID"][] = intval($arSaleManagerGroup["GROUP_ID"]);

		$wizard->SetDefaultVars($defVars);
	}

	function OnPostForm()
	{
		$wizard = &$this->GetWizard();
		if ($wizard->IsNextButtonClick() || $wizard->IsFinishButtonClick())
		{
			$groupID = $wizard->GetVar("groupID");
			if (empty($groupID))
			{
				$this->SetError(GetMessage("WW_STEP5_1")."!!!", "groupID[]");
			}
		}

		if ($wizard->IsPrevButtonClick())
		{
			$paySystem = $wizard->GetVar("paySystem");
			$personType = $wizard->GetVar("personType");

			$bFound = false;
			foreach($personType as $v)
			{
				if(!empty($paySystem[$v]))
					$bFound = true;
			}
			if(!$bFound)
				$wizard->SetCurrentStep("step3");
		}


	}

	function ShowStep()
	{
		$dbUGroup = CGroup::GetList($b="c_sort", $o="ASC", Array("ACTIVE" => "Y"));
		while($arUGroup = $dbUGroup->Fetch())
		{
			if(!in_array($arUGroup["ID"], Array(1, 2)))
				$arGroups[$arUGroup["ID"]] = $arUGroup["NAME"];
		}
		$this->content .= GetMessage("WW_STEP5_2")."<br />".$this->ShowSelectField("groupID[]", $arGroups, Array("multiple" => "multiple", "size" => "6"));
	}
}

class Step6 extends CWizardStep
{
	function InitStep()
	{
		$groupID = Array();
		$this->SetTitle(GetMessage("WW_STEP6"));
		$this->SetSubTitle("");
		$this->SetNextStep("step7");
		$this->SetPrevStep("step5");
		$this->SetFinishStep("final");
		$this->SetStepID("step6");
		$this->SetCancelStep("final");

		$wizard = &$this->GetWizard();
		$siteID = $wizard->GetVar("siteID");
		$groupID = $wizard->GetVar("groupID");
		if(empty($groupID))
			$groupID = Array();

		$arPermType = Array("PERM_VIEW", "PERM_CANCEL", "PERM_MARK", "PERM_DEDUCTION", "PERM_DELIVERY", "PERM_PAYMENT", "PERM_STATUS", "PERM_STATUS_FROM", "PERM_UPDATE", "PERM_DELETE");
		//$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => $siteID), false, false, array("ID", "SORT", "LID", "NAME", "DESCRIPTION"));
		$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array(), false, false, array("ID", "SORT", "LID", "NAME", "DESCRIPTION"));
		while($arStatus = $dbStatus ->Fetch())
		{
			foreach($groupID as $gr)
			{
				$dbPermsMatrix = CSaleStatus::GetPermissionsList(array(), array("STATUS_ID" => $arStatus["ID"], "GROUP_ID" => $gr), false, false, array());
				while ($arPM = $dbPermsMatrix->Fetch())
				{
					foreach($arPermType as $perm)
					{
						$defVars["perm[".$arStatus["ID"]."][".$arPM["GROUP_ID"]."][".$perm."]"] = $arPM[$perm];
					}
				}
			}
		}
		$wizard->SetDefaultVars($defVars);
	}

	function ShowStep()
	{
		CModule::IncludeModule("sale");
		$wizard = &$this->GetWizard();
		$groupID = $wizard->GetVar("groupID");
		$siteID = $wizard->GetVar("siteID");
		$arGr = Array();

		$dbUGroup = CGroup::GetList($b="c_sort", $o="ASC", Array("ACTIVE" => "Y"));
		while($arUGroup = $dbUGroup->Fetch())
			$arGroups[$arUGroup["ID"]] = $arUGroup["NAME"];

		$arPermType = Array("PERM_VIEW", "PERM_CANCEL", "PERM_MARK", "PERM_DEDUCTION", "PERM_DELIVERY", "PERM_PAYMENT", "PERM_STATUS", "PERM_STATUS_FROM", "PERM_UPDATE", "PERM_DELETE");
		if(!empty($groupID))
		{
			foreach($groupID as $v)
			{
				$perm = $GLOBALS["APPLICATION"]->GetGroupRight("sale", $v);
				if($v != 1 && $v != 2 && $perm != "W")
					$arGr[] = $v;
			}
		}

		//$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => $siteID), false, false, array("ID", "SORT", "LID", "NAME", "DESCRIPTION"));
		$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array(), false, false, array("ID", "SORT", "LID", "NAME", "DESCRIPTION"));
		$this->content .= GetMessage("WW_STEP6_1")."<br /><br />";
		while($arStatus = $dbStatus ->GetNext())
		{

			$this->content .= "<b>".$arStatus["NAME"]."</b>";
			$this->content .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"/bitrix/wizards/bitrix/sale.install/styles.css\">";
			$this->content .= "<table class=\"data-table\">";
			$this->content .= "<tr>";
			$this->content .= "<th>".GetMessage("WW_STEP6_2")."</th>";
			foreach($arGr as $v)
			{
				$this->content .= "<th>".$arGroups[$v]."</th>";
			}
			$this->content .= "</tr>";
			foreach($arPermType as $v)
			{
				$this->content .= "<tr>";
				$this->content .= "<th>".GetMessage("SSEN_".$v)."</th>";
				foreach($arGr as $v1)
				{
					$this->content .= "<td>".$this->ShowCheckboxField("perm[".$arStatus["ID"]."][".$v1."][".$v."]", "Y")."</td>";
				}
				$this->content .= "</tr>";
			}
			$this->content .= "</table><br /><br />";

		}

	}
}

class Step7 extends CWizardStep
{
	function InitStep()
	{

		$this->SetTitle(GetMessage("WW_STEP7_5"));
		$this->SetSubTitle("");
		$this->SetNextStep("step8");
		$this->SetPrevStep("step6");
		$this->SetStepID("step7");
		$this->SetFinishStep("install");
		$this->SetCancelStep("cancel");

		$wizard = &$this->GetWizard();
		$defVars = array(
			"1C_GROUP_PERMISSIONS" => explode(",", COption::GetOptionString("catalog", "1C_GROUP_PERMISSIONS")),
			"1C_ELEMENT_ACTION" => COption::GetOptionString("catalog", "1C_ELEMENT_ACTION", "D"),
			"1C_SECTION_ACTION" => COption::GetOptionString("catalog", "1C_SECTION_ACTION", "D"),
			"1C_INTERVAL" => COption::GetOptionString("catalog", "1C_INTERVAL", "30"),
			"1C_FILE_SIZE_LIMIT" => COption::GetOptionString("catalog", "1C_FILE_SIZE_LIMIT", 200*1024),
			"1C_EXPORT_PAYED_ORDERS" => COption::GetOptionString("sale", "1C_EXPORT_PAYED_ORDERS"),
			"1C_EXPORT_ALLOW_DELIVERY_ORDERS" => COption::GetOptionString("sale", "1C_EXPORT_ALLOW_DELIVERY_ORDERS"),
			"1C_EXPORT_FINAL_ORDERS" => COption::GetOptionString("sale", "1C_EXPORT_FINAL_ORDERS"),
			"1C_FINAL_STATUS_ON_DELIVERY" => COption::GetOptionString("sale", "1C_FINAL_STATUS_ON_DELIVERY", "F")
			);

		$wizard->SetDefaultVars($defVars);
	}

	function OnPostForm()
	{
		$wizard = &$this->GetWizard();
		if(LANGUAGE_ID == "en")
			$wizard->SetCurrentStep("step8");
		if ($wizard->IsNextButtonClick() || $wizard->IsFinishButtonClick())
		{
			$login = $wizard->GetVar("login");
			$password = $wizard->GetVar("password");
			$password_rep = $wizard->GetVar("password_rep");
			$email = $wizard->GetVar("email");
			if($login <> '')
			{
				if($password != $password_rep)
				{
					$this->SetError(GetMessage("WW_STEP7_1"), "password");
				}
				if(mb_strlen($password) < 6)
				{
					$this->SetError(GetMessage("WW_STEP7_2"), "password");
				}
				if(mb_strlen($login) < 3)
				{
					$this->SetError(GetMessage("WW_STEP7_3"), "login");
				}
				if(mb_strlen($email) < 5)
				{
					$this->SetError(GetMessage("WW_STEP7_4"), "email");
				}
			}
		}
	}

	function ShowStep()
	{
		CModule::IncludeModule("sale");
		$wizard = &$this->GetWizard();
		$siteID = $wizard->GetVar("siteID");
		$arStatuses = Array("" => GetMessage("SALE_1C_NO"));
		$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID));
		while ($arStatus = $dbStatus->Fetch())
		{
			$arStatuses[$arStatus["ID"]] = "[".$arStatus["ID"]."] ".$arStatus["NAME"];
		}

		$arUGroupsEx = Array();
		$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
		while($arUGroups = $dbUGroups -> Fetch())
		{
			$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
		}

		$arAction = array(
			"N" => GetMessage("CAT_1C_NONE"),
			"A" => GetMessage("CAT_1C_DEACTIVATE"),
			"D" => GetMessage("CAT_1C_DELETE"),
		);

		$arAllOptions = array(
				array("1C_GROUP_PERMISSIONS", GetMessage("CAT_1C_GROUP_PERMISSIONS"), "-", Array("mlist", 5, $arUGroupsEx)),
				array("1C_ELEMENT_ACTION", GetMessage("CAT_1C_ELEMENT_ACTION"), "D", Array("list", $arAction)),
				array("1C_SECTION_ACTION", GetMessage("CAT_1C_SECTION_ACTION"), "D", Array("list", $arAction)),
				array("1C_INTERVAL", GetMessage("CAT_1C_INTERVAL"), "30", Array("text", 20)),
				array("1C_FILE_SIZE_LIMIT", GetMessage("CAT_1C_FILE_SIZE_LIMIT"), 200*1024, Array("text", 20)),
				array("1C_EXPORT_PAYED_ORDERS", GetMessage("SALE_1C_EXPORT_PAYED_ORDERS"), "", Array("checkbox")),
				array("1C_EXPORT_ALLOW_DELIVERY_ORDERS", GetMessage("SALE_1C_EXPORT_ALLOW_DELIVERY_ORDERS"), "", Array("checkbox")),
				array("1C_EXPORT_FINAL_ORDERS", GetMessage("SALE_1C_EXPORT_FINAL_ORDERS"), "", Array("list", $arStatuses)),
				array("1C_FINAL_STATUS_ON_DELIVERY", GetMessage("SALE_1C_FINAL_STATUS_ON_DELIVERY"), "F", Array("list", $arStatuses)),
			);
		$this->content = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/bitrix/wizards/bitrix/sale.install/styles.css\">";
		$this->content .= '<table class="data-table">';
		$this->content .= "<tr><th>".GetMessage("WW_STEP7_6")."</th><td>".$this->ShowSelectField("orderWork", Array("site" => GetMessage("WW_STEP7_7"), "1c" => GetMessage("WW_STEP7_8")))."</td></tr>";

		$this->content .= "<tr><th colspan=\"2\"><b>".GetMessage("WW_STEP7_9")."</b><br /><small>".GetMessage("WW_STEP7_10")."</small></th></tr>";
		$this->content .= "<tr><th>".GetMessage("WW_STEP7_16")."</th><td>".$this->ShowSelectField("1C_GROUP_PERMISSIONS[]", $arUGroupsEx, Array("multiple" => "multiple", "size" => 5))."</td></tr>";
		$this->content .= "<tr><th>".GetMessage("WW_STEP7_11")."<br /><small>".GetMessage("WW_STEP7_12")."</small></th><td>".$this->ShowInputField("text", "login", Array("size" => "20", "maxlength" => "255"))."</td></tr>";
		$this->content .= "<tr><th>".GetMessage("WW_STEP7_13")."<br /><small>".GetMessage("WW_STEP7_14")."</small></th><td>".$this->ShowInputField("password", "password", Array("size" => "20", "maxlength" => "255"))."</td></tr>";
		$this->content .= "<tr><th>".GetMessage("WW_STEP7_15")."</th><td>".$this->ShowInputField("password", "password_rep", Array("size" => "20", "maxlength" => "255"))."</td></tr>";
		$this->content .= "<tr><th>Email</th><td>".$this->ShowInputField("text", "email", Array("size" => "20", "maxlength" => "255"))."</td></tr>";

		$this->content .= "</table>";

	}
}

class Step8 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage("WW_STEP8"));
		$this->SetPrevStep("step7");
		$this->SetNextStep("install");
		$this->SetStepID("step8");
		$this->SetFinishStep("install");
		$this->SetCancelStep("cancel");

		CModule::IncludeModule("sale");
		$wizard = &$this->GetWizard();
		$siteID = $wizard->GetVar("siteID");
		$dbDelivery = CSaleDeliveryHandler::GetList(Array("SORT" => "ASC"), Array("SITE_ID" => $siteID, "ACTIVE" => "Y"));
		while($arDelivery = $dbDelivery->Fetch())
		{
			$arDeliveries[] = $arDelivery["SID"]."_new";
		}

		$dbDelivery = CSaleDelivery::GetList(Array("SORT" => "ASC"), Array("LID" => $siteID, "ACTIVE" => "Y"));
		while($arDelivery = $dbDelivery->Fetch())
		{
			$arDeliveries[] = $arDelivery["ID"];
		}

		$wizard->SetDefaultVars(
			Array(
				"delivery" => $arDeliveries,
				"location" => COption::GetOptionInt("sale", "location", "", $siteID),
			)
		);

	}

	function OnPostForm()
	{
	}

	function ShowStep()
	{
		$wizard = &$this->GetWizard();
		$siteID = $wizard->GetVar("siteID", true);
		CModule::IncludeModule("sale");
		$arDeliveries = Array();

		$dbDelivery = CSaleDeliveryHandler::GetList(Array("SORT" => "ASC"), Array("SITE_ID" => $siteID, "ACTIVE" => "Y"));
		while($arDelivery = $dbDelivery->Fetch())
		{
			$arDeliveries[$arDelivery["SID"]."_new"] = $arDelivery["NAME"]." ".GetMessage("WW_STEP8_1");
		}

		$dbDelivery = CSaleDelivery::GetList(Array("SORT" => "ASC"), Array("LID" => $siteID, "ACTIVE" => "Y"));
		while($arDelivery = $dbDelivery->Fetch())
		{
			$arDeliveries[$arDelivery["ID"]] = $arDelivery["NAME"];
		}

		$location = Array();
		$dbLocationList = CSaleLocation::GetList(
					array("SORT" => "ASC", "COUNTRY_NAME_LANG" => "ASC", "CITY_NAME_LANG" => "ASC"),
					array("LID" => LANGUAGE_ID),
					false,
					false,
					array()
		);
		while($arLocation = $dbLocationList->Fetch())
		{
			$location[$arLocation["ID"]] = $arLocation["COUNTRY_NAME"].($arLocation["CITY_NAME"] <> '' ? " - ".$arLocation["CITY_NAME"] : "");
		}
		$this->content .= GetMessage("WW_STEP8_2")."<br />";
		$this->content .= $this->ShowSelectField("location", $location);

		$this->content .= "<br /><br />".GetMessage("WW_STEP8_3")."<br /><br />";

		foreach($arDeliveries as $k => $v)
		{
			$this->content .= $this->ShowCheckboxField("delivery[]", $k, Array("id" => $k))." <label for=\"".$k."\">".htmlspecialcharsEx($v)."</label><br />";
		}
	}
}

class Install extends CWizardStep
{

	function InitStep()
	{
		$this->SetTitle(GetMessage("WW_1"));
		$this->SetStepID("install");
		$this->SetCancelStep("cancel");
		$this->SetNextStep("final");
		$this->SetPrevStep("step8");
	}

	function OnPostForm()
	{
		$wizard = &$this->GetWizard();
		if ($wizard->IsNextButtonClick())
		{
			$arResult = $wizard->GetVars(true);

			COption::SetOptionString("sale", "order_email", $arResult["orderEmail"]);
			COption::SetOptionString("sale", "delete_after", $arResult["saveBasket"]);
			COption::SetOptionString("sale", "default_currency", $arResult["currencyID"]);

			$arFields["LID"] = $arResult["siteID"];
			$arFields["CURRENCY"] = $arResult["currencyID"];
			CSaleLang::Update(
					$arResult["siteID"],
					Array(
						"LID" => $arResult["siteID"],
						"CURRENCY" => $arResult["currencyID"]
					)
				);

			CSaleGroupAccessToSite::DeleteBySite($arResult["siteID"]);
			foreach($arResult["groupID"] as $v)
			{
				CSaleGroupAccessToSite::Add(
						array(
								"SITE_ID" => $arResult["siteID"],
								"GROUP_ID" => $v
							)
					);
			}

			if(!empty($arResult["1C_GROUP_PERMISSIONS"]))
				COption::SetOptionString("catalog", "1C_GROUP_PERMISSIONS", implode(",", $arResult["1C_GROUP_PERMISSIONS"]));
			COption::SetOptionString("catalog", "1C_ELEMENT_ACTION", $arResult["1C_ELEMENT_ACTION"]);
			COption::SetOptionString("catalog", "1C_SECTION_ACTION", $arResult["1C_SECTION_ACTION"]);
			COption::SetOptionString("catalog", "1C_INTERVAL", $arResult["1C_INTERVAL"]);
			COption::SetOptionString("catalog", "1C_FILE_SIZE_LIMIT", $arResult["1C_FILE_SIZE_LIMIT"]);
			COption::SetOptionString("catalog", "1C_SITE_LIST", $arResult["siteID"]);

			if(!empty($arResult["1C_GROUP_PERMISSIONS"]))
				COption::SetOptionString("sale", "1C_SALE_GROUP_PERMISSIONS", implode(",", $arResult["1C_GROUP_PERMISSIONS"]));
			COption::SetOptionString("sale", "1C_EXPORT_PAYED_ORDERS", $arResult["1C_EXPORT_PAYED_ORDERS"]);
			COption::SetOptionString("sale", "1C_EXPORT_ALLOW_DELIVERY_ORDERS", $arResult["1C_EXPORT_ALLOW_DELIVERY_ORDERS"]);
			COption::SetOptionString("sale", "1C_EXPORT_FINAL_ORDERS", $arResult["1C_EXPORT_FINAL_ORDERS"]);
			COption::SetOptionString("sale", "1C_FINAL_STATUS_ON_DELIVERY", $arResult["1C_FINAL_STATUS_ON_DELIVERY"]);
			COption::SetOptionString("sale", "1C_SALE_SITE_LIST", $arResult["siteID"]);

			foreach($arResult["groupID"] as $v)
			{
				$perm = $GLOBALS["APPLICATION"]->GetGroupRight("sale", $v);
				if($perm != "W")
					$GLOBALS["APPLICATION"]->SetGroupRight("sale", $v, "U");
			}

			$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => $arResult["siteID"]), false, false, array("ID", "LID", "SORT"));
			while($arStatus = $dbStatus ->Fetch())
			{
				$arPerms = Array();
				foreach($arResult["groupID"] as $v)
				{
					$arPerms[] = array(
							"GROUP_ID" => $v,
							"PERM_VIEW" => ((
								$arResult["perm"][$arStatus["ID"]][$v]["PERM_DELETE"] == "Y"
									|| $arResult["perm"][$arStatus["ID"]][$v]["PERM_UPDATE"] == "Y"
									|| $arResult["perm"][$arStatus["ID"]][$v]["PERM_PAYMENT"] == "Y"
									|| $arResult["perm"][$arStatus["ID"]][$v]["PERM_DELIVERY"] == "Y"
									|| $arResult["perm"][$arStatus["ID"]][$v]["PERM_CANCEL"] == "Y")
									? "Y" : $arResult["perm"][$arStatus["ID"]][$v]["PERM_VIEW"]),
							"PERM_CANCEL" => $arResult["perm"][$arStatus["ID"]][$v]["PERM_CANCEL"],
							"PERM_MARK" => $arResult["perm"][$arStatus["ID"]][$v]["PERM_MARK"],
							"PERM_DEDUCTION" => $arResult["perm"][$arStatus["ID"]][$v]["PERM_DEDUCTION"],
							"PERM_DELIVERY" => $arResult["perm"][$arStatus["ID"]][$v]["PERM_DELIVERY"],
							"PERM_PAYMENT" => $arResult["perm"][$arStatus["ID"]][$v]["PERM_PAYMENT"],
							"PERM_STATUS" => $arResult["perm"][$arStatus["ID"]][$v]["PERM_STATUS"],
							"PERM_STATUS_FROM" => $arResult["perm"][$arStatus["ID"]][$v]["PERM_STATUS_FROM"],
							"PERM_UPDATE" => $arResult["perm"][$arStatus["ID"]][$v]["PERM_UPDATE"],
							"PERM_DELETE" => $arResult["perm"][$arStatus["ID"]][$v]["PERM_DELETE"],
						);
				}
				CSaleStatus::Update($arStatus["ID"], Array("PERMS" => $arPerms, "SORT" => $arStatus["SORT"]));
			}

			$paySystemID = Array();
			$paySystem = Array();
			foreach($arResult["paySystem"] as $pType => $pSystem)
			{
				if(!empty($pSystem))
				{
					foreach($pSystem as $v)
					{
						$paySystem[$v][] = $pType;
						if(!in_array($v, $paySystemID))
							$paySystemID[] = $v;
					}
				}
			}

			$dbPaySys = CSalePaySystem::GetList(Array(), Array("ACTIVE" => "Y"), false, false, Array("ID", "ACTIVE"));
			while($arPaySys = $dbPaySys -> Fetch())
			{
				if(!in_array($arPaySys["ID"], $paySystemID))
					CSalePaySystem::Update($arPaySys["ID"], Array("ACTIVE" => "N"));
			}

			foreach($paySystem as $pID => $value)
			{
				$dbPaySysAction = CSalePaySystemAction::GetList(Array(), Array("PAY_SYSTEM_ID" => $pID));
				while($arPaySysAction = $dbPaySysAction->Fetch())
				{
					if(!in_array($arPaySysAction["PERSON_TYPE_ID"], $value))
						CSalePaySystemAction::Delete($arPaySysAction["ID"]);
				}
			}

			function LocalGetPSActionParams($fileName)
			{
				$arPSCorrespondence = array();

				if (file_exists($fileName) && is_file($fileName))
					include($fileName);

				return $arPSCorrespondence;
			}

			foreach($paySystem as $pID => $value)
			{
				foreach($value as $personID)
				{
					$arFields = Array();
					$arPaySysAction = "";
					$dbPaySysAction = CSalePaySystemAction::GetList(Array(), Array("PAY_SYSTEM_ID" => $pID, "PERSON_TYPE_ID" => $personID));
					if($arPaySysAction = $dbPaySysAction->Fetch())
					{
						$pathToAction = $_SERVER["DOCUMENT_ROOT"].$arPaySysAction["ACTION_FILE"];
					}
					else
					{
						$dbPaySysAction = CSalePaySystemAction::GetList(Array(), Array("PAY_SYSTEM_ID" => $pID));
						if($arPaySysActionTmp = $dbPaySysAction->Fetch())
							$pathToAction = $_SERVER["DOCUMENT_ROOT"].$arPaySysActionTmp["ACTION_FILE"];
					}

					if($pathToAction <> '')
					{
						$arPSCorrespondence = LocalGetPSActionParams($pathToAction."/.description.php");
						$arParams = Array();
						foreach($arPSCorrespondence as $k => $v)
						{
							$typeTmp = $arResult[$pID."_".$k."_".$personID];
							$valueTmp = $arResult["VALUE1_".$pID."_".$k."_".$personID];
							if($valueTmp == '')
								$valueTmp = $arResult["VALUE2_".$pID."_".$k."_".$personID];

							$arParams[$k] = Array("TYPE" => $typeTmp, "VALUE" => $valueTmp);
						}
						$arFields = array(
								"NEW_WINDOW" => $arResult["paySystemPopup"][$pID][$personID],
								"PARAMS" => CSalePaySystemAction::SerializeParams($arParams),
							);



						if(intval($arPaySysAction["ID"])>0)
						{
							CSalePaySystemAction::Update($arPaySysAction["ID"], $arFields);
						}
						else
						{
							$arFields["PAY_SYSTEM_ID"] = $pID;
							$arFields["PERSON_TYPE_ID"] = $personID;
							$arFields["NAME"] = $arPaySysActionTmp["NAME"];
							$arFields["ACTION_FILE"] = $arPaySysActionTmp["ACTION_FILE"];
							$arFields["HAVE_PREPAY"] = "N";
							$arFields["HAVE_RESULT"] = "N";
							$arFields["HAVE_ACTION"] = "N";
							$arFields["HAVE_PAYMENT"] = "N";
							$arFields["HAVE_RESULT_RECEIVE"] = "N";

							if (file_exists($pathToAction))
							{
								if (is_dir($pathToAction))
								{
									if (file_exists($pathToAction."/pre_payment.php"))
										$arFields["HAVE_PREPAY"] = "Y";
									if (file_exists($pathToAction."/result.php"))
										$arFields["HAVE_RESULT"] = "Y";
									if (file_exists($pathToAction."/action.php"))
										$arFields["HAVE_ACTION"] = "Y";
									if (file_exists($pathToAction."/payment.php"))
										$arFields["HAVE_PAYMENT"] = "Y";
									if (file_exists($pathToAction."/result_rec.php"))
										$arFields["HAVE_RESULT_RECEIVE"] = "Y";
								}
								else
								{
									$arFields["HAVE_PAYMENT"] = "Y";
								}
							}

							CSalePaySystemAction::Add($arFields);
						}
					}
				}
			}

			COption::SetOptionInt("sale", "location", $arResult["location"], false, $arResult["siteID"]);
			if(empty($arResult["delivery"]))
				$arResult["delivery"] = Array();
			$dbDelivery = CSaleDeliveryHandler::GetList(Array("SORT" => "ASC"), Array("SITE_ID" => $arResult["siteID"], "ACTIVE" => "Y"));
			while($arDelivery = $dbDelivery->Fetch())
			{
				if(!in_array($arDelivery["SID"]."_new", $arResult["delivery"]))
					CSaleDeliveryHandler::Set($arDelivery["SID"], Array("ACTIVE" => "N"));
			}

			$dbDelivery = CSaleDelivery::GetList(Array("SORT" => "ASC"), Array("LID" => $arResult["siteID"], "ACTIVE" => "Y"));
			while($arDelivery = $dbDelivery->Fetch())
			{
				if(!in_array($arDelivery["ID"], $arResult["delivery"]))
					CSaleDelivery::Update($arDelivery["ID"], Array("ACTIVE" => "N"));
			}
			if($arResult["login"] <> '')
			{
				$arFields = Array(
					"LOGIN" => $arResult["login"],
					"PASSWORD" => $arResult["password"],
					"CONFIRM_PASSWORD" => $arResult["password_rep"],
					"EMAIL" => $arResult["email"],
				);
				$user = new CUser();
				$ID = $user->Add($arFields);

				if(intval($ID)>0)
				{
					$sGroups = COption::GetOptionString("main", "new_user_registration_def_group", "");
					CUser::SetUserGroup($ID, array_merge(explode(",", $sGroups), $arResult["1C_GROUP_PERMISSIONS"]));
				}
			}
		}
	}

	function ShowStep()
	{
		$wizard = &$this->GetWizard();
		$arResult = $wizard->GetVars(true);

		$dbSite = CSite::GetByID($arResult["siteID"]);
		$arSite = $dbSite->GetNext();

		CModule::IncludeModule("currency");
		$dbCurrency = CCurrency::GetList($b="SORT", $o="ASC", $arResult["siteID"]);
		while($arCur = $dbCurrency->GetNext())
			$arCurrency[$arCur["CURRENCY"]] = $arCur["FULL_NAME"];

		$dbUGroup = CGroup::GetList($b="c_sort", $o="ASC", Array("ACTIVE" => "Y"));
		while($arUGroup = $dbUGroup->GetNext())
			$arGroups[$arUGroup["ID"]] = $arUGroup["NAME"];

		CModule::IncludeModule("sale");
		$dbPersonType = CSalePersonType::GetList(Array("SORT"=>"ASC"), Array("ACTIVE" => "Y", "LID" => $arResult["siteID"]));
		while($arPersonType = $dbPersonType->GetNext())
			$arPersons[$arPersonType["ID"]] = $arPersonType["NAME"];

		$dbPaySystem = CSalePaySystem::GetList(Array("SORT"=>"ASC"), Array("ACTIVE" => "Y", "LID" => $arResult["siteID"]));
		while($arPaySystem = $dbPaySystem->GetNext())
			$arPaySystems[$arPaySystem["ID"]] = $arPaySystem["NAME"];

		$dbDelivery = CSaleDeliveryHandler::GetList(Array("SORT" => "ASC"), Array("SITE_ID" => $arResult["siteID"], "ACTIVE" => "Y"));
		while($arDelivery = $dbDelivery->GetNext())
			$arDeliveries[$arDelivery["SID"]."_new"] = $arDelivery["NAME"]." ".GetMessage("WW_STEP8_1");

		$dbDelivery = CSaleDelivery::GetList(Array("SORT" => "ASC"), Array("LID" => $arResult["siteID"], "ACTIVE" => "Y"));
		while($arDelivery = $dbDelivery->GetNext())
			$arDeliveries[$arDelivery["ID"]] = $arDelivery["NAME"];

		$this->content = GetMessage("WW_2")."<br />";
		$this->content .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"/bitrix/wizards/bitrix/sale.install/styles.css\">";
		$this->content .= "<table class=\"data-table\">";
		$this->content .= "<tr><th>".GetMessage("WW_STEP1_2")."</th><td>[".$arSite["ID"]."] ".$arSite["NAME"]."</td></tr>";
		$this->content .= "<tr><th>".GetMessage("WW_STEP1_3")."</th><td>".$arResult["orderEmail"]."</td></tr>";
		$this->content .= "<tr><th>".GetMessage("WW_STEP1_5")."</th><td>".$arResult["currencyID"]." (".$arCurrency[$arResult["currencyID"]].")"."</td></tr>";
		$this->content .= "<tr><th>".GetMessage("WW_3")."</th><td>";
			foreach($arResult["groupID"] as $v)
				$this->content .= $arGroups[$v]."<br />";
		$this->content .= "</td></tr>";
		$this->content .= "<tr><th>".GetMessage("WW_4")."</th><td>";
			foreach($arResult["personType"] as $v)
				$this->content .= $arPersons[$v]."<br />";
		$this->content .= "</td></tr>";
		$this->content .= "<tr><th>".GetMessage("WW_5")."</th><td>";

		$arPS = Array();
		foreach($arResult["paySystem"] as $v)
		{
			if(!empty($v))
			{
				foreach($v as $v1)
				{
					if(!in_array($v1, $arPS))
						$arPS[] = $v1;
				}
			}
		}
		foreach($arPS as $v)
			$this->content .= $arPaySystems[$v]."<br />";

		$this->content .= "</td></tr>";
		$this->content .= "<tr><th>".GetMessage("WW_6")."</th><td>";
		if(!empty($arResult["delivery"]))
		{
			foreach($arResult["delivery"] as $v)
				$this->content .= $arDeliveries[$v]."<br />";
		}
		$this->content .= "</td></tr>";
		$this->content .= "</table>";
	}
}

class FinalStep extends CWizardStep
{

	function InitStep()
	{
		$this->SetTitle(GetMessage("WW_7"));
		$this->SetStepID("final");
		$this->SetCancelCaption(GetMessage("WW_CLOSE"));
		$this->SetCancelStep("final");
	}

	function ShowStep()
	{
		$this->content .= GetMessage("WW_8");
	}
}

class CancelStep extends CWizardStep
{

	function InitStep()
	{
		$this->SetTitle(GetMessage("WW_9"));
		$this->SetStepID("cancel");
		$this->SetCancelCaption(GetMessage("WW_CLOSE"));
		$this->SetCancelStep("cancel");
	}

	function ShowStep()
	{
		$this->content .= GetMessage("WW_10");
	}
}
?>