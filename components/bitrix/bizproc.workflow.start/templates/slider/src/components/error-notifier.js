import { Type, Tag, Dom, Text } from 'main.core';
import { Alert, AlertColor } from 'ui.alerts';

export class ErrorNotifier
{
	#errors: [] = [];
	#element: HTMLElement;

	constructor(props: { errors: []})
	{
		this.errors = props.errors;
	}

	set errors(errors: [])
	{
		if (Type.isArray(errors))
		{
			this.#errors = errors;
		}
	}

	render(): HTMLElement
	{
		this.#element = Tag.render`<div>${this.#renderErrors()}</div>`;

		return this.#element;
	}

	show(scrollToElement: boolean = true)
	{
		if (this.#element)
		{
			this.clean();
			Dom.append(this.#renderErrors(), this.#element);

			if (scrollToElement)
			{
				// eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
				BX.scrollToNode(this.#element);
			}
		}
	}

	clean()
	{
		if (this.#element)
		{
			Dom.clean(this.#element);
		}
	}

	#renderErrors(): ?HTMLElement
	{
		if (Type.isArrayFilled(this.#errors))
		{
			const message = (
				this.#errors
					.map((error) => Text.encode(error.message || ''))
					.join('<br/>')
			);

			return (new Alert({ text: message, color: AlertColor.DANGER })).render();
		}

		return null;
	}
}
