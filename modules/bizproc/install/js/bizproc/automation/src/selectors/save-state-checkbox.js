import { SelectorContext } from 'bizproc.automation';

export class SaveStateCheckbox
{
	#context: SelectorContext;
	#checkbox: Element;
	#needSync: boolean;

	constructor(props: {
		context: SelectorContext,
		checkbox: Element,
		needSync: boolean,
	})
	{
		this.#context = props.context;
		this.#checkbox = props.checkbox;
		this.#needSync = props.needSync;

		if (props.needSync)
		{
			const category = 'save_state_checkbox';
			const savedState = this.#context.get('userOptions').get(category, this.#getKey(), 'N');
			if (savedState === 'Y')
			{
				this.#checkbox.checked = true;
			}
		}
	}

	destroy(): void
	{
		if (this.#needSync)
		{
			this.#context.get('userOptions').set('save_state_checkboxes', this.#getKey(), this.#getValue());
		}
	}

	#getKey(): ?string
	{
		return this.#checkbox.getAttribute('data-save-state-key');
	}

	#getValue(): string
	{
		return this.#checkbox.checked ? 'Y'	 : 'N';
	}
}