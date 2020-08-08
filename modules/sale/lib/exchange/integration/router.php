<?php
namespace Bitrix\Sale\Exchange\Integration;


use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Exchange\Integration\Admin;
use Bitrix\Sale\Exchange\Integration\CRM\Placement;
use Bitrix\Sale\Exchange\Integration\Rest;
use Bitrix\Sale\Order;
use Bitrix\Sale\Result;


final class Router
{
	const LOG_DIR = '/bitrix/modules/sale/lib/exchange/integration/log';
	const LOG_PATH = 'router_request.log';

	protected $fields;

	public function __construct()
	{
		$this->fields = new \Bitrix\Sale\Internals\Fields(
			$this->requestJsonDecode(
				\Bitrix\Main\Context::getCurrent()
					->getRequest()
					->toArray()));

		$this->log($this->fields->getValues());
	}

	public function check()
	{
		return $this->checkToken($this->fields->get('AUTH_ID'), $this->fields->get('REFRESH_ID'));
	}

	protected function checkToken($accessToken, $refreshToken)
	{
		$r = new Result();

		$restClient = new Rest\Client\Base([
			"accessToken" => $accessToken,
			"refreshToken" => $refreshToken,
			"endPoint" => Settings::getOAuthRestUrl()
		]);

		try
		{
			$response = $restClient->call("app.info"); //echo '<pre>';print_r($response);die;
		}
		catch (\Exception $exception)
		{
			return $r->addError(new Error(Loc::getMessage("SALE_ROUTER_INTERNAL_SERVER_ERROR", ["#ERROR#"=>$exception->getMessage()])));
		}

		if (isset($response["error"]))
		{

			return $r->addError(new Error(Loc::getMessage("SALE_ROUTER_INTERNAL_SERVER_ERROR_AUHORIZATION",
				['#DESCRIPTION#'=>$response["error_description"], '#ERROR#'=>$response["error"]])
			));
		}
		else if (!isset($response["result"]))
		{
			return $r->addError(new Error(Loc::getMessage("SALE_ROUTER_INTERNAL_SERVER_ERROR_WRONG_RESPONSE")));
		}

		$token = Token::getToken([], (new App\IntegrationB24())->getCode());

		if(is_null($token))
		{
			return $r->addError(new Error(Loc::getMessage("SALE_ROUTER_INTERNAL_SERVER_ERROR_TOKEN_IS_NULL")));
		}

		if($token->getPortalId() !== $response['result']['install']['member_id'])
		{
			return $r->addError(new Error(Loc::getMessage("SALE_ROUTER_INTERNAL_SERVER_ERROR_TOKEN_IS_COMPROMISED")));
		}

		return $r;
	}

	public function redirect()
	{
		$placementType = $this->getPlacement();

		if(Placement\Type::isDefined(
			Placement\Type::resolveId($placementType)))
		{
			$placement = Placement\Factory::create(Placement\Type::resolveId($placementType), $this->fields->getValues());

			$link = Admin\Factory::create($placement->getModeType());

			if($placement->getModeType() == Admin\ModeType::APP_LAYOUT_TYPE)
			{
				$type = $placement->getType();
				if($type == Placement\Type::DEFAULT_TOOLBAR)
				{
					$entityTypeId = $this->resolveTypeId($placement->getEntityTypeId());
					//TODO: fabric
					if($entityTypeId == EntityType::ORDER)
					{
						$orderId = $this->getBySourceEntity(
							EntityType::ORDER, '',
							$placement->getEntityTypeId(), $placement->getEntityId());

						if($orderId>0)
						{
							$orderId = Order::load($orderId) ? $orderId
								:0;
						}

						$dealId = $orderId>0 ? $this->getByDestinationEntity(
								EntityType::ORDER, $orderId,
								CRM\EntityType::DEAL, '')
							:0;

						if($orderId>0 && $dealId>0)
						{
							$link
								->setPageByType(Admin\Registry::SALE_ORDER_VIEW)
								->setField('entityId', $dealId)
								->setField('entityTypeId', CRM\EntityType::DEAL)
								->setField('ID', $orderId)
								->setFilterParams(false)
								->fill()
								->redirect();
						}
						else
						{
							$title = Loc::getMessage("SALE_ROUTER_INTERNAL_ERROR_TITLE");

							if($orderId == 0)
							{
								$message = Loc::getMessage("SALE_ROUTER_ORDER_NOT_FOUND", ['#PLACEMENT_ENTITY_ID#'=>$placement->getEntityId()]);
								$link
									->setPage('/bitrix/services/sale/b24integration/500/rest-app-warning.php')
									->setField('message', urlencode($message))
									->setField('title', $title)
									->redirect();
							}

							$message = Loc::getMessage("SALE_ROUTER_INTERNAL_ERROR");
							$link
								->setPage('/bitrix/services/sale/b24integration/500/rest-app-warning.php')
								->setField('message', urlencode($message))
								->setField('title', $title)
								->redirect();
							 die;
						}
					}
				}
				elseif($type == Placement\Type::DEAL_DETAIL_TOOLBAR)
				{
					if($placement->getTypeHandler() == HandlerType::ORDER_NEW)
					{
						$link
							->setPageByType(Admin\Registry::SALE_ORDER_CREATE)
							->setField('entityId', $placement->getEntityId())
							->setField('entityTypeId', $placement->getEntityTypeId())
							->setFilterParams(false)
							->setField('SITE', SITE_ID)
							->setField('HANDLER', HandlerType::ORDER_NEW)
							->fill()
							->redirect();
					}
					elseif ($placement->getTypeHandler() == HandlerType::ORDER_REGISTRY)
					{
						$link
							->setPageByType(Admin\Registry::SALE_ORDER)
							->setField('entityId', $placement->getEntityId())
							->setField('entityTypeId', $placement->getEntityTypeId())
							->setFilterParams('&set_filter=Y&filter_is_sync_b24=N')
							->fill()
							->redirect();
					}
				}
			}
		}

		$link = Admin\Factory::create(Admin\ModeType::APP_LAYOUT_TYPE);
		$title = Loc::getMessage("SALE_ROUTER_INTERNAL_ERROR_TITLE");
		$message = Loc::getMessage("SALE_ROUTER_PAGE_NOT_FOUND");
		$link
			->setPage('/bitrix/services/sale/b24integration/500/rest-app-warning.php')
			->setField('message', urlencode($message))
			->setField('title', $title)
			->redirect();
	}

	protected function getPlacement()
	{
		return $this->fields->get('PLACEMENT');
	}

	protected function resolveTypeId($typeId)
	{
		if($typeId == CRM\EntityType::ACTIVITY)
		{
			return EntityType::ORDER;
		}
		else
		{
			return EntityType::UNDEFINED;
		}
	}

	protected function getBySourceEntity($sourceEntityTypeId='', $sourceEntityId='', $destinationEntityTypeId='', $destinationEntityId='')
	{
		$relation = Relation\Relation::getByEntity(
			$sourceEntityTypeId, $sourceEntityId,
			$destinationEntityTypeId, $destinationEntityId);

		return isset($relation['SRC_ENTITY_ID'])? $relation['SRC_ENTITY_ID']:0;
	}

	protected function getByDestinationEntity($sourceEntityTypeId='', $sourceEntityId='', $destinationEntityTypeId='', $destinationEntityId='')
	{
		$relation = Relation\Relation::getByEntity(
			$sourceEntityTypeId, $sourceEntityId,
			$destinationEntityTypeId, $destinationEntityId);

		return isset($relation['DST_ENTITY_ID'])? $relation['DST_ENTITY_ID']:0;
	}

	protected function requestJsonDecode($request)
	{
		$request['PLACEMENT_OPTIONS'] = isset($request['PLACEMENT_OPTIONS'])? json_decode($request['PLACEMENT_OPTIONS'], true):[];

		return $request;
	}

	/**
	 * @param $params
	 * @return void
	 */
	protected function log($params)
	{
		if($this->isOnLog() == false)
		{
			return;
		}

		$dir = $_SERVER['DOCUMENT_ROOT'].static::LOG_DIR;
		if(is_dir($dir) || @mkdir($dir, BX_DIR_PERMISSIONS))
		{
			$f = fopen($dir.'/'.static::LOG_PATH, "a+");
			fwrite($f, print_r($params, true));
			fclose($f);
		}
	}

	protected function isOnLog()
	{
		return \Bitrix\Main\Config\Option::get("sale", "log_integration_b24_router_request", 'N') == 'Y';
	}
}