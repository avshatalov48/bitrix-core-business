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
})();