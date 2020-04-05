/*** Settings for sidepanel in admin panel ***/
(function() {
	var iframeMode = window !== window.top;
	var search = window.location.search;
	var sliderMode = search.indexOf("IFRAME=") !== -1 || search.indexOf("IFRAME%3D") !== -1;

	if (iframeMode && sliderMode)
	{
		return;
	}

	if (!BX.SidePanel.Instance)
	{
		return;
	}

	//todo

})();