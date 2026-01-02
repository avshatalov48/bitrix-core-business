import { Dom, Tag, Type, Reflection, Loc } from 'main.core';
import { Lottie } from 'ui.lottie';
import loaderAnimation from '../lottie/landing-site-loader.json';

import './style.css';

export class SiteCopilotTemplateCreator
{
	constructor(options)
	{
		this.options = { ...this.getDefaultOptions(), ...Type.isPlainObject(options) ? options : {} };
		this.container = this.options.container ?? null;
		this.rootPath = this.options.rootPath ?? '';
		this.node = {
			body: null,
		};
		this.interval = null;
		const Helper = Reflection.getClass('top.BX.Helper');
		Helper?.init({
			frameOpenUrl: this.options.helperFrameOpenUrl,
			langId: Loc.getMessage('LANGUAGE_ID'),
		});
	}

	getBody(): HTMLElement
	{
		if (!this.node.body)
		{
			this.node.body = document.querySelector('.landing-site-copilot-body');
		}

		return this.node.body;
	}

	getContainer(): HTMLElement
	{
		return this.container;
	}

	getDefaultOptions(): Object
	{
		return {};
	}

	getStatusStart(): HTMLElement
	{
		Dom.addClass(this.container, '--compact');

		return Tag.render`
			<img class="landing-site-copilot-content-image" src="${this.rootPath}/image/landing-site-skeleton.svg" alt="">
		`;
	}

	getStatusCreate(): HTMLElement
	{
		Dom.removeClass(this.container, '--compact');

		const nodeAnimate = Tag.render`<div class="landing-site-copilot-status-image"></div>`;
		const nodeText = Tag.render`<div class="landing-site-copilot-content-text">${Loc.getMessage('LANDING_SITE_COPILOT_CREATED_1')}</div>`;

		Lottie.loadAnimation({
			container: nodeAnimate,
			renderer: 'svg',
			loop: true,
			autoplay: true,
			animationData: loaderAnimation,
		});

		let i = 2;
		const iMax = 10;
		this.interval = setInterval(() => {
			if (i > iMax)
			{
				i = 1;
			}

			let message = Loc.getMessage(`LANDING_SITE_COPILOT_CREATED_${i}`);

			if (!message)
			{
				i = 1;
				message = Loc.getMessage(`LANDING_SITE_COPILOT_CREATED_${i}`);
			}

			nodeText.innerText = message;
			i++;
		}, 4000);

		return Tag.render`
			<div class="landing-site-copilot-status-wrapper">
				${nodeAnimate}
				${nodeText}
			</div>
		`;
	}

	setStatusStart(): void
	{
		Dom.clean(this.getContainer());
		Dom.append(this.getStatusStart(), this.getContainer());
		Dom.removeClass(this.getBody(), '--created');
		clearInterval(this.interval);
	}

	setStatusCreate(): void
	{
		Dom.clean(this.getContainer());
		Dom.append(this.getStatusCreate(), this.getContainer());
		Dom.addClass(this.getBody(), '--created');
	}

	init()
	{
		this.setStatusStart();
	}
}
