<?php

namespace Bitrix\Main\Mail;

class Converter
{
	public static function htmlToText(string $body): string
	{
		$body = str_replace(array("\n","\r"), '', $body);
		// get <body> inner html if exists
		$innerBody = trim(preg_replace('/(.*?<body[^>]*>)(.*?)(<\/body>.*)/is', '$2', $body));
		$body = $innerBody ?: $body;

		// modify links to text version
		$body = preg_replace_callback(
			"%<a[^>]*?href=(['\"])(?<href>[^\1]*?)(?1)[^>]*?>(?<text>.*?)<\/a>%ims",
			function ($matches)
			{
				$href = $matches['href'];
				$text = trim($matches['text']);
				if (!$href)
				{
					return $matches[0];
				}
				$text = strip_tags($text);
				return ($text ? "$text:" : '') ."\n$href\n";
			},
			$body
		);

		// change <br> to new line
		$body = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $body);

		$body = preg_replace('|(<style[^>]*>)(.*?)(<\/style>)|isU', '', $body);
		$body = preg_replace('|(<script[^>]*>)(.*?)(<\/script>)|isU', '', $body);

		// remove tags
		$body = strip_tags($body);

		// format text to the left side
		$lines = [];
		foreach (explode("\n", trim($body)) as $line)
		{
			$lines[] = trim($line);
		}

		// remove redundant new lines
		$body = preg_replace("/[\\n]{2,}/", "\n\n", implode("\n", $lines));

		// remove redundant spaces
		$body = preg_replace("/[ \\t]{2,}/", "  ", $body);

		// decode html-entities
		return html_entity_decode($body);
	}
}