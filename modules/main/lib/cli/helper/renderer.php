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

	public function replaceFileContent(string $filePath, Template $template, string $startTag, string $endTag): void
	{
		$file = new File($filePath);

		if (!$file->isExists())
		{
			$this->renderToFile($filePath, $template);
			return;
		}

		$fileContent = $file->getContents();
		$templateContent = $template->getContent();

		$startPos = mb_strpos($fileContent, $startTag);
		$endPos = mb_strpos($fileContent, $endTag);

		if ($startPos !== false && $endPos !== false && $endPos > $startPos)
		{
			$beforeStart = mb_substr($fileContent, 0, $startPos + mb_strlen($startTag));
			$afterEnd = mb_substr($fileContent, $endPos);

			$newContent = $beforeStart . PHP_EOL . $templateContent . PHP_EOL . $afterEnd;
		}
		else
		{
			$lastBrace = mb_strrpos($fileContent, '}');

			if ($lastBrace !== false)
			{
				$beforeBrace = mb_substr($fileContent, 0, $lastBrace);
				$afterBrace = mb_substr($fileContent, $lastBrace);

				$newContent = $beforeBrace . PHP_EOL . $templateContent . PHP_EOL . $afterBrace;
			}
			else
			{
				$newContent = $fileContent . PHP_EOL . $templateContent;
			}
		}

		$file->putContents($newContent);
	}
}
