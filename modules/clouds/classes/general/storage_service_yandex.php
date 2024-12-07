<?php
IncludeModuleLangFile(__FILE__);

class CCloudStorageService_Yandex extends CCloudStorageService_S3
{
	protected $set_headers = /*.(array[string]string).*/[];
	protected $new_end_point = '';
	protected $_public = true;
	protected $location = '';

	/**
	 * @return CCloudStorageService
	*/
	public function GetObject()
	{
		return new CCloudStorageService_Yandex();
	}

	/**
	 * @return string
	*/
	public function GetID()
	{
		return 'yandex';
	}

	/**
	 * @return string
	*/
	public function GetName()
	{
		return 'Yandex Object Storage';
	}

	/**
	 * @return array[string]string
	*/
	public function GetLocationList()
	{
		return [
			'' => 'storage.yandexcloud.net',
		];
	}

	/**
	 * @param array[string]string $arBucket
	 * @param bool $bServiceSet
	 * @param string $cur_SERVICE_ID
	 * @param bool $bVarsFromForm
	 * @return string
	*/
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
		$show = (($cur_SERVICE_ID === $this->GetID()) || !$bServiceSet) ? '' : 'none';
		$useHttps = $arSettings['USE_HTTPS'] ?? 'N';

		$result = '
		<tr id="SETTINGS_0_' . $htmlID . '" style="display:' . $show . '" class="settings-tr adm-detail-required-field">
			<td>' . GetMessage('CLO_STORAGE_YANDEX_EDIT_ACCESS_KEY') . ':</td>
			<td><input type="hidden" name="SETTINGS[' . $htmlID . '][ACCESS_KEY]" id="' . $htmlID . 'ACCESS_KEY" value="' . htmlspecialcharsbx($arSettings['ACCESS_KEY']) . '"><input type="text" size="55" name="' . $htmlID . 'INP_ACCESS_KEY" id="' . $htmlID . 'INP_ACCESS_KEY" value="' . htmlspecialcharsbx($arSettings['ACCESS_KEY']) . '" ' . ($arBucket['READ_ONLY'] === 'Y' ? '"disabled"' : '') . ' onchange="BX(\'' . $htmlID . 'ACCESS_KEY\').value = this.value"></td>
		</tr>
		<tr id="SETTINGS_1_' . $htmlID . '" style="display:' . $show . '" class="settings-tr adm-detail-required-field">
			<td>' . GetMessage('CLO_STORAGE_YANDEX_EDIT_SECRET_KEY') . ':</td>
			<td><input type="hidden" name="SETTINGS[' . $htmlID . '][SECRET_KEY]" id="' . $htmlID . 'SECRET_KEY" value="' . htmlspecialcharsbx($arSettings['SECRET_KEY']) . '"><input type="text" size="55" name="' . $htmlID . 'INP_SECRET_KEY" id="' . $htmlID . 'INP_SECRET_KEY" value="' . htmlspecialcharsbx($arSettings['SECRET_KEY']) . '" autocomplete="off" ' . ($arBucket['READ_ONLY'] === 'Y' ? '"disabled"' : '') . ' onchange="BX(\'' . $htmlID . 'SECRET_KEY\').value = this.value">' . (
				array_key_exists('SESSION_TOKEN', $arSettings) ?
				'<input type="hidden" name="SETTINGS[' . $htmlID . '][SESSION_TOKEN]" id="' . $htmlID . 'SESSION_TOKEN" value="' . htmlspecialcharsbx($arSettings['SESSION_TOKEN']) . '">' :
				''
			) . '</td>
		</tr>
		<tr id="SETTINGS_3_' . $htmlID . '" style="display:' . $show . '" class="settings-tr">
			<td nowrap>' . GetMessage('CLO_STORAGE_YANDEX_EDIT_USE_HTTPS') . ':</td>
			<td><input type="hidden" name="SETTINGS[' . $htmlID . '][USE_HTTPS]" id="' . $htmlID . 'KEY" value="N"><input type="checkbox" name="SETTINGS[' . $htmlID . '][USE_HTTPS]" id="' . $htmlID . 'USE_HTTPS" value="Y" ' . ($useHttps == 'Y' ? 'checked="checked"' : '') . '></td>
		</tr>
		';

		return $result;
	}

	/**
	 * @param array[string]string $arBucket
	 * @param array[string]string & $arSettings
	 * @return bool
	*/
	public function CheckSettings($arBucket, &$arSettings)
	{
		global $APPLICATION;
		$aMsg = /*.(array[int][string]string).*/[];

		$result = [
			'ACCESS_KEY' => is_array($arSettings) ? trim($arSettings['ACCESS_KEY']) : '',
			'SECRET_KEY' => is_array($arSettings) ? trim($arSettings['SECRET_KEY']) : '',
			'USE_HTTPS' => is_array($arSettings) && $arSettings['USE_HTTPS'] == 'Y' ? 'Y' : 'N',
		];
		if (is_array($arSettings) && array_key_exists('SESSION_TOKEN', $arSettings))
		{
			$result['SESSION_TOKEN'] = trim($arSettings['SESSION_TOKEN']);
		}

		if ($arBucket['READ_ONLY'] !== 'Y' && $result['ACCESS_KEY'] === '')
		{
			$aMsg[] = [
				'id' => $this->GetID() . 'INP_ACCESS_KEY',
				'text' => GetMessage('CLO_STORAGE_YANDEX_EMPTY_ACCESS_KEY'),
			];
		}

		if ($arBucket['READ_ONLY'] !== 'Y' && $result['SECRET_KEY'] === '')
		{
			$aMsg[] = [
				'id' => $this->GetID() . 'INP_SECRET_KEY',
				'text' => GetMessage('CLO_STORAGE_YANDEX_EMPTY_SECRET_KEY'),
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

	/**
	 * @param string $bucket
	 * @return string
	 **/
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
				return $bucket . '.storage.yandexcloud.net';
			}
			else
			{
				return 'storage.yandexcloud.net';
			}
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
	public function SendRequest($arSettings, $verb, $bucket, $file_name='/', $params='', $content='', $additional_headers=/*.(array[string]string).*/[])
	{
		$file_name = str_replace('+', '%20', $file_name);
		if (isset($additional_headers['x-amz-copy-source']))
		{
			$additional_headers['x-amz-copy-source'] = str_replace('+', '%20', $additional_headers['x-amz-copy-source']);
		}
		return parent::SendRequest($arSettings, $verb, $bucket, $file_name, $params, $content, $additional_headers);
	}

	/**
	 * @param array[string]string $arBucket
	 * @param mixed $arFile
	 * @param bool $encoded
	 * @return string
	*/
	public function GetFileSRC($arBucket, $arFile, $encoded = true)
	{
		/* @var \Bitrix\Main\HttpRequest $request */
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$proto = $request->isHttps() ? 'https' : 'http';

		if ($arBucket['CNAME'] != '')
		{
			$host = $arBucket['CNAME'];
			$pref = '';
		}
		elseif ($proto === 'https' && mb_strpos($arBucket['BUCKET'], '.') !== false)
		{
			$host = 'storage.yandexcloud.net';
			$pref = $arBucket['BUCKET'];
		}
		else
		{
			$host = $arBucket['BUCKET'] . '.storage.yandexcloud.net';
			$pref = '';
		}

		if (is_array($arFile))
		{
			$URI = ltrim($arFile['SUBDIR'] . '/' . $arFile['FILE_NAME'], '/');
		}
		else
		{
			$URI = ltrim($arFile, '/');
		}

		if ($arBucket['PREFIX'] != '')
		{
			if (mb_substr($URI, 0, mb_strlen($arBucket['PREFIX']) + 1) !== $arBucket['PREFIX'] . '/')
			{
				$URI = $arBucket['PREFIX'] . '/' . $URI;
			}
		}

		if ($pref !== '')
		{
			$URI = $pref . '/' . $URI;
		}

		if ($encoded)
		{
			return $proto . '://' . $host . '/' . str_replace('+', '%20', CCloudUtil::URLEncode($URI, 'UTF-8'));
		}
		else
		{
			return $proto . '://' . $host . '/' . $URI;
		}
	}

	/**
	 * @param array[string]string $arBucket
	 * @return bool
	*/
	public function DeleteBucket($arBucket)
	{
		//Do not delete bucket if there is some files left
		if (!$this->IsEmptyBucket($arBucket))
		{
			return false;
		}

		return parent::DeleteBucket($arBucket);
	}

	public function FileCopy($arBucket, $arFile, $filePath)
	{
		$this->streamTimeout = 3600;
		return parent::FileCopy($arBucket, $arFile, $filePath);
	}

	/**
	 * @param array[string]string $arBucket
	 * @param mixed & $NS
	 * @return bool
	*/
	public function CompleteMultipartUpload($arBucket, &$NS)
	{
		$this->streamTimeout = 3600;
		return parent::CompleteMultipartUpload($arBucket, $NS);
	}

	/**
	 * @param int $status
	 * @param string $result
	 * @return bool
	*/
	protected function checkForTokenExpiration($status, $result)
	{
		if ($status == 400 && mb_strpos($result, 'ExpiredToken') !== false)
		{
			return true;
		}
		if ($status == 403 && mb_strpos($result, 'The request signature we calculated does not match the signature you provided.') !== false)
		{
			return true;
		}
		return false;
	}}
