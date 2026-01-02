import { Tag, Dom, Event, Loc } from 'main.core';
import { Lottie } from 'ui.lottie';
import './ai-loader.css';
import loaderAnimation from './landing-site-loader.json';

export class AiLoader
{
	constructor()
	{
		this.container = null;
	}

	getContainer(): HTMLElement
	{
		if (!this.container)
		{
			const loaderNode = Tag.render`<div class="landing-ui-ai-loader__animate"></div>`;

			Lottie.loadAnimation({
				container: loaderNode,
				renderer: 'svg',
				loop: true,
				autoplay: true,
				animationData: loaderAnimation,
			});

			this.container = Tag.render`
				<div class="landing-ui-ai-loader">
					<div class="landing-ui-ai-loader__animate">${loaderNode}</div>
					<div class="landing-ui-ai-loader__text">${Loc.getMessage('LANDING_SITE_GENERATE')}</div>
				</div>
			`;
		}

		return this.container;
	}

	open()
	{
		Dom.append(this.getContainer(), document.body);
	}

	close()
	{
		Event.bind(this.getContainer(), 'transitionend', () => {
			this.container = null;
			Dom.remove(this.getContainer());
		});
		Dom.addClass(this.getContainer(), '--close');
	}
}
