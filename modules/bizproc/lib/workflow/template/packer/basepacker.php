<?php
namespace Bitrix\Bizproc\Workflow\Template\Packer;

use Bitrix\Bizproc\Workflow\Template\Tpl;

abstract class BasePacker
{
	/**
	 * Get data for package.
	 * @param Tpl $tpl
	 * @return mixed
	 */
	abstract public function makePackageData(Tpl $tpl);

	/**
	 * Pack the template.
	 * @param Tpl $tpl
	 * @return Result\Pack
	 */
	abstract public function pack(Tpl $tpl);

	/**
	 * Unpack the data to template
	 * @param $data
	 * @return Result\Unpack
	 */
	abstract public function unpack($data);

	protected function compress($data)
	{
		if (function_exists("gzcompress"))
		{
			$data = gzcompress($data, 9);
		}
		return $data;
	}

	protected function uncompress($data)
	{
		if (is_string($data) && function_exists("gzuncompress"))
		{
			$data = @gzuncompress($data);
		}
		return $data;
	}
}