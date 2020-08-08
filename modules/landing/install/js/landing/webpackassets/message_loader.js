;(function ()
{
	"use strict";

	if (!webPacker)
	{
		return;
	}


	if (!window.BX || !BX.message)
	{
		return;
	}

	webPacker.getModules().forEach(function (mod)
	{
		var currMessages = mod.messages || {};
		var currLang = BX.message('LANGUAGE_ID');
		if (currMessages[currLang])
		{
			currMessages = currMessages[currLang]
		}
		for (var code in currMessages)
		{
			if (!currMessages.hasOwnProperty(code))
			{
				continue;
			}

			var mess = currMessages[code];
			if (typeof mess === 'undefined' || mess === '')
			{
				continue;
			}

			BX.message[code] = mess;
		}
	});
})();