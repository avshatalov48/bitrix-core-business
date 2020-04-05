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

	protected $tagAttributes = [];

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
	 * Set tag attributes.
	 *
	 * @param array $tagAttributes Tag attributes.
	 * @return $this
	 */
	public function setTagAttributes(array $tagAttributes = [])
	{
		$this->tagAttributes = $tagAttributes;
		return $this;
	}

	/**
	 * Return loader file url.
	 *
	 * @return string
	 */
	public function getFileUrl()
	{
		return $this->file->getUri();
	}

	/**
	 * Return loader string.
	 *
	 * @return string
	 */
	public function getString()
	{
		$content = $this->getStringJs();
		$attributes = $this->tagAttributes;
		if ($this->skipMoving)
		{
			$attributes['data-skip-moving'] = 'true';
		}
		$attrs = '';
		foreach ($attributes as $key => $value)
		{
			$attrs .= " " . htmlspecialcharsbx($key) . '="'
				. htmlspecialcharsbx($value) . '"';
		}
		return <<<EOD
<script{$attrs}>
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