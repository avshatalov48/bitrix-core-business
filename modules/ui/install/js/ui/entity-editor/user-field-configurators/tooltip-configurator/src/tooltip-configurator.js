import { Type, Event, Loc, util } from 'main.core';
import { TextInput } from './text-input';

const MAX_TOOLTIP_LENGTH = 250;

export class TooltipConfigurator
{
	#id: string;
	#editor: BX.UI.EntityEditor;
	#field: BX.UI.EntityEditorUserField;
	#previousTooltip: string;
	#caption: string;
	#textInput: TextInput;
	#checkBox: HTMLElement;
	#isShowInput: boolean;
	#checkBoxHandler: function;

	constructor(
		id: string,
		editor: BX.UI.EntityEditor,
		field: BX.UI.EntityEditorUserField,
	)
	{
		this.#id = id !== '' ? id : util.getRandomString(4);
		this.#editor = editor;
		this.#field = field;

		this.#previousTooltip = this.#field?._schemeElement?._hint ?? '';
		this.#isShowInput = Boolean(this.#previousTooltip);

		this.#checkBoxHandler = this.#onCheckBoxClick.bind(this);
		this.#textInput = null;
		this.#checkBox = null;
		this.#caption = Loc.getMessage('UI_ENTITY_EDITOR_UF_CONFIGURATORS_TOOLTIP_CHECKBOX_LABEL');
	}

	validateInputText(): boolean
	{
		const input = this.getInput();
		const text = input.getInputText();

		if (text.length > MAX_TOOLTIP_LENGTH)
		{
			const errorText = Loc.getMessage(
				'UI_ENTITY_EDITOR_UF_CONFIGURATORS_TOOLTIP_LENGTH_VALIDATION_ERROR',
				{
					'#MAX_LENGTH#': MAX_TOOLTIP_LENGTH,
				},
			);
			input.renderError(errorText);

			return false;
		}

		input.renderError('');

		return true;
	}

	setCheckBox(checkBox: HTMLElement)
	{
		if (!Type.isNull(this.#checkBox))
		{
			Event.unbind(this.#checkBox, 'click', this.#checkBoxHandler);
		}

		this.#checkBox = checkBox;
		if (this.isInputEnabled())
		{
			this.#checkBox.checked = true;
		}

		if (!Type.isNull(this.#checkBox))
		{
			Event.bind(this.#checkBox, 'click', this.#checkBoxHandler);
		}
	}

	getTooltip(): string
	{
		return this.isInputEnabled() ? this.getInput().getInputText() : '';
	}

	getCaption(): string
	{
		return this.#caption;
	}

	isInputEnabled(): boolean
	{
		return this.#isShowInput;
	}

	getInput(): TextInput
	{
		if (Type.isNull(this.#textInput))
		{
			this.#textInput = new TextInput(
				this.#id,
				this,
				this.#previousTooltip,
			);
		}

		return this.#textInput;
	}

	#onCheckBoxClick(): void
	{
		this.#isShowInput = !this.#isShowInput;
		this.#textInput.adjustVisibility();
	}
}
