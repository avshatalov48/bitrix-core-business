import { Tag } from 'main.core';
import { DefaultLoader } from './default-loader';

export class TasksScrumPlanLoader extends DefaultLoader
{
	#pageView: string;

	constructor(pageView: string)
	{
		super(pageView);

		this.#pageView = pageView;
	}

	render(): HTMLElement
	{
		return Tag.render`
			<div
				class="sn-spaces__content-loader-container sn-spaces__content-loader-${this.#pageView}"
			></div>
		`;
	}
}
