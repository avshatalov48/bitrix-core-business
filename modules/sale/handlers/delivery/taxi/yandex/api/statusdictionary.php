<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api;

/**
 * Class StatusDictionary
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api
 */
class StatusDictionary
{
	const NEW = 'new';
	const FAILED = 'failed';
	const ESTIMATING = 'estimating';
	const ESTIMATING_FAILED = 'estimating_failed';
	const READY_FOR_APPROVAL = 'ready_for_approval';
	const ACCEPTED = 'accepted';
	const PERFORMER_LOOKUP = 'performer_lookup';
	const PERFORMER_DRAFT = 'performer_draft';
	const PERFORMER_FOUND = 'performer_found';
	const PERFORMER_NOT_FOUND = 'performer_not_found';
	const PICKUP_ARRIVED = 'pickup_arrived';
	const READY_FOR_PICKUP_CONFIRMATION = 'ready_for_pickup_confirmation';
	const PICKUPED = 'pickuped';
	const PAY_WAITING = 'pay_waiting';
	const DELIVERY_ARRIVED = 'delivery_arrived';
	const READY_FOR_DELIVERY_CONFIRMATION = 'ready_for_delivery_confirmation';
	const DELIVERED = 'delivered';
	const RETURNING = 'returning';
	const RETURN_ARRIVED = 'return_arrived';
	const READY_FOR_RETURN_CONFIRMATION = 'ready_for_return_confirmation';
	const RETURNED = 'returned';
	const RETURNED_FINISH = 'returned_finish';
	const CANCELLED = 'cancelled';
	const CANCELLED_WITH_PAYMENT = 'cancelled_with_payment';
	const CANCELLED_BY_TAXI = 'cancelled_by_taxi';
	const CANCELLED_WITH_ITEMS_ON_HANDS = 'cancelled_with_items_on_hands';
	const DELIVERED_FINISH = 'delivered_finish';
}
