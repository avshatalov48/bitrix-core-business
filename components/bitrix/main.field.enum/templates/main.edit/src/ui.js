import {Event, Type, Text} from "main.core";
import {BaseEvent} from "main.core.events";
import {EventEmitter} from 'main.core.events'

export class Ui
{
	fieldName: string = null;
	container: HTMLElement = null;
	valueContainerId: string = null;
	value = null;
	items = null;
	defaultFieldName: string = null;
	block: string = null;
	formName: string = null;
	params = {};

	constructor(params: Params): void
	{
		this.fieldName = (params['fieldName'] || '');
		this.container = document.getElementById(params['container']);
		this.valueContainerId = (params['valueContainerId'] || '');
		this.value = params['value'];
		this.items = params['items'];
		this.block = params['block'];
		this.defaultFieldName = (params['defaultFieldName'] || this.fieldName + '_default');
		this.formName = (params['formName'] || '');
		this.params = (params['params'] || {});
		this.bindElement();
	}

	bindElement(): void
	{
		this.container.appendChild(BX.decl({
			block: this.block,
			name: this.fieldName,
			items: this.items,
			value: this.value,
			params: this.params,
			valueDelete: false
		}));

		this.onChangeHandler = this.onChange.bind(this);
		EventEmitter.subscribe('UI::Select::change', this.onChangeHandler);

		BX.bind(
			this.container,
			'click',
			BX.defer(function(){
				this.onChange({params: this.params, node: this.container.firstChild})
			}.bind(this))
		);
	}

	onChange(eventNode)
	{
		let controlObject;

		if (eventNode instanceof BaseEvent)
		{
			const data = eventNode.getData();
			controlObject = data[0];
		}
		else
		{
			controlObject = eventNode;
		}

		if (!document.getElementById(this.valueContainerId))
		{
			return;
		}

		let currentValue = null;

		if (
			controlObject.node !== null
			&& controlObject.node.getAttribute('data-name') === this.fieldName
		)
		{
			currentValue = JSON.parse(controlObject.node.getAttribute('data-value'));
		}
		else
		{
			return;
		}
		this.changeValue(currentValue);
	}

	changeValue(currentValue)
	{
		let s = '';

		if (!Type.isArray(currentValue))
		{
			if (currentValue === null)
			{
				currentValue = [{VALUE: ''}];
			}
			else
			{
				currentValue = [currentValue];
			}
		}

		if (currentValue.length > 0)
		{
			for (let i = 0; i < currentValue.length; i++)
			{
				s += `<input type="hidden" name="${this.fieldName}" value="${Text.encode(currentValue[i].VALUE)}" />`;
			}
		}
		else
		{
			s += `<input type="hidden" name="${this.fieldName}" value="" />`;
		}
		document.getElementById(this.valueContainerId).innerHTML = s;
		BX.fireEvent(document.getElementById(this.defaultFieldName), 'change');
	}
}
