<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main\Web\HttpClient;

/**
 * Class OPS
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Receives shipping points for user
 * https://otpravka.pochta.ru/specification#/settings-shipping_points
 */
class OPS extends Base
{
	protected $path = "/1.0/user-shipping-points";
	protected $type = HttpClient::HTTP_GET;
}