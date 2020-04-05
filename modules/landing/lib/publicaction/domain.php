<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Domain as DomainCore;
use \Bitrix\Landing\PublicActionResult;

class Domain
{
	/**
	 * Get available domains.
	 * @param array $params Params ORM array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getList($params = array())
	{
		$result = new PublicActionResult();

		$data = array();
		$res = DomainCore::getList($params);
		while ($row = $res->fetch())
		{
			$data[] = $row;
		}
		$result->setResult($data);

		return $result;
	}

	/**
	 * Create new domain.
	 * @param array $fields Domain data.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function add($fields)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$res = DomainCore::add($fields);

		if ($res->isSuccess())
		{
			$result->setResult($res->getId());
		}
		else
		{
			$error->addFromResult($res);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Update domain.
	 * @param int $id Domain id.
	 * @param array $fields Domain new data.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function update($id, $fields)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$res = DomainCore::update($id, $fields);

		if ($res->isSuccess())
		{
			$result->setResult(true);
		}
		else
		{
			$error->addFromResult($res);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Delete domain.
	 * @param int $id Domain id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function delete($id)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$res = DomainCore::delete($id);

		if ($res->isSuccess())
		{
			$result->setResult(true);
		}
		else
		{
			$error->addFromResult($res);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Punycode the domain name.
	 * @param string $domain Domain for code.
	 * @return string
	 */
	public static function punycode($domain)
	{
		$puny = new \CBXPunycode;

		$result = new PublicActionResult();
		$result->setResult(
			$puny->encode($domain)
		);

		return $result;
	}
}