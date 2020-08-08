this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,landing_sliderhacks) {
	'use strict';

	main_core.Event.bind(document, 'click', function (event) {
	  if (main_core.Type.isDomNode(event.target)) {
	    var link = event.target.closest('a:not(.ui-btn):not([data-fancybox])');

	    if (main_core.Type.isDomNode(link)) {
	      if (main_core.Type.isStringFilled(link.href) && link.target !== '_blank') {
	        event.preventDefault();
	        void landing_sliderhacks.SliderHacks.reloadSlider(link.href);
	      }
	    }

	    var pseudoLink = event.target.closest('[data-pseudo-url]');

	    if (main_core.Type.isDomNode(pseudoLink)) {
	      var urlParams = main_core.Dom.attr(pseudoLink, 'data-pseudo-url');

	      if (main_core.Text.toBoolean(urlParams.enabled) && main_core.Type.isStringFilled(urlParams.href)) {
	        if (urlParams.query) {
	          urlParams.href += urlParams.href.indexOf('?') === -1 ? '?' : '&';
	          urlParams.href += urlParams.query;
	        }

	        if (urlParams.target === '_self') {
	          event.stopImmediatePropagation();
	          void landing_sliderhacks.SliderHacks.reloadSlider(urlParams.href);
	        } else {
	          top.open(urlParams.href, urlParams.target);
	        }
	      }
	    }
	  }
	});

}((this.BX.Landing.Wiki = this.BX.Landing.Wiki || {}),BX,BX.Landing));
//# sourceMappingURL=public.bundle.js.map
