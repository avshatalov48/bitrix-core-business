import { Tag, Text, Type } from 'main.core';
import { Icon, Main } from 'ui.icon-set.api.core';

import '../css/components/header.css';

export class Header
{
	#title: string = '';
	#description: string = '';

	constructor(config: { title: string, description: string})
	{
		if (Type.isStringFilled(config.title))
		{
			this.#title = config.title;
		}

		if (Type.isStringFilled(config.description))
		{
			this.#description = config.description;
		}
	}

	render(): HTMLElement
	{
		return Tag.render`
			<div class="bizproc__ws_start__header">
				${this.#renderIcon()}
				${this.#renderContent()}
			</div>
		`;
	}

	#renderIcon(): HTMLElement
	{
		const icon = new Icon({
			icon: Main.BUSINESS_PROCESS_1,
			size: 48,
			color: 'var(--ui-color-palette-white-base)',
		});

		return Tag.render`
			<div class="bizproc__ws_start__header-icon">
				${icon.render()}
			</div>
		`;
	}

	#renderContent(): HTMLElement
	{
		return Tag.render`
			<div class="bizproc__ws_start__header-content">
				${this.#renderTitle()}
				${this.#renderInfo()}
			</div>
		`;
	}

	#renderTitle(): HTMLElement
	{
		return Tag.render`
			<div class="bizproc__ws_start__header__title">
				${Text.encode(this.#title)}
			</div>
		`;
	}

	#renderInfo(): HTMLElement
	{
		return Tag.render`
			<div class="bizproc__ws_start__header__info">
				${Text.encode(this.#description)}
			</div>
		`;
	}
}
