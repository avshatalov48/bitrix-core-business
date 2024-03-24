import { Tag } from 'main.core';

export class DefaultLoader
{
	render(): HTMLElement
	{
		return Tag.render`
			<div class="sn-spaces__content-loader-default-container">
				${this.#renderSvg()}
			</div>
		`;
	}

	#renderSvg(): HTMLElement
	{
		return Tag.render`
			<svg class="sn-spaces__content-loader-circular" viewBox="25 25 50 50">
				<circle
					class="sn-spaces__content-loader-path"
					cx="50"
					cy="50"
					r="20"
					fill="none"
					stroke-miterlimit="10"
				/>
			</svg>
		`;
	}
}