<?
IncludeModuleLangFile(__FILE__);

class CCloudStorageService_HotBox extends CCloudStorageService_S3
{
	protected $set_headers =/*.(array[string]string).*/array();
	protected $new_end_point = '';
	protected $_public = true;
	protected $location = '';
	/**
	 * @return CCloudStorageService
	*/
	function GetObject()
	{
		return new CCloudStorageService_HotBox();
	}
	/**
	 * @return string
	*/
	function GetID()
	{
		return "hot_box";
	}
	/**
	 * @return string
	*/
	function GetName()
	{
		return "HotBox";
	}
	/**
	 * @return array[string]string
	*/
	function GetLocationList()
	{
		return array(
			"" => "hb.bizmrg.com",
		);
	}
	/**
	 * @param array[string]string $arBucket
	 * @param bool $bServiceSet
	 * @param string $cur_SERVICE_ID
	 * @param bool $bVarsFromForm
	 * @return string
	*/
	function GetSettingsHTML($arBucket, $bServiceSet, $cur_SERVICE_ID, $bVarsFromForm)
	{
		if($bVarsFromForm)
			$arSettings = $_POST["SETTINGS"][$this->GetID()];
		else
			$arSettings = unserialize($arBucket["SETTINGS"]);

		if(!is_array($arSettings))
			$arSettings = array("ACCESS_KEY" => "", "SECRET_KEY" => "");

		$htmlID = htmlspecialcharsbx($this->GetID());

		$result = '
		<tr id="SETTINGS_0_'.$htmlID.'" style="display:'.($cur_SERVICE_ID === $this->GetID() || !$bServiceSet? '': 'none').'" class="settings-tr adm-detail-required-field">
			<td>'.GetMessage("CLO_STORAGE_HOTBOX_EDIT_ACCESS_KEY").':</td>
			<td><input type="hidden" name="SETTINGS['.$htmlID.'][ACCESS_KEY]" id="'.$htmlID.'ACCESS_KEY" value="'.htmlspecialcharsbx($arSettings['ACCESS_KEY']).'"><input type="text" size="55" name="'.$htmlID.'INP_ACCESS_KEY" id="'.$htmlID.'INP_ACCESS_KEY" value="'.htmlspecialcharsbx($arSettings['ACCESS_KEY']).'" '.($arBucket['READ_ONLY'] === 'Y'? '"disabled"': '').' onchange="BX(\''.$htmlID.'ACCESS_KEY\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_1_'.$htmlID.'" style="display:'.($cur_SERVICE_ID === $this->GetID() || !$bServiceSet? '': 'none').'" class="settings-tr adm-detail-required-field">
			<td>'.GetMessage("CLO_STORAGE_HOTBOX_EDIT_SECRET_KEY").':</td>
			<td><input type="hidden" name="SETTINGS['.$htmlID.'][SECRET_KEY]" id="'.$htmlID.'SECRET_KEY" value="'.htmlspecialcharsbx($arSettings['SECRET_KEY']).'"><input type="text" size="55" name="'.$htmlID.'INP_SECRET_KEY" id="'.$htmlID.'INP_SECRET_KEY" value="'.htmlspecialcharsbx($arSettings['SECRET_KEY']).'" autocomplete="off" '.($arBucket['READ_ONLY'] === 'Y'? '"disabled"': '').' onchange="BX(\''.$htmlID.'SECRET_KEY\').value = this.value"></td>
		</tr>
		';
		return $result;
	}
	/**
	 * @param array[string]string $arBucket
	 * @param array[string]string & $arSettings
	 * @return bool
	*/
	function CheckSettings($arBucket, &$arSettings)
	{
		global $APPLICATION;
		$aMsg =/*.(array[int][string]string).*/array();

		$result = array(
			"ACCESS_KEY" => is_array($arSettings)? trim($arSettings["ACCESS_KEY"]): '',
			"SECRET_KEY" => is_array($arSettings)? trim($arSettings["SECRET_KEY"]): '',
		);
		if(is_array($arSettings) && array_key_exists("SESSION_TOKEN", $arSettings))
		{
			$result["SESSION_TOKEN"] = trim($arSettings["SESSION_TOKEN"]);
		}

		if($arBucket["READ_ONLY"] !== "Y" && $result["ACCESS_KEY"] === '')
		{
			$aMsg[] = array(
				"id" => $this->GetID()."INP_ACCESS_KEY",
				"text" => GetMessage("CLO_STORAGE_HOTBOX_EMPTY_ACCESS_KEY"),
			);
		}

		if($arBucket["READ_ONLY"] !== "Y" && $result["SECRET_KEY"] === '')
		{
			$aMsg[] = array(
				"id" => $this->GetID()."INP_SECRET_KEY",
				"text" => GetMessage("CLO_STORAGE_HOTBOX_EMPTY_SECRET_KEY"),
			);
		}

		if(!empty($aMsg))
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
	/**
	 * @param string $bucket
	 * @return string
	 **/
	protected function GetRequestHost($bucket, $arSettings)
	{
		if(
			$this->new_end_point != ""
			&& preg_match('#^(http|https)://'.preg_quote($bucket, '#').'(.+?)/#', $this->new_end_point, $match) > 0
		)
		{
			return $bucket.$match[2];
		}
		else
		{
			if ($bucket <> '')
				return $bucket.".hb.bizmrg.com";
			else
				return "hb.bizmrg.com";
		}
	}
	/**
	 * @param array[string]string $arBucket
	 * @param mixed $arFile
	 * @return string
	*/
	function GetFileSRC($arBucket, $arFile)
	{
		$proto = CMain::IsHTTPS()? "https": "http";

		if($arBucket["CNAME"] != "")
		{
			$host = $arBucket["CNAME"];
			$pref = "";
		}
		elseif ($proto === "https" && mb_strpos($arBucket["BUCKET"], ".") !== false)
		{
			$host = "hb.bizmrg.com";
			$pref = $arBucket["BUCKET"];
		}
		else
		{
			$host = $arBucket["BUCKET"].".hb.bizmrg.com";
			$pref = "";
		}

		if(is_array($arFile))
			$URI = ltrim($arFile["SUBDIR"]."/".$arFile["FILE_NAME"], "/");
		else
			$URI = ltrim($arFile, "/");

		if ($arBucket["PREFIX"] != "")
		{
			if(mb_substr($URI, 0, mb_strlen($arBucket["PREFIX"]) + 1) !== $arBucket["PREFIX"]."/")
				$URI = $arBucket["PREFIX"]."/".$URI;
		}

		if ($pref !== "")
		{
			$URI = $pref."/".$URI;
		}

		return $proto."://$host/".CCloudUtil::URLEncode($URI, "UTF-8");
	}
	/**
	 * @param array[string]string $arBucket
	 * @return bool
	*/
	function DeleteBucket($arBucket)
	{
		//Do not delete bucket if there is some files left
		if(!$this->IsEmptyBucket($arBucket))
			return false;

		return parent::DeleteBucket($arBucket);
	}
}
