<?

namespace Bitrix\Sender\Integration\Sale\Preset;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Preset\Templates;
use Bitrix\Sender;

Loc::loadMessages(__FILE__);

/**
 * Class TriggerCampaign
 *
 * @package Bitrix\Sender\Integration\Sale\Preset
 */
class TriggerCampaign
{
	protected static function getMailTemplate(array $params = null)
	{
		if(!isset($params['TITLE']))
		{
			$params['TITLE'] = '%TITLE%';
		}

		if(!isset($params['TEXT']))
		{
			$params['TEXT'] = '%TEXT%';
		}

		$content = Templates\Mail::getTemplateHtml();
		$content = Templates\Mail::replaceTemplateHtml(
			$content,
			[
				'TEXT' => "<br><h2>{$params['TITLE']}</h2><br>{$params['TEXT']}<br><br>"
			]
		);

		return $content;
	}

	protected static function getCoupon($perc = 5)
	{
		if(!is_numeric($perc))
			$perc = 5;

		return '<?EventMessageThemeCompiler::includeComponent(
			"bitrix:sale.discount.coupon.mail",
			"",
			Array(
				"COMPONENT_TEMPLATE" => ".default",
				"DISCOUNT_XML_ID" => "{#SENDER_CHAIN_CODE#}",
				"DISCOUNT_VALUE" => "' . $perc . '",
				"DISCOUNT_UNIT" => "Perc",
				"COUPON_TYPE" => "Order",
				"COUPON_DESCRIPTION" => "{#EMAIL_TO#}"
			)
		);?>';
	}

	protected static function getBasketCart()
	{
		return '<?EventMessageThemeCompiler::includeComponent(
			"bitrix:sale.basket.basket.small.mail",
			"",
			Array(
				"USER_ID" => "{#USER_ID#}",
				"PATH_TO_BASKET" => "/",
				"PATH_TO_ORDER" => "/",
			)
		);?>';
	}

	protected static function getMessagePlaceHolders()
	{
		return array(
			'%BASKET_CART%' => self::getBasketCart(),
			'%COUPON%' => self::getCoupon(5),
			'%COUPON_3%' => self::getCoupon(3),
			'%COUPON_5%' => self::getCoupon(5),
			'%COUPON_7%' => self::getCoupon(7),
			'%COUPON_10%' => self::getCoupon(10),
			'%COUPON_11%' => self::getCoupon(11),
			'%COUPON_15%' => self::getCoupon(15),
			'%COUPON_20%' => self::getCoupon(20),
		);
	}

	/**
	 * Get template types.
	 *
	 * @return array
	 */
	public static function getTemplateCategories()
	{
		$list = [];

		$id = 1000;
		$all = self::getAll();
		foreach ($all as $item)
		{
			$list[] = [
				'id' => $id++,
				'code' => strtoupper($item['CODE']),
				'name' => $item['TYPE'] . '. ' . $item['NAME'],
			];
		}

		return $list;
	}

	/**
	 * Get all.
	 *
	 * @return array
	 */
	public static function getAll()
	{
		$list = [];

		$list[] = self::getForgottenCart(1);
		$list[] = self::getCanceledOrder();
		$list[] = self::getPaidOrder();
		$list[] = self::getDontBuy(90);
		$list[] = self::getDontAuth(111);
		$list[] = self::getDontBuy(180);
		$list[] = self::getDontBuy(360);

		return $list;
	}

	/**
	 * Get forgotten cart.
	 *
	 * @param int $days Days.
	 * @return array
	 */
	public static function getForgottenCart($days)
	{
		$list = array(
			'TYPE' => Loc::getMessage('PRESET_TYPE_BASKET'),
			'CODE' => 'sale_basket',
			'NAME' => Loc::getMessage('PRESET_FORGOTTEN_BASKET_NAME'),
			'DESC_USER' => Loc::getMessage('PRESET_FORGOTTEN_BASKET_DESC_USER'),
			'DESC' => Loc::getMessage('PRESET_FORGOTTEN_BASKET_DESC'),
			'TRIGGER' => array(
				'START' => array(
					'ENDPOINT' => array(
						'MODULE_ID' => 'sale',
						'CODE' => 'basket_forgotten',
						'FIELDS' => array('DAYS_BASKET_FORGOTTEN' => $days)
					)
				),
				'END' => array(
					'ENDPOINT' => array(
						'MODULE_ID' => 'sale',
						'CODE' => 'order_paid',
						'FIELDS' => array()
					)
				),
			),
			'CHAIN' => array(
				array(
					'TIME_SHIFT' => 0,
					'SUBJECT' => Loc::getMessage('PRESET_FORGOTTEN_BASKET_LETTER_1_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_FORGOTTEN_BASKET_LETTER_1_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_FORGOTTEN_BASKET_LETTER_1_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_FORGOTTEN_BASKET_LETTER_2_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_FORGOTTEN_BASKET_LETTER_2_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_FORGOTTEN_BASKET_LETTER_2_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_FORGOTTEN_BASKET_LETTER_3_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_FORGOTTEN_BASKET_LETTER_3_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_FORGOTTEN_BASKET_LETTER_3_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
			)
		);

		foreach ($list['CHAIN'] as $index => $letter)
		{
			$letter['TEMPLATE_TYPE'] = Sender\Templates\Type::getCode(Sender\Templates\Type::BASE);
			$letter['TEMPLATE_ID'] = strtoupper($list['CODE']) . '_' . $index;
			$list['CHAIN'][$index] = $letter;
		}

		return $list;
	}

	/**
	 * Get canceled order.
	 *
	 * @return array
	 */
	public static function getCanceledOrder()
	{
		$list = array(
			'TYPE' => Loc::getMessage('PRESET_TYPE_ORDER'),
			'CODE' => 'sale_order_cancel',
			'NAME' => Loc::getMessage('PRESET_CANCELED_ORDER_NAME'),
			'DESC_USER' => Loc::getMessage('PRESET_CANCELED_ORDER_DESC_USER'),
			'DESC' => Loc::getMessage('PRESET_CANCELED_ORDER_DESC'),
			'TRIGGER' => array(
				'START' => array(
					'ENDPOINT' => array(
						'MODULE_ID' => 'sale',
						'CODE' => 'order_cancel',
						'FIELDS' => array()
					)
				),
				'END' => array(
					'ENDPOINT' => array(
						'MODULE_ID' => 'sale',
						'CODE' => 'order_paid',
						'FIELDS' => array()
					)
				),
			),
			'CHAIN' => array(
				array(
					'TIME_SHIFT' => 0,
					'SUBJECT' => Loc::getMessage('PRESET_CANCELED_ORDER_LETTER_1_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_CANCELED_ORDER_LETTER_1_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_CANCELED_ORDER_LETTER_1_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_CANCELED_ORDER_LETTER_2_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_CANCELED_ORDER_LETTER_2_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_CANCELED_ORDER_LETTER_2_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_CANCELED_ORDER_LETTER_3_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_CANCELED_ORDER_LETTER_3_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_CANCELED_ORDER_LETTER_3_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
			)
		);

		foreach ($list['CHAIN'] as $index => $letter)
		{
			$letter['TEMPLATE_TYPE'] = Sender\Templates\Type::getCode(Sender\Templates\Type::BASE);
			$letter['TEMPLATE_ID'] = strtoupper($list['CODE']) . '_' . $index;
			$list['CHAIN'][$index] = $letter;
		}

		return $list;
	}

	/**
	 * Get paid order.
	 *
	 * @return array
	 */
	public static function getPaidOrder()
	{
		$list = array(
			'TYPE' => Loc::getMessage('PRESET_TYPE_ORDER'),
			'CODE' => 'sale_order_pay',
			'NAME' => Loc::getMessage('PRESET_PAID_ORDER_NAME'),
			'DESC_USER' => Loc::getMessage('PRESET_PAID_ORDER_DESC_USER'),
			'DESC' => Loc::getMessage('PRESET_PAID_ORDER_DESC'),
			'TRIGGER' => array(
				'START' => array(
					'ENDPOINT' => array(
						'MODULE_ID' => 'sale',
						'CODE' => 'order_paid',
						'FIELDS' => array()
					)
				),
				'END' => array(
					'ENDPOINT' => array(
						'MODULE_ID' => 'sale',
						'CODE' => 'order_paid',
						'FIELDS' => array()
					)
				),
			),
			'CHAIN' => array(
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_1_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_1_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_1_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_2_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_2_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_2_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_3_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_3_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_3_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_4_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_4_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_4_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_5_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_5_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_PAID_ORDER_LETTER_5_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
			)
		);

		foreach ($list['CHAIN'] as $index => $letter)
		{
			$letter['TEMPLATE_TYPE'] = Sender\Templates\Type::getCode(Sender\Templates\Type::BASE);
			$letter['TEMPLATE_ID'] = strtoupper($list['CODE']) . '_' . $index;
			$list['CHAIN'][$index] = $letter;
		}

		return $list;
	}

	/**
	 * Get don't buy.
	 *
	 * @param int $days Days.
	 * @return array
	 */
	public static function getDontBuy($days)
	{
		$list = array(
			'TYPE' => Loc::getMessage('PRESET_TYPE_ORDER'),
			'CODE' => 'sale_order_not_create'.$days,
			'NAME' => Loc::getMessage('PRESET_DONT_BUY_NAME', array('%DAYS%' => $days)),
			'DESC_USER' => Loc::getMessage('PRESET_DONT_BUY_DESC_USER', array('%DAYS%' => $days)),
			'DESC' => Loc::getMessage('PRESET_DONT_BUY_DESC_' . $days),
			'TRIGGER' => array(
				'START' => array(
					'ENDPOINT' => array(
						'MODULE_ID' => 'sale',
						'CODE' => 'dont_buy',
						'FIELDS' => array('DAYS_DONT_BUY' => $days),
						'RUN_FOR_OLD_DATA' => ($days > 300 ? 'Y' : 'N')
					)
				),
				'END' => array(
					'ENDPOINT' => array(
						'MODULE_ID' => 'sale',
						'CODE' => 'order_paid',
						'FIELDS' => array()
					)
				),
			),
			'CHAIN' => array(
				array(
					'TIME_SHIFT' => 0,
					'SUBJECT' => Loc::getMessage('PRESET_DONT_BUY_LETTER_1_SUBJECT_' . $days),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_DONT_BUY_LETTER_1_SUBJECT_' . $days),
						'TEXT' => Loc::getMessage('PRESET_DONT_BUY_LETTER_1_MESSAGE_' . $days, static::getMessagePlaceHolders()),
					)),
				),
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_DONT_BUY_LETTER_2_SUBJECT_' . $days),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_DONT_BUY_LETTER_2_SUBJECT_' . $days),
						'TEXT' => Loc::getMessage('PRESET_DONT_BUY_LETTER_2_MESSAGE_' . $days, static::getMessagePlaceHolders()),
					)),
				),
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_DONT_BUY_LETTER_3_SUBJECT_' . $days),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_DONT_BUY_LETTER_3_SUBJECT_' . $days),
						'TEXT' => Loc::getMessage('PRESET_DONT_BUY_LETTER_3_MESSAGE_' . $days, static::getMessagePlaceHolders()),
					)),
				),
			)
		);

		foreach ($list['CHAIN'] as $index => $letter)
		{
			$letter['TEMPLATE_TYPE'] = Sender\Templates\Type::getCode(Sender\Templates\Type::BASE);
			$letter['TEMPLATE_ID'] = strtoupper($list['CODE']) . '_' . $index;
			$list['CHAIN'][$index] = $letter;
		}

		return $list;
	}

	/**
	 * Get don't auth.
	 *
	 * @param int $days Days.
	 * @return array
	 */
	public static function getDontAuth($days)
	{
		$list = array(
			'TYPE' => Loc::getMessage('PRESET_TYPE_ORDER'),
			'CODE' => 'sale_user_dontauth',
			'NAME' => Loc::getMessage('PRESET_DONT_AUTH_NAME'),
			'DESC_USER' => Loc::getMessage('PRESET_DONT_AUTH_DESC_USER'),
			'DESC' => Loc::getMessage('PRESET_DONT_AUTH_DESC', array('%DAYS%' => $days)),
			'TRIGGER' => array(
				'START' => array(
					'ENDPOINT' => array(
						'MODULE_ID' => 'sender',
						'CODE' => 'user_dontauth',
						'FIELDS' => array('DAYS_DONT_AUTH' => $days)
					)
				),
				'END' => array(
					'ENDPOINT' => array(
						'MODULE_ID' => 'sender',
						'CODE' => 'user_auth',
						'FIELDS' => array()
					)
				),
			),
			'CHAIN' => array(
				array(
					'TIME_SHIFT' => 0,
					'SUBJECT' => Loc::getMessage('PRESET_DONT_AUTH_LETTER_1_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_DONT_AUTH_LETTER_1_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_DONT_AUTH_LETTER_1_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_DONT_AUTH_LETTER_2_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_DONT_AUTH_LETTER_2_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_DONT_AUTH_LETTER_2_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_DONT_AUTH_LETTER_3_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_DONT_AUTH_LETTER_3_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_DONT_AUTH_LETTER_3_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
				array(
					'TIME_SHIFT' => 1440,
					'SUBJECT' => Loc::getMessage('PRESET_DONT_AUTH_LETTER_4_SUBJECT'),
					'MESSAGE' => self::getMailTemplate(array(
						'TITLE' => Loc::getMessage('PRESET_DONT_AUTH_LETTER_4_SUBJECT'),
						'TEXT' => Loc::getMessage('PRESET_DONT_AUTH_LETTER_4_MESSAGE', static::getMessagePlaceHolders()),
					)),
				),
			)
		);

		foreach ($list['CHAIN'] as $index => $letter)
		{
			$letter['TEMPLATE_TYPE'] = Sender\Templates\Type::getCode(Sender\Templates\Type::BASE);
			$letter['TEMPLATE_ID'] = strtoupper($list['CODE']) . '_' . $index;
			$list['CHAIN'][$index] = $letter;
		}

		return $list;
	}
}