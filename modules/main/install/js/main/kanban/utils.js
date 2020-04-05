;(function() {

"use strict";

BX.namespace("BX.Kanban");

BX.Kanban.Utils = {

	isValidId: function(id)
	{
		return BX.type.isNumber(id) || BX.type.isNotEmptyString(id);
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

	isEmptyObject: function(obj)
	{
		//noinspection LoopStatementThatDoesntLoopJS
		for (var name in obj)
		{
			return false;
		}

		return true;
	},

	isValidColor: function(hex)
	{
		return BX.type.isNotEmptyString(hex) && hex.match(/^([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/);
	},

	/**
	 *
	 * @returns {String[]}
	 */
	getDefaultColors: function()
	{
		return [
			"00c4fb",
			"47d1e2",
			"75d900",
			"ffab00",
			"ff5752",
			"468ee5",
			"1eae43"
		];
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

	showErrorDialog: function(error, fatal)
	{
		var dialog = BX.PopupWindowManager.create(
			"main-kanban-error-dialog",
			null,
			{
				titleBar: BX.message("MAIN_KANBAN_ERROR"),
				content: "",
				width: 400,
				autoHide: false,
				overlay: true,
				closeByEsc : true,
				closeIcon : true,
				draggable : { restrict : true}
			}
		);

		dialog.setContent(error);

		dialog.setButtons([
			new BX.PopupWindowButton({
				text: (fatal === true)
					? BX.message("MAIN_KANBAN_RELOAD")
					: BX.message("MAIN_KANBAN_ERROR_OK"),
				className: (fatal === true)
					? "popup-window-button-cancel"
					: "",
				events: {
					click: function()
					{
						if (fatal === true)
						{
							BX.reload();
						}
						this.popupWindow.close();
					}
				}
			})
		]);

		dialog.show();

		return dialog;
	}
};

})();

