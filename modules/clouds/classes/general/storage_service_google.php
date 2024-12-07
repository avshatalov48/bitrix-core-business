<?php
IncludeModuleLangFile(__FILE__);

class CCloudStorageService_GoogleStorage extends CCloudStorageService
{
	protected $new_end_point;

	public function GetObject()
	{
		return new CCloudStorageService_GoogleStorage();
	}

	public function GetID()
	{
		return 'google_storage';
	}

	public function GetName()
	{
		return 'Google Storage';
	}

	public function GetLocationList()
	{
		return [
			'EU' => 'Europe',
			'US' => 'United States',
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
			$arSettings = ['PROJECT_ID' => '', 'ACCESS_KEY' => '', 'SECRET_KEY' => ''];
		}

		$htmlID = htmlspecialcharsbx($this->GetID());

		$result = '
		<tr id="SETTINGS_0_' . $htmlID . '" style="display:' . ($cur_SERVICE_ID == $this->GetID() || !$bServiceSet ? '' : 'none') . '" class="settings-tr adm-detail-required-field">
			<td>' . GetMessage('CLO_STORAGE_GOOGLE_EDIT_PROJECT_ID') . ':</td>
			<td><input type="hidden" name="SETTINGS[' . $htmlID . '][PROJECT_ID]" id="' . $htmlID . 'PROJECT_ID" value="' . htmlspecialcharsbx($arSettings['PROJECT_ID']) . '"><input type="text" size="55" name="' . $htmlID . 'INP_" id="' . $htmlID . 'INP_PROJECT_ID" value="' . htmlspecialcharsbx($arSettings['PROJECT_ID']) . '" ' . ($arBucket['READ_ONLY'] == 'Y' ? '"disabled"' : '') . ' onchange="BX(\'' . $htmlID . 'PROJECT_ID\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_1_' . $htmlID . '" style="display:' . ($cur_SERVICE_ID == $this->GetID() || !$bServiceSet ? '' : 'none') . '" class="settings-tr adm-detail-required-field">
			<td>' . GetMessage('CLO_STORAGE_GOOGLE_EDIT_ACCESS_KEY') . ':</td>
			<td><input type="hidden" name="SETTINGS[' . $htmlID . '][ACCESS_KEY]" id="' . $htmlID . 'ACCESS_KEY" value="' . htmlspecialcharsbx($arSettings['ACCESS_KEY']) . '"><input type="text" size="55" name="' . $htmlID . 'INP_ACCESS_KEY" id="' . $htmlID . 'INP_ACCESS_KEY" value="' . htmlspecialcharsbx($arSettings['ACCESS_KEY']) . '" ' . ($arBucket['READ_ONLY'] == 'Y' ? '"disabled"' : '') . ' onchange="BX(\'' . $htmlID . 'ACCESS_KEY\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_2_' . $htmlID . '" style="display:' . ($cur_SERVICE_ID == $this->GetID() || !$bServiceSet ? '' : 'none') . '" class="settings-tr adm-detail-required-field">
			<td>' . GetMessage('CLO_STORAGE_GOOGLE_EDIT_SECRET_KEY') . ':</td>
			<td><input type="hidden" name="SETTINGS[' . $htmlID . '][SECRET_KEY]" id="' . $htmlID . 'SECRET_KEY" value="' . htmlspecialcharsbx($arSettings['SECRET_KEY']) . '"><input type="text" size="55" name="' . $htmlID . 'INP_SECRET_KEY" id="' . $htmlID . 'INP_SECRET_KEY" value="' . htmlspecialcharsbx($arSettings['SECRET_KEY']) . '" autocomplete="off" ' . ($arBucket['READ_ONLY'] == 'Y' ? '"disabled"' : '') . ' onchange="BX(\'' . $htmlID . 'SECRET_KEY\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_3_' . $htmlID . '" style="display:' . ($cur_SERVICE_ID == $this->GetID() || !$bServiceSet ? '' : 'none') . '" class="settings-tr">
			<td>&nbsp;</td>
			<td>' . BeginNote() . GetMessage('CLO_STORAGE_GOOGLE_EDIT_HELP') . EndNote() . '</td>
		</tr>
		';
		return $result;
	}

	public function CheckSettings($arBucket, &$arSettings)
	{
		global $APPLICATION;
		$aMsg = [];

		$result = [
			'PROJECT_ID' => is_array($arSettings) ? trim($arSettings['PROJECT_ID']) : '',
			'ACCESS_KEY' => is_array($arSettings) ? trim($arSettings['ACCESS_KEY']) : '',
			'SECRET_KEY' => is_array($arSettings) ? trim($arSettings['SECRET_KEY']) : '',
		];

		if ($arBucket['READ_ONLY'] !== 'Y' && !mb_strlen($result['PROJECT_ID']))
		{
			$aMsg[] = ['id' => $this->GetID() . 'INP_PROJECT_ID', 'text' => GetMessage('CLO_STORAGE_GOOGLE_EMPTY_PROJECT_ID')];
		}

		if ($arBucket['READ_ONLY'] !== 'Y' && !mb_strlen($result['ACCESS_KEY']))
		{
			$aMsg[] = ['id' => $this->GetID() . 'INP_ACCESS_KEY', 'text' => GetMessage('CLO_STORAGE_GOOGLE_EMPTY_ACCESS_KEY')];
		}

		if ($arBucket['READ_ONLY'] !== 'Y' && !mb_strlen($result['SECRET_KEY']))
		{
			$aMsg[] = ['id' => $this->GetID() . 'INP_SECRET_KEY', 'text' => GetMessage('CLO_STORAGE_GOOGLE_EMPTY_SECRET_KEY')];
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

	public function CreateBucket($arBucket)
	{
		global $APPLICATION;

		if ($arBucket['LOCATION'])
		{
			$content =
				'<CreateBucketConfiguration>'
				. '<LocationConstraint>' . $arBucket['LOCATION'] . '</LocationConstraint>'
				. '</CreateBucketConfiguration>';
		}
		else
		{
			$content = '';
		}

		$response = $this->SendRequest(
			$arBucket['SETTINGS']['ACCESS_KEY'],
			$arBucket['SETTINGS']['SECRET_KEY'],
			'PUT',
			$arBucket['BUCKET'],
			'/',
			'',
			$content,
			[
				'x-goog-project-id' => $arBucket['SETTINGS']['PROJECT_ID'],
			]
		);

		if ($this->status == 409/*Already exists*/)
		{
			$APPLICATION->ResetException();
			return true;
		}
		elseif (is_array($response))
		{
			return true;
		}
		else
		{
			if (defined('BX_CLOUDS_ERROR_DEBUG'))
			{
				AddMessage2Log($this);
			}
			return false;
		}
	}

	public function DeleteBucket($arBucket)
	{
		global $APPLICATION;

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

		$response = $this->SendRequest(
			$arBucket['SETTINGS']['ACCESS_KEY'],
			$arBucket['SETTINGS']['SECRET_KEY'],
			'DELETE',
			$arBucket['BUCKET']
		);

		if ($this->status == 204/*No content*/ || $this->status == 404/*Not exists*/)
		{
			$APPLICATION->ResetException();
			return true;
		}
		elseif (is_array($response))
		{
			return true;
		}
		else
		{
			if (defined('BX_CLOUDS_ERROR_DEBUG'))
			{
				AddMessage2Log($this);
			}
			return false;
		}
	}

	public function IsEmptyBucket($arBucket)
	{
		global $APPLICATION;

		$response = $this->SendRequest(
			$arBucket['SETTINGS']['ACCESS_KEY'],
			$arBucket['SETTINGS']['SECRET_KEY'],
			'GET',
			$arBucket['BUCKET'],
			'/',
			'?max-keys=1' . ($arBucket['PREFIX'] ? '&prefix=' . $arBucket['PREFIX'] : '')
		);

		if ($this->status == 404)
		{
			$APPLICATION->ResetException();
			return true;
		}
		elseif (is_array($response))
		{
			return
				!isset($response['ListBucketResult'])
				|| !is_array($response['ListBucketResult'])
				|| !isset($response['ListBucketResult']['#'])
				|| !is_array($response['ListBucketResult']['#'])
				|| !isset($response['ListBucketResult']['#']['Contents'])
				|| !is_array($response['ListBucketResult']['#']['Contents']);
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
		if ($arBucket['CNAME'])
		{
			$host = $arBucket['CNAME'];
		}
		else
		{
			$host = $arBucket['BUCKET'] . '.commondatastorage.googleapis.com';
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

		/* @var \Bitrix\Main\HttpRequest $request */
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$proto = $request->isHttps() ? 'https' : 'http';

		if ($encoded)
		{
			return $proto . '://' . $host . '/' . CCloudUtil::URLEncode($URI, 'UTF-8', true);
		}
		else
		{
			return $proto . '://' . $host . '/' . $URI;
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
			$arBucket['SETTINGS']['ACCESS_KEY'],
			$arBucket['SETTINGS']['SECRET_KEY'],
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
		global $APPLICATION;

		if ($arBucket['PREFIX'])
		{
			if (mb_substr($filePath, 0, mb_strlen($arBucket['PREFIX']) + 2) !== '/' . $arBucket['PREFIX'] . '/')
			{
				$filePath = '/' . $arBucket['PREFIX'] . '/' . ltrim($filePath, '/');
			}
		}

		$this->SendRequest(
			$arBucket['SETTINGS']['ACCESS_KEY'],
			$arBucket['SETTINGS']['SECRET_KEY'],
			'PUT',
			$arBucket['BUCKET'],
			CCloudUtil::URLEncode($filePath, 'UTF-8', true),
			'',
			'',
			[
				'x-goog-acl' => 'public-read',
				'x-goog-copy-source' => CCloudUtil::URLEncode('/' . $arBucket['BUCKET'] . '/' . ($arBucket['PREFIX'] ? $arBucket['PREFIX'] . '/' : '') . ($arFile['SUBDIR'] ? $arFile['SUBDIR'] . '/' : '') . $arFile['FILE_NAME'], 'UTF-8', true),
				'Content-Type' => $arFile['CONTENT_TYPE']
			]
		);

		if ($this->status == 200)
		{
			return $this->GetFileSRC($arBucket, $filePath);
		}
		else//if($this->status == 404)
		{
			if (defined('BX_CLOUDS_ERROR_DEBUG'))
			{
				AddMessage2Log($this);
			}
			$APPLICATION->ResetException();
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
			$arBucket['SETTINGS']['ACCESS_KEY'],
			$arBucket['SETTINGS']['SECRET_KEY'],
			'DELETE',
			$arBucket['BUCKET'],
			$filePath
		);

		if ($this->status == 204 || $this->status == 404)
		{
			$APPLICATION->ResetException();
			return true;
		}
		else
		{
			if (defined('BX_CLOUDS_ERROR_DEBUG'))
			{
				AddMessage2Log($this);
			}
			$APPLICATION->ResetException();
			return false;
		}
	}

	public function SaveFile($arBucket, $filePath, $arFile)
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
			$arBucket['SETTINGS']['ACCESS_KEY'],
			$arBucket['SETTINGS']['SECRET_KEY'],
			'PUT',
			$arBucket['BUCKET'],
			$filePath,
			'',
			$arFile['content'] ?? fopen($arFile['tmp_name'], 'rb'),
			[
				'x-goog-acl' => 'public-read',
				'Content-Type' => $arFile['type'],
				'Content-Length' => (array_key_exists('content', $arFile) ? strlen($arFile['content']) : filesize($arFile['tmp_name'])),
			]
		);

		if ($this->status == 200)
		{
			return true;
		}
		else
		{
			if (defined('BX_CLOUDS_ERROR_DEBUG'))
			{
				AddMessage2Log($this);
			}
			$APPLICATION->ResetException();
			return false;
		}
	}

	public function ListFiles($arBucket, $filePath, $bRecursive = false)
	{
		global $APPLICATION;

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
		while (true)
		{
			$response = $this->SendRequest(
				$arBucket['SETTINGS']['ACCESS_KEY'],
				$arBucket['SETTINGS']['SECRET_KEY'],
				'GET',
				$arBucket['BUCKET'],
				'/',
				'?' . ($bRecursive ? '' : 'delimiter=/&') . 'prefix=' . rawurlencode($filePath) . '&marker=' . rawurlencode($marker)
			);
			if (
				$this->status == 200
				&& is_array($response)
				&& isset($response['ListBucketResult'])
				&& is_array($response['ListBucketResult'])
				&& isset($response['ListBucketResult']['#'])
				&& is_array($response['ListBucketResult']['#'])
			)
			{
				if (
					isset($response['ListBucketResult']['#']['CommonPrefixes'])
					&& is_array($response['ListBucketResult']['#']['CommonPrefixes'])
				)
				{
					foreach ($response['ListBucketResult']['#']['CommonPrefixes'] as $a)
					{
						$dir_name = mb_substr(rtrim($a['#']['Prefix'][0]['#'], '/'), mb_strlen($filePath));
						$result['dir'][] = $dir_name;
					}
				}

				if (
					isset($response['ListBucketResult']['#']['Contents'])
					&& is_array($response['ListBucketResult']['#']['Contents'])
				)
				{
					foreach ($response['ListBucketResult']['#']['Contents'] as $a)
					{
						$file_name = mb_substr($a['#']['Key'][0]['#'], mb_strlen($filePath));
						$result['file'][] = $file_name;
						$result['file_size'][] = $a['#']['Size'][0]['#'];
						$result['file_mtime'][] = mb_substr($a['#']['LastModified'][0]['#'], 0, 19);
						$result['file_hash'][] = trim($a['#']['ETag'][0]['#'], '"');
						$result['last_key'] = $file_name;
					}
				}

				if (
					isset($response['ListBucketResult']['#']['IsTruncated'])
					&& is_array($response['ListBucketResult']['#']['IsTruncated'])
					&& $response['ListBucketResult']['#']['IsTruncated'][0]['#'] === 'true'
					&& $response['ListBucketResult']['#']['NextMarker'][0]['#'] <> ''
				)
				{
					$marker = $response['ListBucketResult']['#']['NextMarker'][0]['#'];
					continue;
				}
				else
				{
					break;
				}
			}
			else
			{
				if (defined('BX_CLOUDS_ERROR_DEBUG'))
				{
					AddMessage2Log($this);
				}
				$APPLICATION->ResetException();
				return false;
			}
		}

		return $result;
	}

	protected function StartUpload($arBucket, $filePath, $ContentType)
	{
		$filePath = '/' . trim($filePath, '/');
		if ($arBucket['PREFIX'])
		{
			if (mb_substr($filePath, 0, mb_strlen($arBucket['PREFIX']) + 2) !== '/' . $arBucket['PREFIX'] . '/')
			{
				$filePath = '/' . $arBucket['PREFIX'] . $filePath;
			}
		}
		$filePathU = CCloudUtil::URLEncode($filePath, 'UTF-8', true);

		$this->SendRequest(
			$arBucket['SETTINGS']['ACCESS_KEY'],
			$arBucket['SETTINGS']['SECRET_KEY'],
			'POST',
			$arBucket['BUCKET'],
			$filePathU,
			'',
			'',
			[
				'x-goog-acl' => 'public-read',
				'x-goog-resumable' => 'start',
				'Content-Type' => $ContentType,
			]
		);

		if (
			$this->status == 201
			&& is_array($this->headers)
			&& isset($this->headers['Location'])
			&& preg_match('/upload_id=(.*)$/', $this->headers['Location'], $match)
		)
		{
			return [
				'filePath' => $filePath,
				'filePos' => 0,
				'upload_id' => $match[1],
			];
		}

		if (defined('BX_CLOUDS_ERROR_DEBUG'))
		{
			AddMessage2Log($this);
		}

		return false;
	}

	public function InitiateMultipartUpload($arBucket, &$NS, $filePath, $fileSize, $ContentType)
	{
		$upload_info = $this->StartUpload($arBucket, $filePath, $ContentType);
		if ($upload_info)
		{
			$upload_info['fileSize'] = $fileSize;
			$upload_info['ContentType'] = $ContentType;
			$NS = $upload_info;
			return true;
		}
		else
		{
			return false;
		}
	}

	public function GetMinUploadPartSize()
	{
		return 5 * 1024 * 1024; //5MB
	}

	private function UploadRange($filePathU, $arBucket, &$NS, $data, $pos)
	{
		$this->SendRequest(
			$arBucket['SETTINGS']['ACCESS_KEY'],
			$arBucket['SETTINGS']['SECRET_KEY'],
			'PUT',
			$arBucket['BUCKET'],
			$filePathU . '?upload_id=' . rawurlencode($NS['upload_id']),
			'',
			'',
			[
				'Content-Range' => 'bytes */' . $NS['fileSize'],
			]
		);

		$data_len = strlen($data);

		$this->SendRequest(
			$arBucket['SETTINGS']['ACCESS_KEY'],
			$arBucket['SETTINGS']['SECRET_KEY'],
			'PUT',
			$arBucket['BUCKET'],
			$filePathU . '?upload_id=' . rawurlencode($NS['upload_id']),
			'',
			$data,
			[
				'Content-Range' => 'bytes ' . $pos . '-' . ($pos + $data_len - 1) . '/' . $NS['fileSize'],
			]
		);
	}

	public function UploadPartNo($arBucket, &$NS, $data, $part_no)
	{
		global $APPLICATION;
		$part_no = intval($part_no);

		$found = false;
		if (isset($NS['Parts']))
		{
			foreach ($NS['Parts'] as $first_part_no => $part)
			{
				if ($part['part_no'] === ($part_no - 1))
				{
					$found = $first_part_no;
					break;
				}
			}
		}
		else
		{
			$NS['Parts'] = [];
		}

		if ($found === false)
		{
			$partFileName = '/' . trim($NS['filePath'], '/') . '.tmp' . $part_no;
			if ($arBucket['PREFIX'])
			{
				if (mb_substr($partFileName, 0, mb_strlen($arBucket['PREFIX']) + 2) !== '/' . $arBucket['PREFIX'] . '/')
				{
					$partFileName = '/' . $arBucket['PREFIX'] . $partFileName;
				}
			}
			$upload_info = $this->StartUpload($arBucket, $partFileName, $NS['ContentType']);
			if ($upload_info)
			{
				$upload_info['fileSize'] = '*';
				$upload_info['part_no'] = $part_no;
				$found = $part_no;
				$NS['Parts'][$part_no] = $upload_info;
				ksort($NS['Parts']);
			}
			else
			{
				return false;
			}
		}

		$NS['Parts'][$found]['part_no'] = $part_no;
		if (
			(isset($NS['Parts'][$part_no + 1]))
			|| (($NS['Parts'][$found]['part_no'] * $this->GetMinUploadPartSize() + $this->GetMinUploadPartSize()) >= $NS['fileSize'])
		)
		{
			$data_len = strlen($data);
			$NS['Parts'][$found]['fileSize'] = $NS['Parts'][$found]['filePos'] + $data_len;
		}

		$filePath = $NS['Parts'][$found]['filePath'];
		$filePathU = CCloudUtil::URLEncode($filePath, 'UTF-8', true);

		$this->UploadRange($filePathU, $arBucket, $NS['Parts'][$found], $data, $NS['Parts'][$found]['filePos']);

		if (
			$this->status == 308
			&& is_array($this->headers)
			&& preg_match('/^bytes=(\\d+)-(\\d+)$/', $this->headers['Range'], $match)
		)
		{
			$APPLICATION->ResetException();
			$NS['Parts'][$found]['filePos'] = $match[2] + 1;
			return true;
		}
		elseif ($this->status == 200)
		{
			return true;
		}
		else
		{
			if (defined('BX_CLOUDS_ERROR_DEBUG'))
			{
				AddMessage2Log($this);
			}
			return false;
		}
	}

	public function UploadPart($arBucket, &$NS, $data)
	{
		global $APPLICATION;

		$filePath = '/' . trim($NS['filePath'], '/');
		if ($arBucket['PREFIX'])
		{
			if (mb_substr($filePath, 0, mb_strlen($arBucket['PREFIX']) + 2) !== '/' . $arBucket['PREFIX'] . '/')
			{
				$filePath = '/' . $arBucket['PREFIX'] . $filePath;
			}
		}
		$filePathU = CCloudUtil::URLEncode($filePath, 'UTF-8', true);

		$this->UploadRange($filePathU, $arBucket, $NS, $data, $NS['filePos']);

		if (
			$this->status == 308
			&& is_array($this->headers)
			&& preg_match('/^bytes=(\\d+)-(\\d+)$/', $this->headers['Range'], $match)
		)
		{
			$APPLICATION->ResetException();
			$NS['filePos'] = $match[2] + 1;
			return true;
		}
		elseif ($this->status == 200)
		{
			return true;
		}
		else
		{
			if (defined('BX_CLOUDS_ERROR_DEBUG'))
			{
				AddMessage2Log($this);
			}
			return false;
		}
	}

	public function CompleteMultipartUpload($arBucket, &$NS)
	{
		if (isset($NS['Parts']))
		{
			// https://cloud.google.com/storage/docs/xml-api/put-object-compose
			$filePath = '/' . trim($NS['filePath'], '/');
			if ($arBucket['PREFIX'])
			{
				if (mb_substr($filePath, 0, mb_strlen($arBucket['PREFIX']) + 2) !== '/' . $arBucket['PREFIX'] . '/')
				{
					$filePath = '/' . $arBucket['PREFIX'] . $filePath;
				}
			}
			$filePathU = CCloudUtil::URLEncode($filePath, 'UTF-8', true);

			$xml = '<ComposeRequest>';
			foreach ($NS['Parts'] as $part)
			{
				$xml .= '<Component><Name>' . ltrim($part['filePath'], '/') . '</Name></Component>';
			}
			$xml .= '</ComposeRequest>';

			$this->SendRequest(
				$arBucket['SETTINGS']['ACCESS_KEY'],
				$arBucket['SETTINGS']['SECRET_KEY'],
				'PUT',
				$arBucket['BUCKET'],
				$filePathU . '?compose',
				'',
				$xml,
				[
					'x-goog-acl' => 'public-read',
					'Content-Type' => $NS['ContentType'],
				]
			);

			if ($this->status == 200)
			{
				foreach ($NS['Parts'] as $part)
				{
					$this->DeleteFile($arBucket, $part['filePath']);
				}
				return true;
			}
			else
			{
				if (defined('BX_CLOUDS_ERROR_DEBUG'))
				{
					AddMessage2Log($this);
				}
				return false;
			}
		}
		return true;
	}

	public function SendRequest($access_key, $secret_key, $verb, $bucket, $file_name='/', $params='', $content='', $additional_headers=[])
	{
		global $APPLICATION;
		$this->status = 0;

		if (isset($additional_headers['Content-Type']))
		{
			$ContentType = $additional_headers['Content-Type'];
			unset($additional_headers['Content-Type']);
		}
		else
		{
			$ContentType = $content ? 'text/plain' : '';
		}

		if (!array_key_exists('x-goog-api-version', $additional_headers))
		{
			$additional_headers['x-goog-api-version'] = '1';
		}

		$RequestMethod = $verb;
		$RequestURI = $file_name;
		$RequestDATE = gmdate('D, d M Y H:i:s', time()) . ' GMT';

		//Prepare Signature
		$CanonicalizedAmzHeaders = '';
		ksort($additional_headers);
		foreach ($additional_headers as $key => $value)
		{
			if (preg_match('/^x-goog-/', $key))
			{
				$CanonicalizedAmzHeaders .= $key . ':' . $value . "\n";
			}
		}

		$CanonicalizedResource = '/' . $bucket . $RequestURI;

		$StringToSign = $RequestMethod . "\n\n"
			. $ContentType . "\n"
			. $RequestDATE . "\n"
			. $CanonicalizedAmzHeaders . $CanonicalizedResource
		;

		$Signature = base64_encode($this->hmacsha1($StringToSign, $secret_key));
		$Authorization = 'GOOG1 ' . $access_key . ':' . $Signature;

		$request = new Bitrix\Main\Web\HttpClient([
			'redirect' => false,
			'streamTimeout' => $this->streamTimeout,
		]);
		if (isset($additional_headers['option-file-result']))
		{
			$request->setOutputStream($additional_headers['option-file-result']);
		}

		$request->setHeader('Date', $RequestDATE);
		$request->setHeader('Authorization', $Authorization);
		foreach ($additional_headers as $key => $value)
		{
			if (!preg_match('/^option-/', $key))
			{
				$request->setHeader($key, $value);
			}
		}

		if (
			$this->new_end_point
			&& preg_match('#^(http|https)://' . preg_quote($bucket, '#') . '(.+)/#', $this->new_end_point, $match))
		{
			$host = $match[2];
		}
		else
		{
			$host = $bucket . '.commondatastorage.googleapis.com';
		}

		$was_end_point = $this->new_end_point;
		$this->new_end_point = '';

		$this->status = 0;
		$this->host = $host;
		$this->verb = $RequestMethod;
		$this->url = 'http://' . $host . $RequestURI . $params;
		$this->headers = [];
		$this->errno = 0;
		$this->errstr = '';
		$this->result = '';

		$stime = 0;
		$logRequest = false;
		if (defined('BX_CLOUDS_TRACE') && $verb !== 'GET' && $verb !== 'HEAD')
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

		if ($this->status == 200)
		{
			if (isset($additional_headers['option-raw-result']))
			{
				return $this->result;
			}
			elseif ($this->result)
			{
				$obXML = new CDataXML;
				$text = preg_replace('/<' . '\\?XML.*?\\?' . '>/i', '', $this->result);
				if ($obXML->LoadString($text))
				{
					$arXML = $obXML->GetArray();
					if (is_array($arXML))
					{
						return $arXML;
					}
				}
				//XML parse error
				$APPLICATION->ThrowException(GetMessage('CLO_STORAGE_GOOGLE_XML_PARSE_ERROR', ['#errno#' => 1]));
				return false;
			}
			else
			{
				//Empty success result
				return [];
			}
		}
		elseif (
			$this->status == 307  //Temporary redirect
			&& isset($this->headers['Location'])
			&& !$was_end_point //No recurse yet
		)
		{
			$this->new_end_point = $this->headers['Location'];
			return $this->SendRequest(
				$access_key,
				$secret_key,
				$verb,
				$bucket,
				$file_name,
				$params,
				$content,
				$additional_headers
			);
		}
		elseif ($this->status > 0)
		{
			if ($this->result)
			{
				$obXML = new CDataXML;
				if ($obXML->LoadString($this->result))
				{
					$arXML = $obXML->GetArray();
					if (is_array($arXML) && is_string($arXML['Error']['#']['Message'][0]['#']))
					{
						$APPLICATION->ThrowException(GetMessage('CLO_STORAGE_GOOGLE_XML_ERROR', ['#errmsg#' => trim($arXML['Error']['#']['Message'][0]['#'], '.')]));
						return false;
					}
				}
			}
			$APPLICATION->ThrowException(GetMessage('CLO_STORAGE_GOOGLE_XML_PARSE_ERROR', ['#errno#' => 2]));
			return false;
		}
		else
		{
			$APPLICATION->ThrowException(GetMessage('CLO_STORAGE_GOOGLE_XML_PARSE_ERROR', ['#errno#' => 3]));
			return false;
		}
	}

	public function hmacsha1($data, $key)
	{
		if (mb_strlen($key) > 64)
		{
			$key = pack('H*', sha1($key));
		}
		$key = str_pad($key, 64, chr(0x00));
		$ipad = str_repeat(chr(0x36), 64);
		$opad = str_repeat(chr(0x5c), 64);
		$hmac = pack('H*', sha1(($key ^ $opad) . pack('H*', sha1(($key ^ $ipad) . $data))));
		return $hmac;
	}
}
