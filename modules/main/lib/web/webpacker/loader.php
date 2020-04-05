<?php

namespace Bitrix\Main\Web\WebPacker;

/**
 * Class Loader
 *
 * @package Bitrix\Main\Web\WebPacker
 */
class Loader
{
	/** @var Output\File */
	protected $file;

	protected $cacheTtl = 60;

	protected $skipMoving = false;

	/**
	 * Loader constructor.
	 *
	 * @param Output\File $file File output instance.
	 */
	public function __construct(Output\File $file)
	{
		$this->file = $file;
	}

	/**
	 * Set cache ttl.
	 *
	 * @param int $cacheTtl Ttl in seconds.
	 * @return $this
	 */
	public function setCacheTtl($cacheTtl)
	{
		$this->cacheTtl = (int) $cacheTtl;
		return $this;
	}

	/**
	 * Set skip moving.
	 *
	 * @param bool $skip Skip moving.
	 * @return $this
	 */
	public function setSkipMoving($skip)
	{
		$this->skipMoving = (bool) $skip;
		return $this;
	}

	/**
	 * Return loader string.
	 *
	 * @return string
	 */
	public function getString()
	{
		$content = $this->getStringJs();
		$skipMoving = $this->skipMoving ? ' data-skip-moving="true"' : '';
		return <<<EOD
<script{$skipMoving}>
$content
</script>
EOD;

	}

	/**
	 * Return loader js string.
	 *
	 * @return string
	 */
	public function getStringJs()
	{
		$path = $this->file->getUri();
		if (!$path)
		{
			return '';
		}

		$ttl = ($this->cacheTtl ?: 1) * 1000;

		return
<<<EOD
	(function(w,d,u){
		var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/$ttl|0);
		var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
	})(window,document,'$path');
EOD;

	}
}