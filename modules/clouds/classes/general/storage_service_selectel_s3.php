<?php
IncludeModuleLangFile(__FILE__);

class CCloudStorageService_Selectel_S3 extends CCloudStorageService_S3
{
	public function GetObject()
	{
		return new CCloudStorageService_Selectel_S3();
	}

	public function GetID()
	{
		return 'selectel_s3_storage';
	}

	public function GetName()
	{
		return 'Selectel (S3)';
	}

	public function GetLocationList()
	{
		return [
			'' => 's3.storage.selcloud.ru',
		];
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
			$arSettings = ['ACCESS_KEY' => '', 'SECRET_KEY' => ''];
		}

		$htmlID = htmlspecialcharsbx($this->GetID());
		$display = $cur_SERVICE_ID == $this->GetID() || !$bServiceSet ? '' : 'none';

		$result = '
		<tr id="SETTINGS_0_' . $htmlID . '" style="display:' . $display . '" class="settings-tr adm-detail-required-field">
			<td>' . GetMessage('CLO_STORAGE_SELECTEL_S3_EDIT_ACCESS_KEY') . ':</td>
			<td><input type="hidden" name="SETTINGS[' . $htmlID . '][ACCESS_KEY]" id="' . $htmlID . 'ACCESS_KEY" value="' . htmlspecialcharsbx($arSettings['ACCESS_KEY']) . '"><input type="text" size="55" name="' . $htmlID . 'INP_ACCESS_KEY" id="' . $htmlID . 'INP_ACCESS_KEY" value="' . htmlspecialcharsbx($arSettings['ACCESS_KEY']) . '" ' . ($arBucket['READ_ONLY'] === 'Y' ? '"disabled"' : '') . ' onchange="BX(\'' . $htmlID . 'ACCESS_KEY\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_1_' . $htmlID . '" style="display:' . $display . '" class="settings-tr adm-detail-required-field">
			<td>' . GetMessage('CLO_STORAGE_SELECTEL_S3_EDIT_SECRET_KEY') . ':</td>
			<td><input type="hidden" name="SETTINGS[' . $htmlID . '][SECRET_KEY]" id="' . $htmlID . 'SECRET_KEY" value="' . htmlspecialcharsbx($arSettings['SECRET_KEY']) . '"><input type="text" size="55" name="' . $htmlID . 'INP_SECRET_KEY" id="' . $htmlID . 'INP_SECRET_KEY" value="' . htmlspecialcharsbx($arSettings['SECRET_KEY']) . '" autocomplete="off" ' . ($arBucket['READ_ONLY'] === 'Y' ? '"disabled"' : '') . ' onchange="BX(\'' . $htmlID . 'SECRET_KEY\').value = this.value">' . (
				array_key_exists('SESSION_TOKEN', $arSettings) ?
				'<input type="hidden" name="SETTINGS[' . $htmlID . '][SESSION_TOKEN]" id="' . $htmlID . 'SESSION_TOKEN" value="' . htmlspecialcharsbx($arSettings['SESSION_TOKEN']) . '">' :
				''
			) . '</td>
		</tr>
		<tr id="SETTINGS_2_' . $htmlID . '" style="display:' . $display . '" class="settings-tr">
			<td>&nbsp;</td>
			<td>' . BeginNote() . GetMessage('CLO_STORAGE_SELECTEL_S3_EDIT_GUIDE') . EndNote() . '</td>
		</tr>
		';

		return $result;
	}

	public function CheckSettings($arBucket, &$arSettings)
	{
		global $APPLICATION;
		$aMsg = /*.(array[int][string]string).*/[];

		$result = [
			'ACCESS_KEY' => is_array($arSettings) ? trim($arSettings['ACCESS_KEY']) : '',
			'SECRET_KEY' => is_array($arSettings) ? trim($arSettings['SECRET_KEY']) : '',
			'USE_HTTPS' => 'Y',
		];
		if (is_array($arSettings) && array_key_exists('SESSION_TOKEN', $arSettings))
		{
			$result['SESSION_TOKEN'] = trim($arSettings['SESSION_TOKEN']);
		}

		if ($arBucket['READ_ONLY'] !== 'Y' && $result['ACCESS_KEY'] === '')
		{
			$aMsg[] = [
				'id' => $this->GetID() . 'INP_ACCESS_KEY',
				'text' => GetMessage('CLO_STORAGE_SELECTEL_S3_EMPTY_ACCESS_KEY'),
			];
		}

		if ($arBucket['READ_ONLY'] !== 'Y' && $result['SECRET_KEY'] === '')
		{
			$aMsg[] = [
				'id' => $this->GetID() . 'INP_SECRET_KEY',
				'text' => GetMessage('CLO_STORAGE_SELECTEL_S3_EMPTY_SECRET_KEY'),
			];
		}

		if (!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		else
		{
			$arSettings = $result;
		}

		return true;
	}

	protected function GetRequestHost($bucket, $arSettings)
	{
		if (
			$this->new_end_point !== ''
			&& preg_match('#^(http|https)://' . preg_quote($bucket, '#') . '(.+?)/#', $this->new_end_point, $match) > 0
		)
		{
			return $bucket . $match[2];
		}
		else
		{
			if ($bucket !== '')
			{
				return $bucket . '.s3.storage.selcloud.ru';
			}
			else
			{
				return 's3.storage.selcloud.ru';
			}
		}
	}
}
