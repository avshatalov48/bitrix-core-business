;(function () {
	BX.ready(function()
	{
		function PlaceHolderSelectorList(editor, wrap)
		{
			// Call parent constructor
			PlaceHolderSelectorList.superclass.constructor.apply(this, arguments);
			this.id = 'placeholder_selector';
			this.title = BX.message('BXEdPlaceHolderSelectorTitle');
			this.action = 'insertHTML';
			this.zIndex = 3008;

			this.placeHolderList = [];
			editor.On('PlaceHolderSelectorListCreate', [this]);

			this.disabledForTextarea = false;
			this.arValues = [];

			for (var i in this.placeHolderList)
			{
				var value = this.placeHolderList[i];
				value.value = '#' + value.CODE + '#';
				this.arValues.push(
					{
						id: value.CODE,
						name: value.NAME,
						topName: BX.message('BXEdPlaceHolderSelectorTitle'),
						title: value.value + ' - ' + value.DESC,
						className: '',
						style: '',
						action: 'insertHTML',
						value: value.value
					}
				);
			}

			this.Create();
			this.pCont.innerHTML = BX.message('BXEdPlaceHolderSelectorTitle');

			if (wrap)
			{
				wrap.appendChild(this.GetCont());
			}
		}

		BX.extend(PlaceHolderSelectorList, window.BXHtmlEditor.DropDownList);
		window.BXHtmlEditor.Controls['placeholder_selector'] = PlaceHolderSelectorList;
	});
})();