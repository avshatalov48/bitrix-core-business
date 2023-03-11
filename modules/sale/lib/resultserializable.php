<?
namespace Bitrix\Sale;

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Text\Encoding;
use Serializable;

/**
 * Class ResultSerializable
 * For easy transfer via rest & store in cache etc.
 * \Bitrix\Main\Result::$data must contain only serializable data.
 * @package Bitrix\Sale
 */
class ResultSerializable
	extends Result
	implements Serializable
{
	public function serialize(): ?string
	{
		return serialize($this);
	}

	public function unserialize($data): void
	{
		$vars = unserialize($data, ['allowed_classes' => [static::class]]);
		$this->__unserialize($vars);
	}

	public function __serialize(): array
	{
		$result = get_object_vars($this);

		foreach($result as $name => $value)
			if(empty($value))
				unset($result[$name]);

		$result['errors'] = array();

		if($this->errors)
		{
			/** @var Error $error */
			foreach($this->errors->toArray() as $error)
			{
				$result['errors'][] = array(
					'code' => $error->getCode(),
					'message' => $error->getMessage()
				);
			}
		}

		$result['CHARSET'] = ToUpper(SITE_CHARSET);

		return $result;
	}

	public function __unserialize(array $vars): void
	{
		$isNeedRecode = !empty($vars['CHARSET']) && $vars['CHARSET'] != ToUpper(SITE_CHARSET);
		$this->errors = new ErrorCollection();

		foreach($vars as $name => $value)
		{
			if(!property_exists($this, $name))
				continue;

			if($name == 'errors')
			{
				foreach($value as $error)
				{
					if($isNeedRecode)
						$error['message'] = Encoding::convertEncoding($error['message'], $vars['CHARSET'], SITE_CHARSET);

					$this->addError(new Error($error['message'], $error['code']));
				}
			}
			else
			{
				if($isNeedRecode)
					$value = Encoding::convertEncoding($value, $vars['CHARSET'], SITE_CHARSET);

				$this->$name = $value;
			}
		}
	}
}
