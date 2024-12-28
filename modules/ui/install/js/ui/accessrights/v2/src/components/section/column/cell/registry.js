import type { AccessRightItem } from '../../../../store/model/access-rights-model';
import { DependentVariables } from './dependent-variables';
import { Multivariables } from './multivariables';
import { Toggler } from './toggler';
import { Variables } from './variables';

export const Cells = Object.freeze({
	Toggler,
	Variables,
	Multivariables,
	DependentVariables,
});

export function getCellComponent(accessRightItem: AccessRightItem): string
{
	if (accessRightItem.type === 'toggler')
	{
		return 'Toggler';
	}

	if (accessRightItem.type === 'variables')
	{
		return 'Variables';
	}

	if (accessRightItem.type === 'multivariables')
	{
		return 'Multivariables';
	}

	if (accessRightItem.type === 'dependent_variables')
	{
		return 'DependentVariables';
	}

	console.warn('ui.accessrights.v2: Unknown access right type', accessRightItem);

	// vue will render empty cell
	return '';
}
