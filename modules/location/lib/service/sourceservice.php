<?php

namespace Bitrix\Location\Service;

use Bitrix\Location\Common\BaseService;
use Bitrix\Location\Entity\Source;
use Bitrix\Location\Infrastructure\Service\Config\Container;

/**
 * Class SourceService
 * @package Bitrix\Location\Service
 * @internal
 */
final class SourceService extends BaseService
{
	/** @var SourceService */
	protected static $instance;

	/** @var Source|null */
	protected $source;

	/**
	 * @return string
	 */
	public function getSourceCode(): string
	{
		return $this->source->getCode();
	}

	/**
	 * @return Source|null
	 */
	public function getSource(): ?Source
	{
		return $this->source;
	}

	/**
	 * SourceService constructor.
	 * @param Container $config
	 */
	protected function __construct(Container $config)
	{
		parent::__construct($config);
		$this->source = $config->get('source');
	}
}
