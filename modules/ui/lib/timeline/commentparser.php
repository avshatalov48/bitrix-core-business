<?php

namespace Bitrix\UI\Timeline;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class CommentParser
{
	protected $parser;
	protected $userFields;

	public function __construct(array $userFields = [])
	{
		$this->userFields = $userFields;
	}

	public function setUserFields(array $userFields): CommentParser
	{
		$this->userFields = $userFields;

		return $this;
	}

	public function getHtml(string $text): string
	{
		$rules = array(
			"HTML" => "N",
			"ALIGN" => "Y",
			"ANCHOR" => "Y", "BIU" => "Y",
			"IMG" => "Y", "QUOTE" => "Y",
			"CODE" => "Y", "FONT" => "Y",
			"LIST" => "Y", "SMILES" => "Y",
			"NL2BR" => "Y", "MULTIPLE_BR" => "N",
			"VIDEO" => "Y", "LOG_VIDEO" => "N",
			"SHORT_ANCHOR" => "Y"
		);

		$parser = $this->getParser();

		if($parser instanceof \blogTextParser)
		{
			 $result = $parser->convert($text, [], $rules);
		}
		elseif($parser instanceof \forumTextParser)
		{
			$result = $parser->convert($text, $rules, "html", []);
		}
		elseif($parser instanceof \logTextParser)
		{
			$result = $parser->convert($text, [], $rules);
		}
		else
		{
			$result = $parser->convertText($text);
		}

		$result = \Bitrix\Main\Text\Emoji::decode($result);
		$result = preg_replace('/\[[^\]]+\]/', '', $result);

		return $result;
	}

	public function getText(string $text): string
	{
		$parser = $this->getParser();
		if($parser instanceof \blogTextParser || $parser instanceof \forumTextParser)
		{
			$result = $parser::killAllTags($text);
		}
		else
		{
			$result = $parser::clearAllTags($text);
		}

		return preg_replace('/\[[^\]]+\]/', '', $result);
	}

	public function getMentionedUserIds(string $text): array
	{
		$mentionedUserIds = [];

		if(preg_match_all("/\[user\s*=\s*([^\]]*)\](.+?)\[\/user\]/is" . BX_UTF_PCRE_MODIFIER, $text, $matches) && is_array($matches[1]))
		{
			$mentionedUserIds = $matches[1];
			$mentionedUserIds = array_unique($mentionedUserIds);
			foreach($mentionedUserIds as &$mentionedUserId)
			{
				$mentionedUserId = (int) $mentionedUserId;
			}
		}

		return $mentionedUserIds;
	}

	protected function getParser(): \CTextParser
	{
		$languageId = Loc::getCurrentLang();
		if($this->parser === null && Loader::includeModule('blog'))
		{
			$this->parser = new \blogTextParser($languageId);
		}
		if($this->parser === null && Loader::includeModule('forum'))
		{
			$this->parser = new \forumTextParser($languageId);
		}
		if($this->parser === null && Loader::includeModule('socialnetwork'))
		{
			$this->parser = new \logTextParser($languageId);
		}
		if($this->parser === null)
		{
			$this->parser = new \CTextParser();
		}

		if(is_array($this->userFields))
		{
			$this->parser->arUserfields = $this->userFields;
		}

		return $this->parser;
	}
}