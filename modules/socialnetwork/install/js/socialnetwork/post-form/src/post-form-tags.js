import { Dom, Type } from 'main.core';

export class PostFormTags
{
	#formId: string;
	#form: HTMLElement;

	constructor(formId: string, form: HTMLElement)
	{
		if (!Type.isString(formId) || !formId)
		{
			throw new Error('BX.Socialnetwork.PostFormTags: formId not found');
		}

		if (!Type.isDomNode(form))
		{
			throw new Error('BX.Socialnetwork.PostFormTags: form not found');
		}

		this.#formId = formId;
		this.#form = form;
	}

	isFilled(): boolean
	{
		const input = this.#getInput();

		return Type.isDomNode(input) && input.value;
	}

	getValue(): string
	{
		const input = this.#getInput();

		if (!Type.isDomNode(input))
		{
			return '';
		}

		return input.value;
	}

	clear()
	{
		this.#getContainer()
			.querySelectorAll('.feed-add-post-del-but')
			.forEach((tag: HTMLElement) => {
				tag.click();
			})
		;

		this.#hideContainer();
	}

	#getInput(): ?HTMLInputElement
	{
		return this.#getContainer().querySelector('input[name="TAGS"]');
	}

	#getContainer(): HTMLElement
	{
		return this.#form.querySelector(`#post-tags-block-${this.#formId}`);
	}

	#hideContainer(): void
	{
		Dom.style(this.#getContainer(), 'display', 'none');
	}
}
