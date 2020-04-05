;
(function (window)
{
	if (window.BX.clipboard) return;

	var BX = window.BX;

	BX.clipboard = {

		/**
		 * Check browser for supporting copy to clipboard feature
		 * @returns {boolean}
		 */
		isCopySupported: function()
		{
			return !!(
				!!window.getSelection && !!document.execCommand
				&& document.queryCommandSupported && document.queryCommandSupported('copy')
			);
		},



		/**
		 * Copy text to clipboard if this feature supported by browser
		 * @param text string  -  Text for copying
		 * @returns {boolean}
		 */
		copy: function(text)
		{
			if(!text)
			{
				return false;
			}
			if(!this.isCopySupported())
			{
				return false;
			}

			this._removeSelection();

			if(!this.node)
			{
				this.node = document.createElement("a");
				this.node.style.background = "Highlight";
				this.node.style.display = "none";
				document.body.appendChild(this.node);
			}
			this.node.innerText = text;

			var range = document.createRange();
			this.node.style.display = "";
			range.selectNode(this.node);
			window.getSelection().addRange(range);

			var isSuccess = false;
			try{
				isSuccess = document.execCommand('copy');
			}
			catch(err) {}

			this.node.style.display = "none";
			this._removeSelection(range);
			return isSuccess;
		},

		/**
		 * Bind callback function to click event of button.
		 * After clicking to button, shows popup with message of copying result.
		 * If param "hideIfNotSupported" is set and browser not supported feature, then button will not showed.
		 * If param "text" is set, then text will be used for copying from parameter "text".
		 * If param "text" is not set, then text will be used for copying from parameter "nodeButton".
		 *
		 * Description of parameter "params":
		 *  - params.showButtonIfNotSupported boolean  -  Show button if feature is not supported by browser. Default FALSE.
		 *  - params.text null|string|\DomElement  -  String or element with text for copying.
		 *
		 * @param nodeButton \DomElement  -  Button that will trigger an copying. If no "params.text" additionally node with text for copying.
		 * @param params object  -  Parameters
		 * @return void
		 */
		bindCopyClick: function(nodeButton, params)
		{
			params = params || {};
			showButtonIfNotSupported = params.showButtonIfNotSupported || false;
			text = params.text || null;
			popupParams = params.popup || {};

			if(BX.type.isString(nodeButton))
			{
				nodeButton = BX(nodeButton);
			}

			if(!BX.type.isElementNode(nodeButton))
			{
				return;
			}

			if(!this.isCopySupported())
			{
				if(!showButtonIfNotSupported)
				{
					nodeButton.style.display = 'none';
				}

				return;
			}

			var popupId = BX.util.getRandomString(5);
			BX.bind(
				nodeButton,
				'click',
				(function(context, popupId, nodeButton, text, popupParams){
					return function(e){
						context._onCopyClick(popupId, nodeButton, text, popupParams);
						window.BX.PreventDefault(e);
						return true;
					};
				})(this, popupId, nodeButton, text, popupParams)
			);
		},

		_removeSelection: function(range)
		{
			range = range || null;
			var currentSelection = window.getSelection();
			if(range && currentSelection.removeRange)
			{
				currentSelection.removeRange(range);
			}
			else
			{
				currentSelection.removeAllRanges();
			}
		},

		_getText: function(mixed)
		{
			if(!mixed)
			{
				return null;
			}

			if(BX.type.isFunction(mixed))
			{
				return mixed.apply(this, []);
			}
			else if(BX.type.isString(mixed))
			{
				return mixed;
			}
			else if(BX.type.isElementNode(mixed))
			{
				if((mixed.tagName == 'INPUT' || mixed.tagName == 'TEXTAREA') && mixed.value)
				{
					return mixed.value;
				}
				else if(mixed.innerText)
				{
					return mixed.innerText;
				}
			}

			return null;
		},

		_onCopyClick: function(popupId, nodeButton, text, popupParams)
		{
			this.timeoutIds = this.timeoutIds || [];
			text = this._getText(text);
			if(!text)
			{
				text = this._getText(nodeButton);
			}

			var isCopied = false;
			if(text)
			{
				isCopied = this.copy(text);
			}

			var hideTimeout = popupParams.hideTimeout || 1500;
			popupParams = BX.mergeEx(popupParams, {
				content: isCopied ? BX.message('CORE_CLIPBOARD_COPY_SUCCESS') : BX.message('CORE_CLIPBOARD_COPY_FAILURE'),
				darkMode: true,
				autoHide: true,
				zIndex: 1000,
				angle: true,
				offsetLeft: 5,
				bindOptions: {
					position: 'top'
				}
			});
			var popup = new BX.PopupWindow(
				'clipboard_copy_status_' + popupId,
				nodeButton,
				popupParams
			);
			popup.show();

			var timeoutId;
			while(timeoutId = this.timeoutIds.pop()) clearTimeout(timeoutId);
			timeoutId = setTimeout(function(){
				popup.close();
			}, hideTimeout);
			this.timeoutIds.push(timeoutId);
		}
	};

})(window);