<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\UI\Barcode;

/**
 *
 */
class Barcode
{
	private $generator;

	/**
	 * @var string
	 */
	private $type = BarcodeDictionary::TYPE_QR;
	/**
	 * @var string
	 */
	private $format = BarcodeDictionary::FORMAT_PNG;

	/**
	 * @var array
	 */
	private $options = [];

	public function __construct()
	{
		$this->generator = new BarcodeGenerator();
	}

	/**
	 * @param string $data
	 * @return false|string
	 */
	public function render(string $data)
	{
		if ($this->format === BarcodeDictionary::FORMAT_SVG)
		{
			return $this->generator->render_svg($this->type, $data, $this->options);
		}

		$image = $this->generator->render_image($this->type, $data, $this->options);

		ob_start();
		switch ($this->format)
		{
			case BarcodeDictionary::FORMAT_PNG:
				imagepng($image);
				break;
			case BarcodeDictionary::FORMAT_GIF:
				imagegif($image);
				break;
			case BarcodeDictionary::FORMAT_JPEG:
				imagejpeg($image);
				break;
		}
		imagedestroy($image);

		return ob_get_clean();
	}

	/**
	 * @param string $data
	 */
	public function print(string $data): void
	{
		$this->generator->output_image($this->format, $this->type, $data, $this->options);
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function type(string $type): self
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @param string $format
	 * @return $this
	 */
	public function format(string $format): self
	{
		$this->format = $format;
		return $this;
	}

	/**
	 * @param array $options
	 * @return $this
	 */
	public function options(array $options): self
	{
		$this->options = $options;
		return $this;
	}

	/**
	 * @param string $option
	 * @param $value
	 * @return $this
	 */
	public function option(string $option, $value): self
	{
		$this->options[$option] = $value;
		return $this;
	}
}