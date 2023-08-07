<?php
/**
 * Bitrix Framework
 *
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Message;

use Bitrix\Main\Mail\Tracking;
use Bitrix\Main\SiteTable;
use Bitrix\Sender\Integration;

class Tracker
{
	public const TYPE_READ = 1;
	public const TYPE_CLICK = 2;
	public const TYPE_UNSUB = 3;

	/** @var  integer $type Type. */
	protected $type;

	/** @var  string $moduleId Module ID. */
	protected $moduleId;

	/** @var  array $fields Fields. */
	protected $fields = [];

	/** @var  array $uriParameters Uri parameters. */
	protected $uriParameters = [];

	/** @var  string $handlerUri Handler uri. */
	protected $handlerUri;

	/** @var  string $linkDomain Link domain. */
	protected $linkDomain;

	/** @var  string $siteId Site id. */
	protected $siteId;

	private $siteData;

	/**
	 * Constructor.
	 *
	 * @param integer $type Type.
	 */
	public function __construct($type)
	{
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getModuleId()
	{
		return $this->moduleId;
	}

	/**
	 * @param string $moduleId
	 * @return $this
	 */
	public function setModuleId($moduleId)
	{
		$this->moduleId = $moduleId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSiteId()
	{
		return $this->moduleId;
	}

	/**
	 * @param string $siteId
	 * @return $this
	 */
	public function setSiteId($siteId)
	{
		$this->siteId = $siteId;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * @param array $fields Fields.
	 * @return $this
	 */
	public function setFields($fields)
	{
		$this->fields = $fields;
		return $this;
	}

	/**
	 * @param string $key Fields.
	 * @param string|integer|null $value Value.
	 */
	public function addField($key, $value)
	{
		$this->fields[$key] = $value;
	}

	/**
	 * @return array
	 */
	public function getUriParameters()
	{
		return $this->uriParameters;
	}

	/**
	 * @param array $uriParameters
	 * @return $this
	 */
	public function setUriParameters($uriParameters)
	{
		$this->uriParameters = $uriParameters;
		return $this;
	}

	/**
	 * @param string $key Fields.
	 * @param string|integer|null $value Value.
	 * @return $this
	 */
	public function addUriParameter($key, $value)
	{
		$this->uriParameters[$key] = $value;
		return $this;
	}

	/**
	 * Get page uri.
	 *
	 * @return string
	 */
	public function getHandlerUri()
	{
		if (!$this->handlerUri && Integration\Bitrix24\Service::isPortal())
		{
			return Integration\Bitrix24\Service::getTrackingUri(
				$this->type,
				(Integration\Bitrix24\Service::isCloud() ? null : $this->siteId) // $this->siteId is not used for cloud
			);
		}

		return $this->handlerUri;
	}

	/**
	 * Set handler uri.
	 *
	 * @param string $handlerUri
	 * @return $this
	 */
	public function setHandlerUri($handlerUri)
	{
		$this->handlerUri = $handlerUri;
		return $this;
	}

	/**
	 * Get link domain name.
	 *
	 * @return string
	 */
	public function getLinkDomain()
	{
		if ($this->linkDomain === null)
		{
			if (Integration\Bitrix24\Service::isCloud())
			{
				return '';
			}
			if ($this->siteId)
			{
				if ($this->siteData === null)
				{
					$this->siteData = SiteTable::getById($this->siteId)->fetch();
				}
				if ($this->siteData && $this->siteData['SERVER_NAME'])
				{
					return $this->siteData['SERVER_NAME'];
				}
			}
		}

		return $this->linkDomain;
	}

	/**
	 * Set link domain name.
	 *
	 * @param string $linkDomain
	 * @return $this
	 */
	public function setLinkDomain($linkDomain)
	{
		$this->linkDomain = $linkDomain;
		return $this;
	}

	/**
	 * Get link.
	 *
	 * @return string
	 */
	public function getLink()
	{
		$link = '';

		$moduleId = $this->getModuleId();
		$fields = $this->getFields();
		if (!$moduleId || empty($fields))
		{
			return $link;
		}

		$uri = $this->getHandlerUri();
		switch ($this->type)
		{
			case self::TYPE_READ:
				$link = Tracking::getLinkRead($moduleId, $fields, $uri);
				break;

			case self::TYPE_CLICK:
				$link = Tracking::getLinkClick($moduleId, $fields, $uri);
				break;

			case self::TYPE_UNSUB:
				$link = Tracking::getLinkUnsub($moduleId, $fields, $uri);
				break;
		}

		return $link;
	}

	/**
	 * Get as array.
	 *
	 * @return array
	 */
	public function getArray()
	{
		return [
			'MODULE_ID' => $this->getModuleId(),
			'FIELDS' => $this->getFields(),
			'URL_PAGE' => $this->getHandlerUri(),
			'URL_PARAMS' => $this->getUriParameters(),
		];
	}

	/**
	 * Set as array.
	 *
	 * @param array $data Data.
	 * @return $this
	 */
	public function setArray(array $data)
	{
		if (isset($data['MODULE_ID']))
		{
			$this->setModuleId($data['MODULE_ID']);
		}
		if (isset($data['FIELDS']))
		{
			$this->setFields($data['FIELDS']);
		}
		if (isset($data['URL_PARAMS']))
		{
			$this->setUriParameters($data['URL_PARAMS']);
		}
		if (isset($data['URL_PAGE']))
		{
			$this->setHandlerUri($data['URL_PAGE']);
		}

		return $this;
	}
}