;(function(window)
{
	/****************** ATTENTION *******************************
	 * Please do not use Bitrix CoreJS in this class.
	 * This class can be called on page without Bitrix Framework
	*************************************************************/

	if (!window.BX)
	{
		window.BX = {};
	}
	else if (window.BX.message)
	{
		return;
	}

	var BX = window.BX;

	// Attention: If you change this function, dont forget to synchronize it with main/install/js/main/core/core.js:139
	BX.message = function(message)
	{
		if (message === '' || typeof message === "string" || message instanceof String)
		{
			// try to define message by event
			if (typeof BX.message[message] == "undefined" && typeof BX.onCustomEvent !== 'undefined')
			{
				BX.onCustomEvent("onBXMessageNotFound", [message]);
			}

			if (typeof BX.message[message] == "undefined")
			{
				if (typeof BX.debug !== "undefined")
				{
					BX.debug("message undefined: " + message);
				}

				BX.message[message] = "";
			}

			return BX.message[message];
		}
		else if (typeof message === 'object' && message)
		{
			for (var i in message)
			{
				if (message.hasOwnProperty(i))
				{
					BX.message[i] = message[i];
				}
			}

			return true;
		}
	};

})(window);