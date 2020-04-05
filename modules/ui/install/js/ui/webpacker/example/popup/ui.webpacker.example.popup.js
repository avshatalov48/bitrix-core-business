	;(function() {

		"use strict";

		/** @requires module:webpacker */
		/** @var {Object} module */
		if(typeof webPacker === "undefined")
		{
			return;
		}

		module.properties.popup = document.getElementById('ui-webpacker-example-popup');

		var langNode = document.createElement('div');
		langNode.textContent = 'languages: ' + module.languages.join(', ');
		module.properties.popup.appendChild(langNode);
	})();