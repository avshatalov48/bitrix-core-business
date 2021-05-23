BX.Default.Field.Address = function (params)
{
	this.init(params);
};

BX.Default.Field.Address.prototype = {
	init: function (params)
	{
		this.controlId = (params['controlId'] || '');
		this.value = (params['value'] || '');
		this.isMultiple = (params['isMultiple'] === 'true');
		this.nodeJs = (params['nodeJs'] || '');
		this.fieldNameJs = (params['fieldNameJs'] || '');

		let control = new BX.Fileman.UserField.Address(BX(this.controlId),
			{
				value: this.value,
				multiple: this.isMultiple
			}
		);

		control.nodeJs = this.nodeJs;
		control.fieldNameJs = this.fieldNameJs;

		BX.addCustomEvent(control, 'UserFieldAddress::Change', function (value)
		{
			let node = BX(control.nodeJs);
			let html = '';
			if (value.length === 0)
			{
				value = [{text: ''}];
			}

			for (let i = 0; i < value.length; i++)
			{
				let inputValue = value[i].text;

				if (!!value[i].coords)
				{
					inputValue += '|' + value[i].coords.join(';');
				}

				inputValue = BX.util.htmlspecialchars(inputValue);
				html += `<input type="hidden" name="${control.fieldNameJs}" value="${inputValue}" >`;
			}

			node.innerHTML = html;
		});
	},
};