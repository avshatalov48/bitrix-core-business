BX.CSVisibleCheckbox = function(arParams) {
	this.bindElement = arParams.bindElement;
	this.checkboxClassName = arParams.checkboxClassName;
	this.feature = arParams.feature;	
	this.hiddenName = arParams.hiddenName;	
	this.hiddenValue = arParams.hiddenValue;
	this.visibleValue = arParams.visibleValue;
	this.arCheckboxVal = arParams.arCheckboxVal;
	this.checkboxDiv = null;
	this.hidden = null;	
}

BX.CSVisibleCheckbox.prototype.Show = function()
{
	this.checkboxDiv = this.bindElement.appendChild(BX.create('SPAN', {
			props: {
				'className': this.checkboxClassName
			}
		}));
		
	this.hidden = this.checkboxDiv.appendChild(BX.create('INPUT', {
			props: {
				'type': 'hidden',		
				'name': this.hiddenName,
				'value': this.hiddenValue,
				'id': this.hiddenName
			}
		}));		
		
	this.checkboxDiv.appendChild(BX.create('SPAN', {
			props: {
				'className': 'subscribe-checkbox-icon'
			}
		}));

	this.checkboxDiv.appendChild(BX.create('SPAN', {
			props: {
				'className': 'subscribe-checkbox-text'
			},
			html: BX.message('sonetSShowInList')
		}));

	BX.bind(this.checkboxDiv, "click", BX.delegate(this.CheckboxChange, this));
}

BX.CSVisibleCheckbox.prototype.CheckboxChange = function()
{
	var newHiddenValue = null;
	var newCheckboxClassName = null;
	
	for (var i = 0; i < this.arCheckboxVal.length; i++)
	{
		if (this.arCheckboxVal[i] == this.hiddenValue)
		{
			if (i == this.arCheckboxVal.length - 1)
				newHiddenValue = this.arCheckboxVal[0];
			else
				newHiddenValue = this.arCheckboxVal[i + 1];

			break;
		}
	}
	
	BX.adjust(this.hidden, {
		props : {
			'value' : newHiddenValue
		}
	});
	this.hiddenValue = newHiddenValue;

	if (newHiddenValue == 'I')
	{
		if (this.bTopLevel)
			newCheckboxClassName = 'subscribe-checkbox subscribe-checkbox-' + this.visibleValue;
		else
			newCheckboxClassName = 'subscribe-checkbox subscribe-checkbox-i-' + this.visibleValue;
	}
	else
		newCheckboxClassName = 'subscribe-checkbox subscribe-checkbox-' + newHiddenValue;

	BX.adjust(this.checkboxDiv, {
		props : {
			'className' : newCheckboxClassName
		}
	});

}