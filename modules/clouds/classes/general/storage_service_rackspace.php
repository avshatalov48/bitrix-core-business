<?
class CCloudStorageService_RackSpaceCloudFiles extends CCloudStorageService_OpenStackStorage
{
	function GetObject()
	{
		return new CCloudStorageService_RackSpaceCloudFiles();
	}

	function GetID()
	{
		return "rackspace_storage";
	}

	function GetName()
	{
		return "Rackspace Cloud Files";
	}

	function _GetToken($host, $user, $key)
	{
		$result = false;
		$cache_id = "v0|".$host."|".$user."|".$key;
		$obCache = new CPHPCache;

		if($obCache->InitCache(3600, $cache_id, "/"))
		{
			$result = $obCache->GetVars();
		}
		else
		{
			$this->status = 0;
			$this->host = $host;
			$this->verb = "GET";
			$this->url =  "http://".$host."/v1.0";
			$this->headers = array();
			$this->errno = 0;
			$this->errstr = '';
			$this->result = '';

			$logRequest = false;
			if (defined("BX_CLOUDS_TRACE") && $verb !== "GET" && $verb !== "HEAD")
			{
				$stime = microtime(1);
				$logRequest = array(
					"request_id" => md5((string)mt_rand()),
					"portal" => $_SERVER["HTTP_HOST"],
					"verb" => $this->verb,
					"url" => $this->url,
				);
				AddMessage2Log(json_encode($logRequest), 'clouds', 20);
			}

			$request = new Bitrix\Main\Web\HttpClient(array(
				"redirect" => false,
				"streamTimeout" => $this->streamTimeout,
			));
			$request->setHeader("X-Auth-User", $user);
			$request->setHeader("X-Auth-Key", $key);
			$request->query($this->verb, $this->url);

			$this->status = $request->getStatus();
			foreach($request->getHeaders() as $key => $value)
			{
				$this->headers[$key] = is_array($value) ? $value[0] : $value;
			}
			$this->errstr = implode("\n", $request->getError());
			$this->errno = $this->errstr? 255: 0;
			$this->result = $request->getResult();

			if ($logRequest)
			{
				$logRequest["status"] = $this->status;
				$logRequest["time"] = round(microtime(true) - $stime, 6);
				$logRequest["headers"] = $this->headers;
				AddMessage2Log(json_encode($logRequest), 'clouds', 0);
			}

			if (
				$this->status == 301
				&& $this->headers["Location"] <> ''
				&& preg_match("#^https://(.*?)(/.*)\$#", $this->headers["Location"], $arNewLocation)
			)
			{
				$APPLICATION->ResetException();

				$this->status = 0;
				$this->host = $arNewLocation[1];
				$this->verb = "GET";
				$this->url =  "https://".$arNewLocation[1]."/v1.0";
				$this->headers = array();
				$this->errno = 0;
				$this->errstr = '';
				$this->result = '';

				$logRequest = false;
				if (defined("BX_CLOUDS_TRACE") && $verb !== "GET" && $verb !== "HEAD")
				{
					$stime = microtime(1);
					$logRequest = array(
						"request_id" => md5((string)mt_rand()),
						"portal" => $_SERVER["HTTP_HOST"],
						"verb" => $this->verb,
						"url" => $this->url,
					);
					AddMessage2Log(json_encode($logRequest), 'clouds', 20);
				}

				$request = new Bitrix\Main\Web\HttpClient(array(
					"redirect" => false,
					"streamTimeout" => $this->streamTimeout,
				));
				$request->setHeader("X-Auth-User", $user);
				$request->setHeader("X-Auth-Key", $key);
				$request->query($this->verb, $this->url);

				$this->status = $request->getStatus();
				foreach($request->getHeaders() as $key => $value)
				{
					$this->headers[$key] = is_array($value) ? $value[0] : $value;
				}
				$this->errstr = implode("\n", $request->getError());
				$this->errno = $this->errstr? 255: 0;
				$this->result = $request->getResult();

				if ($logRequest)
				{
					$logRequest["status"] = $this->status;
					$logRequest["time"] = round(microtime(true) - $stime, 6);
					$logRequest["headers"] = $this->headers;
					AddMessage2Log(json_encode($logRequest), 'clouds', 0);
				}

				if($this->status == 204)
				{
					if(preg_match("#^https://(.*?)(/.*)\$#", $this->headers["X-Storage-Url"], $arStorage))
					{
						$result = $this->headers;
						$result["X-Storage-Host"] = $arStorage[1];
						$result["X-Storage-Port"] = 443;
						$result["X-Storage-Urn"] = $arStorage[2];
						$result["X-Storage-Proto"] = "ssl://";
					}
				}
			}
		}

		if(is_array($result))
		{
			if($obCache->StartDataCache())
				$obCache->EndDataCache($result);
		}

		return $result;
	}

	function SendCDNRequest($settings, $verb, $bucket, $file_name='', $params='', $content=false, $additional_headers=array())
	{
		$arToken = $this->_GetToken($settings["HOST"], $settings["USER"], $settings["KEY"]);
		if(!$arToken)
			return false;

		if(isset($arToken["X-CDN-Management-Url"]))
		{
			if(preg_match("#^http://(.*?)(|:\d+)(/.*)\$#", $arToken["X-CDN-Management-Url"], $arCDN))
			{
				$Host = $arCDN[1];
				$Port = $arCDN[2];
				$Urn = $arCDN[3];
				$Proto = "http://";
			}
			elseif(preg_match("#^https://(.*?)(|:\d+)(/.*)\$#", $arToken["X-CDN-Management-Url"], $arCDN))
			{
				$Host = $arCDN[1];
				$Port = $arCDN[2];
				$Urn = $arCDN[3];
				$Proto = "https://";
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}

		$this->status = 0;
		$this->host = $Host;
		$this->verb = $verb;
		$this->url =  $Proto.$Host.($Port? $Port: '').$Urn.CCloudUtil::URLEncode("/".$bucket.$file_name.$params, "UTF-8");
		$this->headers = array();
		$this->errno = 0;
		$this->errstr = '';
		$this->result = '';

		$logRequest = false;
		if (defined("BX_CLOUDS_TRACE") && $verb !== "GET" && $verb !== "HEAD")
		{
			$stime = microtime(1);
			$logRequest = array(
				"request_id" => md5((string)mt_rand()),
				"portal" => $_SERVER["HTTP_HOST"],
				"verb" => $this->verb,
				"url" => $this->url,
			);
			AddMessage2Log(json_encode($logRequest), 'clouds', 20);
		}

		$request = new Bitrix\Main\Web\HttpClient(array(
			"redirect" => false,
			"streamTimeout" => $this->streamTimeout,
		));
		$request->setHeader("X-Auth-Token", $arToken["X-Auth-Token"]);
		foreach($additional_headers as $key => $value)
		{
			$request->setHeader($key, $value);
		}
		$request->query($this->verb, $this->url);

		$this->status = $request->getStatus();
		foreach($request->getHeaders() as $key => $value)
		{
			$this->headers[$key] = is_array($value) ? $value[0] : $value;
		}
		$this->errstr = implode("\n", $request->getError());
		$this->errno = $this->errstr? 255: 0;
		$this->result = $request->getResult();

		if ($logRequest)
		{
			$logRequest["status"] = $this->status;
			$logRequest["time"] = round(microtime(true) - $stime, 6);
			$logRequest["headers"] = $this->headers;
			AddMessage2Log(json_encode($logRequest), 'clouds', 0);
		}

		return $request;
	}

	function CreateBucket($arBucket)
	{
		global $APPLICATION;

		$obRequest = $this->SendRequest(
			$arBucket["SETTINGS"],
			"PUT",
			$arBucket["BUCKET"]
		);

		//CDN Enable
		if($this->status == 201)
		{
			$this->SendCDNRequest(
				$arBucket["SETTINGS"],
				"PUT",
				$arBucket["BUCKET"],
				'', //filename
				'', //params
				false, //content
				array(
					"X-CDN-Enabled" => "True",
				)
			);
		}

		return ($this->status == 201)/*Created*/ || ($this->status == 202) /*Accepted*/;
	}

	/**
	 * @param array[string]string $arBucket
	 * @param mixed $arFile
	 * @param boolean $encoded
	 * @return string
	*/
	function GetFileSRC($arBucket, $arFile, $encoded = true)
	{
		global $APPLICATION;

		if ($arBucket["SETTINGS"]["FORCE_HTTP"] === "Y")
			$proto = "http";
		else
			$proto = ($APPLICATION->IsHTTPS()? "https": "http");

		if($arBucket["CNAME"])
		{
			$host = $proto."://".$arBucket["CNAME"];
		}
		else
		{
			$result = false;
			$cache_id = md5(serialize($arBucket));
			$obCache = new CPHPCache;
			if($obCache->InitCache(3600, $cache_id, "/"))
			{
				$result = $obCache->GetVars();
			}
			else
			{
				$this->SendCDNRequest(
					$arBucket["SETTINGS"],
					"HEAD",
					$arBucket["BUCKET"]
				);
				if($this->status == 204)
				{
					$result = array();
					foreach($this->headers as $key => $value)
						$result[mb_strtolower($key)] = $value;
				}
			}

			if($obCache->StartDataCache())
				$obCache->EndDataCache($result);

			if(is_array($result))
				$host = $result["x-cdn-uri"];
			else
				return "/404.php";
		}

		if(is_array($arFile))
			$URI = ltrim($arFile["SUBDIR"]."/".$arFile["FILE_NAME"], "/");
		else
			$URI = ltrim($arFile, "/");

		if($arBucket["PREFIX"])
		{
			if(mb_substr($URI, 0, mb_strlen($arBucket["PREFIX"]) + 1) !== $arBucket["PREFIX"]."/")
				$URI = $arBucket["PREFIX"]."/".$URI;
		}

		if ($encoded)
		{
			return $host."/".CCloudUtil::URLEncode($URI, "UTF-8", true);
		}
		else
		{
			return $host."/".$URI;
		}
	}
}
?>