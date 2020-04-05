<?
namespace Bitrix\Main;

/**
 * @implements Bitrix\Main\Errorable
 */
trait ErrorableImplementation
{
	/** @var ErrorCollection */
	protected $errorCollection;

	/**
	 * Return true if collection has errors.
	 *
	 * @return boolean
	 */
	public function hasErrors()
	{
		if ($this->errorCollection instanceof ErrorCollection)
		{
			return !$this->errorCollection->isEmpty();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Getting array of errors.
	 *
	 * @return Error[]
	 */
	public function getErrors()
	{
		if ($this->errorCollection instanceof ErrorCollection)
		{
			return $this->errorCollection->toArray();
		}
		else
		{
			return [];
		}
	}

	/**
	 * Returns an error with the necessary code.
	 *
	 * @param string|int $code The code of the error.
	 *
	 * @return Error|null
	 */
	public function getErrorByCode($code)
	{
		if ($this->errorCollection instanceof ErrorCollection)
		{
			return $this->errorCollection->getErrorByCode($code);
		}
		else
		{
			return null;
		}
	}
}