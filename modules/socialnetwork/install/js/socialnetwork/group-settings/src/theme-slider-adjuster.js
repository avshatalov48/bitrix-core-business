import { Dom, Event } from 'main.core';
import { EventEmitter } from 'main.core.events';

type Params = {
	sliderWidth: number,
	checkSlider: void,
}

export class ThemeSliderAdjuster
{
	#params: Params;
	#slider;
	#layout: {
		themeBackgroundContainer: HTMLElement,
	};

	constructor(params: Params)
	{
		this.#params = params;
		this.#layout = {};
	}

	bindSliderEvents()
	{
		EventEmitter.subscribe('SidePanel.Slider:onOpenStart', (event) => {
			if (this.#params.checkSlider(event.target))
			{
				this.#slider = event.target;
				this.#updateSliderTheme();

				Dom.removeClass(this.#getImBar(), 'bx-im-bar-default');
			}
			else
			{
				Dom.addClass(this.#getImBar(), 'bx-im-bar-default');
			}
		});

		EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', () => {
			Dom.removeClass(this.#getImBar(), 'bx-im-bar-default');
			if (BX.SidePanel.Instance.getTopSlider() && BX.SidePanel.Instance.getTopSlider() !== this.#slider)
			{
				Dom.addClass(this.#getImBar(), 'bx-im-bar-default');
			}
		});

		new MutationObserver(() => {
			const theme = BX.Intranet.Bitrix24.ThemePicker.Singleton.getAppliedThemeId();
			const themeStyles = document.head.querySelectorAll(`link[data-theme-id="${theme}"`);
			Promise.all([...themeStyles].map((link) => new Promise((resolve) => {
				Event.bind(link, 'load', resolve);
			}))).then(() => this.#updateSliderTheme());
		}).observe(document.head, { childList: true, subtree: false });
	}

	#updateSliderTheme()
	{
		const backgroundNode = this.#getBackgroundNode();
		this.#applyMainThemeStyles(backgroundNode);

		const mainThemeVideo = this.#getMainThemeVideo();
		if (!mainThemeVideo)
		{
			return;
		}

		if (Dom.attr(mainThemeVideo, 'data-theme-id') !== BX.Intranet.Bitrix24.ThemePicker.Singleton.getAppliedThemeId())
		{
			mainThemeVideo?.remove();
			return;
		}

		Dom.clean(backgroundNode);
		Dom.append(this.#getSliderThemeVideo(mainThemeVideo), backgroundNode);

		const mainVideo = mainThemeVideo.querySelector('video');
		if (!mainVideo.dataset.pausePlayEventsBinded)
		{
			mainVideo.addEventListener('pause', () => {
				const sliderVideo = backgroundNode.querySelector('.theme-video-container video');
				sliderVideo.currentTime = mainVideo.currentTime;
				sliderVideo.pause();
			});
			mainVideo.addEventListener('play', () => {
				const sliderVideo = backgroundNode.querySelector('.theme-video-container video');
				sliderVideo.currentTime = mainVideo.currentTime;
				sliderVideo.play();
			});
			mainVideo.dataset.pausePlayEventsBinded = true;
		}
	}

	#applyMainThemeStyles(themeNode: HTMLElement): void
	{
		const mainStyles = getComputedStyle(document.body);
		Dom.style(themeNode, 'backgroundColor', mainStyles.backgroundColor);
		Dom.style(themeNode, 'backgroundImage', mainStyles.backgroundImage);
		Dom.style(themeNode, 'backgroundSize', mainStyles.backgroundSize);
		Dom.style(themeNode, 'backgroundPositionX', this.#getSliderBackgroundOffset());
	}

	#getMainThemeVideo()
	{
		return document.querySelector('.theme-video-container[data-theme-id]');
	}

	#getSliderThemeVideo(mainThemeVideo: HTMLElement): HTMLElement
	{
		const copiedThemeVideo = mainThemeVideo.cloneNode(true);
		Dom.attr(copiedThemeVideo, 'data-theme-id', null);
		Dom.style(copiedThemeVideo, 'zIndex', 1);
		Dom.style(copiedThemeVideo, 'width', '100vw');
		Dom.style(copiedThemeVideo, 'height', '100vh');

		const copiedVideo = copiedThemeVideo.querySelector('video');
		Dom.style(copiedVideo, 'transform', `translateX(${this.#getSliderBackgroundOffset()})`);

		const mainVideo = mainThemeVideo.querySelector('video');
		if (mainVideo.paused)
		{
			copiedVideo.pause();
		}
		else
		{
			copiedVideo.play();
		}
		copiedVideo.currentTime = mainVideo.currentTime;

		return copiedThemeVideo;
	}

	#getSliderBackgroundOffset(): string
	{
		return `calc(-100vw + ${this.#params.sliderWidth}px + ${this.#getImBar().offsetWidth}px)`;
	}

	#getBackgroundNode()
	{
		if (!this.#slider.getContainer().contains(this.#layout.themeBackgroundContainer))
		{
			this.#layout.themeBackgroundContainer?.remove();
			this.#layout.themeBackgroundContainer = BX.Tag.render`
				<div
					data-id="slider-theme-background-container"
					style="
						position: absolute;
						inset: 0;
						width: 100vw;
						height: 100vh;
					"
				>
				</div>
			`;
			this.#slider.getContainer().prepend(this.#layout.themeBackgroundContainer);
		}

		this.#layout.themeBackgroundContainer.querySelector('.theme-video-container')?.remove();

		return this.#layout.themeBackgroundContainer;
	}

	#getImBar()
	{
		return document.getElementById('bx-im-bar');
	}
}