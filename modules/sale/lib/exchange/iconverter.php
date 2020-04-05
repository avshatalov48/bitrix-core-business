<?php
namespace Bitrix\Sale\Exchange;

/**
 * Interface IConverter
 * @package Bitrix\Sale\Exchange
 * @deprecated
 */
interface IConverter
{
    /**
     * @param $documentImport
     * @return array
     */
    public function resolveParams($documentImport);

    /**
     * @param null $entity
     * @param array $fields
     */
    static public function sanitizeFields($entity=null, array &$fields, ISettings $settings);

    public function externalize(array $fields);
}