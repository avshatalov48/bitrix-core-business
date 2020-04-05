;(function(window) {

	if (BX.Scale) return;

	BX.Scale = {
		isObjEmpty: function(o)
		{
			var c = 0,
				result = false;

			for (var k in o)
				c++;

			if(c == 0)
				result = true;

			return result;
		}
	};

})(window);
