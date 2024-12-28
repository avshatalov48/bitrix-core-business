import { Cache } from 'main.core';

/**
 * `BX.UI.Hint.createInstance` takes up to 30% of CPU time when multiple hints are mounted on page
 * (e.g. on a load, search), probably because of `Manager.initByClassName` call in `new Manager`.
 * therefore, we share a Manager instance across all hints in the app
 */
export class HintLocator
{
	static #cache = new Cache.MemoryCache();

	static get(appGuid: string): BX.UI.Hint
	{
		return this.#cache.remember(appGuid, () => {
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
}
