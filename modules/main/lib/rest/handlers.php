<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main\Rest;

use Bitrix\Main\UserConsent;

class Handlers
{
	const SCOPE_USER = 'user';
	const SCOPE_USER_CONSENT = 'userconsent';
	const SCOPE_RATING = 'rating';
	const SCOPE_SMILE = 'smile';
	const SCOPE_USER_FIELD_CONFIG = 'userfieldconfig';

	public static function onRestServiceBuildDescription()
	{
		return array(
			static::SCOPE_USER => array(
				'user.history.list' => array(User::class, 'getHistoryList'),
				'user.history.fields.list' => array(User::class, 'getHistoryFieldsList'),
			),
			static::SCOPE_USER_CONSENT => array(
				'userconsent.consent.add' => array(UserConsent\Rest::class, 'addConsent'),
				'userconsent.agreement.list' => array(UserConsent\Rest::class, 'getAgreementList'),
				'userconsent.agreement.text' => array(UserConsent\Rest::class, 'getAgreementText'),
			),
			static::SCOPE_RATING => array(
				'like.list' => array(Rating::class, 'getLikeList'),
				'like.reactions' => array(Rating::class, 'getLikeReactions'),
			),
			static::SCOPE_SMILE => array(
				'smile.get' => array(Smile::class, 'getList'),
			),
			static::SCOPE_USER_FIELD_CONFIG => array(
				\CRestUtil::EVENTS => UserField::getHandlers(),
			)
		);
	}
}
