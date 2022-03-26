import {Loc, Dom, Tag, Event} from 'main.core';
import 'translit';
import './field.css';

export default class NameCodeField extends BX.UI.EntityEditorMultiText
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);
		this.isShownSymbolicCode = this.getSchemeShowCodeState() === 'true';
		this.allowToGenerateCode = this._editor.isNew();
	}

	getSchemeShowCodeState()
	{
		return BX.prop.get(this.getSchemeElement()._options, 'showCode');
	}

	setSchemeShowCodeState(state)
	{
		this.getSchemeElement()._options['showCode'] = state;
	}

	getValue = function()
	{
		return BX.UI.EntityEditorBoolean.superclass.getValue.apply(this);
	}

	hasContentToDisplay = function()
	{
		return true;
	}

	doPrepareContextMenuItems = function(menuItems)
	{
		if (this.isShownSymbolicCode)
		{
			menuItems.push(
				{
					value: 'hide_symbolic_code',
					text: Loc.getMessage('CATALOG_ENTITY_CARD_HIDE_SYMBOLIC_CODE'),
				}
			);
		}
		else
		{
			menuItems.push(
				{
					value: 'show_symbolic_code',
					text: Loc.getMessage('CATALOG_ENTITY_CARD_SHOW_SYMBOLIC_CODE'),
				}
			);
		}
	}

	processContextMenuCommand(e, command)
	{
		super.processContextMenuCommand(e, command);

		const codeContainerElement = document.getElementById('code_container');
		const nameCodeMarkerElement = document.getElementById('name_code_marker');

		if (command === 'hide_symbolic_code')
		{
			this.isShownSymbolicCode = false;
			this.allowToGenerateCode = this._editor.isNew();

			if(this._mode === BX.UI.EntityEditorMode.edit)
			{
				const codeTextElement = document.getElementById('code_text');
				const codeStateButtonElement = document.getElementById('code_state_button');
				codeTextElement.readOnly = this.allowToGenerateCode;
				if (this.allowToGenerateCode)
				{
					codeTextElement.className = 'ui-ctl-element ui-ctl-element-symbol-code-input-disabled';
					codeStateButtonElement.className = 'ui-ctl-before ui-ctl-icon-chain';
				}
				else
				{
					codeTextElement.className = 'ui-ctl-element';
					codeStateButtonElement.className = 'ui-ctl-before ui-ctl-icon-unchain';
				}
				codeContainerElement.className = 'name-code-container name-code-container-hidden';

				Dom.removeClass(this._innerWrapper, 'ui-entity-editor-content-block--code');
				Dom.addClass(this._innerWrapper, 'ui-entity-editor-content-block--no-code');
				nameCodeMarkerElement.style.display = 'inline';
			}
			else
			{
				this.refreshLayout();
			}
			this.setSchemeShowCodeState(false);
			this._parent.processChildControlSchemeChange(this);
		}
		else if (command === 'show_symbolic_code')
		{
			this.isShownSymbolicCode = true;
			if(this._mode === BX.UI.EntityEditorMode.edit)
			{
				codeContainerElement.className = 'name-code-container';
				Dom.removeClass(this._innerWrapper, 'ui-entity-editor-content-block--no-code');
				Dom.addClass(this._innerWrapper, 'ui-entity-editor-content-block--code');
				nameCodeMarkerElement.style.display = 'none';
			}
			else
			{
				this.refreshLayout();
			}
			this.setSchemeShowCodeState(true);
			this._parent.processChildControlSchemeChange(this);
		}
	}

	createTitleMarker()
	{
		if(this._mode === BX.UI.EntityEditorMode.view)
		{
			return null;
		}

		const display = this.isShownSymbolicCode ? 'none' : 'inline';

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			return Tag.render`<span id="name_code_marker" style="color: rgb(255, 0, 0); display: ${display};">*</span>`;
		}
	}

	layout(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ 'ui-entity-editor-field-multitext' ] });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		const title = this.getTitle();

		const values = this.getValue();
		this._inputValue = values;
		this._innerWrapper = null;
		if(this.isDragEnabled())
		{
			Dom.append(this.createDragButton(), this._wrapper);
		}

		Dom.append(this.createTitleNode(title), this._wrapper);
		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._inputContainer = Tag.render`<div></div>`;

			for (let valueKey in values)
			{
				Dom.append(this.createSingleInput(values[valueKey], valueKey), this._inputContainer);
			}

			this._innerWrapper = Tag.render`<div class="ui-entity-editor-content-block">${this._inputContainer}</div>`;
			if (this.isShownSymbolicCode)
			{
				Dom.addClass(this._innerWrapper, 'ui-entity-editor-content-block--code');
			}
			else
			{
				Dom.addClass(this._innerWrapper, 'ui-entity-editor-content-block--no-code');
			}
		}
		else
		{
			this._innerWrapper = Tag.render`
				<div class="ui-entity-editor-content-block">${this.getViewInnerLayout()}</div>
			`;
		}

		Dom.append(this._innerWrapper, this._wrapper);

		if(this.isContextMenuEnabled())
		{
			Dom.append(this.createContextMenuButton() ,this._wrapper);
		}

		if(this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	}

	validate(result)
	{
		if(this._mode !== BX.UI.EntityEditorMode.edit)
		{
			throw 'BX.UI.EntityEditorMultiText. Invalid validation context';
		}

		if(!this.isEditable())
		{
			return true;
		}

		this.clearError();

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		let isEmptyField = false;
		if(this._inputContainer)
		{
			const nameTextElement = document.getElementById('name_text');

			if (BX.util.trim(nameTextElement.value) === '')
			{
				isEmptyField = true;
				Dom.addClass(nameTextElement.parentNode, "ui-ctl-danger");
			}
			else
			{
				Dom.removeClass(nameTextElement.parentNode, "ui-ctl-danger");
			}
		}

		const isValid = !this.isRequired() || !isEmptyField;
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._input);
		}
		return isValid;
	}

	showError(error, anchor)
	{
		if(!this._errorContainer)
		{
			this._errorContainer = Tag.render`<div class="ui-entity-editor-field-error-text"></div>`;
		}

		this._errorContainer.innerHTML = BX.util.htmlspecialchars(error);
		if (this._wrapper)
		{
			Dom.append(this._errorContainer, this._wrapper);
		}
		this._hasError = true;
	}

	createSingleInput(value, name)
	{
		const inputWrapper = Tag.render`
			<div id="${name.toLowerCase()}_container"></div>
		`;
		const inputContainer = Tag.render`
			<div class="ui-ctl ui-ctl-w100 ui-ctl-textbox"></div>
		`;

		let input;
		if (this.getLineCount() > 1)
		{
			input = Tag.render`
				<textarea
					class="ui-ctl-element ui-entity-editor-field-textarea"
					name="${name}"
					id="${name.toLowerCase() + '_text'}"
					rows="${this.getLineCount()}">${BX.util.htmlspecialchars(value) || ''}</textarea>
			`;
		}
		else
		{
			input = Tag.render`
				<input
					class="ui-ctl-element"
					name="${name}"
					id="${name.toLowerCase() + '_text'}"
					type="text"
					value="${BX.util.htmlspecialchars(value) || ''}"/>
			`;
		}

		Event.bind(input, 'input', this.#onInputHandler.bind(this, name));

		if (name === 'CODE')
		{
			if (!this.isShownSymbolicCode)
			{
				Dom.addClass(inputWrapper, 'name-code-container-hidden');
			}

			if (this.allowToGenerateCode === true)
			{
				Dom.addClass(input, 'ui-ctl-element-symbol-code-input-disabled');
				Dom.attr(input, 'readonly', this.allowToGenerateCode);
			}

			Dom.addClass(inputContainer, 'ui-ctl-ext-before-icon');
			Dom.addClass(inputWrapper, 'name-code-container');

			const chainState = this.allowToGenerateCode ? 'chain' : 'unchain';
			const button = Tag.render`
				<button name="${name}" class="ui-ctl-before ui-ctl-icon-${chainState}" id="code_state_button"></button>
			`;
			Event.bind(button, 'click', this.#onCodeStateButtonClick.bind(this));
			Dom.append(button, inputContainer);
		}

		const label = this.#creatLabelForEditMode(name);

		Dom.append(label, inputWrapper);
		Dom.append(input, inputContainer);
		Dom.append(inputContainer, inputWrapper);

		return inputWrapper;
	}

	#creatLabelForEditMode(name)
	{
		const label = Tag.render`<label class="ui-entity-editor-block-title"></label>`;
		let labelText;

		if (name === 'CODE')
		{
			labelText = Tag.render`<span>${Loc.getMessage('CATALOG_ENTITY_CARD_SYMBOLIC_CODE')}</span>`;
			Dom.append(labelText, label);
			Dom.append(this.#getHintNode(), label);
		}
		else
		{
			labelText = Tag.render`
				<span>
					${Loc.getMessage('CATALOG_ENTITY_CARD_NAME')}
					<span style="color: rgb(255, 0, 0);">*</span>
				</span>
			`;
			Dom.append(labelText, label);
		}

		return label;
	}

	#onInputHandler(name)
	{
		this._changeHandler();
		if (this.allowToGenerateCode && name === 'NAME')
		{
			const codeTextElement = document.getElementById('code_text');
			const nameTextElement = document.getElementById('name_text');
			codeTextElement.value = BX.translit(nameTextElement.value, null);
		}
	}

	#getHintNode()
	{
		return BX.UI.Hint.createNode(Loc.getMessage('CATALOG_ENTITY_CARD_SYMBOLIC_CODE_HINT'));
	}

	#onCodeStateButtonClick()
	{
		const codeTextElement = document.getElementById('code_text');
		const nameTextElement = document.getElementById('name_text');
		const codeStateButtonElement = document.getElementById('code_state_button');

		this.allowToGenerateCode = !this.allowToGenerateCode;
		codeTextElement.readOnly = this.allowToGenerateCode;

		if (this.allowToGenerateCode)
		{
			codeTextElement.className = 'ui-ctl-element ui-ctl-element-symbol-code-input-disabled';
			codeStateButtonElement.className = 'ui-ctl-before ui-ctl-icon-chain';
			codeTextElement.value = BX.translit(nameTextElement.value, null);
		}
		else
		{
			codeTextElement.className = 'ui-ctl-element';
			codeStateButtonElement.className = 'ui-ctl-before ui-ctl-icon-unchain';

			const nameTextElement = document.getElementById('name_text');
			const newValue = BX.translit(nameTextElement.value, null);

			if (codeTextElement.value !== newValue)
			{
				this.markAsChanged();
			}
			codeTextElement.value = newValue;
		}
	}

	getViewInnerLayout()
	{
		const textValue = Tag.render`
			<div class="ui-entity-editor-content-block-text"></div>
		`;

		const values = this.getValue();

		if (!this.isShownSymbolicCode)
		{
			Dom.append(Tag.render`<p>${BX.util.htmlspecialchars(values.NAME)}</p>`, textValue);

			return textValue;
		}

		Dom.append(Tag.render`
			<div class="ui-entity-editor-symbol-code-label">
				${Loc.getMessage('CATALOG_ENTITY_CARD_NAME')}
			</div>
		`, textValue);

		Dom.append(Tag.render`<p>${BX.util.htmlspecialchars(values.NAME)}</p>`, textValue);

		Dom.addClass(textValue, 'ui-entity-editor-symbol-code');
		const codeValue = values.CODE === '' ? Loc.getMessage('UI_ENTITY_EDITOR_FIELD_EMPTY') : values.CODE;
		const chainClass = this.allowToGenerateCode
			? 'ui-entity-editor-symbol-code-value-chain'
			: 'ui-entity-editor-symbol-code-value-unchain'
		;

		Dom.append(Tag.render`
			<div class="ui-entity-editor-symbol-code-box">
				<div class="ui-entity-editor-symbol-code-label">
					${Loc.getMessage('CATALOG_ENTITY_CARD_SYMBOLIC_CODE')}
				</div>
				<div class="ui-entity-editor-symbol-code-value ${chainClass}">
					${BX.util.htmlspecialchars(codeValue)}
				</div>
			</div>
		`, textValue);

		return textValue;
	}
}