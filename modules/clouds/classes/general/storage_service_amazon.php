<?php
IncludeModuleLangFile(__FILE__);

class CCloudStorageService_AmazonS3 extends CCloudStorageService_S3
{
	/**
	 * @return CCloudStorageService
	*/
	function GetObject()
	{
		return new CCloudStorageService_AmazonS3();
	}
	/**
	 * @return string
	*/
	function GetID()
	{
		return "amazon_s3";
	}
	/**
	 * @return string
	*/
	function GetName()
	{
		return "Amazon Simple Storage Service";
	}
	/**
	 * @return array[string]string
	*/
	function GetLocationList()
	{
		// http://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region
		return array(
			"" => "US Standard",
			"us-east-2" => "US East (Ohio)",
			"us-east-1" => "US East (N. Virginia)",
			"us-west-1" => "US West (N. California)",
			"us-west-2" => "US West (Oregon)",
			"ap-east-1" => "Asia Pacific (Hong Kong)",
			"ap-south-1" => "Asia Pacific (Mumbai)",
			"ap-northeast-3" => "Asia Pacific (Osaka-Local)",
			"ap-northeast-2" => "Asia Pacific (Seoul)",
			"ap-southeast-1" => "Asia Pacific (Singapore)",
			"ap-southeast-2" => "Asia Pacific (Sydney)",
			"ap-northeast-1" => "Asia Pacific (Tokyo)",
			"ca-central-1" => "Canada (Central)",
			"eu-central-1" => "Europe (Frankfurt)",
			"eu-west-1" => "Europe (Ireland)",
			"eu-west-2" => "Europe (London)",
			"eu-west-3" => "Europe (Paris)",
			"eu-north-1" => "Europe (Stockholm)",
			"me-south-1" => "Middle East (Bahrain)",
			"sa-east-1" => "South America (Sao Paulo)",
		);
	}
	/**
	 * @return array[string]string
	*/
	function GetAPList()
	{
		// https://docs.aws.amazon.com/general/latest/gr/s3.html
		return array(
			"" => "s3.amazonaws.com",
			"us-east-2" => "s3.us-east-2.amazonaws.com",
			"us-east-1" => "s3.us-east-1.amazonaws.com",
			"us-west-1" => "s3.us-west-1.amazonaws.com",
			"us-west-2" => "s3.us-west-2.amazonaws.com",
			"ap-east-1" => "s3.ap-east-1.amazonaws.com",
			"ap-south-1" => "s3.ap-south-1.amazonaws.com",
			"ap-northeast-3" => "s3.ap-northeast-3.amazonaws.com",
			"ap-northeast-2" => "s3.ap-northeast-2.amazonaws.com",
			"ap-southeast-1" => "s3.ap-southeast-1.amazonaws.com",
			"ap-southeast-2" => "s3.ap-southeast-2.amazonaws.com",
			"ap-northeast-1" => "s3.ap-northeast-1.amazonaws.com",
			"ca-central-1" => "s3.ca-central-1.amazonaws.com",
			"cn-north-1" => "s3.cn-north-1.amazonaws.com.cn",
			"cn-northwest-1" => "s3.cn-northwest-1.amazonaws.com.cn",
			"eu-central-1" => "s3.eu-central-1.amazonaws.com",
			"eu-west-1" => "s3.eu-west-1.amazonaws.com",
			"eu-west-2" => "s3.eu-west-2.amazonaws.com",
			"eu-west-3" => "s3.eu-west-3.amazonaws.com",
			"eu-north-1" => "s3.eu-north-1.amazonaws.com",
			"sa-east-1" => "s3.sa-east-1.amazonaws.com",
			"me-south-1" => "s3.me-south-1.amazonaws.com",
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
			<td>'.GetMessage("CLO_STORAGE_AMAZON_EDIT_ACCESS_KEY").':</td>
			<td><input type="hidden" name="SETTINGS['.$htmlID.'][ACCESS_KEY]" id="'.$htmlID.'ACCESS_KEY" value="'.htmlspecialcharsbx($arSettings['ACCESS_KEY']).'"><input type="text" size="55" name="'.$htmlID.'INP_ACCESS_KEY" id="'.$htmlID.'INP_ACCESS_KEY" value="'.htmlspecialcharsbx($arSettings['ACCESS_KEY']).'" '.($arBucket['READ_ONLY'] === 'Y'? '"disabled"': '').' onchange="BX(\''.$htmlID.'ACCESS_KEY\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_1_'.$htmlID.'" style="display:'.($cur_SERVICE_ID === $this->GetID() || !$bServiceSet? '': 'none').'" class="settings-tr adm-detail-required-field">
			<td>'.GetMessage("CLO_STORAGE_AMAZON_EDIT_SECRET_KEY").':</td>
			<td><input type="hidden" name="SETTINGS['.$htmlID.'][SECRET_KEY]" id="'.$htmlID.'SECRET_KEY" value="'.htmlspecialcharsbx($arSettings['SECRET_KEY']).'"><input type="text" size="55" name="'.$htmlID.'INP_SECRET_KEY" id="'.$htmlID.'INP_SECRET_KEY" value="'.htmlspecialcharsbx($arSettings['SECRET_KEY']).'" autocomplete="off" '.($arBucket['READ_ONLY'] === 'Y'? '"disabled"': '').' onchange="BX(\''.$htmlID.'SECRET_KEY\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_2_'.$htmlID.'" style="display:'.($cur_SERVICE_ID === $this->GetID() || !$bServiceSet? '': 'none').'" class="settings-tr">
			<td>&nbsp;</td>
			<td>'.BeginNote().GetMessage("CLO_STORAGE_AMAZON_EDIT_HELP").EndNote().'</td>
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
				"text" => GetMessage("CLO_STORAGE_AMAZON_EMPTY_ACCESS_KEY"),
			);
		}

		if($arBucket["READ_ONLY"] !== "Y" && $result["SECRET_KEY"] === '')
		{
			$aMsg[] = array(
				"id" => $this->GetID()."INP_SECRET_KEY",
				"text" => GetMessage("CLO_STORAGE_AMAZON_EMPTY_SECRET_KEY"),
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
		elseif ($this->location)
		{
			if ($bucket <> '')
				return $bucket.".s3.".$this->location.".amazonaws.com";
			else
				return "s3.".$this->location.".amazonaws.com";
		}
		else
		{
			if ($bucket <> '')
				return $bucket.".s3.amazonaws.com";
			else
				return "s3.amazonaws.com";
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

		static $aps = null;
		if (!$aps)
			$aps = self::GetAPList();

		if($arBucket["CNAME"] != "")
		{
			$host = $arBucket["CNAME"];
			$pref = "";
		}
		elseif ($proto === "https" && strpos($arBucket["BUCKET"], ".") !== false)
		{
			if (isset($aps[$arBucket["LOCATION"]]))
				$host = $aps[$arBucket["LOCATION"]];
			else
				$host = $aps[""];

			$pref = $arBucket["BUCKET"];
		}
		else
		{
			if (isset($aps[$arBucket["LOCATION"]]))
				$host = $arBucket["BUCKET"].".".$aps[$arBucket["LOCATION"]];
			else
				$host = $aps[""];

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

		return $proto."://$host/".CCloudUtil::URLEncode($URI, "UTF-8", true);
	}
}
