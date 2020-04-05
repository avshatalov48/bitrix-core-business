<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Main\UserConsent;

use Bitrix\Main\Error;
use Bitrix\Rest\RestException;

/**
 * Class Rest
 * @package Bitrix\Main\UserConsent
 */
class Rest
{
	/**
	 * Get agreement list.
	 *
	 * @param array $query Query parameters.
	 * @param int $nav Navigation.
	 * @param \CRestServer $server Rest server.
	 * @return array
	 */
	public static function getAgreementList($query, $nav = 0, \CRestServer $server)
	{
		return Internals\AgreementTable::getList(array(
			'select' => array('ID', 'NAME', 'ACTIVE', 'LANGUAGE_ID'),
			'order' => array('ID' => 'DESC')
		))->fetchAll();
	}

	/**
	 * Get agreement text.
	 *
	 * @param array $query Query parameters.
	 * @param int $nav Navigation.
	 * @param \CRestServer $server Rest server.
	 * @return array
	 * @throws RestException
	 */
	public static function getAgreementText($query, $nav = 0, \CRestServer $server)
	{
		$query = array_change_key_case($query, CASE_LOWER);
		$id = empty($query['id']) ? null : $query['id'];
		$replace = empty($query['replace']) ? [] : $query['replace'];
		$replace = is_array($replace) ? $replace : [];

		$agreement = self::getAgreementById($id);
		$agreement->setReplace($replace);

		return [
			'LABEL' => $agreement->getLabelText(),
			'TEXT' => $agreement->getText(),
		];
	}

	/**
	 * Add consent.
	 *
	 * @param array $query Query parameters.
	 * @param int $nav Navigation.
	 * @param \CRestServer $server Rest server.
	 * @return int
	 * @throws RestException
	 */
	public static function addConsent($query, $nav = 0, \CRestServer $server)
	{
		$query = array_change_key_case($query, CASE_UPPER);
		$agreementId = isset($query['AGREEMENT_ID']) ? $query['AGREEMENT_ID'] : null;
		self::getAgreementById($agreementId);

		$result = Internals\ConsentTable::add([
			'AGREEMENT_ID' => $agreementId,
			'USER_ID' => isset($query['USER_ID']) ? $query['USER_ID'] : null,
			'IP' => isset($query['IP']) ? $query['IP'] : null,
			'URL' => isset($query['URL']) ? $query['URL'] : null,
			'ORIGIN_ID' => isset($query['ORIGIN_ID']) ? $query['ORIGIN_ID'] : null,
			'ORIGINATOR_ID' => isset($query['ORIGINATOR_ID']) ? $query['ORIGINATOR_ID'] : null,
		]);

		if (!$result->isSuccess())
		{
			self::printErrors($result->getErrors());
		}

		return $result->getId();
	}

	/**
	 * Get agreement instance by ID.
	 *
	 * @param int|null $id ID.
	 * @return Agreement
	 * @throws RestException
	 */
	protected static function getAgreementById($id)
	{
		$agreement = new Agreement($id);
		if ($agreement->hasErrors())
		{
			self::printErrors($agreement->getErrors());
		}

		return $agreement;
	}

	/**
	 * Print rest errors.
	 *
	 * @param Error[] $errors Errors.
	 * @throws RestException
	 */
	protected static function printErrors(array $errors)
	{
		foreach ($errors as $error)
		{
			throw new RestException(
				$error->getMessage(),
				RestException::ERROR_ARGUMENT,
				\CRestServer::STATUS_WRONG_REQUEST
			);
		}
	}
}