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
    public function sanitizeFields($entity=null, array &$fields);

    /**
     * @param ISettings $settings
     */
    public function loadSettings(ISettings $settings);


    public function externalize(array $fields);
}