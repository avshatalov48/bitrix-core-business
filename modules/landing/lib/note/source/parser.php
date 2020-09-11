<?php
namespace Bitrix\Landing\Note\Source;

use \Bitrix\Main\Web\DOM;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\File;

class Parser
{
	/**
	 * Download URL for files in text.
	 */
	const ACTION_DOWNLOAD_FILE = '/bitrix/tools/disk/uf.php?attachedId=#attachedId#&amp;action=download&amp;ncc=1';

	/**
	 * Returns converted to html bb text.
	 * @param string $text Text to convert.
	 * @return string
	 */
	protected static function convertText(string $text): string
	{
		static $parser = null;

		if ($parser === null)
		{
			$parser = new \CTextParser;
			$parser->allow['SMILES'] = 'N';
		}

		// prepare before parsing
		$text = preg_replace('#\[P\]\s*\[VIDEO#s', '[VIDEO', $text);
		$text = preg_replace('#\[/VIDEO\]\s*\[/P\]#s', '[/VIDEO]', $text);

		$text = $parser->convertText($text);

		// prepare after parsing
		$text = preg_replace('#<p>\s*</p>#s', '<br/>', $text);

		return $text;
	}

	/**
	 * Returns video data by video src.
	 * @param string $videoSrc Video source url.
	 * @return array|null
	 */
	protected static function getVideoContent(string $videoSrc): ?array
	{
		if (preg_match('#^//www.youtube.com/embed/([^?]+)#i', $videoSrc, $matches))
		{
			$ytCode = $matches[1];

			return [
				'src' => $videoSrc,
				'source' => 'https://www.youtube.com/watch?v=' . $ytCode,
				'preview' => '//img.youtube.com/vi/' .$ytCode . '/sddefault.jpg'
			];
		}

		return null;
	}

	/**
	 * Returns img content by attache id.
	 * @param int|string $attacheId Attache id.
	 * @param array $files Files array.
	 * @return array|null
	 */
	protected static function getImgContent($attacheId, array $files = []): ?array
	{
		if (isset($files[$attacheId]))
		{
			$fileItem = $files[$attacheId];
			$file = \CFile::getFileArray($fileItem['file_id']);
			if (strpos($file['CONTENT_TYPE'], 'image/') === 0)
			{
				$fileIO = new \Bitrix\Main\IO\File(
					Manager::getDocRoot() . $file['SRC']
				);
				if ($fileIO->isExists())
				{
					$newFile = Manager::savePicture([
						$fileItem['file_name'],
						base64_encode($fileIO->getContents())
					]);
					if ($newFile)
					{
						return [
							'id' => $newFile['ID'],
							'src' => File::getFilePath($newFile['ID'])
						];
					}
				}
			}
		}

		return null;
	}

	/**
	 * Replaces files in text content.
	 * @param string $text Content.
	 * @param array $files Files array.
	 * @return string
	 */
	protected static function replaceFilesInContent(string $text, array $files = []): string
	{
		$replace = [];

		foreach ($files as $fId => $item)
		{
			if ($item['prefix'])
			{
				$actionUrl = str_replace(
					'#attachedId#',
					$item['id'],
					self::ACTION_DOWNLOAD_FILE
				);
				$replace['[DISK FILE ID=' . $fId . ']'] =
					'<a href="' . $actionUrl . '" target="_blank">' .
						$item['file_name'] .
					'</a>';
			}
		}

		if ($replace)
		{
			$text = str_replace(
				array_keys($replace),
				array_values($replace),
				$text
			);
		}

		return $text;
	}

	/**
	 * Returns block info from DOM Node.
	 * @param DOM\Node $node Node instance.
	 * @param array $params Additional params.
	 * @return array|null
	 */
	protected static function getBlockFromNode(\Bitrix\Main\Web\DOM\Node $node, array $params = []): ?array
	{
		$type = 'text';
		$content = $node->getOuterHTML();
		$attrs = $node->getAttributes();

		// quote / code
		if (isset($attrs['class']) && in_array($attrs['class']->getValue(), ['quote', 'code']))
		{
			$type = $attrs['class']->getValue();
			$regExp = '#^<div class="' . $type . '">\s*<table class="' . $type . '"><tr><td>' .
			          '(.*?)</td></tr></table></div>$#is';
			if (preg_match($regExp, $content, $matches))
			{
				$content = $matches[1];
			}
			if ($type == 'code' && preg_match('#^<pre>(.*?)</pre>$#is', $content, $matches))
			{
				$content = $matches[1];
			}
		}
		// table
		else if (isset($attrs['class']) && $attrs['class']->getValue() == 'data-table')
		{
			$type = 'table';
		}
		// video
		else if (($node->getNodeName() == 'IFRAME') && isset($attrs['src']))
		{
			$type = 'video';
			$content = self::getVideoContent(
				$attrs['src']->getValue()
			);
		}
		// picture / file on a single line
		else if (preg_match('/^\[DISK FILE ID=([^\]]+)\]/is', trim($content), $matches))
		{
			$type = 'img';
			$content = self::getImgContent($matches[1], $params['files']);;
		}

		// replace files in content
		if (
			$type == 'text' &&
			isset($params['files']) &&
			is_array($params['files'])
		)
		{
			$content = self::replaceFilesInContent($content, $params['files']);
		}

		if ($content)
		{
			return [
				'type' => $type,
				'content' => $content
			];
		}

		return null;
	}

	/**
	 * Converts bb text to semantic blocks.
	 * @param string $text BB text.
	 * @param array $params Additional params.
	 * @return array
	 */
	public static function textToBlocks(string $text, array $params = []): array
	{
		$count = -1;
		$blocks = [];

		$dom = new DOM\Document;
		$text = self::convertText($text);
		$dom->loadHTML($text);

		foreach ($dom->getChildNodesArray() as $child)
		{
			$bewBlock = self::getBlockFromNode($child, $params);
			if ($bewBlock)
			{
				if (
					$blocks &&
					$bewBlock['type'] == 'text' &&
					$blocks[$count]['type'] == 'text'
				)
				{
					$blocks[$count]['content'] .= $bewBlock['content'];
				}
				else
				{
					$count++;
					$blocks[] = $bewBlock;
				}
			}
		}

		// trim block content
		foreach ($blocks as $key => $item)
		{
			if ($item['type'] == 'text')
			{
				if (!trim(str_replace(['<br />', '<br/>'], '', $item['content'])))
				{
					unset($blocks[$key]);
				}
			}
		}

		return array_values($blocks);
	}
}