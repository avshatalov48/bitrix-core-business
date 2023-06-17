const storageKey = 'iblockPropertyDetails:deferredSlider'

export class Sliders
{
	static getDeferredSlider(): ?String
	{
		const sliderName = top[storageKey];

		top[storageKey] = null;

		return sliderName;
	}

	static setDeferredSlider(sliderName: String)
	{
		top[storageKey] = sliderName;
	}
}
