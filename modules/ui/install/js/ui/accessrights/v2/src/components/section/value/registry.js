import { ServiceLocator } from '../../../service/service-locator';
import type { AccessRightItem } from '../../../store/model/access-rights-model';

import { DependentVariables as DependentVariablesRow } from '../title-column/value/dependent-variables';
import { Multivariables as MultivariablesRow } from '../title-column/value/multivariables';
import { Variables as VariablesRow } from '../title-column/value/variables';

import { DependentVariables as DependentVariablesCell } from './../column/value/dependent-variables';
import { Multivariables as MultivariablesCell } from './../column/value/multivariables';
import { Toggler as TogglerCell } from './../column/value/toggler';
import { Variables as VariablesCell } from './../column/value/variables';

export const Cells = Object.freeze({
	DependentVariables: DependentVariablesCell,
	Multivariables: MultivariablesCell,
	Toggler: TogglerCell,
	Variables: VariablesCell,
});

export const Rows = Object.freeze({
	DependentVariables: DependentVariablesRow,
	Multivariables: MultivariablesRow,
	// no row value for toggler
	Variables: VariablesRow,
});

export function getValueComponent(accessRightItem: AccessRightItem): string
{
	const type = ServiceLocator.getValueTypeByRight(accessRightItem);
	if (!type)
	{
		// vue will render empty cell
		return '';
	}

	return type.getComponentName();
}
