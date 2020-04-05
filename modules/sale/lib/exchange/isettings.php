<?php
namespace Bitrix\Sale\Exchange;


interface ISettings
{
    /**
     * @param $entityTypeId
     * @return bool
     */
    public function isImportableFor($entityTypeId);

    /**
     * @param $entityTypeId
     * @return int
     */
    public function paySystemIdFor($entityTypeId);

    /**
     * @param $entityTypeId
     * @return int
     */
    public function shipmentServiceFor($entityTypeId);

    /**
     * @return string
     */
    public function getSiteId();

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @return self
     */
    public static function getCurrent();

    /**
     * @return string
     */
    public function prefixFor($typeId);

    /**
	 * @return string
	 */
	public function canCreateOrder($typeId);

	/**
	 * @return string
	 */
	public function finalStatusIdFor($typeId);

	/**
	 * @return string
	 */
	public function finalStatusOnDeliveryFor($typeId);

	/**
	 * @return string
	 */
	public function changeStatusFor($typeId);
}