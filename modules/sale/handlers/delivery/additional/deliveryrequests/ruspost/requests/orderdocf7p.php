<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

/**
 * Class OrderDocF7P
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Genere\ates printing form F7p
 * https://otpravka.pochta.ru/specification#/documents-create_f7_f22
 */
class OrderDocF7P extends BaseFile
{
	protected $path = "/1.0/forms/{id}/f7pdf";
	protected $type = \Bitrix\Main\Web\HttpClient::HTTP_GET;
}