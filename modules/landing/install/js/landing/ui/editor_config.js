;(function() {
	"use strict";

	/**
	 * Sets p tag as default paragraph tag (div by default)
	 */
	document.execCommand("defaultParagraphSeparator", false, "p");

	/**
	 * Allows setts styles as css properties
	 */
	document.execCommand("styleWithCSS", false, true);


	if (top !== window)
	{
		parent.document.addEventListener("keydown", function(event) {
			parent.BX.onCustomEvent(parent.document, "iframe:keydown", [event]);
		});
	}
})();