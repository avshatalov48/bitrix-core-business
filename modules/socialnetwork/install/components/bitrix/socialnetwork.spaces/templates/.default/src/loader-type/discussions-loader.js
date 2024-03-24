import { Tag } from 'main.core';
import { DefaultLoader } from './default-loader';

export class DiscussionsLoader extends DefaultLoader
{
	render(): HTMLElement
	{
		return Tag.render`
			<div
				class="sn-spaces__content-loader-container sn-spaces__content-loader-discussions"
			></div>
		`;
	}
}
