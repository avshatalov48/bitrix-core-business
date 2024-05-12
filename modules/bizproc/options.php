<?php

/** @global CMain $APPLICATION */

$module_id = "bizproc";
$bizprocPerms = $APPLICATION::GetGroupRight($module_id);
if ($bizprocPerms >= "R") :

	CModule::IncludeModule("bizproc");

	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/options.php");
	IncludeModuleLangFile(__FILE__);

	$dbSites = CSite::GetList("", "", ["ACTIVE" => "Y"]);
	$arSites = [];
	$aSubTabs = [];
	while ($site = $dbSites->Fetch())
	{
		$site["ID"] = htmlspecialcharsbx($site["ID"]);
		$site["NAME"] = htmlspecialcharsbx($site["NAME"]);
		$arSites[] = $site;

		$aSubTabs[] = ["DIV" => "opt_site_" . $site["ID"], "TAB" => "(" . $site["ID"] . ") " . $site["NAME"], 'TITLE' => ''];
	}
	$subTabControl = new CAdminViewTabControl("subTabControl", $aSubTabs);

	if ($_SERVER['REQUEST_METHOD'] === "GET" && !empty($RestoreDefaults) && $bizprocPerms === "W" && check_bitrix_sessid())
	{
		COption::RemoveOption("bizproc");
	}

	$arAllOptions = [
		["log_cleanup_days", GetMessage("BIZPROC_LOG_CLEANUP_DAYS"), "90", ["text", 3]],
		["search_cleanup_days", GetMessage("BIZPROC_SEARCH_CLEANUP_DAYS"), "180", ["text", 3]],
		["log_skip_types", GetMessage("BIZPROC_LOG_SKIP_TYPES"), "1,2", ["checkboxlist", [
			1 => GetMessage("BIZPROC_LOG_SKIP_TYPES_1_1"),
			2 => GetMessage("BIZPROC_LOG_SKIP_TYPES_2_1"),
		]]],
		["automation_no_forced_tracking", GetMessage("BIZPROC_AUTOMATION_NO_FORCED_TRACKING"), "N", ["checkbox"]],
		["limit_simultaneous_processes", GetMessage("BIZPROC_LIMIT_SIMULTANEOUS_PROCESSES"), "", ["text", 3]],
		["employee_compatible_mode", GetMessage("BIZPROC_EMPLOYEE_COMPATIBLE_MODE"), "N", ["checkbox"]],
		//	array("name_template", GetMessage("BIZPROC_NAME_TEMPLATE"), "", Array("select", 35))
	];

	$strWarning = "";
	if ($_SERVER['REQUEST_METHOD'] === "POST" && !empty($Update) && $bizprocPerms === "W" && check_bitrix_sessid())
	{
		COption::SetOptionString("bizproc", "log_cleanup_days", ($log_cleanup_days ?? 0));
		if ($log_cleanup_days > 0)
		{
			CAgent::AddAgent("CBPTrackingService::ClearOldAgent();", "bizproc", "N", 86400);
		}
		else
		{
			CAgent::RemoveAgent("CBPTrackingService::ClearOldAgent();", "bizproc");
		}

		COption::SetOptionString("bizproc", "search_cleanup_days", ($search_cleanup_days ?? 0));
		if ($search_cleanup_days > 0)
		{
			CAgent::AddAgent(\Bitrix\Bizproc\Worker\Workflow\ClearFilterAgent::getName(), "bizproc", "N", 86400);
			CAgent::AddAgent(\Bitrix\Bizproc\Worker\Task\ClearSearchContentAgent::getName(), "bizproc", "N", 86400);
		}
		else
		{
			CAgent::RemoveAgent(\Bitrix\Bizproc\Worker\Workflow\ClearFilterAgent::getName(), "bizproc");
			CAgent::RemoveAgent(\Bitrix\Bizproc\Worker\Task\ClearSearchContentAgent::getName(), "bizproc");
		}

		COption::SetOptionString("bizproc", "employee_compatible_mode", ($employee_compatible_mode ?? 'N') === "Y" ? "Y" : "N");
		COption::SetOptionString("bizproc", "limit_simultaneous_processes", ($limit_simultaneous_processes ?? 0) ? $limit_simultaneous_processes : 0);
		COption::SetOptionString("bizproc", "log_skip_types", ($log_skip_types ?? '') ? implode(',', $log_skip_types) : "");
		COption::SetOptionString("bizproc", "automation_no_forced_tracking", ($automation_no_forced_tracking ?? 'N') === "Y" ? "Y" : "N");

		\Bitrix\Main\Config\Option::set("bizproc", "use_gzip_compression", $_REQUEST["use_gzip_compression"]);
		\Bitrix\Main\Config\Option::set("bizproc", "locked_wi_path", $_REQUEST["locked_wi_path"]);

		CBPSchedulerService::setDelayMinLimit($_REQUEST["delay_min_limit"], $_REQUEST['delay_min_limit_type']);

		foreach ($arSites as $site)
		{
			if (isset($_POST["name_template_" . $site["LID"]]))
			{
				if (empty($_POST["name_template_" . $site["LID"]]))
					COption::RemoveOption("bizproc", "name_template", $site["LID"]);
				else
					COption::SetOptionString("bizproc", "name_template", $_POST["name_template_" . $site["LID"]], false, $site["LID"]);
			}
		}
	}

	if ($strWarning <> '')
		CAdminMessage::ShowMessage($strWarning);

	$aTabs = [
		["DIV" => "edit1", "TAB" => GetMessage("BIZPROC_TAB_SET"), "ICON" => "", "TITLE" => GetMessage("BIZPROC_TAB_SET_ALT")],
	];
	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	?>
	<form method="POST"
		name="bizproc_opt_form"
		action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($mid ?? 'bizproc') ?>&lang=<?= LANGUAGE_ID ?>"
		ENCTYPE="multipart/form-data"><?php
		echo bitrix_sessid_post();
		$tabControl->BeginNextTab();
		?>
		<?php
		for ($i = 0, $cnt = count($arAllOptions); $i < $cnt; $i++):
			$Option = $arAllOptions[$i];
			$val = COption::GetOptionString("bizproc", $Option[0], $Option[2]);
			$type = $Option[3];
			?>
			<tr>
				<td width="50%" valign="top"><?php
					if ($type[0] == "checkbox")
						echo "<label for=\"" . htmlspecialcharsbx($Option[0]) . "\">" . $Option[1] . "</label>";
					else
						echo $Option[1];
					?>:
				</td>
				<td width="50%" valign="top">
					<?php
					if ($type[0] == "checkbox"):?>
						<input type="checkbox" name="<?php
						echo htmlspecialcharsbx($Option[0]) ?>" id="<?php
						echo htmlspecialcharsbx($Option[0]) ?>" value="Y"<?php
						if ($val == "Y")
							echo " checked"; ?>>
					<?php
					elseif ($type[0] == "text"):?>
						<input type="text" size="<?php
						echo $type[1] ?>" value="<?php
						echo htmlspecialcharsbx($val) ?>" name="<?php
						echo htmlspecialcharsbx($Option[0]) ?>">
					<?php
					elseif ($type[0] == "textarea"):?>
						<textarea rows="<?php
						echo $type[1] ?>" cols="<?php
						echo $type[2] ?>" name="<?php
						echo htmlspecialcharsbx($Option[0]) ?>"><?php
							echo htmlspecialcharsbx($val) ?></textarea>
					<?php
					elseif ($type[0] == "checkboxlist"):?>
						<?php
						$arVal = explode(',', $val);
						?>
						<?php
						foreach ($type[1] as $k => $v):?>
							<input type="checkbox" name="<?php
							echo htmlspecialcharsbx($Option[0]) ?>[]" id="<?php
							echo htmlspecialcharsbx($Option[0] . '_' . $k) ?>" value="<?= $k ?>"<?php
							if (in_array($k, $arVal))
								echo " checked"; ?>>
							<label for="<?= htmlspecialcharsbx($Option[0] . '_' . $k) ?>"><?php
								echo htmlspecialcharsbx($v) ?></label><br>
						<?php
						endforeach; ?>
					<?php
					endif ?>
				</td>
			</tr>
		<?php
		endfor; ?>
		<tr>
			<td width="50%" valign="top"><?= GetMessage("BIZPROC_OPT_USE_GZIP_COMPRESSION") ?>:</td>
			<td width="50%" valign="top">
				<select name="use_gzip_compression">
					<?php
					$useGZipCompression = \Bitrix\Main\Config\Option::get("bizproc", "use_gzip_compression", ""); ?>
					<option value="" <?php
					if (empty($useGZipCompression))
						echo "selected"; ?>><?= GetMessage("BIZPROC_OPT_USE_GZIP_COMPRESSION_EMPTY") ?></option>
					<option value="Y" <?php
					if ($useGZipCompression == "Y")
						echo "selected"; ?>><?= GetMessage("BIZPROC_OPT_USE_GZIP_COMPRESSION_Y") ?></option>
					<option value="N" <?php
					if ($useGZipCompression == "N")
						echo "selected"; ?>><?= GetMessage("BIZPROC_OPT_USE_GZIP_COMPRESSION_N") ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td width="50%" valign="top"><?= GetMessage("BIZPROC_OPT_LOCKED_WI_PATH") ?>:</td>
			<td width="50%" valign="top">
				<?php
				$path = \Bitrix\Main\Config\Option::get("bizproc", "locked_wi_path", "/bizproc/bizproc/?type=is_locked"); ?>
				<input type="text" size="40" name="locked_wi_path" value="<?= htmlspecialcharsbx($path) ?>">
			</td>
		</tr>
		<tr>
			<td width="50%" valign="top"><?= GetMessage("BIZPROC_OPT_TIME_LIMIT") ?>:</td>
			<td width="50%" valign="top">
				<?php
				[$delayTime, $delayType] = CBPSchedulerService::getDelayMinLimit(true);
				?>
				<input type="text" name="delay_min_limit" value="<?= $delayTime ?>" size="5" />
				<select name="delay_min_limit_type">
					<option value="s"<?= ($delayType == "s") ? " selected" : "" ?>><?= GetMessage("BIZPROC_OPT_TIME_LIMIT_S") ?></option>
					<option value="m"<?= ($delayType == "m") ? " selected" : "" ?>><?= GetMessage("BIZPROC_OPT_TIME_LIMIT_M") ?></option>
					<option value="h"<?= ($delayType == "h") ? " selected" : "" ?>><?= GetMessage("BIZPROC_OPT_TIME_LIMIT_H") ?></option>
					<option value="d"<?= ($delayType == "d") ? " selected" : "" ?>><?= GetMessage("BIZPROC_OPT_TIME_LIMIT_D") ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td valign="top" colspan="2" align="center">
				<?php
				$subTabControl->Begin();
				foreach ($arSites as $site)
				{
					$subTabControl->BeginNextTab();
					$curVal = COption::GetOptionString("bizproc", "name_template", "", $site["LID"]);
					?>
					<label><?= GetMessage("BIZPROC_NAME_TEMPLATE") ?></label>:
					<select name="name_template_<?php
					echo $site["LID"] ?>">
					<?php
						$arNameTemplates = CSite::GetNameTemplates();
						$arNameTemplates = array_reverse($arNameTemplates, true); //prepend array with default '' => Site Format value
						$arNameTemplates[""] = GetMessage("BIZPROC_OPTIONS_NAME_IN_SITE_FORMAT");
						$arNameTemplates = array_reverse($arNameTemplates, true);
						foreach ($arNameTemplates as $template => $phrase)
						{
							$template = str_replace(["#NOBR#", "#/NOBR#"], ["", ""], $template);
							?>
							<option value="<?= $template ?>" <?= (($template == $curVal) ? " selected" : "") ?> ><?= $phrase ?></option><?php
						}
						?>
					</select>
					<?php
				}
				$subTabControl->End();
				?>
			</td>
		</tr>
		<?php
		$tabControl->Buttons(); ?>
		<script>
			function RestoreDefaults()
			{
				if (confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
					window.location = "<?= $APPLICATION->GetCurPage() ?>?RestoreDefaults=Y&lang=<?= LANG ?>&mid=<?= urlencode($mid) ?>&<?= bitrix_sessid_get() ?>";
			}
		</script>

		<input type="submit" class="adm-btn-save" <?php
		if ($bizprocPerms < "W")
			echo "disabled" ?> name="Update" value="<?php
		echo GetMessage("MAIN_SAVE") ?>">
		<input type="hidden" name="Update" value="Y">
		<input type="reset" name="reset" value="<?php
		echo GetMessage("MAIN_RESET") ?>">
		<input type="button" <?php
		if ($bizprocPerms < "W")
			echo "disabled" ?> title="<?php
		echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>" OnClick="RestoreDefaults();" value="<?php
		echo GetMessage("MAIN_RESTORE_DEFAULTS") ?>">
		<?php
		$tabControl->End(); ?>
	</form>
<?php
endif;
