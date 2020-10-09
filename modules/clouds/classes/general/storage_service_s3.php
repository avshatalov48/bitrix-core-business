<?php
IncludeModuleLangFile(__FILE__);

class CCloudStorageService_S3 extends CCloudStorageService
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
		return new CCloudStorageService_S3();
	}
	/**
	 * @return string
	*/
	function GetID()
	{
		return "generic_s3";
	}
	/**
	 * @return string
	*/
	function GetName()
	{
		return "S3 compatible storage";
	}
	/**
	 * @return array[string]string
	*/
	function GetLocationList()
	{
		return array(
			"" => "N/A",
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
		{
			$arSettings = array(
				"HOST" => "",
				"ACCESS_KEY" => "",
				"SECRET_KEY" => "",
			);
		}

		$htmlID = htmlspecialcharsbx($this->GetID());

		$result = '
		<tr id="SETTINGS_0_'.$htmlID.'" style="display:'.($cur_SERVICE_ID === $this->GetID() || !$bServiceSet? '': 'none').'" class="settings-tr adm-detail-required-field">
			<td>'.GetMessage("CLO_STORAGE_S3_EDIT_HOST").':</td>
			<td><input type="hidden" name="SETTINGS['.$htmlID.'][HOST]" id="'.$htmlID.'HOST" value="'.htmlspecialcharsbx($arSettings['HOST']).'"><input type="text" size="55" name="'.$htmlID.'INP_HOST" id="'.$htmlID.'INP_HOST" value="'.htmlspecialcharsbx($arSettings['HOST']).'" '.($arBucket['READ_ONLY'] == 'Y'? '"disabled"': '').' onchange="BX(\''.$htmlID.'HOST\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_1_'.$htmlID.'" style="display:'.($cur_SERVICE_ID === $this->GetID() || !$bServiceSet? '': 'none').'" class="settings-tr adm-detail-required-field">
			<td>'.GetMessage("CLO_STORAGE_S3_EDIT_ACCESS_KEY").':</td>
			<td><input type="hidden" name="SETTINGS['.$htmlID.'][ACCESS_KEY]" id="'.$htmlID.'ACCESS_KEY" value="'.htmlspecialcharsbx($arSettings['ACCESS_KEY']).'"><input type="text" size="55" name="'.$htmlID.'INP_ACCESS_KEY" id="'.$htmlID.'INP_ACCESS_KEY" value="'.htmlspecialcharsbx($arSettings['ACCESS_KEY']).'" '.($arBucket['READ_ONLY'] === 'Y'? '"disabled"': '').' onchange="BX(\''.$htmlID.'ACCESS_KEY\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_2_'.$htmlID.'" style="display:'.($cur_SERVICE_ID === $this->GetID() || !$bServiceSet? '': 'none').'" class="settings-tr adm-detail-required-field">
			<td>'.GetMessage("CLO_STORAGE_S3_EDIT_SECRET_KEY").':</td>
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
			"HOST" => is_array($arSettings)? trim($arSettings["HOST"]): '',
			"ACCESS_KEY" => is_array($arSettings)? trim($arSettings["ACCESS_KEY"]): '',
			"SECRET_KEY" => is_array($arSettings)? trim($arSettings["SECRET_KEY"]): '',
		);

		if($arBucket["READ_ONLY"] !== "Y" && $result["HOST"] === '')
		{
			$aMsg[] = array(
				"id" => $this->GetID()."INP_HOST",
				"text" => GetMessage("CLO_STORAGE_S3_EMPTY_HOST"),
			);
		}

		if($arBucket["READ_ONLY"] !== "Y" && $result["ACCESS_KEY"] === '')
		{
			$aMsg[] = array(
				"id" => $this->GetID()."INP_ACCESS_KEY",
				"text" => GetMessage("CLO_STORAGE_S3_EMPTY_ACCESS_KEY"),
			);
		}

		if($arBucket["READ_ONLY"] !== "Y" && $result["SECRET_KEY"] === '')
		{
			$aMsg[] = array(
				"id" => $this->GetID()."INP_SECRET_KEY",
				"text" => GetMessage("CLO_STORAGE_S3_EMPTY_SECRET_KEY"),
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
	 * @param string $data
	 * @param string $key
	 * @return string
	*/
	function hmacsha1($data, $key)
	{
		if(mb_strlen($key) > 64)
			$key=pack('H*', sha1($key));
		$key = str_pad($key, 64, chr(0x00));
		$ipad = str_repeat(chr(0x36), 64);
		$opad = str_repeat(chr(0x5c), 64);
		$hmac = pack('H*', sha1(($key^$opad).pack('H*', sha1(($key^$ipad).$data))));
		return $hmac;
	}
	/**
	 * @param array[string]string $arSettings
	 * @param string $RequestMethod
	 * @param string $bucket
	 * @param string $RequestURI
	 * @param string $ContentType
	 * @param array[string]string $additional_headers
	 * @param string $params
	 * @param string|resource $content
	 * @return array[string]string
	*/
	function SignRequest($arSettings, $RequestMethod, $bucket, $RequestURI, $ContentType, $additional_headers, $params = "", $content = "")
	{
		static $search = array("+", "=", "%7E");
		static $replace = array("%20", "%3D", "~");

		if (is_resource($content))
		{
			$streamPosition = ftell($content);
			$hashResource = hash_init("sha256");
			hash_update_stream($hashResource, $content);
			$HashedPayload = hash_final($hashResource);
			fseek($content, $streamPosition);
		}
		else
		{
			$HashedPayload = hash("sha256", $content, false);
		}
		$additional_headers["x-amz-content-sha256"] = $HashedPayload;

		$Time = time();
		$RequestDate = gmdate('Ymd', $Time);
		$RequestTime = gmdate('Ymd', $Time).'T'.gmdate('His', $Time).'Z';
		$additional_headers["x-amz-date"] = $RequestTime;

		$CanonicalizedResource = $RequestURI <> ''? (string)str_replace($search, $replace, $RequestURI): "/";

		$CanonicalQuery = explode("&", ltrim($params, "?"));
		sort($CanonicalQuery);
		$CanonicalQueryString = implode("&", $CanonicalQuery);

		$CanonicalHeaders = /*.(array[string]string).*/ array();
		foreach($additional_headers as $key => $value)
		{
			$key = mb_strtolower($key);
			if (isset($CanonicalHeaders[$key]))
				$CanonicalHeaders[$key] .= ",";
			else
				$CanonicalHeaders[$key] = $key.":";
			$CanonicalHeaders[$key] .= trim($value, " \t\n\r");
		}
		ksort($CanonicalHeaders);
		$CanonicalHeadersString = implode("\n", $CanonicalHeaders);
		$SignedHeaders = implode(";", array_keys($CanonicalHeaders));

		$CanonicalRequest = "";
		$CanonicalRequest .= $RequestMethod."\n";
		$CanonicalRequest .= $CanonicalizedResource."\n";
		$CanonicalRequest .= $CanonicalQueryString."\n";
		$CanonicalRequest .= $CanonicalHeadersString."\n\n";
		$CanonicalRequest .= $SignedHeaders."\n";
		$CanonicalRequest .= $HashedPayload;

		$Algorithm = "AWS4-HMAC-SHA256";
		$Region = $this->location? $this->location: 'us-east-1';
		$Service = "s3";
		$Scope = $RequestDate."/".$Region."/".$Service."/aws4_request";

		$StringToSign = "";
		$StringToSign .= $Algorithm."\n";
		$StringToSign .= $RequestTime."\n";
		$StringToSign .= $Scope."\n";
		$StringToSign .= hash("sha256", $CanonicalRequest, false);

		$kSecret  = $arSettings["SECRET_KEY"];
		$kDate    = hash_hmac("sha256", $RequestDate, "AWS4".$kSecret, true);
		$kRegion  = hash_hmac("sha256", $Region, $kDate, true);
		$kService = hash_hmac("sha256", $Service, $kRegion, true);
		$kSigning = hash_hmac("sha256", "aws4_request", $kService, true);

		$Signature = hash_hmac("sha256", $StringToSign, $kSigning, false);

		$Authorization = "$Algorithm Credential=$arSettings[ACCESS_KEY]/$Scope,SignedHeaders=$SignedHeaders,Signature=$Signature";

		return array(
			"Date" => $RequestTime,
			"Authorization" => $Authorization,
			"x-amz-date" => $RequestTime,
			"x-amz-content-sha256" => $HashedPayload,
		);
	}
	/**
	 * @param string $location
	 * @return void
	 **/
	function SetLocation($location)
	{
		if ($location)
			$this->location = $location;
		else
			$this->location = "";
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
				return $bucket.".".$arSettings["HOST"];
			else
				return $arSettings["HOST"];
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
		global $APPLICATION;
		$this->status = 0;

		$obRequest = new CHTTP;
		if (isset($additional_headers["option-file-result"]))
		{
			$obRequest->fp = $additional_headers["option-file-result"];
		}

		if(isset($additional_headers["Content-Type"]))
		{
			$ContentType = $additional_headers["Content-Type"];
		}
		else
		{
			$ContentType = $content != ""? 'text/plain': '';
		}
		unset($additional_headers["Content-Type"]);

		foreach($this->set_headers as $key => $value)
		{
			$additional_headers[$key] = $value;
		}

		if(array_key_exists("SESSION_TOKEN", $arSettings))
		{
			$additional_headers["x-amz-security-token"] = $arSettings["SESSION_TOKEN"];
		}

		$additional_headers["host"] = $this->GetRequestHost($bucket, $arSettings);

		foreach($this->SignRequest($arSettings, $verb, $bucket, $file_name, $ContentType, $additional_headers, $params, $content) as $key => $value)
		{
			$obRequest->additional_headers[$key] = $value;
		}

		foreach($additional_headers as $key => $value)
			if(preg_match("/^(option-|host\$)/", $key) == 0)
				$obRequest->additional_headers[$key] = $value;

		$host = $additional_headers["host"];

		$was_end_point = $this->new_end_point;
		$this->new_end_point = '';

		$this->status = 0;
		$this->host = $host;
		$this->verb = $verb;
		$this->url =  $file_name.$params;
		$this->headers = array();
		$this->errno = 0;
		$this->errstr = '';
		$this->result = '';

		$request_id = '';
		if (defined("BX_CLOUDS_TRACE") && $verb !== "GET" && $verb !== "HEAD")
		{
			$stime = microtime(1);
			$request_id = md5((string)mt_rand());
			AddMessage2Log('{'
				.'"request_id": "'.$request_id.'";'
				.'"portal":"'.(CModule::IncludeModule('replica')? getNameByDomain(): $_SERVER["HOST_NAME"]).'";'
				.'"verb":"'.$verb.'";'
				.'"host":"'.$host.'";'
				.'"uri":"'.$file_name.$params.'"'
			.'}', '', 0);
		}

		$obRequest->Query($verb, $host, 80, $file_name.$params, $content, '', $ContentType);

		if ($request_id != '')
		{
			AddMessage2Log('{'
				.'"request_id": "'.$request_id.'";'
				.'"portal":"'.(CModule::IncludeModule('replica')? getNameByDomain(): $_SERVER["HOST_NAME"]).'";'
				.'"verb":"'.$verb.'";'
				.'"host":"'.$host.'";'
				.'"uri":"'.$file_name.$params.'";'
				.'"status":"'.$obRequest->status.'";'
				.'"time": "'.(round(microtime(1)-$stime,6)).'"'
			.'}', '', 0);
		}

		$this->status = $obRequest->status;
		$this->headers = $obRequest->headers;
		$this->errno = $obRequest->errno;
		$this->errstr = $obRequest->errstr;
		$this->result = $obRequest->result;

		if($obRequest->status == 200)
		{
			if(
				isset($additional_headers["option-raw-result"])
				|| isset($additional_headers["option--result"])
			)
			{
				return $obRequest->result;
			}
			elseif($obRequest->result != "")
			{
				$obXML = new CDataXML;
				$text = preg_replace("/<"."\\?XML.*?\\?".">/i", "", $obRequest->result);
				if($obXML->LoadString($text))
				{
					$arXML = $obXML->GetArray();
					if(is_array($arXML))
					{
						return $arXML;
					}
				}
				//XML parse error
				$e = new CApplicationException(GetMessage('CLO_STORAGE_S3_XML_PARSE_ERROR', array('#errno#'=>'1')));
				$APPLICATION->ThrowException($e);
				return false;
			}
			else
			{
				//Empty success result
				return array();
			}
		}
		elseif(
			$obRequest->status == 307  //Temporary redirect
			&& isset($obRequest->headers["Location"])
			&& $was_end_point === "" //No recurse yet
		)
		{
			$this->new_end_point = $obRequest->headers["Location"];
			return $this->SendRequest(
				$arSettings,
				$verb,
				$bucket,
				$file_name,
				$params,
				$content,
				$additional_headers
			);
		}
		elseif($obRequest->status > 0)
		{
			if($obRequest->result != "")
			{
				$obXML = new CDataXML;
				if($obXML->LoadString($obRequest->result))
				{
					$node = $obXML->SelectNodes("/Error/Message");
					if (is_object($node))
					{
						$errorMessage = trim($node->textContent(), '.');
						$e = new CApplicationException(GetMessage('CLO_STORAGE_S3_XML_ERROR', array(
							'#errmsg#' => $errorMessage,
						)));
						$APPLICATION->ThrowException($e);
						return false;
					}
				}
			}
			$e = new CApplicationException(GetMessage('CLO_STORAGE_S3_XML_PARSE_ERROR', array('#errno#'=>'2')));
			$APPLICATION->ThrowException($e);
			return false;
		}
		else
		{
			$e = new CApplicationException(GetMessage('CLO_STORAGE_S3_XML_PARSE_ERROR', array('#errno#'=>'3')));
			$APPLICATION->ThrowException($e);
			return false;
		}
	}

	function ListBuckets($arBucket)
	{
		global $APPLICATION;

		$result = array(
			"bucket" => array(),
			"ctime" => array(),
		);

		$this->SetLocation($arBucket["LOCATION"]);
		$marker = $pageSize > 0? $filePath.$pageMarker: '';
		while(true)
		{
			$response = $this->SendRequest(
				$arBucket["SETTINGS"],
				'GET',
				'',
				'/',
				''
			);

			if(
				$this->status == 200
				&& is_array($response)
				&& isset($response["ListAllMyBucketsResult"])
				&& is_array($response["ListAllMyBucketsResult"])
				&& isset($response["ListAllMyBucketsResult"]["#"])
				&& is_array($response["ListAllMyBucketsResult"]["#"])
			)
			{
				$ListAllMyBucketsResult = $response["ListAllMyBucketsResult"]["#"];
				if(
					isset($ListAllMyBucketsResult["Buckets"])
					&& is_array($ListAllMyBucketsResult["Buckets"])
					&& isset($ListAllMyBucketsResult["Buckets"][0])
					&& is_array($ListAllMyBucketsResult["Buckets"][0])
					&& isset($ListAllMyBucketsResult["Buckets"][0]["#"])
					&& is_array($ListAllMyBucketsResult["Buckets"][0]["#"])
				)
				{
					foreach($ListAllMyBucketsResult["Buckets"][0]["#"]["Bucket"] as $Bucket)
					{
						$Name = $Bucket["#"]["Name"][0]["#"];
						$CreationDate = $Bucket["#"]["CreationDate"][0]["#"];
						$result["bucket"][] = $APPLICATION->ConvertCharset(urldecode($Name), "UTF-8", LANG_CHARSET);
						$result["ctime"][] = strtotime($CreationDate);
					}
				}
			}
			elseif ($this->checkForTokenExpiration($this->status, $this->result))
			{
				$this->tokenHasExpired = true;
				return false;
			}
			else
			{
				return false;
			}
			break;
		}

		return $result;
	}
	/**
	 * @param array[string]string $arBucket
	 * @return bool
	*/
	function CreateBucket($arBucket)
	{
		global $APPLICATION;

		$arFiles = $this->ListFiles($arBucket, '/', false, 1);
		if(is_array($arFiles))
			return true;

		// The bucket already exists and user has specified wrong location
		if (
			$this->status == 301
			&& $arBucket["LOCATION"] != ""
			&& $this->GetLastRequestHeader('x-amz-bucket-region') != ''
			&& $this->GetLastRequestHeader('x-amz-bucket-region') != $arBucket["LOCATION"]
		)
		{
			return false;
		}

		if($arBucket["LOCATION"] != "")
			$content =
				'<CreateBucketConfiguration xmlns="http://s3.amazonaws.com/doc/2006-03-01/">'.
				'<LocationConstraint>'.$arBucket["LOCATION"].'</LocationConstraint>'.
				'</CreateBucketConfiguration>';
		else
			$content = '';

		$this->SetLocation($arBucket["LOCATION"]);
		$response = $this->SendRequest(
			$arBucket["SETTINGS"],
			'PUT',
			$arBucket["BUCKET"],
			'/',
			'',
			$content
		);

		if($this->status == 409/*Already exists*/)
		{
			$APPLICATION->ResetException();
			return true;
		}
		else
		{
			return is_array($response);
		}
	}
	/**
	 * @param array[string]string $arBucket
	 * @return bool
	*/
	function IsEmptyBucket($arBucket)
	{
		global $APPLICATION;

		$this->SetLocation($arBucket["LOCATION"]);
		$response = $this->SendRequest(
			$arBucket["SETTINGS"],
			'GET',
			$arBucket["BUCKET"],
			'/',
			'?max-keys=1'.($arBucket["PREFIX"] != ""? '&prefix='.$arBucket["PREFIX"].'/': '')
		);

		if($this->status == 404 || $this->status == 403)
		{
			$APPLICATION->ResetException();
			return true;
		}
		elseif(is_array($response))
		{
			return
				!isset($response["ListBucketResult"])
				|| !is_array($response["ListBucketResult"])
				|| !isset($response["ListBucketResult"]["#"])
				|| !is_array($response["ListBucketResult"]["#"])
				|| !isset($response["ListBucketResult"]["#"]["Contents"])
				|| !is_array($response["ListBucketResult"]["#"]["Contents"]);
		}
		else
		{
			return false;
		}
	}
	/**
	 * @param array[string]string $arBucket
	 * @return bool
	*/
	function DeleteBucket($arBucket)
	{
		global $APPLICATION;

		if($arBucket["PREFIX"] != "")
		{
			//Do not delete bucket if there is some files left
			if(!$this->IsEmptyBucket($arBucket))
				return false;

			//Let's pretend we deleted the bucket
			return true;
		}

		$this->SetLocation($arBucket["LOCATION"]);
		$response = $this->SendRequest(
			$arBucket["SETTINGS"],
			'DELETE',
			$arBucket["BUCKET"]
		);

		if(
			$this->status == 204/*No content*/
			|| $this->status == 404/*Not exists*/
			|| $this->status == 403/*Access denied*/
		)
		{
			$APPLICATION->ResetException();
			return true;
		}
		else
		{
			return is_array($response);
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
			$host = $arBucket["SETTINGS"]["HOST"];
			$pref = $arBucket["BUCKET"];
		}
		else
		{
			$host = $arBucket["BUCKET"].".".$arBucket["SETTINGS"]["HOST"];
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
	 * @param string $filePath
	 * @return bool
	*/
	function FileExists($arBucket, $filePath)
	{
		global $APPLICATION;

		if($arBucket["PREFIX"] != "")
		{
			if(mb_substr($filePath, 0, mb_strlen($arBucket["PREFIX"]) + 2) != "/".$arBucket["PREFIX"]."/")
				$filePath = "/".$arBucket["PREFIX"]."/".ltrim($filePath, "/");
		}
		$filePath = CCloudUtil::URLEncode($filePath, "UTF-8", true);

		$this->SetLocation($arBucket["LOCATION"]);
		$this->SendRequest(
			$arBucket["SETTINGS"],
			'HEAD',
			$arBucket["BUCKET"],
			$filePath
		);

		if($this->status == 200)
		{
			if (isset($this->headers["Content-Length"]) && $this->headers["Content-Length"] > 0)
				return $this->headers["Content-Length"];
			else
				return true;
		}
		elseif($this->status == 206)
		{
			$APPLICATION->ResetException();
			return true;
		}
		else//if($this->status == 404)
		{
			$APPLICATION->ResetException();
			return false;
		}
	}

	function FileCopy($arBucket, $arFile, $filePath)
	{
		global $APPLICATION;

		if($arBucket["PREFIX"])
		{
			if(mb_substr($filePath, 0, mb_strlen($arBucket["PREFIX"]) + 2) != "/".$arBucket["PREFIX"]."/")
				$filePath = "/".$arBucket["PREFIX"]."/".ltrim($filePath, "/");
		}

		$additional_headers = array();
		if($this->_public)
			$additional_headers["x-amz-acl"] = "public-read";
		$additional_headers["x-amz-copy-source"] = CCloudUtil::URLEncode("/".$arBucket["BUCKET"]."/".($arBucket["PREFIX"]? $arBucket["PREFIX"]."/": "").($arFile["SUBDIR"]? $arFile["SUBDIR"]."/": "").$arFile["FILE_NAME"], "UTF-8", true);
		$additional_headers["Content-Type"] = $arFile["CONTENT_TYPE"];

		if (
			defined("BX_CLOUDS_COUNTERS_DEBUG")
			&& !preg_match(BX_CLOUDS_COUNTERS_DEBUG, $filePath)
		)
		{
			\CCloudsDebug::getInstance("copy")->startAction($filePath);
		}

		$this->SetLocation($arBucket["LOCATION"]);
		$this->SendRequest(
			$arBucket["SETTINGS"],
			'PUT',
			$arBucket["BUCKET"],
			CCloudUtil::URLEncode($filePath, "UTF-8", true),
			'',
			'',
			$additional_headers
		);

		if (
			defined("BX_CLOUDS_COUNTERS_DEBUG")
			&& !preg_match(BX_CLOUDS_COUNTERS_DEBUG, $filePath)
		)
		{
			\CCloudsDebug::getInstance("copy")->endAction();
		}

		if (
			defined("BX_CLOUDS_COUNTERS_DEBUG")
			&& $this->status == 200
			&& !preg_match(BX_CLOUDS_COUNTERS_DEBUG, $filePath)
		)
		{
			\CCloudsDebug::getInstance()->startAction(CCloudUtil::URLEncode($filePath, "UTF-8"));
		}

		if($this->status == 200)
		{
			return $this->GetFileSRC($arBucket, $filePath);
		}
		elseif (
			$this->status == 400
			&& ($e = $APPLICATION->GetException())
			&& is_object($e)
			&& preg_match("/The specified copy source is larger than the maximum allowable size for a copy source: ([0-9]+)/i", $e->GetString(), $match)
		)
		{
			$sizeLimit = $match[1];
			$this->SendRequest(
				$arBucket["SETTINGS"],
				'HEAD',
				$arBucket["BUCKET"],
				CCloudUtil::URLEncode("/".($arBucket["PREFIX"]? $arBucket["PREFIX"]."/": "").($arFile["SUBDIR"]? $arFile["SUBDIR"]."/": "").$arFile["FILE_NAME"], "UTF-8", true)
			);

			$fileSize = false;
			if($this->status == 200)
			{
				if (isset($this->headers["Content-Length"]) && $this->headers["Content-Length"] > 0)
					$fileSize = $this->headers["Content-Length"];
			}
			if (!$fileSize)
			{
				$APPLICATION->ResetException();
				return false;
			}

			//Multipart copy goes here
			$additional_headers = array();
			if($this->_public)
				$additional_headers["x-amz-acl"] = "public-read";
			$additional_headers["Content-Type"] = $arFile["CONTENT_TYPE"];

			$response = $this->SendRequest(
				$arBucket["SETTINGS"],
				'POST',
				$arBucket["BUCKET"],
				CCloudUtil::URLEncode($filePath, "UTF-8", true),
				'?uploads=',
				'',
				$additional_headers
			);

			if(
				$this->status == 200
				&& is_array($response)
				&& isset($response["InitiateMultipartUploadResult"])
				&& is_array($response["InitiateMultipartUploadResult"])
				&& isset($response["InitiateMultipartUploadResult"]["#"])
				&& is_array($response["InitiateMultipartUploadResult"]["#"])
				&& isset($response["InitiateMultipartUploadResult"]["#"]["UploadId"])
				&& is_array($response["InitiateMultipartUploadResult"]["#"]["UploadId"])
				&& isset($response["InitiateMultipartUploadResult"]["#"]["UploadId"][0])
				&& is_array($response["InitiateMultipartUploadResult"]["#"]["UploadId"][0])
				&& isset($response["InitiateMultipartUploadResult"]["#"]["UploadId"][0]["#"])
				&& is_string($response["InitiateMultipartUploadResult"]["#"]["UploadId"][0]["#"])
			)
			{
				$uploadId = $response["InitiateMultipartUploadResult"]["#"]["UploadId"][0]["#"];
				$parts = array();
			}
			else
			{
				$APPLICATION->ResetException();
				return false;
			}

			$pos = 0;
			$part_no = 0;
			while ($pos < $fileSize)
			{
				$additional_headers = array();
				$additional_headers["x-amz-copy-source"] = CCloudUtil::URLEncode("/".$arBucket["BUCKET"]."/".($arBucket["PREFIX"]? $arBucket["PREFIX"]."/": "").($arFile["SUBDIR"]? $arFile["SUBDIR"]."/": "").$arFile["FILE_NAME"], "UTF-8", true);
				$additional_headers["x-amz-copy-source-range"] = "bytes=".$pos."-".(min($fileSize, $pos + $sizeLimit) - 1);

				$response = $this->SendRequest(
					$arBucket["SETTINGS"],
					'PUT',
					$arBucket["BUCKET"],
					CCloudUtil::URLEncode($filePath, "UTF-8", true),
					'?partNumber='.($part_no + 1).'&uploadId='.urlencode($uploadId),
					'',
					$additional_headers
				);

				if(
					$this->status == 200
					&& is_array($response)
					&& isset($response["CopyPartResult"])
					&& is_array($response["CopyPartResult"])
					&& isset($response["CopyPartResult"]["#"])
					&& is_array($response["CopyPartResult"]["#"])
					&& isset($response["CopyPartResult"]["#"]["ETag"])
					&& is_array($response["CopyPartResult"]["#"]["ETag"])
					&& isset($response["CopyPartResult"]["#"]["ETag"][0])
					&& is_array($response["CopyPartResult"]["#"]["ETag"][0])
					&& isset($response["CopyPartResult"]["#"]["ETag"][0]["#"])
					&& is_string($response["CopyPartResult"]["#"]["ETag"][0]["#"])
				)
				{
					$parts[$part_no] = trim($response["CopyPartResult"]["#"]["ETag"][0]["#"], '"');
				}
				else
				{
					$APPLICATION->ResetException();
					return false;
				}
				$part_no++;
				$pos += $sizeLimit;
			}

			ksort($parts);
			$data = "";
			foreach($parts as $PartNumber => $ETag)
			{
				$data .= "<Part><PartNumber>".($PartNumber+1)."</PartNumber><ETag>".$ETag."</ETag></Part>\n";
			}

			$this->SendRequest(
				$arBucket["SETTINGS"],
				'POST',
				$arBucket["BUCKET"],
				CCloudUtil::URLEncode($filePath, "UTF-8", true),
				'?uploadId='.urlencode($uploadId),
				"<CompleteMultipartUpload>".$data."</CompleteMultipartUpload>"
			);

			if($this->status == 200)
			{
				return $this->GetFileSRC($arBucket, $filePath);
			}
			$APPLICATION->ResetException();
			return false;
		}
		else//if($this->status == 404)
		{
			$APPLICATION->ResetException();
			return false;
		}
	}

	function DownloadToFile($arBucket, $arFile, $filePath)
	{
		$io = CBXVirtualIo::GetInstance();
		$obRequest = new CHTTP;
		$obRequest->follow_redirect = true;
		return $obRequest->Download($this->GetFileSRC($arBucket, $arFile), $io->GetPhysicalName($filePath));
	}

	function DeleteFile($arBucket, $filePath)
	{
		global $APPLICATION;

		if($arBucket["PREFIX"])
		{
			if(mb_substr($filePath, 0, mb_strlen($arBucket["PREFIX"]) + 2) != "/".$arBucket["PREFIX"]."/")
				$filePath = "/".$arBucket["PREFIX"]."/".ltrim($filePath, "/");
		}
		$filePath = CCloudUtil::URLEncode($filePath, "UTF-8", true);

		$this->SetLocation($arBucket["LOCATION"]);
		$this->SendRequest(
			$arBucket["SETTINGS"],
			'DELETE',
			$arBucket["BUCKET"],
			$filePath
		);

		$status = $this->status;
		if(
			$this->status == 204
			&& mb_strpos($filePath, '+') !== false
			&& $this->FileExists($arBucket, str_replace('+', '%2B', $filePath))
		)
		{
			$this->SendRequest(
				$arBucket["SETTINGS"],
				'DELETE',
				$arBucket["BUCKET"],
				str_replace('+', '%2B', $filePath)
			);
			$status = $this->status;
		}

		if (
			defined("BX_CLOUDS_COUNTERS_DEBUG")
			&& $this->status == 204
			&& !preg_match(BX_CLOUDS_COUNTERS_DEBUG, $filePath)
		)
		{
			\CCloudsDebug::getInstance()->startAction($filePath);
		}

		if($status == 204)
		{
			$APPLICATION->ResetException();
			return true;
		}
		else
		{
			$APPLICATION->ResetException();
			return false;
		}
	}

	function SaveFile($arBucket, $filePath, $arFile)
	{
		global $APPLICATION;

		if($arBucket["PREFIX"])
		{
			if(mb_substr($filePath, 0, mb_strlen($arBucket["PREFIX"]) + 2) != "/".$arBucket["PREFIX"]."/")
				$filePath = "/".$arBucket["PREFIX"]."/".ltrim($filePath, "/");
		}
		$filePath = CCloudUtil::URLEncode($filePath, "UTF-8", true);

		$additional_headers = array();
		if($this->_public)
			$additional_headers["x-amz-acl"] = "public-read";
		$additional_headers["Content-Type"] = $arFile["type"];
		$additional_headers["Content-Length"] = (array_key_exists("content", $arFile)? CUtil::BinStrlen($arFile["content"]): filesize($arFile["tmp_name"]));

		if (
			defined("BX_CLOUDS_COUNTERS_DEBUG")
			&& !preg_match(BX_CLOUDS_COUNTERS_DEBUG, $filePath)
		)
		{
			\CCloudsDebug::getInstance("put")->startAction($filePath);
		}

		$this->SetLocation($arBucket["LOCATION"]);
		$this->SendRequest(
			$arBucket["SETTINGS"],
			'PUT',
			$arBucket["BUCKET"],
			$filePath,
			'',
			(array_key_exists("content", $arFile)? $arFile["content"]: fopen($arFile["tmp_name"], "rb")),
			$additional_headers
		);

		if (
			defined("BX_CLOUDS_COUNTERS_DEBUG")
			&& !preg_match(BX_CLOUDS_COUNTERS_DEBUG, $filePath)
		)
		{
			\CCloudsDebug::getInstance("put")->endAction();
		}

		if (
			defined("BX_CLOUDS_COUNTERS_DEBUG")
			&& $this->status == 200
			&& !preg_match(BX_CLOUDS_COUNTERS_DEBUG, $filePath)
		)
		{
			\CCloudsDebug::getInstance()->startAction($filePath);
		}

		if($this->status == 200)
		{
			return true;
		}
		elseif ($this->checkForTokenExpiration($this->status, $this->result))
		{
			$this->tokenHasExpired = true;
			return false;
		}
		elseif($this->status == 403)
		{
			AddMessage2Log($this);
			return false;
		}
		else
		{
			$APPLICATION->ResetException();
			return false;
		}
	}

	function ListFiles($arBucket, $filePath, $bRecursive = false, $pageSize = 0, $pageMarker = '')
	{
		global $APPLICATION;
		static $search = array("+", "%7E");
		static $replace = array("%20", "~");
		$result = array(
			"dir" => array(),
			"file" => array(),
			"file_size" => array(),
			"file_mtime" => array(),
			"file_hash" => array(),
		);

		$filePath = trim($filePath, '/');
		if($filePath <> '')
		{
			$filePath .= '/';
		}

		if($arBucket["PREFIX"])
		{
			if(mb_substr($filePath, 0, mb_strlen($arBucket["PREFIX"]) + 2) != "/".$arBucket["PREFIX"]."/")
				$filePath = $arBucket["PREFIX"]."/".ltrim($filePath, "/");
		}
		$filePath = $APPLICATION->ConvertCharset($filePath, LANG_CHARSET, "UTF-8");

		$this->SetLocation($arBucket["LOCATION"]);
		$marker = $pageSize > 0? $filePath.$pageMarker: '';
		while(true)
		{
			$response = $this->SendRequest(
				$arBucket["SETTINGS"],
				'GET',
				$arBucket["BUCKET"],
				'/',
				'?'.($bRecursive? '': 'delimiter=%2F&').'prefix='.str_replace($search, $replace, urlencode($filePath))
					.'&marker='.str_replace("+", "%20", urlencode($marker))
			);

			if(
				$this->status == 200
				&& is_array($response)
				&& isset($response["ListBucketResult"])
				&& is_array($response["ListBucketResult"])
				&& isset($response["ListBucketResult"]["#"])
				&& is_array($response["ListBucketResult"]["#"])
			)
			{
				if(
					isset($response["ListBucketResult"]["#"]["CommonPrefixes"])
					&& is_array($response["ListBucketResult"]["#"]["CommonPrefixes"])
				)
				{
					foreach($response["ListBucketResult"]["#"]["CommonPrefixes"] as $a)
					{
						$dir_name = mb_substr(rtrim($a["#"]["Prefix"][0]["#"], "/"), mb_strlen($filePath));
						$result["dir"][] = $APPLICATION->ConvertCharset(urldecode($dir_name), "UTF-8", LANG_CHARSET);
					}
				}

				$lastKey = null;
				if(
					isset($response["ListBucketResult"]["#"]["Contents"])
					&& is_array($response["ListBucketResult"]["#"]["Contents"])
				)
				{
					foreach($response["ListBucketResult"]["#"]["Contents"] as $a)
					{
						$file_name = mb_substr($a["#"]["Key"][0]["#"], mb_strlen($filePath));
						if ($file_name <> '' && mb_substr($file_name, -1) !== '/')
						{
							$result["file"][] = $APPLICATION->ConvertCharset($file_name, "UTF-8", LANG_CHARSET);
							$result["file_size"][] = $a["#"]["Size"][0]["#"];
							$result["file_mtime"][] = mb_substr($a["#"]["LastModified"][0]["#"], 0, 19);
							$result["file_hash"][] = trim($a["#"]["ETag"][0]["#"], '"');
							$lastKey = $a["#"]["Key"][0]["#"];
							if ($pageSize > 0 && count($result["file"]) >= $pageSize)
							{
								return $result;
							}
						}
					}
				}

				if(
					isset($response["ListBucketResult"]["#"]["IsTruncated"])
					&& is_array($response["ListBucketResult"]["#"]["IsTruncated"])
					&& $response["ListBucketResult"]["#"]["IsTruncated"][0]["#"] === "true"
				)
				{
					if ($response["ListBucketResult"]["#"]["NextMarker"][0]["#"] <> '')
					{
						$marker = $response["ListBucketResult"]["#"]["NextMarker"][0]["#"];
						continue;
					}
					elseif ($lastKey !== null)
					{
						$marker = $lastKey;
						continue;
					}
				}

				break;
			}
			elseif ($this->checkForTokenExpiration($this->status, $this->result))
			{
				$this->tokenHasExpired = true;
				return false;
			}
			else
			{
				AddMessage2Log($this);
				return false;
			}
		}

		return $result;
	}

	function InitiateMultipartUpload($arBucket, &$NS, $filePath, $fileSize, $ContentType)
	{
		$filePath = '/'.trim($filePath, '/');
		if($arBucket["PREFIX"])
		{
			if(mb_substr($filePath, 0, mb_strlen($arBucket["PREFIX"]) + 2) != "/".$arBucket["PREFIX"]."/")
				$filePath = "/".$arBucket["PREFIX"].$filePath;
		}
		$filePathU = CCloudUtil::URLEncode($filePath, "UTF-8", true);

		$additional_headers = array();
		if($this->_public)
			$additional_headers["x-amz-acl"] = "public-read";
		$additional_headers["Content-Type"] = $ContentType;

		$this->SetLocation($arBucket["LOCATION"]);
		$response = $this->SendRequest(
			$arBucket["SETTINGS"],
			'POST',
			$arBucket["BUCKET"],
			$filePathU,
			'?uploads=',
			'',
			$additional_headers
		);

		if(
			$this->status == 200
			&& is_array($response)
			&& isset($response["InitiateMultipartUploadResult"])
			&& is_array($response["InitiateMultipartUploadResult"])
			&& isset($response["InitiateMultipartUploadResult"]["#"])
			&& is_array($response["InitiateMultipartUploadResult"]["#"])
			&& isset($response["InitiateMultipartUploadResult"]["#"]["UploadId"])
			&& is_array($response["InitiateMultipartUploadResult"]["#"]["UploadId"])
			&& isset($response["InitiateMultipartUploadResult"]["#"]["UploadId"][0])
			&& is_array($response["InitiateMultipartUploadResult"]["#"]["UploadId"][0])
			&& isset($response["InitiateMultipartUploadResult"]["#"]["UploadId"][0]["#"])
			&& is_string($response["InitiateMultipartUploadResult"]["#"]["UploadId"][0]["#"])
		)
		{
			$NS = array(
				"filePath" => $filePath,
				"UploadId" => $response["InitiateMultipartUploadResult"]["#"]["UploadId"][0]["#"],
				"Parts" => array(),
			);
			return true;
		}
		elseif ($this->checkForTokenExpiration($this->status, $this->result))
		{
			$this->tokenHasExpired = true;
			return false;
		}
		else
		{
			return false;
		}
	}

	function GetMinUploadPartSize()
	{
		return BX_S3_MIN_UPLOAD_PART_SIZE;
	}

	function UploadPartNo($arBucket, &$NS, $data, $part_no)
	{
		$filePath = '/'.trim($NS["filePath"], '/');
		if($arBucket["PREFIX"])
		{
			if(mb_substr($filePath, 0, mb_strlen($arBucket["PREFIX"]) + 2) != "/".$arBucket["PREFIX"]."/")
				$filePath = "/".$arBucket["PREFIX"].$filePath;
		}
		$filePath = CCloudUtil::URLEncode($filePath, "UTF-8", true);

		$this->SetLocation($arBucket["LOCATION"]);
		$this->SendRequest(
			$arBucket["SETTINGS"],
			'PUT',
			$arBucket["BUCKET"],
			$filePath,
			'?partNumber='.($part_no + 1).'&uploadId='.urlencode($NS["UploadId"]),
			$data
		);

		if($this->status == 200 && is_array($this->headers))
		{
			foreach ($this->headers as $key => $value)
			{
				if (mb_strtolower($key) === "etag")
				{
					$NS["Parts"][$part_no] = $value;
					return true;
				}
			}
		}
		return false;
	}
	/**
	 * @param array[string]string $arBucket
	 * @param mixed & $NS
	 * @param string $data
	 * @return bool
	*/
	function UploadPart($arBucket, &$NS, $data)
	{
		return $this->UploadPartNo($arBucket, $NS, $data, count($NS["Parts"]));
	}
	/**
	 * @param array[string]string $arBucket
	 * @param mixed & $NS
	 * @return bool
	*/
	function CompleteMultipartUpload($arBucket, &$NS)
	{
		$filePath = '/'.trim($NS["filePath"], '/');
		if($arBucket["PREFIX"])
		{
			if(mb_substr($filePath, 0, mb_strlen($arBucket["PREFIX"]) + 2) != "/".$arBucket["PREFIX"]."/")
				$filePath = "/".$arBucket["PREFIX"].$filePath;
		}
		$filePath = CCloudUtil::URLEncode($filePath, "UTF-8", true);

		ksort($NS["Parts"]);
		$data = "";
		foreach($NS["Parts"] as $PartNumber => $ETag)
		{
			$data .= "<Part><PartNumber>".($PartNumber+1)."</PartNumber><ETag>".$ETag."</ETag></Part>\n";
		}

		if (
			defined("BX_CLOUDS_COUNTERS_DEBUG")
			&& !preg_match(BX_CLOUDS_COUNTERS_DEBUG, $filePath)
		)
		{
			\CCloudsDebug::getInstance("post")->startAction($filePath);
		}

		$this->SetLocation($arBucket["LOCATION"]);
		$this->SendRequest(
			$arBucket["SETTINGS"],
			'POST',
			$arBucket["BUCKET"],
			$filePath,
			'?uploadId='.urlencode($NS["UploadId"]),
			"<CompleteMultipartUpload>".$data."</CompleteMultipartUpload>"
		);

		if (
			defined("BX_CLOUDS_COUNTERS_DEBUG")
			&& !preg_match(BX_CLOUDS_COUNTERS_DEBUG, $filePath)
		)
		{
			\CCloudsDebug::getInstance("post")->endAction();
		}

		if (
			defined("BX_CLOUDS_COUNTERS_DEBUG")
			&& $this->status == 200
			&& !preg_match(BX_CLOUDS_COUNTERS_DEBUG, $filePath)
		)
		{
			\CCloudsDebug::getInstance()->startAction($filePath);
		}

		return $this->status == 200;
	}
	/**
	 * @param array[string]string $arBucket
	 * @param mixed & $NS
	 * @return bool
	*/
	public function CancelMultipartUpload($arBucket, &$NS)
	{
		$filePath = '/'.trim($NS["filePath"], '/');
		if($arBucket["PREFIX"])
		{
			if(substr($filePath, 0, strlen($arBucket["PREFIX"])+2) != "/".$arBucket["PREFIX"]."/")
				$filePath = "/".$arBucket["PREFIX"].$filePath;
		}
		$filePath = CCloudUtil::URLEncode($filePath, "UTF-8", true);

		if ($NS["UploadId"])
		{
			$this->SetLocation($arBucket["LOCATION"]);
			$this->SendRequest(
				$arBucket["SETTINGS"],
				'DELETE',
				$arBucket["BUCKET"],
				$filePath,
				'?uploadId='.urlencode($NS["UploadId"]),
				''
			);
		}
	}
	/**
	 * @param bool $state
	 * @return void
	 */
	function SetPublic($state = true)
	{
		$this->_public = $state !== false;
	}
	/**
	 * @param string $name
	 * @param string $value
	 * @return void
	*/
	function SetHeader($name, $value)
	{
		$this->set_headers[$name] = $value;
	}
	/**
	 * @param string $name
	 * @return void
	 */
	function UnsetHeader($name)
	{
		unset($this->set_headers[$name]);
	}
	/**
	 * @param int $status
	 * @param string $result
	 * @return bool
	*/
	protected function checkForTokenExpiration($status, $result)
	{
		if ($status == 400 && mb_strpos($result, 'ExpiredToken') !== false)
			return true;
		if ($status == 400 && mb_strpos($result, 'token is malformed') !== false)
			return true;
		if ($status == 403 && mb_strpos($result, 'The AWS Access Key Id you provided does not exist in our records.') !== false)
			return true;
		return false;
	}
}
