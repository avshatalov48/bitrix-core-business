<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$ORDER_ID = IntVal($ORDER_ID);

if (CModule::IncludeModule("sale"))
{
	$dbOrder = CSaleOrder::GetList(
			array("DATE_UPDATE" => "DESC"),
			array("LID" => LANG, "USER_ID" => IntVal($USER->GetID()), "ID" => $ORDER_ID),
			false,
			false,
			array("*")
		);
	if ($arOrder = $dbOrder->Fetch())
	{
		$dbPaySysAction = CSalePaySystemAction::GetList(
				array(),
				array(
						"PAY_SYSTEM_ID" => $arOrder["PAY_SYSTEM_ID"],
						"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"]
					),
				false,
				false,
				array("ACTION_FILE", "PARAMS")
			);

		if ($arPaySysAction = $dbPaySysAction->Fetch())
		{
			if (strlen($arPaySysAction["ACTION_FILE"]) > 0)
			{
				$PAYER_NAME = "";
				$GLOBALS["SALE_INPUT_PARAMS"] = array();

				$dbUser = CUser::GetByID($arOrder["USER_ID"]);
				if ($arUser = $dbUser->Fetch())
					$GLOBALS["SALE_INPUT_PARAMS"]["USER"] = $arUser;

				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"] = $arOrder;
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"] = DoubleVal($arOrder["PRICE"]) - DoubleVal($arOrder["SUM_PAID"]);

				$arDateInsert = explode(" ", $arOrder["DATE_INSERT"]);
				if (is_array($arDateInsert) && count($arDateInsert) > 0)
					$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT_DATE"] = $arDateInsert[0];
				else
					$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT_DATE"] = $arOrder["DATE_INSERT"];

				$arCurOrderProps = array();
				$dbOrderPropVals = CSaleOrderPropsValue::GetList(
						array(),
						array("ORDER_ID" => $ORDER_ID),
						false,
						false,
						array("ID", "CODE", "VALUE", "ORDER_PROPS_ID", "PROP_TYPE", "PROP_IS_PAYER")
					);
				while ($arOrderPropVals = $dbOrderPropVals->Fetch())
				{
					$arCurOrderPropsTmp = CSaleOrderProps::GetRealValue(
							$arOrderPropVals["ORDER_PROPS_ID"],
							$arOrderPropVals["CODE"],
							$arOrderPropVals["PROP_TYPE"],
							$arOrderPropVals["VALUE"],
							LANGUAGE_ID
						);
					foreach ($arCurOrderPropsTmp as $key => $value)
					{
						$arCurOrderProps[$key] = $value;
					}

					if ($arOrderPropVals["PROP_IS_PAYER"] == "Y")
						$PAYER_NAME = $arOrderPropVals["VALUE"];
				}

				$GLOBALS["SALE_INPUT_PARAMS"]["PROPERTY"] = $arCurOrderProps;

				$GLOBALS["SALE_CORRESPONDENCE"] = CSalePaySystemAction::UnSerializeParams($arPaySysAction["PARAMS"]);

				$pathToAction = $_SERVER["DOCUMENT_ROOT"].$arPaySysAction["ACTION_FILE"];

				$pathToAction = str_replace("\\", "/", $pathToAction);
				while (substr($pathToAction, strlen($pathToAction) - 1, 1) == "/")
					$pathToAction = substr($pathToAction, 0, strlen($pathToAction) - 1);

				if (file_exists($pathToAction))
				{
					if (is_dir($pathToAction))
					{
						if (file_exists($pathToAction."/payment.php"))
							include($pathToAction."/payment.php");
					}
					else
					{
						include($pathToAction);
					}
				}
			}
		}
	}
}
?>