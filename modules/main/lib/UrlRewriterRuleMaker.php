<?php

namespace Bitrix\Main;

/**
 * Class UrlRewriterRuleMaker
 *
 * Helper used for sef rules creation.
 *
 * @package Bitrix\Main
 */
class UrlRewriterRuleMaker
{
	protected $condition = "";
	protected $variables = [];
	protected $rule = "";

	/**
	 * @param string $sefRule SEF_RULE component parameter value.
	 *
	 * @return void
	 */
	public function process($sefRule)
	{
		$this->rule = "";
		$this->variables = [];
		$this->condition = "#^" . preg_replace_callback("/(#[a-zA-Z0-9_]+#)/", [$this, "_callback"], $sefRule) . "\\??(.*)#";
		$i = 0;
		foreach ($this->variables as $variableName)
		{
			$i++;
			if ($this->rule)
			{
				$this->rule .= "&";
			}
			$this->rule .= $variableName . "=\$" . $i;
		}
		$i++;
		$this->rule .= "&\$" . $i;
	}

	/**
	 * Returns CONDITION field of the sef rule based on what was processed.
	 *
	 * @return string
	 */
	public function getCondition()
	{
		return $this->condition;
	}

	/**
	 * Returns RULE field of the sef rule based on what was processed.
	 *
	 * @return string
	 */
	public function getRule()
	{
		return $this->rule;
	}

	/**
	 * Internal method used for preg_replace processing.
	 *
	 * @param array $match match array.
	 *
	 * @return string
	 */
	protected function _callback(array $match)
	{
		$this->variables[] = trim($match[0], "#");
		if (str_ends_with($match[0], "_PATH#"))
		{
			return "(.+?)";
		}
		return "([^/]+?)";
	}
}
