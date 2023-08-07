;(function() {
	"use strict";

	BX.ready(function() {
		const elements = [].slice.call(
			document.querySelectorAll("h1, h2, h3, h4, h5, [data-auto-font-scale]")
		);
		new BX.Landing.UI.Tool.AutoFontScale(elements);
	});
})();