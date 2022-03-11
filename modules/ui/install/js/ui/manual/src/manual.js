import {ajax, Type, Tag} from 'main.core';

type Params = {
	manualCode: string,
	urlParams: Object,
	analyticsLabel: Object,
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
		this.analyticsLabel = Type.isPlainObject(params.analyticsLabel) ? params.analyticsLabel : {};

		this.sidePanelId = 'manual-side-panel-' + this.manualCode;
	}

	static show(manualCode: string, urlParams = {}, analyticsLabel = {}): void
	{
		const manual = new Manual({ manualCode, urlParams, analyticsLabel });

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
				width: this.width
			}
		);
	}

	createFrame(): Promise
	{
		return new Promise((resolve, reject) => {
			ajax.runAction(
				'ui.manual.getInitParams',
				{
					data: {
						manualCode: this.manualCode,
						urlParams: this.urlParams
					},
					analyticsLabel: this.analyticsLabel
				}
			)
				.then((response: ResponseInitParams) => {
					resolve(this.renderFrame(response.data.url));
				});
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