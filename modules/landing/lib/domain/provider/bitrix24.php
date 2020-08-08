<?php
namespace Bitrix\Landing\Domain\Provider;

use Bitrix\Landing\Manager;
use Bitrix\Landing\Domain\Provider;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Loader;

class Bitrix24 extends Provider
{
	/**
	 * Returns unique provider code.
	 * @return string
	 */
	public function getCode(): string
	{
		return 'bitrix24';
	}
	
	/**
	 * Returns true, if provider is available.
	 * @return bool
	 */
	public function enable(): bool
	{
		return Manager::getZone() == 'ru' && //tmp
				ModuleManager::isModuleInstalled('bitrix24');
	}

	/**
	 * Returns true, if domain is available for registration.
	 * @param string $domainName Domain name
	 * @return bool
	 */
	public function isEnableForRegistration(string $domainName): bool
	{
		$res = \CControllerClient::ExecuteEvent(
			'OnMailControllerWhoisDomain',
			[
				'DOMAIN' => trim($domainName)
			]
		);
		return $res && !(isset($res['result']) && $res['result'] === true);
	}

	/**
	 * Returns keywords by domain name.
	 * @param string $domainName
	 * @return array
	 */
	protected static function getKeywordsByDomain(string $domainName): array
	{
		$domainName = str_replace('.', '-', $domainName);
		$domainParts = explode('-', $domainName);
		if (count($domainParts) > 2)
		{
			array_pop($domainParts);
		}

		return $domainParts;
	}

	/**
	 * Returns suggested domains by basic domain name.
	 * @param string $domainName Domain name.
	 * @param array $tld Domain tld.
	 * @return array
	 */
	public function getSuggestedDomains(string $domainName, array $tld): array
	{
		$domains = [];
		$words = $this->getKeywordsByDomain($domainName);

		if ($words)
		{
			$res = \CControllerClient::ExecuteEvent(
				'OnMailControllerSuggestDomain',
				[
					'WORD1' => $words[0],
					'WORD2' => isset($words[1]) ? $words[1] : '',
					'TLDS' => array_map('mb_strtolower', $tld)
				]
			);
			if ($res && isset($res['result']) && is_array($res['result']))
			{
				$domains = $res['result'];
			}
		}

		return $domains;
	}

	/**
	 * Registration new domain. Returns true on success create.
	 * @param string $domainName Domain name.
	 * @param array $params Additional params.
	 * @return bool
	 */
	public function registrationDomain(string $domainName, array $params = []): bool
	{
		$dns = \Bitrix\Landing\Domain\Register::getDNSRecords();
		$domainName = trim($domainName);

		$dnsParams = [];
		$dnsParams[] = [
			'type' => 'cname',
			'name' => 'www',
			'value' => isset($params['CNAME'])
					? $params['CNAME'] : $dns['CNAME']
		];
		$dnsParams[] = [
			'type' => 'a',
			'value' => $dns['INA']
		];

		$res = \CControllerClient::ExecuteEvent(
			'OnMailControllerRegDomain',
			[
				'DOMAIN' => $domainName,
				'IP' => $_SERVER['REMOTE_ADDR'],
				'DNS' => $dnsParams
			]
		);

		if (isset($res['result']) && $res['result'] === true)
		{
			return true;
		}
		// we try detect that this domain is property of current portal
		else
		{
			$res = \CControllerClient::ExecuteEvent(
				'OnMailControllerGetMemberDomains'
			);
			if (isset($res['result']) && is_array($res['result']))
			{
				$puny = new \CBXPunycode;
				$domainNameEncoded = $puny->encode($domainName);
				if (
					in_array($domainName, $res['result']) ||
					in_array($domainNameEncoded, $res['result'])
				)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Returns all current portal domains.
	 * @return array
	 */
	public function getPortalDomains(): array
	{
		$res = \CControllerClient::ExecuteEvent(
			'OnMailControllerGetMemberDomains'
		);
		if (isset($res['result']) && is_array($res['result']))
		{
			return $res['result'];
		}
		return [];
	}
}