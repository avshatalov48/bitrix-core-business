import CatalogPage from './catalog-page';
import Slider from './slider';

class CatalogSettings
{
	#page: CatalogPage;

	constructor(settings)
	{
		this.#page = CatalogPage.init(settings);

		this.#page.subscribe('change', this.#onEventChangeData.bind(this));
	}

	render(): HTMLElement
	{
		return this.#page.getPage();
	}

	#onEventChangeData()
	{
		this.#page.onChange();
	}
}

export {
	CatalogSettings,
	Slider,
};
