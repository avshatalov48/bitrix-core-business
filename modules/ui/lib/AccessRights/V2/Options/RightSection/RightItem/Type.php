<?php

namespace Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem;

use Bitrix\Main\Access\Permission\PermissionDictionary;

enum Type: string
{
	case Variables = 'variables'; /** @see PermissionDictionary::TYPE_VARIABLES */
	case Toggler = 'toggler'; /** @see PermissionDictionary::TYPE_TOGGLER */
	case MultiVariables = 'multivariables'; /** @see PermissionDictionary::TYPE_MULTIVARIABLES */
	case DependentVariables = 'dependent_variables'; /** @see PermissionDictionary::TYPE_DEPENDENT_VARIABLES */
}
