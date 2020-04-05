;(function ()
{
	BX.Report.Dashboard.Utils =
	{
		forEach: function(objects, callback)
		{
			Object.keys(objects).map(callback);
		},
		getClass: function(fullClassName)
		{
			if (!BX.type.isNotEmptyString(fullClassName))
			{
				return null;
			}

			var classFn = null;
			var currentNamespace = window;
			var namespaces = fullClassName.split(".");
			for (var i = 0; i < namespaces.length; i++)
			{
				var namespace = namespaces[i];
				if (!currentNamespace[namespace])
				{
					return null;
				}

				currentNamespace = currentNamespace[namespace];
				classFn = currentNamespace;
			}

			return classFn;
		},
		isDarkColor: function(hex)
		{
			if (!this.isValidColor(hex))
			{
				return false;
			}

			if (hex.length === 3)
			{
				hex = hex.replace(/([a-f0-9])/gi, "$1$1");
			}

			hex = hex.toLowerCase();
			var defaultColors = this.getDefaultColors();
			if (BX.util.in_array(hex, defaultColors))
			{
				return true;
			}

			var bigint = parseInt(hex, 16);
			var red = (bigint >> 16) & 255;
			var green = (bigint >> 8) & 255;
			var blue = bigint & 255;
			var brightness = (red * 299 + green * 587 + blue * 114) / 1000;
			return brightness < 128;
		},
		/**
		 *
		 * @returns {String[]}
		 */
		getDefaultColors: function()
		{
			return [
				"eec200",
				"00c4fb",
				"47d1e2",
				"75d900",
				"ffab00",
				"ff5752",
				"468ee5",
				"1eae43",
				"f7d622",
				"4fc3f7",
				'9dcf00',
				'f6ce00'
			];
		},
		isValidColor: function(hex)
		{
			return BX.type.isNotEmptyString(hex) && hex.match(/^([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/);
		}
	}
})();