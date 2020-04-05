	;(function() {

		"use strict";

		/** @requires module:webpacker */
		/** @var {Object} module Current module.*/
		if(typeof webPacker === "undefined")
		{
			return;
		}

		function Example()
		{

		}
		Example.prototype.init = function ()
		{
			var popup = webPacker.getModule('ui.webpacker.example.popup').properties['popup'];

			var infoNode = document.createElement('div');
			infoNode.textContent = module.name + ' properties: ' + JSON.stringify(module.properties);
			popup.appendChild(infoNode);
		};

		window.WebPackerExample = new Example;

	})();