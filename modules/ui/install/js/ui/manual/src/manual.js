import { ajax, Type, Tag } from 'main.core';

type Params = {
	manualCode: string,
	urlParams?: Object,
	analyticsLabel?: Object,
	analytics?: Object,
	width?: number
}

type ResponseInitParams = {
	data: {
		url: string
	}
}

export class Manual
{
	constructor(params: Params): void
	{
		this.manualCode = Type.isString(params.manualCode) ? params.manualCode : '';
		this.width = Type.isNumber(params.width) ? params.width : 1000;
		this.urlParams = Type.isPlainObject(params.urlParams) ? params.urlParams : {};
		this.analyticsLabel = Type.isPlainObject(params.analyticsLabel) ? params.analyticsLabel : null;
		this.analytics = Type.isPlainObject(params.analytics) ? params.analytics : null;

		this.sidePanelId = `manual-side-panel-${this.manualCode}`;
	}

	static show(...args): void
	{
		let manualCode;
		let urlParams;
		let analyticsLabel;
		let analytics;

		if (Type.isPlainObject(args[0]) && args.length === 1)
		{
			({ manualCode, urlParams = {}, analyticsLabel = null, analytics = null } = args[0]);
		}
		else
		{
			[manualCode, urlParams, analyticsLabel, analytics] = args;
		}

		const manual = new Manual({
			manualCode,
			urlParams,
			analyticsLabel,
			analytics,
		});

		manual.open();
	}

	open(): void
	{
		if (this.isOpen())
		{
			return;
		}

		BX.SidePanel.Instance.open(
			this.sidePanelId,
			{
				contentCallback: () => this.createFrame(),
				width: this.width,
			},
		);
	}

	createFrame(): Promise
	{
		const config = {
			data: {
				manualCode: this.manualCode,
				urlParams: this.urlParams,
			},
		};

		if (this.analyticsLabel)
		{
			config.analyticsLabel = this.analyticsLabel;
		}
		else if (this.analytics)
		{
			config.analytics = this.analytics;
		}

		return new Promise((resolve, reject) => {
			// eslint-disable-next-line promise/catch-or-return
			ajax.runAction('ui.manual.getInitParams', config)
				.then((response: ResponseInitParams) => {
					resolve(this.renderFrame(response.data.url));
				})
			;
		});
	}

	renderFrame(url: string): HTMLElement
	{
		const frameStyles = 'position: absolute; left: 0; top: 0; padding: 0;'
			+ ' border: none; margin: 0; width: 100%; height: 100%;';

		return Tag.render`<iframe style="${frameStyles}" src="${url}"></iframe>`;
	}

	getSidePanel(): BX.SidePanel.Slider
	{
		return BX.SidePanel.Instance.getSlider(this.sidePanelId);
	}

	isOpen(): boolean
	{
		return this.getSidePanel() && this.getSidePanel().isOpen();
	}
}
