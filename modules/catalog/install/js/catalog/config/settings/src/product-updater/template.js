import { ProductSettingsUpdater } from './updater';
import { Dom, Loc, Tag } from 'main.core';
import 'ui.progressbar';

export default class
{
	#settings: Object;
	#currentIblockName = null;
	#allCount = 0;
	#doneCount = 0;
	#onComplete: function;

	#elements = {};

	constructor(props)
	{
		this.#settings = props.settings;
		this.#onComplete = props.onComplete;

		(new ProductSettingsUpdater({
			settings: this.#settings,
			events: {
				onProgress: (data) => {
					this.#currentIblockName = data.currentIblockName;
					this.#allCount = data.allCnt;
					this.#doneCount = data.doneCnt;
					this.#redraw();
				},
				onComplete: () => {
					this.#onComplete();
				},
			},
		})).startOperation();
	}

	#getProgressWidth(): string
	{
		let width = 0;
		if (this.#allCount > 0)
		{
			width = Math.round((this.#doneCount / this.#allCount) * 100);
		}

		return `${width}%`;
	}

	#redraw()
	{
		this.#elements.text.innerHTML = Loc.getMessage('CAT_CONFIG_SETTINGS_OUT_OF')
			.replace('#PROCESSED#', this.#doneCount)
			.replace('#TOTAL#', this.#allCount)
		;
		this.#elements.currentIblock.innerHTML = Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_SETTINGS_CURRENT_CATALOG')
			.replace('#CATALOG_NAME#', this.#currentIblockName)
		;
		Dom.style(this.#elements.progressBar, 'width', this.#getProgressWidth());
	}

	render(): HTMLElement
	{
		const processedText = Loc.getMessage('CAT_CONFIG_SETTINGS_OUT_OF')
			.replace('#PROCESSED#', this.#doneCount)
			.replace('#TOTAL#', this.#allCount)
		;
		this.#elements.text = Tag.render`
			<div class="ui-progressbar-text-after">
				${processedText}
			</div>
		`;
		this.#elements.currentIblock = Tag.render`
			<div style="padding-top: 10px;">
			</div>
		`;
		this.#elements.progressBar = Tag.render`
			<div class="ui-progressbar-bar"></div>
		`;
		Dom.style(this.#elements.progressBar, 'width', this.#getProgressWidth());

		return Tag.render`
			<div>
				<div class="ui-progressbar ui-progressbar-column">
					<div style="font-weight: bold;" class="ui-progressbar-text-before">
						${Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_SETTINGS_UPDATE_TITLE')}
					</div>
					<div class="ui-progressbar-track">
						${this.#elements.progressBar}
					</div>
					${this.#elements.text}
				</div>
				<div style="color: rgb(83, 92, 105); font-size: 12px;">
					${Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_SETTINGS_UPDATE_WAIT')}
					${this.#elements.currentIblock}
				</div>
			</div>
		`;
	}
}
