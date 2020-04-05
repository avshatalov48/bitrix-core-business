<?php

namespace Bitrix\Main\Engine\Response\DataType;

use Bitrix\Main\Web\Uri;

/**
 * Class ContentUri marks uri which provides content (file content).
 * It gives chance to process uri by some rest application without any bearer tokens.
 * So, module "rest" converts ContentUri to two items. One of them is original uri and
 * second is machine uri.
 * @package Bitrix\Main\Engine\Response\DataType
 *
 */
final class ContentUri extends Uri
{}