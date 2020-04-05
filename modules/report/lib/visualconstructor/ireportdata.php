<?php

namespace Bitrix\Report\VisualConstructor;

/**
 * Interface IReportData
 * @package Bitrix\Report\VisualConstructor
 */
interface IReportData
{
	/**
	 * Called every time when calculate some report result before passing some concrete handler, such us getMultipleData or getSingleData.
	 * Here you can get result of configuration fields of report, if report in widget you can get configurations of widget.
	 *
	 * @return mixed
	 */
	public function prepare();
}