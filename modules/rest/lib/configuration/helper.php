<?php

namespace Bitrix\Rest\Configuration;

use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\EventManager;
use Bitrix\Main\Config\Option;
use Bitrix\Security\Filter\Auditor;

class Helper
{
	const TYPE_SECTION_TOTAL = 'total';

	public $prefixAppContext = 'app';
	protected $optionRatio = '~import_configuration_app_ratio_data';
	protected $optionUsesConfigurationApp = 'uses_configuration_app';
	protected $appConfigurationFolderBackup = 'appConfiguration';
	/** @var Helper|null  */
	private static $instance = null;
	private $sanitizer = null;
	private function __construct()
	{

	}

	/**
	 * @return Helper
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new Helper();
		}

		return self::$instance;
	}

	/**
	 * Sanitize bad value.
	 * @param string $value Bad value.
	 * @param bool &$bad Return true, if value is bad.
	 * @param string $splitter Splitter for bad content.
	 * @return string Good value.
	 */
	public function sanitize($value, &$bad = false, $splitter = ' ')
	{
		if (!is_bool($bad))
		{
			$bad = false;
		}

		if ($this->sanitizer === null)
		{
			$this->sanitizer = false;
			if (Loader::includeModule('security'))
			{
				$this->sanitizer = new Auditor\Xss(
					$splitter
				);
			}
		}

		if ($this->sanitizer)
		{
			// bad value exists
			if (is_array($value))
			{
				foreach ($value as &$val)
				{
					$val = $this->sanitize($val, $bad, $splitter);
				}
				unset($val);
			}
			elseif ($this->sanitizer->process($value))
			{
				$bad = true;
				$value = $this->sanitizer->getFilteredValue();
			}
		}

		return $value;
	}

	public function getStorageBackupParam()
	{
		return [
			'NAME' => $this->appConfigurationFolderBackup,
			'MODULE_ID' => 'rest',
			'ENTITY_TYPE' => ProxyDiskType::className(),
			'ENTITY_ID' => 1,
		];
	}

	public function getStorageBackup()
	{
		$storage = false;
		if(Loader::includeModule('disk'))
		{
			$storage = \Bitrix\Disk\Driver::getInstance()->addStorageIfNotExist(
				$this->getStorageBackupParam()
			);
		}
		return $storage;
	}

	//uses configuration app
	public function getUsesConfigurationApp()
	{
		return Option::get('rest', $this->optionUsesConfigurationApp, '');
	}

	public function setUsesConfigurationApp($code)
	{
		$result = true;
		try
		{
			Option::set('rest', $this->optionUsesConfigurationApp, $code);
		}
		catch (\Exception $e)
		{
			$result = false;
		}

		return $result;
	}

	public function deleteUsesConfigurationApp()
	{
		Option::delete('rest', array('name' => $this->optionUsesConfigurationApp));
		return true;
	}

	//ratio data import
	public function getRatio()
	{
		$data = Option::get('rest', $this->optionRatio);
		if ($data)
		{
			$data = Json::decode($data);
		}
		else
		{
			$data = [];
		}
		return $data;
	}

	public function addRatio($type, $ratioData = [])
	{
		$result = true;
		if (is_array($ratioData))
		{
			$data = $this->getRatio();
			if (!$data[$type])
			{
				$data[$type] = [];
			}
			foreach ($ratioData as $old => $new)
			{
				$data[$type][$old] = $new;
			}
			Option::set('rest', $this->optionRatio, Json::encode($data));
		}
		return $result;
	}

	public function clearRatio($type)
	{
		$data = $this->getRatio();
		if (array_key_exists($type, $data))
		{
			unset($data[$type]);
			Option::set('rest', $this->optionRatio, Json::encode($data));
		}
		return true;
	}

	public function deleteRatio()
	{
		Option::delete('rest', array('name' => $this->optionRatio));
		return true;
	}
}