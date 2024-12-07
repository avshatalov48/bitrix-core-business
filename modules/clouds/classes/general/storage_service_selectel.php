<?php
IncludeModuleLangFile(__FILE__);

class CCloudStorageService_Selectel extends CCloudStorageService_OpenStackStorage
{
	public function GetObject()
	{
		return new CCloudStorageService_Selectel();
	}

	public function GetID()
	{
		return 'selectel_storage';
	}

	public function GetName()
	{
		return 'Selectel (OpenStack deprecated)';
	}

	public function GetSettingsHTML($arBucket, $bServiceSet, $cur_SERVICE_ID, $bVarsFromForm)
	{
		if ($bVarsFromForm)
		{
			$arSettings = $_POST['SETTINGS'][$this->GetID()];
		}
		else
		{
			$arSettings = unserialize($arBucket['SETTINGS'], ['allowed_classes' => false]);
		}

		if (!is_array($arSettings))
		{
			$arSettings = ['HOST' => 'auth.selcdn.ru', 'USER' => '', 'KEY' => ''];
		}

		$htmlID = htmlspecialcharsbx($this->GetID());
		$display = $cur_SERVICE_ID == $this->GetID() || !$bServiceSet ? '' : 'none';
		$isNew = !isset($arBucket['ID']) || !$arBucket['ID'];

		if (!$isNew)
		{
			$result = '
			<tr id="SETTINGS_3_' . $htmlID . '" style="display:' . $display . '" class="settings-tr">
				<td>' . GetMessage('CLO_STORAGE_SELECTEL_EDIT_MIGRATION') . ':</td>
				<td><input type="checkbox" name="SETTINGS[' . $htmlID . '][MIGRATE_TO]" id="' . $htmlID . '_MIGRATE" value="generic_s3" onclick="
					BX(\'' . $htmlID . 'INP_HOST\').value = this.checked ? \'s3.storage.selcloud.ru\' : \'' . htmlspecialcharsbx($arSettings['HOST']) . '\';
					BX(\'SETTINGS_1_' . $htmlID . '\').style.display = this.checked ? \'none\' : \'\';
					BX(\'SETTINGS_2_' . $htmlID . '\').style.display = this.checked ? \'none\' : \'\';
					BX(\'NSETTINGS_4_' . $htmlID . '\').style.display = this.checked ? \'\' : \'none\';
					BX(\'NSETTINGS_5_' . $htmlID . '\').style.display = this.checked ? \'\' : \'none\';
					BX(\'NSETTINGS_6_' . $htmlID . '\').style.display = this.checked ? \'\' : \'none\';
					BX(\'NSETTINGS_7_' . $htmlID . '\').style.display = this.checked ? \'\' : \'none\';
				"></td>
			</tr>
			<tr id="NSETTINGS_4_' . $htmlID . '" style="display:none" class="settings-tr">
				<td>&nbsp;</td>
				<td>' . BeginNote() . GetMessage('CLO_STORAGE_SELECTEL_EDIT_MIGRATION_GUIDE') . EndNote() . '</td>
			</tr>
			';
		}
		else
		{
			$result = '';
		}

		$result .= '
		<tr id="SETTINGS_0_' . $htmlID . '" style="display:' . $display . '" class="settings-tr adm-detail-required-field">
			<td>' . GetMessage('CLO_STORAGE_SELECTEL_EDIT_HOST') . ':</td>
			<td><input type="text" size="55" name="SETTINGS[' . $htmlID . '][HOST]" id="' . $htmlID . 'INP_HOST" value="' . htmlspecialcharsbx($arSettings['HOST']) . '" ' . ($arBucket['READ_ONLY'] == 'Y' ? '"disabled"' : '') . '></td>
		</tr>
		<tr id="SETTINGS_1_' . $htmlID . '" style="display:' . $display . '" class="settings-tr adm-detail-required-field">
			<td>' . GetMessage('CLO_STORAGE_SELECTEL_EDIT_USER') . ':</td>
			<td><input type="text" size="55" name="SETTINGS[' . $htmlID . '][USER]" id="' . $htmlID . 'INP_USER" value="' . htmlspecialcharsbx($arSettings['USER']) . '" ' . ($arBucket['READ_ONLY'] == 'Y' ? '"disabled"' : '') . '></td>
		</tr>
		<tr id="SETTINGS_2_' . $htmlID . '" style="display:' . $display . '" class="settings-tr adm-detail-required-field">
			<td>' . GetMessage('CLO_STORAGE_SELECTEL_EDIT_KEY') . ':</td>
			<td><input type="text" size="55" name="SETTINGS[' . $htmlID . '][KEY]" id="' . $htmlID . 'INP_KEY" value="' . htmlspecialcharsbx($arSettings['KEY']) . '" autocomplete="off" ' . ($arBucket['READ_ONLY'] == 'Y' ? '"disabled"' : '') . '</td>
		</tr>
		';

		if (!$isNew)
		{
			$result .= '
			<tr id="NSETTINGS_5_' . $htmlID . '" style="display:none" class="settings-tr adm-detail-required-field">
				<td>' . GetMessage('CLO_STORAGE_S3_EDIT_ACCESS_KEY') . ':</td>
				<td><input type="text" size="55" name="SETTINGS[' . $htmlID . '][ACCESS_KEY]" id="' . $htmlID . 'INP_ACCESS_KEY" value="' . htmlspecialcharsbx($arSettings['ACCESS_KEY']) . '" ' . ($arBucket['READ_ONLY'] === 'Y' ? '"disabled"' : '') . '></td>
			</tr>
			<tr id="NSETTINGS_6_' . $htmlID . '" style="display:none" class="settings-tr adm-detail-required-field">
				<td>' . GetMessage('CLO_STORAGE_S3_EDIT_SECRET_KEY') . ':</td>
				<td><input type="text" size="55" name="SETTINGS[' . $htmlID . '][SECRET_KEY]" id="' . $htmlID . 'INP_SECRET_KEY" value="' . htmlspecialcharsbx($arSettings['SECRET_KEY']) . '" autocomplete="off" ' . ($arBucket['READ_ONLY'] === 'Y' ? '"disabled"' : '') . '></td>
			</tr>
			<tr id="NSETTINGS_7_' . $htmlID . '" style="display:none" class="settings-tr">
				<td>' . GetMessage('CLO_STORAGE_S3_EDIT_USE_HTTPS') . ':</td>
				<td><input type="hidden" name="SETTINGS[' . $htmlID . '][USE_HTTPS]" id="' . $htmlID . 'KEY" value="N"><input type="checkbox" name="SETTINGS[' . $htmlID . '][USE_HTTPS]" id="' . $htmlID . 'USE_HTTPS" value="Y" checked="checked"></td>
			</tr>
			';
		}

		return $result;
	}

	public function CheckSettings($arBucket, &$arSettings)
	{
		if (is_array($arSettings))
		{
			$arSettings['HOST'] = 'auth.selcdn.ru';
		}

		return parent::CheckSettings($arBucket, $arSettings);
	}
}
