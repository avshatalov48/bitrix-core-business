<?php
namespace Bitrix\Sale\Exchange;


use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Result;

interface ICollision
{
    /**
     * @param $entityTypeId
     * @param $typeId
     * @param Entity $entity
     * @param null $message
     * @return mixed
     */
    public function addItem($entityTypeId, $typeId, Entity $entity, $message=null);

    /**
     * @param $entityTypeId
     * @return mixed
     */
    public static function getCurrent($entityTypeId);

    /**
     * @return mixed
     */
    public function getEntity();

    /**
     * @return int
     */
    public function getTypeId();

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @return string
     */
    public function getTypeName();

    /**
     * @param Entity $entity
     */
    public function setEntity(Entity $entity);

	/**
	 * @param ImportBase $entity
	 * @return Result
	 */
	public function resolve(ImportBase $entity);
}