import { Tag, Dom, Type, Text } from 'main.core';

export class ErrorsView
{
	#errors: Array<{ message: string }> = [];

	constructor(props: {
		errors: Array<{ message: string }>,
	})
	{
		if (Type.isArrayFilled(props.errors))
		{
			this.#errors = props.errors;
		}
	}

	render(): HTMLElement
	{
		return Tag.render`
			<div class="bizproc-workflow-timeline_error-wrapper">
				<div class="bizproc-workflow-timeline_error-inner">
					${this.#errors.map(({ message }) => Tag.render`
						<p class="bizproc-workflow-timeline_error-text">${Text.encode(message)}</p>
					`)}
					<div class="bizproc-workflow-timeline_error-img"></div>
				</div>
			</div>
		`;
	}

	renderTo(target: HTMLElement): void
	{
		Dom.append(this.render(), target);
	}
}
