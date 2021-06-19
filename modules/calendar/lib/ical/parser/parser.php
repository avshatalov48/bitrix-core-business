<?php


namespace Bitrix\Calendar\ICal\Parser;


use Generator;

class Parser
{
	/**
	 * @var string|null
	 */
	private $content;
	/**
	 * @var Generator|null
	 */
	private $linesGenerator;
	/**
	 * @var Calendar
	 */
	private $component;

	/**
	 * @param string $content
	 * @return Parser
	 */
	public static function createInstance(string $content): Parser
	{
		return new self($content);
	}

	/**
	 * Parser constructor.
	 * @param string $content
	 */
	public function __construct(string $content)
	{
		$this->content = $content;
	}

	/**
	 * @return $this
	 */
	public function parse(): Parser
	{
		$this->linesGenerator = $this->getLinesGenerator();
		$this->component = $this->handle();

		return $this;
	}

	/**
	 * @return Generator|null
	 */
	private function getLinesGenerator(): ?Generator
	{
		$tmp = explode("\r\n", $this->content);

		for ($i = 0, $length = count($tmp); $i < $length; $i++)
		{
			$line = rtrim($tmp[$i]);

			while (isset($tmp[$i + 1]) && mb_strlen($tmp[$i + 1]) > 0 && ($tmp[$i + 1][0] === ' ' || $tmp[$i + 1][0] === "\t" ))
			{
				$line .= rtrim(mb_substr($tmp[++$i],1));
			}

			yield $line;
		}

		return null;
	}

	/**
	 * @return ParserComponent|null
	 */
	private function handle(): ?ParserComponent
	{
		$componentName = '';
		$properties = [];
		$componentsCollection = new ComponentsCollection();

		while ($str = $this->linesGenerator->current())
		{
			$line = Line::createInstance($str);
			$line->parse();

			if ($line->isBegin())
			{
				if ($componentName)
				{
					$componentsCollection->add($this->handle());
				}
				else
				{
					$componentName = mb_strtolower($line->getValue());
				}
			}
			elseif ($line->isEnd())
			{
				return FactoryComponents::createInstance($componentName)
					->createComponent($properties, $componentsCollection)
					->getComponent();
			}
			elseif (in_array($line->getName(), ['attendee', 'attach']))
			{
				$properties[$line->getName()][] = ParserPropertyType::createInstance($line->getName())
					->addParameters($line->getParams())
					->setValue($line->getValue());
			}
			else
			{
				$properties[$line->getName()] = ParserPropertyType::createInstance($line->getName())
					->addParameters($line->getParams())
					->setValue($line->getValue());
			}

			$this->linesGenerator->next();
		}

		return null;
	}

	/**
	 * @return Calendar|null
	 */
	public function getCalendarComponent(): ?Calendar
	{
		return ($this->component instanceof Calendar)
			? $this->component
			: null
		;
	}

//	private static function _ValidUtf8( $data ) {
//		$rx  = '[\xC0-\xDF]([^\x80-\xBF]|$)';
//		$rx .= '|[\xE0-\xEF].{0,1}([^\x80-\xBF]|$)';
//		$rx .= '|[\xF0-\xF7].{0,2}([^\x80-\xBF]|$)';
//		$rx .= '|[\xF8-\xFB].{0,3}([^\x80-\xBF]|$)';
//		$rx .= '|[\xFC-\xFD].{0,4}([^\x80-\xBF]|$)';
//		$rx .= '|[\xFE-\xFE].{0,5}([^\x80-\xBF]|$)';
//		$rx .= '|[\x00-\x7F][\x80-\xBF]';
//		$rx .= '|[\xC0-\xDF].[\x80-\xBF]';
//		$rx .= '|[\xE0-\xEF]..[\x80-\xBF]';
//		$rx .= '|[\xF0-\xF7]...[\x80-\xBF]';
//		$rx .= '|[\xF8-\xFB]....[\x80-\xBF]';
//		$rx .= '|[\xFC-\xFD].....[\x80-\xBF]';
//		$rx .= '|[\xFE-\xFE]......[\x80-\xBF]';
//		$rx .= '|^[\x80-\xBF]';
//
//		return ( ! (bool) preg_match('!'.$rx.'!', $data) );
//	}
}