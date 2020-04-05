<?php

namespace Bitrix\Main\Mail\Internal;

use Bitrix\Main\Entity;
use Bitrix\Main\Config;
use Bitrix\Main\Security;

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

	// @TODO: invalidate smtp cache on update and delete

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type'    => 'integer',
				'primary'      => true,
				'autocomplete' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'EMAIL' => array(
				'data_type' => 'string',
				'required'  => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
			'IS_CONFIRMED' => array(
				'data_type' => 'boolean',
			),
			'IS_PUBLIC' => array(
				'data_type' => 'boolean',
			),
			'OPTIONS' => array(
				'data_type'  => 'text',
				'save_data_modification' => function()
				{
					return array(
						function ($value)
						{
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
						}
					);
				},
				'fetch_data_modification' => function()
				{
					return array(
						function ($value)
						{
							$value = unserialize($value);

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
										}
										catch (Security\SecurityException $e)
										{
										}
									}
								}
							}

							return $value;
						}
					);
				},
			),
		);
	}

}
