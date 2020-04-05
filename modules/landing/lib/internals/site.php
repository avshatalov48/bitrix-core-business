<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Domain;
use \Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

class SiteTable extends Entity\DataManager
{
	/**
	 * For save callbacks.
	 */
	const ACTION_TYPE_ADD = 'add';

	/**
	 * For save callbacks.
	 */
	const ACTION_TYPE_UPDATE = 'update';

	/**
	 * Stored fields for save separate.
	 * @var array
	 */
	protected static $additionalFields = array();

	/**
	 * Disable callback.
	 * @var boolean
	 */
	protected static $disableCallback = false;

	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_site';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		$types = \Bitrix\Landing\Site::getTypes();

		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID'
			)),
			'CODE' => new Entity\StringField('CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_CODE'),
				'required' => true
			)),
			'ACTIVE' => new Entity\StringField('ACTIVE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_ACTIVE'),
				'default_value' => 'Y'
			)),
			'DELETED' => new Entity\StringField('DELETED', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_DELETED'),
				'default_value' => 'N'
			)),
			'TITLE' => new Entity\StringField('TITLE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_TITLE'),
				'required' => true
			)),
			'XML_ID' => new Entity\StringField('XML_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_XML_ID')
			)),
			'DESCRIPTION' => new Entity\StringField('DESCRIPTION', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DESCRIPTION')
			)),
			'TYPE' => new Entity\EnumField('TYPE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_TYPE'),
				'values' => array_keys($types),
				'default_value' => array_shift(array_keys($types))
			)),
			'TPL_ID' => new Entity\IntegerField('TPL_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_TPL_ID'),
				'default_value' => 0
			)),
			'DOMAIN_ID' => new Entity\IntegerField('DOMAIN_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DOMAIN_ID'),
				'required' => true
			)),
			'DOMAIN' => new Entity\ReferenceField(
				'DOMAIN',
				'Bitrix\Landing\Internals\DomainTable',
				array('=this.DOMAIN_ID' => 'ref.ID')
			),
			'SMN_SITE_ID' => new Entity\StringField('SMN_SITE_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SMN_SITE_ID')
			)),
			'LANDING_ID_INDEX' => new Entity\IntegerField('LANDING_ID_INDEX', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_ID_INDEX')
			)),
			'LANDING_ID_404' => new Entity\IntegerField('LANDING_ID_404', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_ID_404')
			)),
			'LANDING_ID_503' => new Entity\IntegerField('LANDING_ID_503', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_ID_503')
			)),
			'LANG' => new Entity\IntegerField('LANG', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANG')
			)),
			'CREATED_BY_ID' => new Entity\IntegerField('CREATED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CREATED_BY_ID'),
				'required' => true
			)),
			'CREATED_BY' => new Entity\ReferenceField(
				'CREATED_BY',
				'Bitrix\Main\UserTable',
				array('=this.CREATED_BY_ID' => 'ref.ID')
			),
			'MODIFIED_BY_ID' => new Entity\IntegerField('MODIFIED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_MODIFIED_BY_ID'),
				'required' => true
			)),
			'MODIFIED_BY' => new Entity\ReferenceField(
				'MODIFIED_BY',
				'Bitrix\Main\UserTable',
				array('=this.MODIFIED_BY_ID' => 'ref.ID')
			),
			'DATE_CREATE' => new Entity\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_CREATE'),
				'required' => true
			)),
			'DATE_MODIFY' => new Entity\DatetimeField('DATE_MODIFY', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_MODIFY'),
				'required' => true
			))
		);
	}

	/**
	 * Return site controller class, or pseudo.
	 * @return string
	 */
	protected static function getSiteController()
	{
		return Manager::getExternalSiteController();
	}

	/**
	 * Check CODE unique in site group.
	 * @param string $code Site code.
	 * @param int $currentId Current site id.
	 * @param int $domainId Domain id.
	 * @return boolean
	 */
	protected static function checkUniqueInDomain($code, $currentId, $domainId)
	{
		$res = self::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'!ID' => $currentId,
				'DOMAIN_ID' => $domainId,
				'=CODE' => $code
			)
		));
		return $res->fetch() ? false : true;
	}

	/**
	 * Is bitrix24.site subdomain?
	 * @param string $domainName Domain name.
	 * @return boolean
	 */
	protected static function isB24Domain($domainName)
	{
		return (substr($domainName, -14) == '.bitrix24.site') ||
			   (substr($domainName, -14) == '.bitrix24.shop') ||
			   (substr($domainName, -16) == '.bitrix24shop.by') ||
			   (substr($domainName, -16) == '.bitrix24site.by');
	}

	/**
	 * Customize controller message.
	 * @param SystemException $ex Exception from controller.
	 * @return Entity\EntityError
	 */
	protected static function customizeControllerError(SystemException $ex)
	{
		$code = str_replace(' ', '', $ex->getMessage());
		$code = strtoupper($code);
		$message = Loc::getMessage('LANDING_CONTROLLER_ERROR_' . $code);
		$message = $message ? $message : $ex->getMessage();

		return new Entity\EntityError(
			$message,
			'CONTROLLER_ERROR_' . $code
		);
	}

	/**
	 * Check 'bitrix'-named domain.
	 * @param string $domainName Domain name.
	 * @return boolean
	 */
	public static function checkBitrixUse($domainName)
	{
		$isB24Domain = self::isB24Domain($domainName);
		$disableMask = '/bitrix[^\.]*\.bitrix[^\.]+\.[a-z]+$/';
		if (
			Manager::isB24() &&
			(
				$isB24Domain && preg_match_all($disableMask, $domainName)
				||
				!$isB24Domain && strpos($domainName, 'bitrix') !== false
			)
		)
		{
			return true;
		}
		return false;
	}

	/**
	 * Prepare change to save.
	 * @param Entity\Event $event Event instance.
	 * @param string $actionType Action type: add / update.
	 * @return Entity\EventResult
	 */
	protected static function prepareChange(Entity\Event $event, $actionType)
	{
		$result = new Entity\EventResult();
		$fields = $event->getParameter('fields');
		$primary = $event->getParameter('primary');
		$unsetFields = array();
		$modifyFields = array();
		$siteController = self::getSiteController();

		// if delete, set unpublic always
		if (isset($fields['DELETED']))
		{
			$modifyFields['ACTIVE'] = 'N';
		}

		// additional fields save after
		if (array_key_exists('ADDITIONAL_FIELDS', $fields))
		{
			self::$additionalFields = $fields['ADDITIONAL_FIELDS'];
			$unsetFields[] = 'ADDITIONAL_FIELDS';
		}
		else
		{
			self::$additionalFields = array();
		}

		// check rights for site domain
		if (
			array_key_exists('DOMAIN_ID', $fields) &&
			!Manager::isB24()
		)
		{
			// for check rights call upper level
			$res = Domain::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'ID' => $fields['DOMAIN_ID']
				)
			));
			if (!$res->fetch())
			{
				$result->unsetFields($unsetFields);
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_DOMAIN_NOT_EXIST'),
						'DOMAIN_NOT_FOUND'
					)
				));
				return $result;
			}
		}

		// check active first (limit count)
		if (
			isset($fields['ACTIVE']) &&
			$fields['ACTIVE'] == 'Y'
		)
		{
			if (
				$primary &&
				!array_key_exists('TYPE', $fields)
			)
			{
				$res = self::getList(array(
					 'select' => array(
						'TYPE'
					 ),
					 'filter' => array(
						'ID' => $primary['ID']
					 )
				 ));
				if ($row = $res->fetch())
				{
					$fields['TYPE'] = $row['TYPE'];
				}
			}
			if (!array_key_exists('TYPE', $fields))
			{
				$fields['TYPE'] = null;
			}
			$canPublicSite = Manager::checkFeature(
				Manager::FEATURE_PUBLICATION_SITE,
				$primary
				? array(
					'filter' => array(
						'!ID' => $primary['ID'],
					),
					'type' => $fields['TYPE']
				)
				: array(
					'type' => $fields['TYPE']
				)
			);
			if (!$canPublicSite)
			{
				$result->unsetFields($unsetFields);
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_PUBLIC_SITE_REACHED'),
						'PUBLIC_SITE_REACHED'
					)
				));
				return $result;
			}
		}

		// prepare CODE - base part of URL
		if (array_key_exists('CODE', $fields))
		{
			$fields['CODE'] = trim($fields['CODE']);
			if ($fields['CODE'] == '')
			{
				$fields['CODE'] = '/';
			}
			elseif (substr($fields['CODE'], -1) != '/')
			{
				$fields['CODE'] .= '/';
			}
			if (substr($fields['CODE'], 0, 1) != '/')
			{
				$fields['CODE'] = '/' . $fields['CODE'];
			}
			// get domain id if no exists
			if (!array_key_exists('DOMAIN_ID', $fields) && $primary)
			{
				$site = self::getById($primary['ID'])->fetch();
				$fields['DOMAIN_ID'] = $site['DOMAIN_ID'];
			}
			// check CODE unique in site group
			if (array_key_exists('DOMAIN_ID', $fields))
			{
				$unique = self::checkUniqueInDomain(
					$fields['CODE'],
					$primary ? $primary['ID'] : 0,
					$fields['DOMAIN_ID']
				);
				if (!$unique)
				{
					$result->unsetFields($unsetFields);
					$result->setErrors(array(
						new Entity\EntityError(
							Loc::getMessage('LANDING_TABLE_ERROR_SITE_CODE_IS_NOT_UNIQUE2'),
							'CODE_IS_NOT_UNIQUE'
						)
					));
					return $result;
				}
			}
			// else change CODE
			$modifyFields['CODE'] = $fields['CODE'];
		}

		// create/get domain by name (reg in b24.site if Bitrix24)
		if (
			array_key_exists('DOMAIN_ID', $fields) &&
			(
				preg_replace('/[\d]/', '', trim($fields['DOMAIN_ID'])) != '' ||
				Manager::isB24()
			)
		)
		{
			$domainId = 0;
			$domainName = strtolower(trim($fields['DOMAIN_ID']));
			$domainNameOld = '';

			// fix for full name
			if ($domainName != '')
			{
				$puny = new \CBXPunycode;
				$domainName = $puny->encode($domainName);
				// check correct name
				if (!preg_match('/^[a-z0-9\-\.]+\.[a-z0-9\-]{2,20}$/i', $domainName))
				{
					$result->unsetFields($unsetFields);
					$result->setErrors(array(
						new Entity\EntityError(
							Loc::getMessage('LANDING_TABLE_ERROR_DOMAIN_IS_INCORRECT2'),
							'DOMAIN_IS_INCORRECT'
						)
					));
					return $result;
				}
				// check allow custom domain
				if (
					!self::isB24Domain($domainName) &&
					!Manager::checkFeature(Manager::FEATURE_CUSTOM_DOMAIN)
				)
				{
					$result->unsetFields($unsetFields);
					$result->setErrors(array(
						new Entity\EntityError(
							Loc::getMessage('LANDING_TABLE_ERROR_CUSTOM_DOMAIN_ISNT_ALLOWED'),
							'CUSTOM_DOMAIN_ISNT_ALLOWED'
						)
					));
					return $result;
				}
			}

			// if add - unset domain_id, else - get current domain of site
			if ($actionType == self::ACTION_TYPE_ADD)
			{
				$modifyFields['DOMAIN_ID'] = 0;
			}
			else
			{
				if ($primary)
				{
					$res = self::getList(array(
						'select' => array(
							'DOMAIN_ID',
							'DOMAIN_NAME' => 'DOMAIN.DOMAIN'
						),
						'filter' => array(
							'ID' => $primary['ID']
						)
					));
					if ($row = $res->fetch())
					{
						$domainNameOld = strtolower($row['DOMAIN_NAME']);
						$domainId = $row['DOMAIN_ID'];
					}
				}
				$unsetFields[] = 'DOMAIN_ID';
			}

			// check CODE unique in site group
			if ($domainId && array_key_exists('CODE', $fields))
			{
				$unique = self::checkUniqueInDomain(
					$fields['CODE'],
					$primary ? $primary['ID'] : 0,
					$domainId
				);
				if (!$unique)
				{
					$result->unsetFields($unsetFields);
					$result->setErrors(array(
						new Entity\EntityError(
							Loc::getMessage('LANDING_TABLE_ERROR_SITE_CODE_IS_NOT_UNIQUE2'),
							'CODE_IS_NOT_UNIQUE'
						)
					));
					return $result;
				}
			}

			// if domain name now changed
			if (
				$domainName != $domainNameOld ||
				$actionType == self::ACTION_TYPE_ADD
			)
			{
				$domainExist = false;

				// check domain exist
				if ($domainName != '')
				{
					$resDomain = Domain::getList(array(
						'select' => array(
							'ID'
						),
						'filter' => array(
							'=DOMAIN' => $domainName
						)
					));
					if ($rowDomain = $resDomain->fetch())
					{
						$domainExist = true;
						$resSite = Site::getList(array(
							'select' => array(
								'ID'
							),
							'filter' => array(
								'DOMAIN_ID' => $rowDomain['ID'],
								'=DELETED' => 'Y'
							)
		  				));
						if ($resSite->fetch())
						{
							$result->setErrors(
								array(
									new Entity\EntityError(
										Loc::getMessage('LANDING_TABLE_ERROR_DOMAIN_EXIST_TRASH'),
										'DOMAIN_EXIST_TRASH'
									)
								)
							);
							return $result;
						}
					}
					elseif (Manager::isB24())
					{
						try
						{
							$domainExist = $siteController::isDomainExists($domainName);
						}
						catch (SystemException $ex)
						{
							$result->unsetFields($unsetFields);
							$result->setErrors(array(
								self::customizeControllerError($ex)
							));
							return $result;
						}
					}
				}
				if ($domainExist)
				{
					$result->unsetFields($unsetFields);
					if (self::checkBitrixUse($domainName))
					{
						$result->setErrors(
							array(
								new Entity\EntityError(
									Loc::getMessage('LANDING_TABLE_ERROR_DOMAIN_BITRIX_DISABLE'),
									'DOMAIN_DISABLE'
								)
							)
						);
					}
					else
					{
						$result->setErrors(
							array(
								new Entity\EntityError(
									Loc::getMessage('LANDING_TABLE_ERROR_DOMAIN_EXIST'),
									'DOMAIN_EXIST'
								)
							)
						);
					}

					return $result;
				}

				// check available external service
				try
				{
					$siteController::isDomainExists('repo.bitrix24.site');
				}
				catch (SystemException $ex)
				{
					$result->unsetFields($unsetFields);
					$result->setErrors(array(
						self::customizeControllerError($ex)
					));
					return $result;
				}

				// handler on add / update
				$eventManager = \Bitrix\Main\EventManager::getInstance();
				$eventManager->addEventHandler(
					'landing',
					$actionType == self::ACTION_TYPE_ADD
					? '\\' . __NAMESPACE__ . '\\Site::onAfterAdd'
					: '\\' . __NAMESPACE__ . '\\Site::onAfterUpdate',
					function(Entity\Event $event) use ($domainId, $domainName, $domainNameOld, $result, $unsetFields, $siteController)
					{
						$primary = $event->getParameter('primary');
						$fields = $event->getParameter('fields');

						if ($primary)
						{
							// create domain
							if (!$domainId)
							{
								// action in b24
								if (Manager::isB24())
								{
									$publicUrl = Manager::getPublicationPath(
										$primary['ID']
									);
									try
									{
										$row = self::getList(array(
											'select' => array(
												'TYPE'
											),
											'filter' => array(
												'ID' => $primary['ID']
											)
									 	))->fetch();
										if ($row['TYPE'] == 'STORE')// fix for controller
										{
											$row['TYPE'] = 'shop';
										}
										if ($domainName)
										{
											$siteController::addDomain(
												$domainName,
												$publicUrl,
												'N',
												$row['TYPE']
											);
										}
										else
										{
											$domainName = $siteController::addRandomDomain(
												$publicUrl,
												$row['TYPE']
											);
										}
									}
									catch (SystemException $ex)
									{
										$result->unsetFields($unsetFields);
										$result->setErrors(array(
											self::customizeControllerError($ex)
										));
										return $result;
									}
								}
								// add new domain
								if ($domainName)
								{
									$resDomain = Domain::add(array(
										'ACTIVE' => 'Y',
										'DOMAIN' => $domainName
									));
									$domainId = $resDomain->getId();
									if ($domainId)
									{
										SiteTable::$disableCallback = true;
										SiteTable::update($primary['ID'], array(
											'DOMAIN_ID' => $domainId
										));
									}
								}
							}
							// update domain
							else
							{
								$res = Domain::update($domainId, array(
									'DOMAIN' => $domainName
								));
								if ($res->isSuccess())
								{
									if (Manager::isB24())
									{
										try
										{
											$publicUrl = Manager::getPublicationPath(
												$primary['ID']
											);
											$siteController::updateDomain(
												$domainNameOld,
												$domainName,
												$publicUrl
											);
										}
										catch (SystemException $ex)
										{
											$result->unsetFields($unsetFields);
											$result->setErrors(array(
												self::customizeControllerError($ex)
											));
											return $result;
										}
									}
								}
							}
						}
					}
				);
			}
		}

		$result->unsetFields($unsetFields);
		$result->modifyFields($modifyFields);

		return $result;
	}

	/**
	 * Before add handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeAdd(Entity\Event $event)
	{
		$result = new Entity\EventResult();

		if (self::$disableCallback)
		{
			return $result;
		}

		$fields = $event->getParameter('fields');

		// check site limit
		if (
			!Manager::checkFeature(
				Manager::FEATURE_CREATE_SITE,
				array(
					'type' => $fields['TYPE']
				)
			)
		)
		{
			$result->unsetFields(array('ADDITIONAL_FIELDS'));
			$result->setErrors(array(
				new Entity\EntityError(
					Loc::getMessage('LANDING_TABLE_ERROR_SITE_LIMIT_REACHED'),
					'SITE_LIMIT_REACHED'
				)
			));
			return $result;
		}

		return self::prepareChange($event, self::ACTION_TYPE_ADD);
	}

	/**
	 * Before update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeUpdate(Entity\Event $event)
	{
		if (self::$disableCallback)
		{
			return new Entity\EventResult();
		}

		return self::prepareChange($event, self::ACTION_TYPE_UPDATE);
	}

	/**
	 * Save additional fields after add / update.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	protected static function saveAdditionalFields(Entity\Event $event)
	{
		$result = new Entity\EventResult();

		if (!empty(self::$additionalFields))
		{
			$primary = $event->getParameter('primary');
			Site::saveAdditionalFields(
				$primary['ID'],
				self::$additionalFields
			);
		}

		return $result;
	}

	/**
	 * Get entity rows.
	 * @param array $params Params array.
	 * @return \Bitrix\Main\ORM\Query\Result
	 */
	public static function getList(array $params = array())
	{
		if (
			!isset($params['filter']) ||
			!is_array($params['filter'])
		)
		{
			$params['filter'] = array();
		}
		if (
			!isset($params['filter']['DELETED']) &&
			!isset($params['filter']['=DELETED'])
		)
		{
			$params['filter']['=DELETED'] = 'N';
		}

		return parent::getList($params);
	}

	/**
	 * After add handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnAfterAdd(Entity\Event $event)
	{
		if (self::$disableCallback)
		{
			return true;
		}

		return self::saveAdditionalFields($event);
	}

	/**
	 * After update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnAfterUpdate(Entity\Event $event)
	{
		if (self::$disableCallback)
		{
			return true;
		}

		// for B24 we must update domain
		if (Manager::isB24())
		{
			$siteController = self::getSiteController();
			$primary = $event->getParameter('primary');
			$res = self::getList(array(
				'select' => array(
					'ACTIVE', 'DELETED',
					'DOMAIN_NAME' => 'DOMAIN.DOMAIN'
				),
				'filter' => array(
					'ID' => $primary['ID'],
					'=DELETED' => ['Y', 'N']
				)
			));
			if ($row = $res->fetch())
			{
				try
				{
					// now external domains always are active
					$siteController::activateDomain(
						$row['DOMAIN_NAME']
					);
				}
				catch (\Bitrix\Main\SystemException $ex)
				{
					//
				}
			}
		}

		return self::saveAdditionalFields($event);
	}

	/**
	 * Before delete handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeDelete(Entity\Event $event)
	{
		if (self::$disableCallback)
		{
			return true;
		}

		$result = new Entity\EventResult();
		$primary = $event->getParameter('primary');
		$siteController = self::getSiteController();

		if ($primary)
		{
			// check if site is not empty
			$res = LandingTable::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'SITE_ID' => $primary['ID']
				)
			));
			if ($res->fetch())
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_SITE_IS_NOT_EMPTY'),
						'SITE_IS_NOT_EMPTY'
					)
				));
				return $result;
			}

			// delete in b24.site
			if (Manager::isB24())
			{
				$res = self::getList(array(
					'select' => array(
						'DOMAIN_ID',
						'DOMAIN_NAME' => 'DOMAIN.DOMAIN'
					),
					'filter' => array(
						'ID' => $primary['ID'],
						'DELETED' => ['Y', 'N']
					)
				));
				if ($row = $res->fetch())
				{
					$domainId = $row['DOMAIN_ID'];
					$domainName = $row['DOMAIN_NAME'];
					$eventManager = \Bitrix\Main\EventManager::getInstance();
					$eventManager->addEventHandler(
						'landing',
						'\\' . __NAMESPACE__ . '\\Site::onAfterDelete',
						function(Entity\Event $event) use ($domainId, $domainName, $result, $siteController)
						{
							$res = self::getList(array(
								'select' => array(
									'ID'
								),
								'filter' => array(
									'DOMAIN_ID' => $domainId,
									'DELETED' => ['Y', 'N']
								)
							));
							if (!$res->fetch())
							{
								DomainTable::delete($domainId);
								try
								{
									$siteController::deleteDomain($domainName);
								}
								catch (SystemException $ex)
								{
									$result->setErrors(array(
							   			self::customizeControllerError($ex)
							   		));
									return $result;
								}
							}
						}
					);
				}
			}
		}

		return $result;
	}

	/**
	 * After delete handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		if (self::$disableCallback)
		{
			return true;
		}

		$result = new Entity\EventResult();
		$primary = $event->getParameter('primary');

		// delete all inner landings
		if ($primary)
		{
			$res = LandingTable::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'SITE_ID' => $primary['ID']
				)
			));
			while ($row = $res->fetch())
			{
				\Bitrix\Landing\Landing::delete($row['ID'], true);
			}

			\Bitrix\Landing\Syspage::deleteForSite($primary['ID']);
			\Bitrix\Landing\File::deleteFromSite($primary['ID']);
			\Bitrix\Landing\Hook::deleteForSite($primary['ID']);
			\Bitrix\Landing\TemplateRef::setForSite($primary['ID'], array());
			\Bitrix\Landing\UrlRewrite::removeForSite($primary['ID']);
		}

		return $result;
	}
}