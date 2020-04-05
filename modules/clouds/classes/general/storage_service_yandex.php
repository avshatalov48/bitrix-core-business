<?
IncludeModuleLangFile(__FILE__);

class CCloudStorageService_Yandex extends CCloudStorageService_AmazonS3
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
		return "yandex";
	}
	/**
	 * @return string
	*/
	function GetName()
	{
		return "Yandex Object Storage";
	}
	/**
	 * @return array[string]string
	*/
	function GetLocationList()
	{
		return array(
			"" => "storage.yandexcloud.net",
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
			<td>'.GetMessage("CLO_STORAGE_S3_EDIT_ACCESS_KEY").':</td>
			<td><input type="hidden" name="SETTINGS['.$htmlID.'][ACCESS_KEY]" id="'.$htmlID.'ACCESS_KEY" value="'.htmlspecialcharsbx($arSettings['ACCESS_KEY']).'"><input type="text" size="55" name="'.$htmlID.'INP_ACCESS_KEY" id="'.$htmlID.'INP_ACCESS_KEY" value="'.htmlspecialcharsbx($arSettings['ACCESS_KEY']).'" '.($arBucket['READ_ONLY'] === 'Y'? '"disabled"': '').' onchange="BX(\''.$htmlID.'ACCESS_KEY\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_1_'.$htmlID.'" style="display:'.($cur_SERVICE_ID === $this->GetID() || !$bServiceSet? '': 'none').'" class="settings-tr adm-detail-required-field">
			<td>'.GetMessage("CLO_STORAGE_S3_EDIT_SECRET_KEY").':</td>
			<td><input type="hidden" name="SETTINGS['.$htmlID.'][SECRET_KEY]" id="'.$htmlID.'SECRET_KEY" value="'.htmlspecialcharsbx($arSettings['SECRET_KEY']).'"><input type="text" size="55" name="'.$htmlID.'INP_SECRET_KEY" id="'.$htmlID.'INP_SECRET_KEY" value="'.htmlspecialcharsbx($arSettings['SECRET_KEY']).'" autocomplete="off" '.($arBucket['READ_ONLY'] === 'Y'? '"disabled"': '').' onchange="BX(\''.$htmlID.'SECRET_KEY\').value = this.value"></td>
		</tr>
		';
		return $result;
	}

	protected function GetRequestHost($bucket)
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
				return $bucket.".storage.yandexcloud.net";
			else
				return "storage.yandexcloud.net";
		}
	}
	/**
	 * @param array[string]string $arSettings
	 * @param string $verb
	 * @param string $bucket
	 * @param string $file_name
	 * @param string $params
	 * @param string $content
	 * @param array[string]string $additional_headers
	 * @return mixed
	*/
	function SendRequest($arSettings, $verb, $bucket, $file_name='/', $params='', $content='', $additional_headers=/*.(array[string]string).*/array())
	{
		$file_name = str_replace("+", "%20", $file_name);
		return parent::SendRequest($arSettings, $verb, $bucket, $file_name, $params, $content, $additional_headers);
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
		elseif ($proto === "https" && strpos($arBucket["BUCKET"], ".") !== false)
		{
			$host = "storage.yandexcloud.net";
			$pref = $arBucket["BUCKET"];
		}
		else
		{
			$host = $arBucket["BUCKET"].".storage.yandexcloud.net";
			$pref = "";
		}

		if(is_array($arFile))
			$URI = ltrim($arFile["SUBDIR"]."/".$arFile["FILE_NAME"], "/");
		else
			$URI = ltrim($arFile, "/");

		if ($arBucket["PREFIX"] != "")
		{
			if(substr($URI, 0, strlen($arBucket["PREFIX"])+1) !== $arBucket["PREFIX"]."/")
				$URI = $arBucket["PREFIX"]."/".$URI;
		}

		if ($pref !== "")
		{
			$URI = $pref."/".$URI;
		}

		return $proto."://$host/".str_replace("+", "%20", CCloudUtil::URLEncode($URI, "UTF-8"));
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
