<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api;

/**
 * Class StatusDictionary
 * @package Sale\Handlers\Delivery\YandexTaxi\Api
 * @internal
 */
final class StatusDictionary
{
	public const NEW = 'new';
	public const FAILED = 'failed';
	public const ESTIMATING = 'estimating';
	public const ESTIMATING_FAILED = 'estimating_failed';
	public const READY_FOR_APPROVAL = 'ready_for_approval';
	public const ACCEPTED = 'accepted';
	public const PERFORMER_LOOKUP = 'performer_lookup';
	public const PERFORMER_DRAFT = 'performer_draft';
	public const PERFORMER_FOUND = 'performer_found';
	public const PERFORMER_NOT_FOUND = 'performer_not_found';
	public const PICKUP_ARRIVED = 'pickup_arrived';
	public const READY_FOR_PICKUP_CONFIRMATION = 'ready_for_pickup_confirmation';
	public const PICKUPED = 'pickuped';
	public const PAY_WAITING = 'pay_waiting';
	public const DELIVERY_ARRIVED = 'delivery_arrived';
	public const READY_FOR_DELIVERY_CONFIRMATION = 'ready_for_delivery_confirmation';
	public const DELIVERED = 'delivered';
	public const RETURNING = 'returning';
	public const RETURN_ARRIVED = 'return_arrived';
	public const READY_FOR_RETURN_CONFIRMATION = 'ready_for_return_confirmation';
	public const RETURNED = 'returned';
	public const RETURNED_FINISH = 'returned_finish';
	public const CANCELLED = 'cancelled';
	public const CANCELLED_WITH_PAYMENT = 'cancelled_with_payment';
	public const CANCELLED_BY_TAXI = 'cancelled_by_taxi';
	public const CANCELLED_WITH_ITEMS_ON_HANDS = 'cancelled_with_items_on_hands';
	public const DELIVERED_FINISH = 'delivered_finish';
}
