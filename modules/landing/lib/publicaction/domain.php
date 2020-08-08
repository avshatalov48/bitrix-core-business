<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Site;
use \Bitrix\Landing\Domain as DomainCore;
use \Bitrix\Landing\PublicActionResult;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Domain\Register;
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
		$params = $result->sanitizeKeys($params);

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
		$result = new PublicActionResult();

		if (!is_string($domain))
		{
			return $result;
		}

		$puny = new \CBXPunycode;
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
					'=DELETED' => 'Y',
					'CHECK_PERMISSIONS' => 'N'
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
			$return['domain'] &&
			Manager::isB24()
		)
		{
			try
			{
				$siteController = Manager::getExternalSiteController();
				if ($siteController)
				{
				 	$checkResult = $siteController::isDomainExists(
						$return['domain']
					);
					$return['available'] = $checkResult < 2;
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

	/**
	 * Returns info about domain registration.
	 * @param string $domainName Domain name.
	 * @param array $tld Domain tld.
	 * @return PublicActionResult
	 */
	public static function whois(string $domainName, array $tld): PublicActionResult
	{
		$result = new PublicActionResult();
		$domainName = trim($domainName);
		$return = [
			'enable' => false,
			'suggest' => []
		];

		// registrator instance
		$regInstance = Register::getInstance();
		if ($regInstance && !$regInstance->enable())
		{
			$result->setResult($return);
			return $result;
		}

		// internal enable first
		$res = DomainCore::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=DOMAIN' => $domainName
			]
		]);
		if (!$res->fetch())
		{
			$return['enable'] = $regInstance->isEnableForRegistration($domainName);
		}

		// get suggested domains
		if (!$return['enable'])
		{
			$return['suggest'] = $regInstance->getSuggestedDomains($domainName, $tld);
		}

		$result->setResult($return);

		return $result;
	}
}
