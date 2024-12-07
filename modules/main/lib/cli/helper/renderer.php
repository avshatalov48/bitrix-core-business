<?php

namespace Bitrix\Main\Cli\Helper;

use Bitrix\Main\Cli\Helper\Renderer\Template;
use Bitrix\Main\IO\File;

final class Renderer
{
	public function renderToFile(string $filePath, Template $template): void
	{
		$file = new File($filePath);
		$file->putContents($template->getContent());
	}
}
