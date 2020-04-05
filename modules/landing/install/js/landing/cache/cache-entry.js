;(function() {
	"use strict";

	BX.namespace("BX.Landing.Cache");

	var encodeDataValue = BX.Landing.Utils.encodeDataValue;


	BX.Landing.Cache.Entry = function(args, value)
	{
		this.args = encodeDataValue(args);
		this.value = value;
	};

	BX.Landing.Cache.Entry.prototype = {
		has: function(args)
		{
			return encodeDataValue(args) === this.args;
		}
	};
})();