import { Type } from 'main.core';
import { SliderProvider } from './providers/slider-provider';

export class InfoHelper
{
	static frameUrlTemplate : '';
	static frameNode : null;
	static popupLoader : null;
	static availableDomainList : null;
	static frameUrl: '';
	static inited: false;
	static sliderProviderForOldFormat: null;

	static init(params): void
	{
		this.sliderProviderForOldFormat = new SliderProvider({
			width: 700,
			frameUrlTemplate: params.frameUrlTemplate,
		});
	}

	static __showExternal(code, option): void
	{
		this.sliderProviderForOldFormat?.__showExternal(code, option);
	}

	static show(code, params): void
	{
		if (!Type.isPlainObject(params))
		{
			params = {};
		}

		this.init({});
		this.sliderProviderForOldFormat?.show(code, params);
	}

	static close(): void
	{
		this.sliderProviderForOldFormat?.close();
	}

	static getContent()
	{
		return this.sliderProviderForOldFormat?.getContent();
	}

	static getFrame()
	{
		return this.sliderProviderForOldFormat?.getFrame();
	}

	static showFrame(frame)
	{
		this.sliderProviderForOldFormat?.showFrame(frame);
	}

	static getLoader()
	{
		return this.sliderProviderForOldFormat?.getLoader();
	}

	static getSliderId(): string
	{
		return this.sliderProviderForOldFormat?.getId();
	}

	static getSlider()
	{
		return this.sliderProviderForOldFormat?.getSlider();
	}

	static reloadParent(): void
	{
		let slider = false;
		const sliderTop = BX.SidePanel.Instance.getTopSlider();

		if (sliderTop)
		{
			slider = BX.SidePanel.Instance.getPreviousSlider(sliderTop);
		}

		if (slider)
		{
			slider.reload();
		}
		else
		{
			window.location.reload();
		}
	}

	static isOpen(): boolean
	{
		return this.sliderProviderForOldFormat?.isOpen();
	}

	static isInited(): boolean
	{
		return this.inited;
	}
}