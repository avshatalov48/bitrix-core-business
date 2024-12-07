<?php
IncludeModuleLangFile(__FILE__);

class CCloudStorageService_OpenStackStorage extends CCloudStorageService
{
	public function GetObject()
	{
		return new CCloudStorageService_OpenStackStorage();
	}

	public function GetID()
	{
		return 'openstack_storage';
	}

	public function GetName()
	{
		return 'OpenStack Object Storage';
	}

	public function GetLocationList()
	{
		return [
			'' => 'N/A',
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
			$arSettings = [
				'HOST' => '',
				'USER' => '',
				'KEY' => '',
				'FORCE_HTTP' => 'N',
			];
		}

		$htmlID = htmlspecialcharsbx($this->GetID());
		$show = (($cur_SERVICE_ID == $this->GetID()) || !$bServiceSet) ? '' : 'none';

		$result = '
		<tr id="SETTINGS_2_' . $htmlID . '" style="display:' . $show . '" class="settings-tr adm-detail-required-field">
			<td>' . GetMessage('CLO_STORAGE_OPENSTACK_EDIT_HOST') . ':</td>
			<td><input type="hidden" name="SETTINGS[' . $htmlID . '][HOST]" id="' . $htmlID . 'HOST" value="' . htmlspecialcharsbx($arSettings['HOST']) . '"><input type="text" size="55" name="' . $htmlID . 'INP_HOST" id="' . $htmlID . 'INP_HOST" value="' . htmlspecialcharsbx($arSettings['HOST']) . '" ' . ($arBucket['READ_ONLY'] == 'Y' ? '"disabled"' : '') . ' onchange="BX(\'' . $htmlID . 'HOST\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_0_' . $htmlID . '" style="display:' . $show . '" class="settings-tr adm-detail-required-field">
			<td>' . GetMessage('CLO_STORAGE_OPENSTACK_EDIT_USER') . ':</td>
			<td><input type="hidden" name="SETTINGS[' . $htmlID . '][USER]" id="' . $htmlID . 'USER" value="' . htmlspecialcharsbx($arSettings['USER']) . '"><input type="text" size="55" name="' . $htmlID . 'INP_" id="' . $htmlID . 'INP_USER" value="' . htmlspecialcharsbx($arSettings['USER']) . '" ' . ($arBucket['READ_ONLY'] == 'Y' ? '"disabled"' : '') . ' onchange="BX(\'' . $htmlID . 'USER\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_1_' . $htmlID . '" style="display:' . $show . '" class="settings-tr adm-detail-required-field">
			<td>' . GetMessage('CLO_STORAGE_OPENSTACK_EDIT_KEY') . ':</td>
			<td><input type="hidden" name="SETTINGS[' . $htmlID . '][KEY]" id="' . $htmlID . 'KEY" value="' . htmlspecialcharsbx($arSettings['KEY']) . '"><input type="text" size="55" name="' . $htmlID . 'INP_KEY" id="' . $htmlID . 'INP_KEY" value="' . htmlspecialcharsbx($arSettings['KEY']) . '" autocomplete="off" ' . ($arBucket['READ_ONLY'] == 'Y' ? '"disabled"' : '') . ' onchange="BX(\'' . $htmlID . 'KEY\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_3_' . $htmlID . '" style="display:' . $show . '" class="settings-tr">
			<td>' . GetMessage('CLO_STORAGE_OPENSTACK_FORCE_HTTP') . ':</td>
			<td><input type="hidden" name="SETTINGS[' . $htmlID . '][FORCE_HTTP]" id="' . $htmlID . 'KEY" value="N"><input type="checkbox" name="SETTINGS[' . $htmlID . '][FORCE_HTTP]" id="' . $htmlID . 'FORCE_HTTP" value="Y" ' . ($arSettings['FORCE_HTTP'] == 'Y' ? 'checked="checked"' : '') . '></td>
		</tr>
		';
		return $result;
	}

	public function CheckSettings($arBucket, &$arSettings)
	{
		global $APPLICATION;
		$aMsg = [];

		$result = [
			'HOST' => is_array($arSettings) ? trim($arSettings['HOST']) : '',
			'USER' => is_array($arSettings) ? trim($arSettings['USER']) : '',
			'KEY' => is_array($arSettings) ? trim($arSettings['KEY']) : '',
			'FORCE_HTTP' => is_array($arSettings) && $arSettings['FORCE_HTTP'] == 'Y' ? 'Y' : 'N',
		];

		if ($arBucket['READ_ONLY'] !== 'Y' && !mb_strlen($result['HOST']))
		{
			$aMsg[] = ['id' => $this->GetID() . 'INP_HOST', 'text' => GetMessage('CLO_STORAGE_OPENSTACK_EMPTY_HOST')];
		}

		if ($arBucket['READ_ONLY'] !== 'Y' && !mb_strlen($result['USER']))
		{
			$aMsg[] = ['id' => $this->GetID() . 'INP_USER', 'text' => GetMessage('CLO_STORAGE_OPENSTACK_EMPTY_USER')];
		}

		if ($arBucket['READ_ONLY'] !== 'Y' && !mb_strlen($result['KEY']))
		{
			$aMsg[] = ['id' => $this->GetID() . 'INP_KEY', 'text' => GetMessage('CLO_STORAGE_OPENSTACK_EMPTY_KEY')];
		}

		if (empty($aMsg))
		{
			if (!$this->_GetToken($result['HOST'], $result['USER'], $result['KEY']))
			{
				$aMsg[] = ['text' => GetMessage('CLO_STORAGE_OPENSTACK_ERROR_GET_TOKEN')];
			}
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

	public function _GetToken($host, $user, $key)
	{
		global $APPLICATION;
		static $results = [];
		$cache_id = 'v0|' . $host . '|' . $user . '|' . $key;

		if (array_key_exists($cache_id, $results))
		{
			$result = $results[$cache_id];
		}
		else
		{
			$result = false;
			$obCache = new CPHPCache;

			if ($obCache->InitCache(600, $cache_id, '/')) /*TODO make setting*/
			{
				$result = $obCache->GetVars();
			}
			else
			{
				$this->status = 0;
				$this->host = $host;
				$this->verb = 'GET';
				$this->url = 'http://' . $host . '/v1.0';
				$this->headers = [];
				$this->errno = 0;
				$this->errstr = '';
				$this->result = '';

				$stime = 0;
				$logRequest = false;
				if (defined('BX_CLOUDS_TRACE') && $this->verb !== 'GET' && $this->verb !== 'HEAD')
				{
					$stime = microtime(1);
					$logRequest = [
						'request_id' => md5((string)mt_rand()),
						'portal' => $_SERVER['HTTP_HOST'],
						'verb' => $this->verb,
						'url' => $this->url,
					];
					AddMessage2Log(json_encode($logRequest), 'clouds', 20);
				}

				$request = new Bitrix\Main\Web\HttpClient([
					'redirect' => false,
					'streamTimeout' => $this->streamTimeout,
				]);
				$request->setHeader('X-Auth-User', $user);
				$request->setHeader('X-Auth-Key', $key);
				$request->query($this->verb, $this->url);

				$this->status = $request->getStatus();
				foreach ($request->getHeaders() as $headerKey => $headerValue)
				{
					$this->headers[$headerKey] = is_array($headerValue) ? $headerValue[0] : $headerValue;
				}
				$this->errstr = implode("\n", $request->getError());
				$this->errno = $this->errstr ? 255 : 0;
				$this->result = $request->getResult();

				if ($logRequest)
				{
					$logRequest['status'] = $this->status;
					$logRequest['time'] = round(microtime(true) - $stime, 6);
					$logRequest['headers'] = $this->headers;
					AddMessage2Log(json_encode($logRequest), 'clouds', 0);
				}

				if ($this->status == 412)
				{
					$APPLICATION->ResetException();

					$this->status = 0;
					$this->host = $host;
					$this->verb = 'GET';
					$this->url = 'http://' . $host . '/auth/v1.0';
					$this->headers = [];
					$this->errno = 0;
					$this->errstr = '';
					$this->result = '';

					$logRequest = false;
					if (defined('BX_CLOUDS_TRACE') && $this->verb !== 'GET' && $this->verb !== 'HEAD')
					{
						$stime = microtime(1);
						$logRequest = [
							'request_id' => md5((string)mt_rand()),
							'portal' => $_SERVER['HTTP_HOST'],
							'verb' => $this->verb,
							'url' => $this->url,
						];
						AddMessage2Log(json_encode($logRequest), 'clouds', 20);
					}

					$request = new Bitrix\Main\Web\HttpClient([
						'redirect' => false,
						'streamTimeout' => $this->streamTimeout,
					]);
					$request->setHeader('X-Auth-User', $user);
					$request->setHeader('X-Auth-Key', $key);
					$request->query($this->verb, $this->url);

					$this->status = $request->getStatus();
					foreach ($request->getHeaders() as $headerKey => $headerValue)
					{
						$this->headers[$headerKey] = is_array($headerValue) ? $headerValue[0] : $headerValue;
					}
					$this->errstr = implode("\n", $request->getError());
					$this->errno = $this->errstr ? 255 : 0;
					$this->result = $request->getResult();

					if ($logRequest)
					{
						$logRequest['status'] = $this->status;
						$logRequest['time'] = round(microtime(true) - $stime, 6);
						$logRequest['headers'] = $this->headers;
						AddMessage2Log(json_encode($logRequest), 'clouds', 0);
					}
				}

				if ($this->status == 204 || $this->status == 200)
				{
					$arStorage = [];
					if (preg_match('#^http://(.*?)(|:\d+)(/.*)$#', $this->headers['X-Storage-Url'], $arStorage))
					{
						$result = $this->headers;
						$result['X-Storage-NoProtoUrl'] = $arStorage[1] . ($arStorage[2] === ':80' ? '' : $arStorage[2]) . $arStorage[3];
						$result['X-Storage-Host'] = $arStorage[1];
						$result['X-Storage-Port'] = $arStorage[2] ? mb_substr($arStorage[2], 1) : 80;
						$result['X-Storage-Urn'] = $arStorage[3];
						$result['X-Storage-Proto'] = '';
					}
				}
			}

			if (is_array($result))
			{
				if ($obCache->StartDataCache())
				{
					$obCache->EndDataCache($result);
				}
			}

			$results[$cache_id] = $result;
		}

		return $result;
	}

	public function SendRequest($settings, $verb, $bucket, $file_name='', $params='', $content=false, $additional_headers=[])
	{
		$arToken = $this->_GetToken($settings['HOST'], $settings['USER'], $settings['KEY']);
		if (!$arToken)
		{
			return false;
		}

		$request = new Bitrix\Main\Web\HttpClient([
			'redirect' => false,
			'streamTimeout' => $this->streamTimeout,
		]);
		if (isset($additional_headers['option-file-result']))
		{
			$request->setOutputStream($additional_headers['option-file-result']);
		}

		$RequestURI = $file_name;

		$ContentType = 'N';
		$request->setHeader('X-Auth-Token', $arToken['X-Auth-Token']);
		foreach ($additional_headers as $key => $value)
		{
			if ($key == 'Content-Type')
			{
				$ContentType = $value;
			}
			else
			{
				$request->setHeader($key, $value);
			}
		}

		$this->status = 0;
		$this->host = $arToken['X-Storage-Host'];
		$this->port = $arToken['X-Storage-Port'];
		$this->verb = $verb;
		$this->url = rtrim($arToken['X-Storage-Url'], '/') . '/' . $bucket . $RequestURI . $params;
		$this->headers = [];
		$this->errno = 0;
		$this->errstr = '';
		$this->result = '';

		$stime = 0;
		$logRequest = false;
		if (defined('BX_CLOUDS_TRACE') && $this->verb !== 'GET' && $this->verb !== 'HEAD')
		{
			$stime = microtime(1);
			$logRequest = [
				'request_id' => md5((string)mt_rand()),
				'portal' => $_SERVER['HTTP_HOST'],
				'verb' => $this->verb,
				'url' => $this->url,
			];
			AddMessage2Log(json_encode($logRequest), 'clouds', 20);
		}

		$request->setHeader('Content-type', $ContentType);
		$request->query($this->verb, $this->url, $content);

		$this->status = $request->getStatus();
		foreach ($request->getHeaders() as $key => $value)
		{
			$this->headers[$key] = is_array($value) ? $value[0] : $value;
		}
		$this->errstr = implode("\n", $request->getError());
		$this->errno = $this->errstr ? 255 : 0;
		$this->result = $request->getResult();

		if ($logRequest)
		{
			$logRequest['status'] = $this->status;
			$logRequest['time'] = round(microtime(true) - $stime, 6);
			$logRequest['headers'] = $this->headers;
			AddMessage2Log(json_encode($logRequest), 'clouds', 0);
		}

		return $request;
	}

	public function CreateBucket($arBucket)
	{
		$this->SendRequest(
			$arBucket['SETTINGS'],
			'PUT',
			$arBucket['BUCKET'],
			'', //filename
			'', //params
			false, //content
			[
				'X-Container-Read' => '.r:*',
				'X-Container-Meta-Web-Listings' => 'false',
				'X-Container-Meta-Type' => 'public',
			]
		);

		return ($this->status == 201)/*Created*/ || ($this->status == 202) /*Accepted*/;
	}

	public function DeleteBucket($arBucket)
	{
		if ($arBucket['PREFIX'])
		{
			//Do not delete bucket if there is some files left
			if (!$this->IsEmptyBucket($arBucket))
			{
				return false;
			}

			//Do not delete bucket if there is some files left in other prefixes
			$arAllBucket = $arBucket;
			$arBucket['PREFIX'] = '';
			if (!$this->IsEmptyBucket($arAllBucket))
			{
				return true;
			}
		}

		$this->SendRequest(
			$arBucket['SETTINGS'],
			'DELETE',
			$arBucket['BUCKET']
		);

		if (
			$this->status == 204/*No Content*/
			|| $this->status == 404/*Not Found*/
		)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function IsEmptyBucket($arBucket)
	{
		$this->SendRequest(
			$arBucket['SETTINGS'],
			'GET',
			$arBucket['BUCKET'],
			'',
			'?limit=1&format=xml' . ($arBucket['PREFIX'] ? '&prefix=' . $arBucket['PREFIX'] : '')
		);

		$arXML = false;
		if ($this->status && $this->result)
		{
			$obXML = new CDataXML;
			$text = preg_replace('/<' . '\\?XML.*?\\?' . '>/i', '', $this->result);
			if ($obXML->LoadString($text))
			{
				$arXML = $obXML->GetArray();
			}
		}

		if ($this->status == 404)
		{
			return true;
		}
		elseif (is_array($arXML))
		{
			return
				!isset($arXML['container'])
				|| !is_array($arXML['container'])
				|| !isset($arXML['container']['#'])
				|| !is_array($arXML['container']['#'])
				|| !isset($arXML['container']['#']['object'])
				|| !is_array($arXML['container']['#']['object']);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param array[string]string $arBucket
	 * @param mixed $arFile
	 * @param bool $encoded
	 * @return string
	*/
	public function GetFileSRC($arBucket, $arFile, $encoded = true)
	{
		$forceHttp = $arBucket['SETTINGS']['FORCE_HTTP'] ?? 'N';
		if ($forceHttp === 'Y')
		{
			$proto = 'http';
		}
		else
		{
			/* @var \Bitrix\Main\HttpRequest $request */
			$request = \Bitrix\Main\Context::getCurrent()->getRequest();
			$proto = $request->isHttps() ? 'https' : 'http';
		}

		if ($arBucket['CNAME'])
		{
			$host = $proto . '://' . $arBucket['CNAME'];
		}
		else
		{
			$arToken = $this->_GetToken(
				$arBucket['SETTINGS']['HOST'],
				$arBucket['SETTINGS']['USER'],
				$arBucket['SETTINGS']['KEY']
			);

			if (is_array($arToken))
			{
				if ($arToken['X-Storage-NoProtoUrl'])
				{
					$host = $proto . '://' . $arToken['X-Storage-NoProtoUrl'] . '/' . $arBucket['BUCKET'];
				}
				else
				{
					$host = $arToken['X-Storage-Url'] . '/' . $arBucket['BUCKET'];
				}
			}
			else
			{
				return '/404.php';
			}
		}

		if (is_array($arFile))
		{
			$URI = ltrim($arFile['SUBDIR'] . '/' . $arFile['FILE_NAME'], '/');
		}
		else
		{
			$URI = ltrim($arFile, '/');
		}

		if ($arBucket['PREFIX'])
		{
			if (mb_substr($URI, 0, mb_strlen($arBucket['PREFIX']) + 1) !== $arBucket['PREFIX'] . '/')
			{
				$URI = $arBucket['PREFIX'] . '/' . $URI;
			}
		}

		if ($encoded)
		{
			return $host . '/' . CCloudUtil::URLEncode($URI, 'UTF-8', true);
		}
		else
		{
			return $host . '/' . $URI;
		}
	}

	public function FileExists($arBucket, $filePath)
	{
		global $APPLICATION;

		if ($arBucket['PREFIX'])
		{
			if (mb_substr($filePath, 0, mb_strlen($arBucket['PREFIX']) + 2) !== '/' . $arBucket['PREFIX'] . '/')
			{
				$filePath = '/' . $arBucket['PREFIX'] . '/' . ltrim($filePath, '/');
			}
		}
		$filePath = CCloudUtil::URLEncode($filePath, 'UTF-8', true);

		$this->SendRequest(
			$arBucket['SETTINGS'],
			'HEAD',
			$arBucket['BUCKET'],
			$filePath
		);

		if ($this->status == 200)
		{
			if (isset($this->headers['Content-Length']) && $this->headers['Content-Length'] > 0)
			{
				return $this->headers['Content-Length'];
			}
			else
			{
				return true;
			}
		}
		elseif ($this->status == 206)
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

	public function FileCopy($arBucket, $arFile, $filePath)
	{
		if ($arBucket['PREFIX'])
		{
			if (mb_substr($filePath, 0, mb_strlen($arBucket['PREFIX']) + 2) !== '/' . $arBucket['PREFIX'] . '/')
			{
				$filePath = '/' . $arBucket['PREFIX'] . '/' . ltrim($filePath, '/');
			}
		}
		$fileSource = CCloudUtil::URLEncode('/' . $arBucket['BUCKET'] . '/' . ($arBucket['PREFIX'] ? $arBucket['PREFIX'] . '/' : '') . ($arFile['SUBDIR'] ? $arFile['SUBDIR'] . '/' : '') . $arFile['FILE_NAME'], 'UTF-8', true);

		$this->SendRequest(
			$arBucket['SETTINGS'],
			'PUT',
			$arBucket['BUCKET'],
			CCloudUtil::URLEncode($filePath, 'UTF-8', true),
			'',
			false,
			[
				'X-Copy-From' => $fileSource,
				'Content-Type' => $arFile['CONTENT_TYPE'],
			]
		);

		if ($this->status == 200 || $this->status == 201)
		{
			return $this->GetFileSRC($arBucket, $filePath);
		}
		else
		{
			return false;
		}
	}

	public function DeleteFile($arBucket, $filePath)
	{
		global $APPLICATION;

		if ($arBucket['PREFIX'])
		{
			if (mb_substr($filePath, 0, mb_strlen($arBucket['PREFIX']) + 2) !== '/' . $arBucket['PREFIX'] . '/')
			{
				$filePath = '/' . $arBucket['PREFIX'] . '/' . ltrim($filePath, '/');
			}
		}
		$filePath = CCloudUtil::URLEncode($filePath, 'UTF-8', true);

		$this->SendRequest(
			$arBucket['SETTINGS'],
			'DELETE',
			$arBucket['BUCKET'],
			$filePath
		);

		//Try to fix space in the path
		if ($this->status == 404 && mb_strpos($filePath, '+') !== false)
		{
			$filePath = str_replace('+', '%20', $filePath);
			$this->SendRequest(
				$arBucket['SETTINGS'],
				'DELETE',
				$arBucket['BUCKET'],
				$filePath
			);
		}

		if ($this->status == 204 || $this->status == 404)
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

	public function SaveFile($arBucket, $filePath, $arFile)
	{
		if ($arBucket['PREFIX'])
		{
			if (mb_substr($filePath, 0, mb_strlen($arBucket['PREFIX']) + 2) !== '/' . $arBucket['PREFIX'] . '/')
			{
				$filePath = '/' . $arBucket['PREFIX'] . '/' . ltrim($filePath, '/');
			}
		}
		$filePath = CCloudUtil::URLEncode($filePath, 'UTF-8', true);

		if (array_key_exists('content', $arFile))
		{
			$this->SendRequest(
				$arBucket['SETTINGS'],
				'PUT',
				$arBucket['BUCKET'],
				$filePath,
				'',
				$arFile['content'],
				[
					'Content-Type' => $arFile['type'],
					'Content-Length' => strlen($arFile['content']),
				]
			);
		}
		else
		{
			$this->SendRequest(
				$arBucket['SETTINGS'],
				'PUT',
				$arBucket['BUCKET'],
				$filePath,
				'',
				fopen($arFile['tmp_name'], 'rb'),
				[
					'Content-Type' => $arFile['type'],
					'Content-Length' => filesize($arFile['tmp_name']),
				]
			);
		}

		if ($this->status == 201)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function ListFiles($arBucket, $filePath, $bRecursive = false)
	{
		$result = [
			'dir' => [],
			'file' => [],
			'file_size' => [],
			'file_mtime' => [],
			'file_hash' => [],
			'last_key' => '',
		];

		$filePath = trim($filePath, '/');
		if ($filePath !== '')
		{
			$filePath .= '/';
		}

		if ($arBucket['PREFIX'])
		{
			if (mb_substr($filePath, 0, mb_strlen($arBucket['PREFIX']) + 2) !== '/' . $arBucket['PREFIX'] . '/')
			{
				$filePath = $arBucket['PREFIX'] . '/' . ltrim($filePath, '/');
			}
		}
		$filePath = str_replace(' ', '+', $filePath);

		$marker = '';
		$new_marker = false;
		while (true)
		{
			$this->SendRequest(
				$arBucket['SETTINGS'],
				'GET',
				$arBucket['BUCKET'],
				'/',
				$s = '?format=xml&' . ($bRecursive ? '' : '&delimiter=/') . '&prefix=' . urlencode($filePath) . '&marker=' . urlencode($marker)
			);
			$bFound = false;
			if ($this->result && $this->status == 200)
			{
				$obXML = new CDataXML;
				$text = preg_replace('/<' . '\\?XML.*?\\?' . '>/i', '', $this->result);
				if ($obXML->LoadString($text))
				{
					$arXML = $obXML->GetArray();
					if (
						isset($arXML['container'])
						&& is_array($arXML['container'])
						&& isset($arXML['container']['#'])
						&& is_array($arXML['container']['#'])
						&& !empty($arXML['container']['#'])
					)
					{
						if (
							isset($arXML['container']['#']['object'])
							&& is_array($arXML['container']['#']['object'])
							&& !empty($arXML['container']['#']['object'])
						)
						{
							$bFound = true;
							foreach ($arXML['container']['#']['object'] as $a)
							{
								$new_marker = $a['#']['name'][0]['#'];
								if ($a['#']['content_type'][0]['#'] === 'application/directory')
								{
									$dir_name = trim(mb_substr($a['#']['name'][0]['#'], mb_strlen($filePath)), '/');
									$result['dir'][$dir_name] = true;
								}
								else
								{
									$file_name = mb_substr($a['#']['name'][0]['#'], mb_strlen($filePath));
									if (!in_array($file_name, $result['file'], true))
									{
										$result['file'][] = $file_name;
										$result['file_size'][] = $a['#']['bytes'][0]['#'];
										$result['file_mtime'][] = mb_substr($a['#']['last_modified'][0]['#'], 0, 19);
										$result['file_hash'][] = $a['#']['hash'][0]['#'];
										$result['last_key'] = $file_name;
									}
								}
							}
						}

						if (
							isset($arXML['container']['#']['subdir'])
							&& is_array($arXML['container']['#']['subdir'])
							&& !empty($arXML['container']['#']['subdir'])
						)
						{
							$bFound = true;
							foreach ($arXML['container']['#']['subdir'] as $a)
							{
								$new_marker = $a['@']['name'];
								$dir_name = trim(mb_substr($a['@']['name'], mb_strlen($filePath)), '/');
								$result['dir'][rawurldecode($dir_name)] = true;
							}
						}
					}
				}
			}
			else
			{
				return false;
			}

			if ($new_marker === $marker)
			{
				break;
			}

			if (!$bFound)
			{
				break;
			}

			$marker = $new_marker;
		}

		$result['dir'] = array_keys($result['dir']);

		return $result;
	}

	public function InitiateMultipartUpload($arBucket, &$NS, $filePath, $fileSize, $ContentType)
	{
		$filePath = '/' . trim($filePath, '/');
		if ($arBucket['PREFIX'])
		{
			if (mb_substr($filePath, 0, mb_strlen($arBucket['PREFIX']) + 2) !== '/' . $arBucket['PREFIX'] . '/')
			{
				$filePath = '/' . $arBucket['PREFIX'] . $filePath;
			}
		}

		$NS = [
			'filePath' => $filePath,
			'fileTemp' => CCloudStorage::translit('/tmp' . str_replace(' ', '_', $filePath), '/'),
			'partsCount' => 0,
			'Parts' => [],
			'Content-Type' => $ContentType,
		];

		return true;
	}

	public function GetMinUploadPartSize()
	{
		return 5 * 1024 * 1024; //5MB
	}

	public function UploadPartNo($arBucket, &$NS, $data, $part_no)
	{
		$filePath = $NS['fileTemp'] . '/' . sprintf('%06d', $part_no + 1);
		$filePath = CCloudUtil::URLEncode($filePath, 'UTF-8', true);

		$this->SendRequest(
			$arBucket['SETTINGS'],
			'PUT',
			$arBucket['BUCKET'],
			$filePath,
			'',
			$data
		);

		if ($this->status == 201)
		{
			$NS['partsCount']++;
			$NS['Parts'][$part_no] = $filePath;
			return true;
		}
		else
		{
			return false;
		}
	}

	public function UploadPart($arBucket, &$NS, $data)
	{
		return $this->UploadPartNo($arBucket, $NS, $data, count($NS['Parts']));
	}

	public function CompleteMultipartUpload($arBucket, &$NS)
	{
		$filePath = CCloudUtil::URLEncode($NS['fileTemp'], 'UTF-8', true);

		$this->SendRequest(
			$arBucket['SETTINGS'],
			'PUT',
			$arBucket['BUCKET'],
			$filePath,
			'',
			false,
			[
				'Content-Length' => 0,
				'Content-Type' => $NS['Content-Type'],
				'X-Object-Manifest' => $arBucket['BUCKET'] . $filePath . '/',
			]
		);

		if ($this->status == 201)
		{
			$fileSource = CCloudUtil::URLEncode('/' . $arBucket['BUCKET'] . $NS['fileTemp'], 'UTF-8', true);

			$this->SendRequest(
				$arBucket['SETTINGS'],
				'PUT',
				$arBucket['BUCKET'],
				CCloudUtil::URLEncode($NS['filePath'], 'UTF-8', true),
				'',
				false,
				[
					'Content-Type' => $NS['Content-Type'],
					'X-Copy-From' => $fileSource,
				]
			);

			if (
				$this->status == 201
				|| $this->status == 200
			)
			{
				$result = true;
			}
			else
			{
				$result = false;
			}

			$this->DeleteFile($arBucket, $NS['fileTemp']);
			ksort($NS['Parts']);
			foreach ($NS['Parts'] as $tmpPath)
			{
				$this->DeleteFile($arBucket, $tmpPath);
			}

			return $result;
		}
		else
		{
			//May be delete uploaded tmp file?
			AddMessage2Log($this);
			return false;
		}
	}
}
