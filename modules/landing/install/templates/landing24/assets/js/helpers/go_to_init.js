;(function() {
	"use strict";

	BX.ready(function(){
		if (BX.hasClass(document.body, 'g-upper-show'))
		{
			// add button
			document.body.appendChild(
				BX.create("A", {
					'html' : '<i class="hs-icon hs-icon-arrow-top"></i>',
					'props' : {'className' : 'js-go-to u-go-to-v1'},
					'attrs' : {
						'data-type' : 'fixed',
						'data-position' : '{"bottom": 15, "left": 15}',
						'data-offset-top' : '400',
						'data-compensation' : '.u-header',
						'data-show-effect' : 'zoomIn'
					}
				})
			);

			// init button
			$.HSCore.components.HSGoTo.init('.js-go-to');
		}

	});

})();