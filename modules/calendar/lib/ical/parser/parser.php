<?php


namespace Bitrix\Calendar\ICal\Parser;


use Bitrix\Main\ArgumentException;
use Generator;

class Parser
{
	private $data;
	/**
	 * @var Generator|null
	 */
	private $linesGenerator;
	/**
	 * @var array
	 */
	private $components;

	public static function getInstance($data): Parser
	{
		return new self($data);
	}

	public function __construct($data)
	{
		$this->data = $data;
	}

	public function handleData(): Parser
	{
		$this->linesGenerator = $this->getLine();
		$this->components[] = $this->parse();

		return $this;
	}

	private function getLine()
	{
		$tmp = explode("\n", $this->data);

		for ($i = 0; $i < count($tmp); $i++)
		{
			$line = rtrim($tmp[$i]);

			while (isset($tmp[$i + 1]) && strlen($tmp[$i + 1]) > 0 && ($tmp[$i + 1]{0} == ' ' || $tmp[$i + 1]{0} == "\t" ))
			{
				$line .= rtrim(substr($tmp[++$i],1));
			}

			yield $line;
		}

		return null;
	}

	private function parse()
	{
		$componentName = '';
		$attachProperties = [];
		$attachSubComponents = [];

		while ($str = $this->linesGenerator->current())
		{
			$line = Line::getInstance($str);
			$line->prepareData();

			if ($line->isBegin())
			{
				if ($componentName)
				{
					$attachSubComponents[] = $this->parse();
				}
				else
				{
					$componentName = strtolower($line->getValue());
				}
			}
			elseif ($line->isEnd())
			{
				$component = FactoryComponents::createInstance($componentName);
				$component->createComponent($attachProperties, $attachSubComponents);
				return $component->getComponent();
			}
			else
			{
				if (in_array($line->getName(), ['attendee', 'attach']))
				{
					$attachProperties[$line->getName()][] = [
						'value' => $line->getValue(),
						'parameter' => $line->getParams()
					];
				}
				else
				{
					$attachProperties[$line->getName()] = [
						'value' => $line->getValue(),
						'parameter' => $line->getParams()
					];
				}
			}

			$this->linesGenerator->next();
		}

		return null;
	}

	public function getComponents(): array
	{
		return $this->components;
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