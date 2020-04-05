<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Site;
use \Bitrix\Landing\Domain as DomainCore;
use \Bitrix\Landing\PublicActionResult;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\SystemException;

class Domain
{
	/**
	 * Get available domains.
	 * @param array $params Params ORM array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getList(array $params = array())
	{
		$result = new PublicActionResult();

		$data = array();
		$res = DomainCore::getList($params);
		while ($row = $res->fetch())
		{
			if (isset($row['DATE_CREATE']))
			{
				$row['DATE_CREATE'] = (string) $row['DATE_CREATE'];
			}
			if (isset($row['DATE_MODIFY']))
			{
				$row['DATE_MODIFY'] = (string) $row['DATE_MODIFY'];
			}
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
	public static function add(array $fields)
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
	public static function update($id, array $fields)
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
	 * @return \Bitrix\Landing\PublicActionResult
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

	/**
	 * Checks if domain is available and puny it.
	 * @param string $domain Domain name.
	 * @param array $filter Additional filter for exclude in domain search.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function check($domain, array $filter = [])
	{
		$puny = new \CBXPunycode;
		$result = new PublicActionResult();
		$domain = trim($domain);
		$return = [
			'available' => true,
			'domain' => $puny->encode($domain),
			'deleted' => false
		];

		// additional filter
		if (!is_array($filter))
		{
			$filter = [];
		}
		$filter['=DOMAIN'] = $return['domain'];

		// check domain
		$res = DomainCore::getList([
			'select' => [
				'ID'
			],
			'filter' => $filter
		]);
		$return['available'] = !($domainRow = $res->fetch());

		// check sites in trash
		if (!$return['available'])
		{
			$resSite = Site::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'DOMAIN_ID' => $domainRow['ID'],
					'=DELETED' => 'Y'
				)
			));
			if ($resSite->fetch())
			{
				$return['available'] = false;
				$return['deleted'] = true;
			}
		}

		// external available check
		if (
			$return['available'] &&
			Manager::isB24()
		)
		{
			try
			{
				$siteController = Manager::getExternalSiteController();
				if ($siteController)
				{
					$return['available'] = !(boolean)$siteController::isDomainExists(
						$return['domain']
					);
				}
			}
			catch (SystemException $ex)
			{
			}
		}

		// set result and return
		$result->setResult($return);
		return $result;
	}
}