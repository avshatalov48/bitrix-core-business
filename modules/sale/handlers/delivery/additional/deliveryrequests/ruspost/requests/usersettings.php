<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main\Web\HttpClient;

/**
 * Class OPS
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Receives shipping points for user
 * https://otpravka.pochta.ru/specification#/settings-shipping_points
 */
class UserSettings extends Base
{
	protected $path = "/1.0/settings";
	protected $type = HttpClient::HTTP_GET;
}