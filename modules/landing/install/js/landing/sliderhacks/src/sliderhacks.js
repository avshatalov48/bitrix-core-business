import {Dom, Event, Runtime, Tag, Cache, Uri, Type} from 'main.core';
import {Loader} from 'main.loader';

import './css/style.css';

/**
 * @memberOf BX.Landing
 */
export class SliderHacks
{
	static cache = new Cache.MemoryCache();

	static getContentArea(): HTMLElement
	{
		return SliderHacks.cache.remember('contentArea', () => {
			return document.querySelector('.landing-main');
		});
	}

	static getContentLoader(): Loader
	{
		return SliderHacks.cache.remember('contentLoader', () => {
			const wrapper = Tag.render`<div class="landing-content-loader"></div>`;
			const loader = new Loader({
				target: wrapper,
			});

			loader.show();

			return wrapper;
		});
	}

	static showContentLoader()
	{
		const contentArea = SliderHacks.getContentArea();
		const contentLoader = SliderHacks.getContentLoader();
		Dom.style(contentArea, 'position', 'relative');
		Dom.append(contentLoader, contentArea);
	}

	static hideContentLoader()
	{
		Dom.style(SliderHacks.getContentArea(), 'position', null);
		Dom.remove(SliderHacks.getContentLoader());
	}

	static reloadSlider(url: string, context): Promise<any>
	{
		return new Promise((resolve) => {
			const slider = BX.SidePanel.Instance.getSliderByWindow(context || window);

			if (slider)
			{
				SliderHacks.showContentLoader();

				const srcFrame = slider.getFrame();
				const frame = Runtime.clone(srcFrame);

				frame.src = Uri.addParam(url, {IFRAME: 'Y'});
				slider.iframe = frame;

				Dom.style(frame, {
					position: 'absolute',
					opacity: 0,
					left: 0,
					transition: '200ms opacity ease',
				});
				Dom.insertAfter(frame, srcFrame);

				Event.bind(frame, 'load', (event) => {
					if (Type.isFunction(slider.handleFrameLoad))
					{
						slider.handleFrameLoad(event);
					}
					else
					{
						console.error('SliderHacks: slider.handleFrameLoad is not a function');
					}

					setTimeout(() => {
						Dom.style(frame, 'opacity', null);
						setTimeout(() => {
							Dom.remove(srcFrame);
							resolve();
						}, 200);
					}, 200);
				});
			}
			else
			{
				resolve();
			}
		});
	}
}