<?php

define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

global $APPLICATION;
global $USER;

if (!check_bitrix_sessid() || !$USER->IsAuthorized() || $_SERVER['REQUEST_METHOD'] != "POST")
{
	CMain::FinalActions();
}

if (
	!isset($_REQUEST['save_desktop'])
	&& (!isset($_REQUEST['save_gadget']) || (isset($_REQUEST['refresh']) && $_REQUEST['refresh'] == "Y"))
)
{
	CUtil::JSPostUnescape();
}

$desktop_page = intval($_REQUEST['desktop_page'] ?? 0);
$action = $_REQUEST['action'] ?? '';

if (isset($_REQUEST['desktop_backurl']) && $_REQUEST['desktop_backurl'] && strpos($_REQUEST['desktop_backurl'], "/") === 0)
{
	$desktop_backurl = $_REQUEST['desktop_backurl'];
}
else
{
	$desktop_backurl = "";
}

$allOptions = CUserOptions::GetOption("intranet", "~gadgets_admin_index", []);

if ($action === "new")
{
	$arUserOptions = [];
}
else
{
	$arUserOptions = $allOptions[$desktop_page] ?? [];
}

if (isset($_POST["type"]) && $_POST["type"] == "desktop")
{
	if (
		!isset($arUserOptions["COLS"])
		|| $arUserOptions["COLS"] <= 0
		|| $arUserOptions["COLS"] >= 10
	)
	{
		$cols_count = 2;
	}
	else
	{
		$cols_count = $arUserOptions["COLS"];
	}

	$desktop_name = $arUserOptions["NAME"] ?? '';

	if (isset($arUserOptions["arCOLUMN_WIDTH"]) && is_array($arUserOptions["arCOLUMN_WIDTH"]))
	{
		for($i = 0; $i < count($arUserOptions["arCOLUMN_WIDTH"]); $i++)
		{
			$arCOLUMN_WIDTH[$i] = $arUserOptions["arCOLUMN_WIDTH"][$i];
		}
	}
	else
	{
		$arCOLUMN_WIDTH = array("50%", "50%");
	}

	if (isset($_REQUEST['save_desktop']))
	{
		unset($_POST['save_desktop']);

		$arUserOptions["COLS"] = intval($_REQUEST["SETTINGS_COLUMNS"]);
		$arUserOptions["NAME"] = trim($_REQUEST["SETTINGS_NAME"]);

		for ($i=0; $i < $arUserOptions["COLS"]; $i++)
		{
			$arUserOptions["arCOLUMN_WIDTH"][$i] = ${"SETTINGS_COLUMN_WIDTH_".$i};
		}

		if ($action === "new")
		{
			$desktop_page = count($allOptions);
		}

		if ($action === "delete")
		{
			unset($allOptions[$desktop_page]);
			$arTmp = array();
			foreach($allOptions as $arDesktop)
				$arTmp[] = $arDesktop;
			$allOptions = $arTmp;
		}
		else
		{
			$allOptions[$desktop_page] = $arUserOptions;
		}

		CUserOptions::SetOption("intranet", "~gadgets_admin_index", $allOptions);

		?>
		<script type="text/javascript">
		<?php
		if ($action === "new")
		{
			?>
			top.BX.closeWait(); top.BX.WindowManager.Get().AllowClose(); top.BX.WindowManager.Get().Close();
			top.location.href = '<?=htmlspecialcharsbx(CUtil::JSEscape($desktop_backurl)).(strpos($desktop_backurl, "?") === false ? "?" : "&")."dt_page=".$desktop_page?>';
			<?php
		}
		else
		{
			?>
			top.BX.closeWait(); top.BX.WindowManager.Get().AllowClose(); top.BX.WindowManager.Get().Close();
			top.BX.reload();
			<?php
		}
		?></script><?php

		CMain::FinalActions();
	}
	else
	{
		$GLOBALS["APPLICATION"]->SetTitle(GetMessage("CMDESKTOP_ADMIN_SETTINGS_DIALOG_TITLE"));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

		?>
		<script type="text/javascript">
			BX.ready(function()
				{
					if (BX('SETTINGS_COLUMNS'))
					{
						BX.bind(BX('SETTINGS_COLUMNS'), 'keyup', BX.adminPanel.recalcDesktopSettingsDialog);
						BX.bind(BX('SETTINGS_COLUMNS'), 'blur', BX.adminPanel.recalcDesktopSettingsDialog);
					}
					current_col_count = <?=htmlspecialcharsbx($cols_count)?>;
				}
			);
		</script>
		<div class="bx-core-dialog-content">
		<form method="POST" name="frm_desktop_settings" id="frm_desktop_settings" action="<?= $APPLICATION->GetCurPageParam(); ?>" enctype="multipart/form-data">
		<?= bitrix_sessid_post(); ?>
		<input type="hidden" name="type" value="desktop">
		<input type="hidden" name="desktop_page" value="<?=$desktop_page?>">
		<input type="hidden" name="action" value="<?=htmlspecialcharsbx($action)?>">
		<input type="hidden" name="desktop_backurl" value="<?=htmlspecialcharsbx(CUtil::JSEscape($desktop_backurl))?>">
		<input type="hidden" name="save_desktop" value="Y">
		<table class="edit-table" width="100%"><tbody>
		<tr>
			<td width="40%"><?=GetMessage('CMDESKTOP_ADMIN_DESKTOP_NAME')?></td>
			<td width="60%"><input type="text" size="40" maxlength="100" id="SETTINGS_NAME" name="SETTINGS_NAME" value="<?=htmlspecialcharsbx($desktop_name)?>"></td>
		</tr>
		<tr>
			<td width="40%"><?=GetMessage('CMDESKTOP_ADMIN_COLUMNS')?></td>
			<td width="60%"><input type="text" size="2" maxlength="1" id="SETTINGS_COLUMNS" name="SETTINGS_COLUMNS" value="<?=htmlspecialcharsbx($cols_count)?>"></td>
		</tr>
		<?php
		for ($i=0; $i < $cols_count; $i++)
		{
			?>
			<tr class="bx-gd-admin-settings-col">
				<td width="40%"><?=GetMessage("CMDESKTOP_ADMIN_COLUMN_WIDTH").($i+1)?></td>
				<td width="60%"><input type="text" size="5" maxlength="6" id="SETTINGS_COLUMN_WIDTH_<?=$i?>" name="SETTINGS_COLUMN_WIDTH_<?=$i?>" value="<?=htmlspecialcharsbx($arCOLUMN_WIDTH[$i])?>"></td>
			</tr>
			<?php
		}
		?>
		</table>
		</form>
		</div>
		<?php

		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	}
}
elseif (isset($_POST["type"]) && $_POST["type"] == "gadget")
{
	$gdid = $_POST['gid'] ?? '';
	$gdid = preg_replace("/[^a-z0-9@_]/i", "", $gdid);

	$p = mb_strpos($gdid, "@");
	if($p !== false)
	{
		$gadget_id = mb_substr($gdid, 0, $p);
		$arGadget = BXGadget::GetById($gadget_id, true);

		if($arGadget)
		{
			if (isset($_REQUEST['save_gadget']) && (!isset($_REQUEST['refresh']) || $_REQUEST['refresh'] != "Y"))
			{
				$arSettings = [];
				foreach($_POST as $key => $value)
				{
					if (mb_strpos($key, "GP_") !== 0)
						continue;

					$key = mb_substr($key, 3);
					$arSettings[$key] = $value;
				}

				$arUserOptions["GADGETS"][$gdid]["SETTINGS"] = $arSettings;

				$allOptions[$desktop_page] = $arUserOptions;

				CUserOptions::SetOption("intranet", "~gadgets_admin_index", $allOptions);

				?><script type="text/javascript">
				top.BX.closeWait(); top.BX.WindowManager.Get().AllowClose(); top.BX.WindowManager.Get().Close();
				top.BX.reload();
				</script><?php

				CMain::FinalActions();
			}
			else
			{
				$arGadgetParams = $arGadget["USER_PARAMETERS"];

				if(
					isset($arUserOptions["GADGETS"][$gdid]["SETTINGS"])
					&& is_array($arUserOptions["GADGETS"][$gdid]["SETTINGS"])
				)
				{
					foreach($arUserOptions["GADGETS"][$gdid]["SETTINGS"] as $p=>$v)
						if(is_set($arGadgetParams, $p))
							$arGadgetParams[$p]["VALUE"] = $v;
				}

				$arFormGadgetParams = $arGadgetParams;

				if (isset($_REQUEST["refresh"]) && $_REQUEST["refresh"] == "Y")
					foreach($_REQUEST as $key => $value)
						if (mb_strpos($key, "GP_") === 0)
							$arFormGadgetParams[mb_substr($key, 3)]["VALUE"] = $value;

				$arGadget = BXGadget::GetById($gadget_id, true, $arFormGadgetParams);
				$arGadgetParams = $arGadget["USER_PARAMETERS"];

				if(
					isset($arUserOptions["GADGETS"][$gdid]["SETTINGS"])
					&& is_array($arUserOptions["GADGETS"][$gdid]["SETTINGS"])
				)
				{
					foreach($arUserOptions["GADGETS"][$gdid]["SETTINGS"] as $p=>$v)
						if(is_set($arGadgetParams, $p) && !array_key_exists("GP_".$p, $_REQUEST))
							$arGadgetParams[$p]["VALUE"] = $v;
				}

				?>
				<div class="bx-core-dialog-content">
				<form method="POST" name="frm_desktop_settings" id="frm_desktop_settings" action="<?= $APPLICATION->GetCurPageParam("", array("refresh")); ?>" enctype="multipart/form-data">
				<?= bitrix_sessid_post(); ?>
				<input type="hidden" name="type" value="gadget">
				<input type="hidden" name="desktop_page" value="<?=$desktop_page?>">
				<input type="hidden" name="save_gadget" value="Y">
				<input type="hidden" name="gid" value="<?=htmlspecialcharsbx($gdid)?>">
				<table class="edit-table" width="100%"><tbody>
				<?php
				foreach($arGadgetParams as $param_id => $arGadgetParam)
				{
					?>
					<tr class="bx-gd-admin-settings-col">
						<td width="40%"><?=$arGadgetParam["NAME"]?></td>
						<td width="60%"><?php
						$input_id = $param_id;

						if($arGadgetParam["TYPE"] == "STRING")
						{
							if (isset($_REQUEST['refresh']) && $_REQUEST['refresh'] == "Y" && $_REQUEST["GP_".$input_id] <> '')
								$val_tmp = $_REQUEST["GP_".$input_id];
							elseif (isset($arGadgetParam["VALUE"]) && $arGadgetParam["VALUE"] <> '')
								$val_tmp = $arGadgetParam["VALUE"];
							else
								$val_tmp = $arGadgetParam["DEFAULT"];

							?><input type="text" name="GP_<?=$input_id?>" size="40" value="<?=htmlspecialcharsbx($val_tmp)?>"><?php
						}
						elseif($arGadgetParam["TYPE"] == "LIST")
						{
							if ($arGadgetParam["MULTIPLE"] == "Y")
							{
								if (isset($_REQUEST['refresh']) && $_REQUEST['refresh'] == "Y" && is_array($_REQUEST["GP_".$input_id]))
									$val_tmp = $_REQUEST["GP_".$input_id];
								elseif (isset($arGadgetParam["VALUE"]) && is_array($arGadgetParam["VALUE"]))
									$val_tmp = $arGadgetParam["VALUE"];
								elseif (isset($arGadgetParam["DEFAULT"]) && is_array($arGadgetParam["DEFAULT"]))
									$val_tmp = $arGadgetParam["DEFAULT"];
								else
									$val_tmp = array();
							}
							else
							{
								if (isset($_REQUEST['refresh']) && $_REQUEST['refresh'] == "Y" && $_REQUEST["GP_".$input_id] <> '')
									$val_tmp = $_REQUEST["GP_".$input_id];
								elseif (isset($arGadgetParam["VALUE"]) && $arGadgetParam["VALUE"] <> '')
									$val_tmp = $arGadgetParam["VALUE"];
								elseif (isset($arGadgetParam["DEFAULT"]) && $arGadgetParam["DEFAULT"] <> '')
									$val_tmp = $arGadgetParam["DEFAULT"];
							}

							?><select style="width:100%" name="GP_<?=$input_id?><?=($arGadgetParam["MULTIPLE"]=="Y"?'[]':'')?>"<?=($arGadgetParam["MULTIPLE"]=="Y"?' multiple="multiple"':'')?><?php if(isset($arGadgetParam["REFRESH"]) && $arGadgetParam["REFRESH"] == "Y"):?> onchange="BX.WindowManager.Get().PostParameters('refresh=Y')"<?php endif;?>><?php
							foreach($arGadgetParam["VALUES"] as $key => $value)
							{
								$is_selected = '';
								if(is_array($val_tmp))
								{
									if (
										$arGadgetParam["MULTIPLE"] == "Y"
										&& in_array($key, $val_tmp)
									)
										$is_selected = " selected";
								}
								else
									$is_selected = ($val_tmp== $key ? " selected" : '');

								?><option value="<?=$key?>"<?=$is_selected?>><?=$value?></option><?php
							}
							?></select><?php
						}
						elseif($arGadgetParam["TYPE"] == "CHECKBOX")
						{
							if ($_REQUEST['refresh'] == "Y" && $_REQUEST["GP_".$input_id] <> '')
								$val_tmp = $_REQUEST["GP_".$input_id];
							elseif ($arGadgetParam["VALUE"] <> '')
								$val_tmp = $arGadgetParam["VALUE"];
							else
								$val_tmp = $arGadgetParam["DEFAULT"];

							?><input type="checkbox" name="GP_<?=$input_id?>" value="Y"<?=($val_tmp=="Y"?' checked':'')?><?php if($arGadgetParam["REFRESH"] == "Y"):?> onchange="BX.WindowManager.Get().PostParameters('refresh=Y')"<?php endif;?>><?php
						}
						?>
						</td>
					</tr>
					<?php
				}
				?>
				</table>
				</form>
				<script type="text/javascript">
					top.BX.WindowManager.Get().SetButtons([top.BX.WindowManager.Get().btnSave, top.BX.WindowManager.Get().btnCancel]);
				</script>
				</div>
				<?php
			}
		}
	}
}

CMain::FinalActions();
