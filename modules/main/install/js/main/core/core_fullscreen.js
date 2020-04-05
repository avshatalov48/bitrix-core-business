/**
 * Full screen mode API
 */

;(function(window){

	BX.requestFullScreen = function (elem)
	{
		if (elem.requestFullscreen)
			elem.requestFullscreen();
		else if (elem.mozRequestFullScreen)
			elem.mozRequestFullScreen();
		else if (elem.webkitRequestFullscreen)
			elem.webkitRequestFullscreen();
	};

	BX.cancelFullScreen = function ()
	{
		if (document.cancelFullScreen)
			document.cancelFullScreen();
		else if (document.mozCancelFullScreen)
			document.mozCancelFullScreen();
		else if (document.webkitCancelFullScreen)
			document.webkitCancelFullScreen();
	};

	BX.getFullScreenElement = function ()
	{
		if (document.fullscreenElement)
			return document.fullscreenElement;
		else if (document.mozFullScreenElement)
			return document.mozFullScreenElement;
		else if (document.webkitFullscreenElement)
			return document.webkitFullscreenElement;
	};

})(window);