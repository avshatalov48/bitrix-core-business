<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2020 Bitrix
 */

namespace Bitrix\Main\Routing;

use Bitrix\Main\Type\ParameterDictionary;

/**
 * @package    bitrix
 * @subpackage main
 */
class Route
{
	/** @var string Defined by user */
	protected $uri;

	/** @var string uri with prefix */
	protected $fullUri;

	/** @var string Defined by compile() */
	protected $matchUri;

	/** @var array [name => pattern] Defined by compile() */
	protected $parameters;

	/** @var ParameterDictionary Set by router->match() */
	protected $parametersValues;

	/** @var callable */
	protected $controller;

	/** @var Options */
	protected $options;

	public function __construct($uri, $controller)
	{
		$this->uri = $uri;
		$this->controller = $controller;
	}

	/**
	 * @return Options
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @param Options $options
	 */
	public function setOptions($options)
	{
		$this->options = $options;
	}

	/**
	 * @return callable
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * @return ParameterDictionary
	 */
	public function getParametersValues()
	{
		if ($this->parametersValues === null)
		{
			$this->parametersValues = new ParameterDictionary;
		}

		return $this->parametersValues;
	}

	public function compile()
	{
		if ($this->matchUri !== null)
		{
			return;
		}

		$this->matchUri = "#^{$this->getUri()}$#";
		$this->parameters = [];

		// there are parameters, collect them
		preg_match_all('/{([a-z0-9_]+)}/i', $this->getUri(), $matches);
		$parameterNames = $matches[1];

		foreach ($parameterNames as $parameterName)
		{
			$pattern = null;

			// check options for custom pattern
			if ($this->options)
			{
				if ($this->options->hasWhere($parameterName))
				{
					// custom pattern
					$pattern = $this->options->getWhere($parameterName);
				}
				elseif ($this->options->hasDefault($parameterName))
				{
					// can be empty
					$pattern = '[^/]*';
				}
			}

			if ($pattern === null)
			{
				// general case
				$pattern = '[^/]+';
			}

			$this->parameters[$parameterName] = $pattern;

			// put pattern in uri
			$this->matchUri = str_replace(
				"{{$parameterName}}",
				"(?<{$parameterName}>{$pattern})",
				$this->matchUri
			);
		}
	}

	public function compileFromCache($cacheData)
	{
		$this->matchUri = $cacheData['matchUri'];
		$this->parameters = $cacheData['parameters'];
	}

	public function getCompileCache()
	{
		$this->compile();

		return [
			'matchUri' => $this->matchUri,
			'parameters' => $this->parameters
		];
	}

	public function match($uriPath)
	{
		if (strpos($this->getUri(), '{') !== false)
		{
			// compile regexp
			$this->compile();

			// match
			$result = preg_match($this->matchUri, $uriPath, $matches);

			if ($result)
			{
				// set parameters to the request
				$requestParameters = [];
				$parametersList = array_keys($this->parameters);

				foreach ($parametersList as $parameter)
				{
					if ($matches[$parameter] === '' && $this->options && $this->options->hasDefault($parameter))
					{
						// set default value if optional parameter is empty
						$requestParameters[$parameter] = $this->options->getDefault($parameter);
					}
					else
					{
						$requestParameters[$parameter] = $matches[$parameter];
					}
				}

				// set default values if parameter with the same name wasn't set in request
				// e.g. "RULE" => "download=1&objectId=\$1"
				if (!empty($defaultValues = $this->options->getDefault()))
				{
					foreach ($defaultValues as $parameter => $defaultValue)
					{
						if (!in_array($parameter, $parametersList))
						{
							$requestParameters[$parameter] = $defaultValue;
						}
					}
				}

				return $requestParameters;
			}
		}
		else
		{
			if ($uriPath === $this->getUri())
			{
				$requestParameters = [];

				// set default values if parameter with the same name wasn't set in request
				// e.g. "RULE" => "download=1&objectId=\$1"
				if (!empty($defaultValues = $this->options->getDefault()))
				{
					foreach ($defaultValues as $parameter => $defaultValue)
					{
						$requestParameters[$parameter] = $defaultValue;
					}
				}

				return $requestParameters ?: true;
			}
		}

		return false;
	}

	function getUri()
	{
		if ($this->fullUri === null)
		{
			$this->fullUri = $this->uri;

			// concat with option prefix and cache
			if ($this->options && $this->options->hasPrefix())
			{
				$this->fullUri = $this->options->getFullPrefix().'/'.$this->uri;
			}
		}

		return $this->fullUri;
	}
}