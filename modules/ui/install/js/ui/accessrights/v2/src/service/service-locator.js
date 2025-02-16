import { Cache } from 'main.core';
import type { AccessRightItem } from '../store/model/access-rights-model';
import { DependentVariables } from './value/type/dependent-variables';
import { Multivariables } from './value/type/multivariables';
import { Toggler } from './value/type/toggler';
import type { ValueType } from './value/type/value-type';
import { Variables } from './value/type/variables';

export class ServiceLocator
{
	static #cache = new Cache.MemoryCache();

	/**
	 * `BX.UI.Hint.createInstance` takes up to 30% of CPU time when multiple hints are mounted on page
	 * (e.g. on a load, search), probably because of `Manager.initByClassName` call in `new Manager`.
	 * therefore, we share a Manager instance across all hints in the app
	 */
	static getHint(appGuid: string): BX.UI.Hint
	{
		return this.#cache.remember(`hint-${appGuid}`, () => {
			return BX.UI.Hint.createInstance({
				id: `ui-access-rights-v2-hint-${appGuid}`,
				popupParameters: {
					className: 'ui-access-rights-v2-popup-pointer-events ui-hint-popup',
					autoHide: true,
					darkMode: true,
					maxWidth: 280,
					offsetTop: 0,
					offsetLeft: 8,
					angle: true,
					animation: 'fading-slide',
				},
			});
		});
	}

	static getValueTypeByRight(right: AccessRightItem): ?ValueType
	{
		return this.getValueType(right.type);
	}

	static getValueType(type: string): ?ValueType
	{
		const stringType = String(type);

		return this.#cache.remember(stringType, () => {
			if (stringType === 'dependent_variables')
			{
				return new DependentVariables();
			}

			if (stringType === 'multivariables')
			{
				return new Multivariables();
			}

			if (stringType === 'toggler')
			{
				return new Toggler();
			}

			if (stringType === 'variables')
			{
				return new Variables();
			}

			console.warn('ui.accessrights.v2: Unknown access right type', type);

			return null;
		});
	}
}
