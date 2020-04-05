<?
/** @global CMain $APPLICATION */
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader;


if (isset($_REQUEST['lid']) && !empty($_REQUEST['lid']))
{
	if (!is_string($_REQUEST['lid']))
		die();
	if (preg_match('/^[a-z0-9_]{2}$/i', $_REQUEST['lid']))
		define('SITE_ID', $_REQUEST['lid']);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Loader::includeModule('catalog'))
	return;

Loc::loadMessages(__FILE__);

if ($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["action"])>0 && check_bitrix_sessid())
{
	$APPLICATION->RestartBuffer();

	switch ($_POST["action"])
	{
		case "catalogSetAdd2Basket":
			if (is_array($_POST["set_ids"]))
			{
				foreach($_POST["set_ids"] as $itemID)
				{
					if (!is_string($itemID))
						continue;
					$itemID = (int)$itemID;
					if ($itemID <= 0)
						continue;

					$product_properties = true;
					if (!empty($_POST["setOffersCartProps"]))
					{
						$product_properties = CIBlockPriceTools::GetOfferProperties(
							$itemID,
							$_POST["iblockId"],
							$_POST["setOffersCartProps"]
						);
					}
					$ratio = 1;
					if ($_POST["itemsRatio"][$itemID])
						$ratio = $_POST["itemsRatio"][$itemID];

					Add2BasketByProductID($itemID, $ratio, array("LID" => $_POST["lid"]), $product_properties);
				}
			}
			break;
	}

	die();
}