<?php

namespace Bitrix\Main\UI\Viewer\Transformation;

use Bitrix\Transformer\FileTransformer;

abstract class Transformation
{
	public static function getInputContentTypes()
	{
		return [];
	}

	abstract public function getOutputContentType();
	abstract public function getOutputExtension();

	/**
	 * @return FileTransformer
	 */
	abstract public function buildTransformer();

	/**
	 * Returns maximum file size for transformation.
	 *
	 * @return int
	 */
	public function getInputMaxSize()
	{
		return 0;
	}
}