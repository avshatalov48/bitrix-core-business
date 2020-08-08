import Element from "./components/element";
import "./css/autocomplete.css";
import { Popup } from 'main.popup';

const CLASSES = {
	select: "main-ui-control",
	dropdownShown: "autocomplete-select--opened",
	multiselect: "main-ui-multi-select",
	label: "main-ui-square-container",
	placeholder: "autocomplete-placeholder",
	dropdown: "popup-select-content",
	option: "main-ui-select-inner-item",
	remove: "main-ui-item-icon main-ui-square-delete",
	optionDisabled: "autocomplete-option--disabled",
	autocompleteInput: "main-ui-control main-ui-control-string",
	selectedLabel: "main-ui-square",
	selectedOption: "autocomplete-option--selected",
	placeholderHidden: "autocomplete-placeholder--hidden",
	optionHidden: "autocomplete-option--hidden"
};

class Autocomplete
{
	constructor(element, config)
	{
		this._config = {
			...config,
			classNames: {
				...CLASSES,
				...config.classNames
			},
			disabledOptions: []
		};
		this._state = {
			opened: false
		};
		this._icons = [];
		this._holderElement = element;

		this._boundHandleClick = this._handleClick.bind(this);
		this._boundUnselectOption = this._unselectOption.bind(this);
		this._boundSortOptions = this._sortOptions.bind(this);

		this._body = new Element(document.body);

		this._create(element);

		if (!this._config.value)
		{
			return;
		}
		this._setValue();
	}

	setOptions(data)
	{
		data.forEach(value => {
			let exists = false;
			this._config.options.forEach(option => {
				if (value.id === option.id)
				{
					exists = true;
				}
			});
			if (!exists)
			{
				this._config.options.push(value);
			}
		});
		this._options = this._generateOptions();
	}

	value()
	{
		return this._config.value;
	}

	reset()
	{
		this._config.value = this._config.multiple ? [] : null;
		this._setValue();
	}

	// Private methods
	_create(_element)
	{
		const element = typeof _element === "string" ? document.querySelector(_element) : _element;

		this._parent = new Element(element);

		const selector = element.querySelectorAll(
			`div[data-name=${element.dataset.name}]`
		)[0];
		let selectClone = selector.cloneNode(true);
		element.removeChild(selector);

		this._select = new Element(selectClone);
		this._label = new Element("span", { class: this._config.classNames.label });
		this._optionsWrapper = new Element("div", { class: this._config.classNames.dropdown });

		if (this._config.multiple)
		{
			this._select.addClass(this._config.classNames.multiselect);
		}

		this._options = this._generateOptions();

		this._select.addEventListener("click", this._boundHandleClick);
		this._select.append(this._label.get());

		let deleteButton = this._parent.get().parentNode.querySelectorAll('div.main-ui-control-value-delete');
		if(deleteButton.length > 0)
		{
			BX.bind(deleteButton[0], "click", this.reset.bind(this));
			this._select.append(deleteButton[0]);
		}

		this._parent.append(this._select.get());
		this._placeholder = new Element("div",
			{
				class: this._config.classNames.placeholder,
				textContent: this._config.placeholder
			}
		);
		this._select.append(this._placeholder.get());

		this._popup = new Popup({
			id: "autocomplete" + Math.random(),
			bindElement: _element,
			zIndex: 3000,
			width: 515,
			maxHeight: 300
		});

		this._popup.setContent(this._optionsWrapper.get());
	}

	_generateOptions()
	{
		if (this._config.autocomplete && !this._autocomplete)
		{
			this._autocomplete = new Element("input", {
				class: this._config.classNames.autocompleteInput,
				name: `autocomplete-${this._parent.get().dataset.name}`,
				type: "text"
			});
			this._autocomplete.addEventListener("input", this._boundSortOptions);

			this._optionsWrapper.append(this._autocomplete.get());
		}

		return this._config.options.map(_option => {
			let preOption =
				document
					.querySelectorAll(
						`div.${this._config.classNames.option}[data-value="${_option.id}"]`
					);
			if (preOption.length > 0)
			{
				return new Element(preOption[0]);
			}

			const option = new Element("div", {
				class: `${this._config.classNames.option}${_option.disabled ?
					" " + this._config.classNames.optionDisabled : ""}`,
				value: _option.id,
				textContent: _option.name,
				disabled: _option.disabled
			});
			if (_option.disabled)
			{
				this._config.disabledOptions.push(String(_option.id));
			}
			this._optionsWrapper.append(option.get());

			return option;
		});
	}

	_handleClick(event)
	{
		event.stopPropagation();

		if (event.target.className === this._config.classNames.autocompleteInput)
		{
			return;
		}

		if (this._state.opened)
		{
			const option = this._options.find(_option => {
				return _option.get() === event.target;
			});

			if (option)
			{
				this._setValue(option.get().getAttribute("data-value"), true);
			}

			this._popup.close();
			this._select.removeClass(this._config.classNames.dropdownShown);
			this._body.removeEventListener("click", this._boundHandleClick);
			this._select.addEventListener("click", this._boundHandleClick);

			this._state.opened = false;
			return;
		}

		if (event.target.className === this._config.icon)
		{
			return;
		}

		this._popup.show();
		this._select.addClass(this._config.classNames.dropdownShown);
		this._body.addEventListener("click", this._boundHandleClick);
		this._select.removeEventListener("click", this._boundHandleClick);

		this._state.opened = true;

		if (this._autocomplete)
		{
			this._autocomplete.focus();
		}
	}

	_prepareDataValue()
	{
		let dataValue = [];

		this._config.options.forEach(_option => {
			this._config.value.forEach(_value => {
				if(_option.id.toString() === _value)
				{
					dataValue.push(
						{
							NAME: _option.name,
							VALUE: _option.id.toString()
						}
					)
				}
			});
		})

		this._parent.get().dataset.value = JSON.stringify(dataValue);
		this._select.get().dataset.value = JSON.stringify(dataValue);

		return dataValue;
	}

	_setValue(value, manual, unselected)
	{
		if (this._config.disabledOptions.indexOf(value) > -1)
		{
			return;
		}
		if (value && !unselected)
		{
			this._config.value = this._config.multiple ? [...this._config.value || [], value] : value;
		}
		if (value && unselected)
		{
			this._config.value = value;
		}
		this._options.forEach(_option => {
			_option.removeClass(this._config.classNames.selectedOption);
		});
		this._placeholder.removeClass(this._config.classNames.placeholderHidden);

		if (this._config.multiple)
		{
			const options = this._config.value.map(_value => {
				const option = this._config.options.find(_option => {
					return _option.id.toString() === _value;
				});

				if (!option)
				{
					return false;
				}
				const optionNode = this._options.find(
					_option => {
						return _option.get().getAttribute("data-value") === option.id.toString();
					}
				);

				optionNode.addClass(this._config.classNames.selectedOption);

				return option;
			});

			if (options.length)
			{
				this._placeholder.addClass(this._config.classNames.placeholderHidden);
			}
			this._selectOptions(options, manual);
			this._prepareDataValue();
			return;
		}

		const option = this._config.value ?
			this._config.options.find(_option => _option.id.toString() === this._config.value) :
			this._config.options[0];

		const optionNode = this._options.find(
			_option => _option.get().getAttribute("data-value") === option.id.toString()
		);

		this._prepareDataValue();
		if (!this._config.value)
		{
			this._label.setText("");
			return;
		}
		optionNode.addClass(this._config.classNames.selectedOption);
		this._placeholder.addClass(this._config.classNames.placeholderHidden);
		this._selectOption(option, manual);
	}

	_selectOption(option, manual)
	{
		this._selectedOption = option;

		this._label.setText(option.name);

		if (this._config.onChange && manual)
		{
			this._config.onChange(option.id,this._prepareDataValue());
		}
	}

	_selectOptions(options, manual)
	{
		this._label.setText("");

		this._icons = options.map(_option => {
			const selectedLabel = new Element("span", {
				class: this._config.classNames.selectedLabel,
				textContent: _option.name
			});

			const remove = new Element("span", {
				class: `${this._config.classNames.remove}`,
				value: _option.id
			});

			remove.addEventListener("click", this._boundUnselectOption);

			selectedLabel.append(remove.get());
			this._label.append(selectedLabel.get());

			return remove.get();
		});

		if (manual)
		{
			// eslint-disable-next-line no-magic-numbers
			this._optionsWrapper.setTop(Number(this._select.getHeight().split("px")[0]) + 5);
		}

		if (this._config.onChange && manual)
		{
			this._config.onChange(this._config.value, this._prepareDataValue());
		}
	}

	_unselectOption(event)
	{
		const newValue = [...this._config.value];
		const index = newValue.indexOf(event.target.getAttribute("data-value"));

		// eslint-disable-next-line no-magic-numbers
		if (index !== -1)
		{
			newValue.splice(index, 1);
		}

		this._setValue(newValue, true, true);
	}

	_sortOptions(event)
	{
		this._options.forEach(_option => {
			if (!_option.get().textContent.toLowerCase().startsWith(event.target.value.toLowerCase()))
			{
				_option.addClass(this._config.classNames.optionHidden);
				return;
			}
			_option.removeClass(this._config.classNames.optionHidden);
		});
	}
}

export default Autocomplete;
