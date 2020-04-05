<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

/**
 * Class OrderDocForms
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Generates printing forms for shipments before creating the batch
 * https://otpravka.pochta.ru/specification#/documents-create_forms_backlog
 */
class OrderDocForms extends BaseFile
{
	protected $path = "/1.0/forms/{id}/forms";
	protected $type = \Bitrix\Main\Web\HttpClient::HTTP_GET;
}