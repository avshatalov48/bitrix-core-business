<?php

namespace Bitrix\Bizproc\Result;

class WebResultHandler implements DeviceResultHandler
{
	public function handle(RenderedResult $renderedResult): array
	{
		switch ($renderedResult->status)
		{
			case RenderedResult::BB_CODE_RESULT:
				return [
					'text' => \CBPViewHelper::prepareTaskDescription(
						\CBPHelper::convertBBtoText(
							preg_replace('|\n+|', "\n", trim($renderedResult->text ?? '')),
						)),
					'status' => $renderedResult->status,
				];

			case RenderedResult::USER_RESULT:
				return [
					'text' => \CBPHelper::convertBBtoText(
						preg_replace('|\n+|', "\n", trim($renderedResult->text ?? '')),
					),
					'status' => $renderedResult->status,
				];

			case RenderedResult::NO_RIGHTS:
				return [
					'text' => $renderedResult->text ?? '',
					'status' => $renderedResult->status,
				];
		}
	}
}
