BX.namespace("BX.Desktop.Field.Enum");

BX.Desktop.Field.Enum = function (params)
{
	this.fieldName = null;
	this.container = null;
	this.valueContainerId = null;
	this.value = null;
	this.items = null;
	this.defaultFieldName = null;
	this.block = null;
	this.formName = null;
	this.params = {};
	this.init(params);
};
BX.Desktop.Field.Enum.prototype = {
	init: function(params){
		this.fieldName = (params['fieldName'] || '');
		this.container = BX(params['container']);
		this.valueContainerId = (params['valueContainerId'] || '');
		this.value = params['value'];
		this.items = params['items'];
		this.block = params['block'];
		this.defaultFieldName = (params['defaultFieldName'] || this.fieldName+'_default');
		this.formName = (params['formName'] || '');
		this.params = (params['params'] || {});
		this.bindElement();
	},
	bindElement: function (){
		this.container.appendChild(BX.decl({
			block: this.block,
			name: this.fieldName,
			items: this.items,
			value: this.value,
			params: this.params,
			valueDelete: false
		}));

		BX.addCustomEvent(
			window,
			'UI::Select::change',
			this.onChange.bind(this)
		);

		BX.bind(
			this.container,
			'click',
			BX.defer(function(){
				this.onChange({params: this.params, node: this.container.firstChild})
			}.bind(this))
		);
	},
	onChange: function(controlObject, value)
	{
		if (!BX(this.valueContainerId))
		{
			return;
		}

		var currentValue = null;

		if(
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
	},
	changeValue: function(currentValue)
	{
		var s = '';
		if(!BX.type.isArray(currentValue))
		{
			if(currentValue === null)
			{
				currentValue = [{VALUE:''}];
			}
			else
			{
				currentValue = [currentValue];
			}
		}

		if(currentValue.length > 0)
		{
			for(var i = 0; i < currentValue.length; i++)
			{
				s += '<input type="hidden" name="'+this.fieldName+'" value="'+BX.util.htmlspecialchars(currentValue[i].VALUE)+'" />';
			}
		}
		else
		{
			s += '<input type="hidden" name="'+this.fieldName+'" value="" />';
		}
		BX(this.valueContainerId).innerHTML = s;
		BX.fireEvent(BX(this.defaultFieldName), 'change');
	}
};
