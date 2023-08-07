;(function(){

	BX.addCustomEvent('BX.UI.EntityConfigurationManager:onCreateClick', function(e) {
		e.data.isCanceled = true;

		const editor = BX.UI.EntityEditor.getDefault();
		const createUrl = editor.getConfigurationFieldManager().getCreationPageUrl('custom');

		if (createUrl)
		{
			top.BX.SidePanel.Instance.open(createUrl);
		}
	})

})();
