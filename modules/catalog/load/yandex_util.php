<?
define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);
define('NO_AGENT_CHECK', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if (check_bitrix_sessid())
{
	echo "<script type=\"text/javascript\">\n";

	$bNoTree = true;
	$bIBlock = false;
	$IBLOCK_ID = intval($_REQUEST['IBLOCK_ID']);
	if ($IBLOCK_ID > 0)
	{
		CModule::IncludeModule("iblock");
		$rsIBlocks = CIBlock::GetByID($IBLOCK_ID);
		if ($arIBlock = $rsIBlocks->Fetch())
		{
			$bRightBlock = CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_admin_display");
			if ($bRightBlock)
			{
				echo "window.parent.Tree = [];";
				echo "window.parent.Tree[0] = [];";

				$bIBlock = true;
				$iterator = CIBlockSection::GetList(
					array("LEFT_MARGIN"=>"ASC"),
					array("IBLOCK_ID"=>$IBLOCK_ID),
					false,
					array("ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "LEFT_MARGIN", "RIGHT_MARGIN")
				);
				while ($row = $iterator->Fetch())
				{
					$bNoTree = false;
					$row["ID"] = (int)$row["ID"];
					$row["IBLOCK_SECTION_ID"] = (int)$row["IBLOCK_SECTION_ID"];
					$row["LEFT_MARGIN"] = (int)$row["LEFT_MARGIN"];
					$row["RIGHT_MARGIN"] = (int)$row["RIGHT_MARGIN"];
					if ($row["RIGHT_MARGIN"] - $row["LEFT_MARGIN"] > 1)
					{
						?>window.parent.Tree[<?=$row["ID"];?>] = [];<?
					}
					?>window.parent.Tree[<?=$row["IBLOCK_SECTION_ID"];?>][<?=$row["ID"];?>]=Array('<?echo CUtil::JSEscape(htmlspecialcharsbx($row["NAME"]));?>', '');<?
				}
			}
		}
	}
	if ($bNoTree && !$bIBlock)
	{
		echo "window.parent.buildNoMenu();";
	}
	else
	{
		echo "window.parent.buildMenu();";
	}

	echo "</script>";
}