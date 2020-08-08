<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($arResult["ShowReady"]=="Y" || $arResult["ShowDelay"]=="Y" || $arResult["ShowSubscribe"]=="Y" || $arResult["ShowNotAvail"]=="Y")
{
	foreach ($arResult["GRID"]["HEADERS"] as $id => $arHeader):
		$arHeader["name"] = (isset($arHeader["name"]) ? (string)$arHeader["name"] : '');
		if ($arHeader["name"] == '')
		{
			$arResult["GRID"]["HEADERS"][$id]["name"] = GetMessage("SALE_".$arHeader["id"]);
			if($arResult["GRID"]["HEADERS"][$id]["name"] == '')
				$arResult["GRID"]["HEADERS"][$id]["name"] = GetMessage("SALE_".str_replace("_FORMATED", "", $arHeader["id"]));
		}
	endforeach;

?><table class="sale_basket_small"><?
	if ($arResult["ShowReady"]=="Y")
	{
		?><tr><td align="center"><? echo GetMessage("TSBS_READY"); ?></td></tr>
		<tr><td><ul><?
		foreach ($arResult["ITEMS"]["AnDelCanBuy"] as &$v)
		{
			?><li><?
			foreach ($arResult["GRID"]["HEADERS"] as $id => $arHeader)
			{
				if(isset($v[$arHeader['id']]) && !empty($v[$arHeader['id']]))
				{
					if(in_array($arHeader['id'], array("NAME")))
					{
						if ('' != $v["DETAIL_PAGE_URL"])
						{
							?><a href="<?echo $v["DETAIL_PAGE_URL"]; ?>"><b><?echo $v[$arHeader['id']]?></b></a><br /><?
						}
						else
						{
							?><b><?echo $v[$arHeader['id']]?></b><br /><?
						}
					}
					else if(in_array($arHeader['id'], array("PRICE_FORMATED")))
					{
						?><?= $arHeader['name']?>:&nbsp;<b><?echo $v[$arHeader['id']]?></b><br /><?
					}
					else if(in_array($arHeader['id'], ["DETAIL_PICTURE", "PREVIEW_PICTURE"]) && !empty($v[$arHeader['id']."_SRC"]))
					{
						?><?= $arHeader['name']?>:&nbsp;<br/><img src="<?echo $v[$arHeader['id']."_SRC"]?>"><br/><?
					}
					else
					{
						?><?= $arHeader['name']?>:&nbsp;<?echo $v[$arHeader['id']]?><br /><?
					}
				}
			}
			?></li><?
		}
		if (isset($v))
			unset($v);
		?></ul></td></tr><?
		if ('' != $arParams["PATH_TO_BASKET"])
		{
			?><tr><td align="center"><a href="<?=$arParams["PATH_TO_BASKET"]?>"><?= GetMessage("TSBS_2BASKET") ?></a>
			</td></tr><?
		}
		if ('' != $arParams["PATH_TO_ORDER"])
		{
			?><tr><td align="center"><a href="<?=$arParams["PATH_TO_ORDER"]?>"><?= GetMessage("TSBS_2ORDER") ?></a>
			</td></tr><?
		}
	}
	if ($arResult["ShowDelay"]=="Y")
	{
		?><tr><td align="center"><?= GetMessage("TSBS_DELAY") ?></td></tr>
		<tr><td><ul>
		<?
		foreach ($arResult["ITEMS"]["DelDelCanBuy"] as &$v)
		{
			?><li><?
			foreach ($arResult["GRID"]["HEADERS"] as $id => $arHeader)
			{
				if(isset($v[$arHeader['id']]) && !empty($v[$arHeader['id']]))
				{
					if(in_array($arHeader['id'], array("NAME")))
					{
						if ('' != $v["DETAIL_PAGE_URL"])
						{
							?><a href="<?echo $v["DETAIL_PAGE_URL"]; ?>"><b><?echo $v[$arHeader['id']]?></b></a><br /><?
						}
						else
						{
							?><b><?echo $v[$arHeader['id']]?></b><br /><?
						}
					}
					else if(in_array($arHeader['id'], array("PRICE_FORMATED")))
					{
						?><?= $arHeader['name']?>:&nbsp;<b><?echo $v[$arHeader['id']]?></b><br /><?
					}
					else if(in_array($arHeader['id'], ["DETAIL_PICTURE", "PREVIEW_PICTURE"]) && !empty($v[$arHeader['id']."_SRC"]))
					{
						?><?= $arHeader['name']?>:&nbsp;<br/><img src="<?echo $v[$arHeader['id']."_SRC"]?>"><br/><?
					}
					else
					{
						?><?= $arHeader['name']?>:&nbsp;<?echo $v[$arHeader['id']]?><br /><?
					}
				}
			}
			?></li><?
		}
		if (isset($v))
			unset($v);
		?></ul></td></tr><?
		if ('' != $arParams["PATH_TO_BASKET"])
		{
			?><tr><td align="center"><a href="<?=$arParams["PATH_TO_BASKET"]?>"><?= GetMessage("TSBS_2BASKET") ?></a>
			</td></tr><?
		}
	}
	if ($arResult["ShowSubscribe"]=="Y")
	{
		?><tr><td align="center"><?= GetMessage("TSBS_SUBSCRIBE") ?></td></tr>
		<tr><td><ul><?
		foreach ($arResult["ITEMS"]["ProdSubscribe"] as &$v)
		{
			?><li><?
			foreach ($arResult["GRID"]["HEADERS"] as $id => $arHeader)
			{
				if(isset($v[$arHeader['id']]) && !empty($v[$arHeader['id']]))
				{
					if(in_array($arHeader['id'], array("NAME")))
					{
						if ('' != $v["DETAIL_PAGE_URL"])
						{
							?><a href="<?echo $v["DETAIL_PAGE_URL"]; ?>"><b><?echo $v[$arHeader['id']]?></b></a><br /><?
						}
						else
						{
							?><b><?echo $v[$arHeader['id']]?></b><br /><?
						}
					}
					else if(in_array($arHeader['id'], array("PRICE_FORMATED")))
					{
						?><?= $arHeader['name']?>:&nbsp;<b><?echo $v[$arHeader['id']]?></b><br /><?
					}
					else if(in_array($arHeader['id'], ["DETAIL_PICTURE", "PREVIEW_PICTURE"]) && !empty($v[$arHeader['id']."_SRC"]))
					{
						?><?= $arHeader['name']?>:&nbsp;<br/><img src="<?echo $v[$arHeader['id']."_SRC"]?>"><br/><?
					}
					else
					{
						?><?= $arHeader['name']?>:&nbsp;<?echo $v[$arHeader['id']]?><br /><?
					}
				}
			}
			?></li><?
		}
		if (isset($v))
			unset($v);
		?></ul></td></tr><?
	}
	if ($arResult["ShowNotAvail"]=="Y")
	{
		?><tr><td align="center"><?= GetMessage("TSBS_UNAVAIL") ?></td></tr>
		<tr><td><ul><?
		foreach ($arResult["ITEMS"]["nAnCanBuy"] as &$v)
		{
			?><li><?
			foreach ($arResult["GRID"]["HEADERS"] as $id => $arHeader)
			{
				if(isset($v[$arHeader['id']]) && !empty($v[$arHeader['id']]))
				{
					if(in_array($arHeader['id'], array("NAME")))
					{
						if ('' != $v["DETAIL_PAGE_URL"])
						{
							?><a href="<?echo $v["DETAIL_PAGE_URL"]; ?>"><b><?echo $v[$arHeader['id']]?></b></a><br /><?
						}
						else
						{
							?><b><?echo $v[$arHeader['id']]?></b><br /><?
						}
					}
					else if(in_array($arHeader['id'], array("PRICE_FORMATED")))
					{
						?><?= $arHeader['name']?>:&nbsp;<b><?echo $v[$arHeader['id']]?></b><br /><?
					}
					else if(in_array($arHeader['id'], ["DETAIL_PICTURE", "PREVIEW_PICTURE"]) && !empty($v[$arHeader['id']."_SRC"]))
					{
						?><?= $arHeader['name']?>:&nbsp;<br/><img src="<?echo $v[$arHeader['id']."_SRC"]?>"><br/><?
					}
					else
					{
						?><?= $arHeader['name']?>:&nbsp;<?echo $v[$arHeader['id']]?><br /><?
					}
				}
			}
			?></li><?
		}
		if (isset($v))
			unset($v);
		?></ul></td></tr><?
	}
	?></table><?
}
?>