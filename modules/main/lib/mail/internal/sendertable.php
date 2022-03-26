<?php

namespace Bitrix\Main\Mail\Internal;

use Bitrix\Main\Entity;
use Bitrix\Main\Config;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security;
use Bitrix\Main\ORM\Fields;

/**
 * Class SenderTable
 * @package Bitrix\Main\Mail\Internal
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Sender_Query query()
 * @method static EO_Sender_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Sender_Result getById($id)
 * @method static EO_Sender_Result getList(array $parameters = [])
 * @method static EO_Sender_Entity getEntity()
 * @method static \Bitrix\Main\Mail\Internal\Sender createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Mail\Internal\EO_Sender_Collection createCollection()
 * @method static \Bitrix\Main\Mail\Internal\Sender wakeUpObject($row)
 * @method static \Bitrix\Main\Mail\Internal\EO_Sender_Collection wakeUpCollection($rows)
 */
class SenderTable extends Entity\DataManager
{

	public static function getTableName()
	{
		return 'b_main_mail_sender';
	}

	public static function add(array $data)
	{
		$result = parent::add($data);

		\Bitrix\Main\Mail\Sender::clearCustomSmtpCache($data['EMAIL']);

		return $result;
	}


	public static function getObjectClass()
	{
		return Sender::class;
	}

	// @TODO: invalidate smtp cache on update and delete

	public static function getMap()
	{
		return [
			(new Fields\IntegerField("ID"))
				->configurePrimary(true)
				->configureAutocomplete(true)
				->configureTitle(Loc::getMessage("main_mail_sender_id_title")),

			(new Fields\StringField("EMAIL"))
				->configureRequired(true)
				->configureTitle(Loc::getMessage("main_mail_sender_email_title")),

			(new Fields\StringField("NAME"))
				->configureTitle(Loc::getMessage("main_mail_sender_name_title")),

			(new Fields\IntegerField("USER_ID"))
				->configureAutocomplete(true)
				->configureTitle(Loc::getMessage("main_mail_sender_user_id_title")),

			(new Fields\BooleanField("IS_CONFIRMED"))
				->configureStorageValues("0", "1")
				->configureDefaultValue("0")
				->configureTitle(Loc::getMessage("main_mail_sender_is_confirmed_title")),

			(new Fields\BooleanField("IS_PUBLIC"))
				->configureStorageValues("0", "1")
				->configureDefaultValue("0")
				->configureTitle(Loc::getMessage("main_mail_sender_is_public_title")),

			(new Fields\ArrayField("OPTIONS"))
				->configureSerializationPhp()
				->configureRequired(true)
				->configureTitle(Loc::getMessage("main_mail_sender_options_title"))
				->addSaveDataModifier(function($value)
					{
						$value = unserialize($value, ['allowed_classes' => false]);
						if (!empty($value['smtp']['password']))
						{
							$value['smtp']['encrypted'] = false;

							$cryptoOptions = Config\Configuration::getValue('crypto');
							if (!empty($cryptoOptions['crypto_key']))
							{
								try
								{
									$cipher = new Security\Cipher();

									$value['smtp']['password'] = $cipher->encrypt(
										$value['smtp']['password'],
										$cryptoOptions['crypto_key']
									);
									$value['smtp']['encrypted'] = true;
								}
								catch (Security\SecurityException $e)
								{
								}
							}

							$value['smtp']['password'] = base64_encode($value['smtp']['password']);
						}

						return serialize($value);
					})
				->addFetchDataModifier(function($value)
				{
					if (!empty($value['smtp']['password']))
					{
						$value['smtp']['password'] = base64_decode($value['smtp']['password']);

						if (!empty($value['smtp']['encrypted']))
						{
							$cryptoOptions = Config\Configuration::getValue('crypto');
							if (!empty($cryptoOptions['crypto_key']))
							{
								try
								{
									$cipher = new Security\Cipher();

									$value['smtp']['password'] = $cipher->decrypt(
										$value['smtp']['password'],
										$cryptoOptions['crypto_key']
									);
									unset($value['smtp']['encrypted']);
								} catch (Security\SecurityException $e)
								{
								}
							}
						}
					}

					if (!empty($value['smtp']) && is_array($value['smtp']))
					{
						if (empty($value['smtp']['protocol']))
						{
							if (465 == $value['smtp']['port'])
							{
								$value['smtp']['protocol'] = 'smtps';
							} else if (587 == $value['smtp']['port'])
							{
								$value['smtp']['protocol'] = 'smtp';
							}
						}
					}

					return $value;
				})
		];
	}

}
