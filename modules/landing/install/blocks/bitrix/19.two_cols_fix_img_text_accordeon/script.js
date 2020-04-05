;(function () {

	"use strict";


	// reinit slider after add new element in DOM
	BX.addCustomEvent("BX.Landing.Block:Card:add", function (event) {
		var newId = "accordeon-card-id" + Math.round(Math.random()*1000);

		var link = BX.findChild(event.card, {className:"landing-block-card-accordeon-element-title-link"}, true);
		if(link)
			BX.adjust(link, {attrs: {href: "#" + newId, 'aria-controls': newId}});

		var body = BX.findChild(event.card, {className:"landing-block-card-accordeon-element-body"}, true);
		if(body)
			BX.adjust(body, {attrs: {id: newId}});

		// $.HSCore.components.HSTabs.init('[role="tablist"]');
	});


	// set additional slider options after removing card
	BX.addCustomEvent("BX.Landing.Block:Card:remove", function (event) {
	});

})();