;(function () {
	var BX = window.BX;
	if (BX && BX["EditForm"] && BX['EditForm']["Field"] && BX["EditForm"]["Field"]["Enum"])
		return;
	BX.namespace("BX.EditForm.Field.Enum");

	BX.EditForm.Field.Enum = (function (params) {
		this.controlNodeIdJs = params.controlNodeIdJs;
		this.valueContainerIdJs = params.valueContainerIdJs;
		this.fieldNameJs = params.fieldNameJs;
		this.isMultiple = params.isMultiple;
		this.htmlFieldNameJs = params.htmlFieldNameJs;

		this.items = JSON.parse(params.items);
		this.value = JSON.parse(params.value);
		this.params = JSON.parse(params.params);

		var uiSelectClass = 'main-ui-select';
		if (this.isMultiple){
			uiSelectClass = 'main-ui-multi-select';
		}

		BX(params.controlNodeIdJs).appendChild(
			BX.decl({
				block: uiSelectClass,
				name: this.fieldNameJs,
				items: this.items,
				value: this.value,
				params: this.params,
				valueDelete: this.isMultiple
			})
		);

		this.click = BX.delegate(this.click, this);
		BX.bind(BX(params.controlNodeIdJs), 'click', this.click);

		BX.addCustomEvent(
			window,
			'UI::Select::change',
			BX.EditForm.Field.Enum.prototype.changeHandler
		);

	});
	BX.EditForm.Field.Enum.prototype = {
		changeHandler: function (controlObject, value) {
			var currentValue = JSON.parse(controlObject.node.getAttribute('data-value'));
			var params = JSON.parse(controlObject.node.getAttribute('data-params'));
			if (params.isMulti) {
				var s = '';
				if (BX.type.isArray(currentValue)) {
					if (currentValue.length > 0) {
						for (var i in currentValue) {
							s += '<input type="hidden" name="'+controlObject.params.fieldName+'[]" value="'+BX.util.htmlspecialchars(currentValue[i].VALUE)+'" />';
						}
					} else {
						s += '<input type="hidden" name="'+controlObject.params.fieldName+'[]" value="" />';
					}

					BX(controlObject.params.fieldName + '_value').innerHTML = s;
				}
			} else {
				//if (controlObject.params.fieldName === this.fieldNameJs) {
				if (BX.type.isPlainObject(currentValue)) {
					BX(controlObject.params.fieldName + '_value').value = currentValue['VALUE'];
				}
				//}
			}
		},
		click: function () {
			this.changeHandler(
				{
					params: this.params,
					node: BX(this.controlNodeIdJs).firstChild
				}
			);
		}
	};
})();