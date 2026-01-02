<?php

namespace Bitrix\Main\DB;

/**
 * Exception is thrown when database returns the duplicate entry error on query execution.
 */
class DuplicateEntryException extends SqlQueryException
{
}
