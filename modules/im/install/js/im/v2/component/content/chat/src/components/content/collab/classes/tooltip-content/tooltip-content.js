import { Tag } from 'main.core';

import { IconKey } from './icon-key';

import './css/tooltip-content.css';

type CollabTooltipParams = {
	title: string,
	text: string,
	iconKey: $Values<typeof IconKey>,
}

export class CollabTooltipContent
{
	#title: string;
	#text: string;
	#iconKey: $Values<typeof IconKey>;

	constructor(params: CollabTooltipParams)
	{
		this.#title = params.title;
		this.#text = params.text;
		this.#iconKey = params.iconKey;
	}

	render(): HTMLElement
	{
		return Tag.render`
			<div class="bx-im-collab-tooltip__scope bx-im-collab-tooltip__container">
				<div class="bx-im-collab-tooltip__icon --${this.#iconKey}"></div>
				<div class="bx-im-collab-tooltip__title-container">
					<div class="bx-im-collab-tooltip__title">
						${this.#title}
					</div>
					<div class="bx-im-collab-tooltip__text">
						${this.#text}
					</div>
				</div>
			</div>
		`;
	}
}
