;(function() {
	"use strict";

	BX.ready(function() {
		var elements = [].slice.call(
			document.querySelectorAll("h1, h2, h3, h4, h5, [data-auto-font-scale]")
		);
		new BX.Landing.UI.Tool.autoFontScale(elements);
	});
})();