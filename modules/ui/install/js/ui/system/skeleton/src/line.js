import { Tag } from 'main.core';

import './style.css';

export const Line = (width: number, height: number, radius: number): HTMLElement => {
	const style = Object.entries({ width, height, radius })
		.map(([prop, value]) => (value ? `--${prop}: ${value}px;` : ''))
	;

	return Tag.render`<div class="ui-skeleton" style="${style.join('')}"></div>`;
};
