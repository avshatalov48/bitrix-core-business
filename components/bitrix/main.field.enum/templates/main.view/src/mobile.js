import {Loc} from 'main.core';

const nodeEnum = (function ()
{
	const nodeEnum = function (select, container, isInlineEdit)
	{
		this.click = BX.delegate(this.click, this);
		this.callback = BX.delegate(this.callback, this);
		this.multiple = false;
		this.select = null;
		this.container = null;
		this.isInlineEdit = null;
		this.titles = [];
		this.values = [];
		this.defaultTitles = [];
		this.init(select, container, isInlineEdit);
	};
	nodeEnum.prototype = {
		init: function (select, container, isInlineEdit = true)
		{
			if (BX(select) && BX(container))
			{
				this.select = select;
				this.container = container;
				this.isInlineEdit = isInlineEdit;
				if (!this.select.hasAttribute('bx-bound'))
				{
					this.select.setAttribute('bx-bound', 'Y');
					BX.addCustomEvent(select, 'onChange', BX.delegate(function ()
					{
						this.multiple = this.select.hasAttribute('multiple');
						this.initValues();
					}, this));
					BX.bind(this.container, 'click', this.click);
				}
				this.multiple = select.hasAttribute('multiple');
				this.initValues();
			}
		},
		initValues: function ()
		{
			this.titles = [];
			this.values = [];
			this.defaultTitles = [];
			for (let ii = 0; ii < this.select.options.length; ii++)
			{
				this.titles.push(this.select.options[ii].textContent.trim());
				this.values.push(this.select.options[ii].value);
				if (this.select.options[ii].hasAttribute('selected'))
				{
					this.defaultTitles.push(this.select.options[ii].innerHTML);
				}
			}
		},
		click: function (e)
		{
			this.show();
			return BX.PreventDefault(e);
		},
		show: function ()
		{
			if (this.titles.length > 0)
			{
				BXMobileApp.UI.SelectPicker.show({
					callback: this.callback,
					values: this.titles,
					multiselect: this.multiple,
					default_value: this.defaultTitles
				});
			}
		},
		callback: function (data)
		{
			this.defaultTitles = [];
			if (data && data.values && data.values.length > 0)
			{
				let keys = [];
				for (let ii = 0; ii < this.titles.length; ii++)
				{
					for (let jj = 0; jj < data.values.length; jj++)
					{
						if (this.titles[ii] === data.values[jj])
						{
							keys.push(this.values[ii]);
							this.defaultTitles.push(this.titles[ii]);
							break;
						}
					}
				}
				let html = '';
				for (let ii = 0; ii < this.select.options.length; ii++)
				{
					this.select.options[ii].removeAttribute('selected');
					if (BX.util.in_array(this.select.options[ii].value, keys))
					{
						this.select.options[ii].setAttribute('selected', 'selected');
						if (this.multiple)
						{
							html += this.select.options[ii].innerHTML + '<br>';
						}
						else
						{
							html = this.select.options[ii].innerHTML;
						}
					}
				}
				if (html === '' && !this.multiple)
				{
					html = `<span style="color:grey">${Loc.getMessage('interface_form_select')}</span>`;
				}
				this.container.innerHTML = html;
			}
			else
			{
				for (let ii = 0; ii < this.select.options.length; ii++)
				{
					this.select.options[ii].removeAttribute('selected');
				}

				this.container.innerHTML = Loc.getMessage('USER_TYPE_ENUM_NO_VALUE');
			}

			if (this.isInlineEdit)
			{
				BX.onCustomEvent(this, 'onChange', [this, this.select]);
			}
		}
	};
	return nodeEnum;
})();
window.app.exec('enableCaptureKeyboard', true);

BX.Mobile.Field.Enum = function (params)
{
	this.init(params);
};
BX.Mobile.Field.Enum.prototype = {
	__proto__: BX.Mobile.Field.prototype,
	bindElement: function (node)
	{
		let result = null;
		if (BX(node))
		{
			result = new nodeEnum(
				node,
				BX(`${node.id}_select`),
				(node.dataset.isInlineEdit !== 'false')
			);
		}
		return result;
	},
};
