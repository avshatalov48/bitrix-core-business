import {Tag} from 'main.core';

export default function layout()
{
	const container = Tag.render`
		<div class="main-ui-loader main-ui-hide">
			<svg class="main-ui-loader-svg" viewBox="25 25 50 50">
				<circle class="main-ui-loader-svg-circle" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10">
			</svg>
		</div>
	`;

	const circle = container.querySelector('.main-ui-loader-svg-circle');

	return {container, circle};
}