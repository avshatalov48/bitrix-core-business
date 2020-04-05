
BX.saleAccountPay = (function()
{
	var classDescription = function(params)
	{
		this.messages = params.alertMessages || {};
		this.nameValue = params.nameValue || "buyMoney";
		this.ajaxUrl = params.url;
		this.signedParams = params.signedParams || {};
		this.wrapperId = params.wrapperId || "";
		this.templateFolder = params.templateFolder;
		this.wrapper = document.getElementById('bx-sap'+ this.wrapperId);

		this.changeInputContainer = this.wrapper.getElementsByClassName('sale-accountpay-fixedpay-container')[0];
		this.paySystemsContainer = this.wrapper.getElementsByClassName('sale-accountpay-pp')[0];
		this.inputElement = this.wrapper.getElementsByClassName('sale-accountpay-input')[0];
		this.submitButton = this.wrapper.getElementsByClassName('btn')[0];
		this.checkboxList = this.wrapper.getElementsByClassName('sale-accountpay-pp-company');

		BX.ready(BX.proxy(this.init, this));
	};
	
	classDescription.prototype.init = function()
	{
		this.checkboxList[0].checked = "checked";

		BX.bindDelegate(this.paySystemsContainer, 'click', { 'className': 'sale-accountpay-pp-company' }, BX.proxy(
			function(event)
			{
				var oldChosenCheckboxList = this.wrapper.querySelectorAll('.sale-accountpay-pp-company-checkbox:checked');
				Array.prototype.forEach.call(oldChosenCheckboxList, function(checkbox)
				{
					checkbox.checked = false;
					checkbox.parentNode.parentNode.classList.remove('bx-selected');
				});
				var parent = event.target.parentNode;
				parent.classList.add('bx-selected');
				parent.parentNode.classList.add('bx-selected');
				parent.querySelector('.sale-accountpay-pp-company-checkbox').checked = 'checked';
				return this;
			}, this)
		);

		BX.bind(this.inputElement, 'input', BX.delegate(
			function ()
			{
				this.inputElement.value = this.inputElement.value.replace(/[^\d,.]*/g, '')
					.replace(/\,/g, '.')
					.replace(/([,.])[,.]+/g, '$1')
					.replace(/^[^\d]*(\d+([.,]\d{0,5})?).*$/g, '$1');
			}, this)
		);
		
		BX.bindDelegate(this.changeInputContainer, 'click', { 'className': 'sale-accountpay-fixedpay-item' }, BX.proxy(
			function(event)
			{
				this.inputElement.value = parseInt(event.target.innerText);
			}, this)
		);

		BX.bind(this.submitButton, 'click', BX.delegate(
			function(event)
			{
				event.preventDefault();
				if (parseFloat(this.inputElement.value) <= 0 || this.inputElement.value == "")
				{
					window.alert( BX.util.htmlspecialchars(this.messages.wrongInput));
					return false;
				}
				this.startLoader();
				BX.ajax(
					{
						method: 'POST',
						dataType: 'html',
						url: this.ajaxUrl,
						data:
						{
							sessid: BX.bitrix_sessid(),
							buyMoney: this.inputElement.value,
							paySystemId: this.wrapper.querySelector('.sale-accountpay-pp-company-checkbox:checked').value,
							signedParamsString: this.signedParams
						},
						onsuccess: BX.proxy(function(result)
						{
							while (this.wrapper.firstChild)
							{
								this.wrapper.removeChild(this.wrapper.firstChild);
							}
							this.wrapper.innerHTML = result;
							this.endLoader();
						},this),
						onfailure: BX.proxy(function()
						{
							return this;
						}, this)
					}, this
				);
				this.destroy();
			}, this)
		);
		return this;
	};

	classDescription.prototype.destroy = function()
	{
		this.messages = null;
		this.nameValue = null;
		this.signedParams = null;
		this.changeInputContainer = null;
		this.paySystemsContainer = null;
		this.inputElement = null;
		this.submitButton = null;
		this.checkboxList = null;
	};

	/**
	 * Showing loader image with overlay.
	 */
	classDescription.prototype.startLoader = function()
	{
		if (this.BXFormPosting === true)
		{
			return false;
		}

		this.BXFormPosting = true;

		if (!this.loadingScreen)
		{
			this.loadingScreen = new BX.PopupWindow("loading_screen", this.submitButton,
			{
				overlay: {backgroundColor: 'white', opacity: '80'},
				autoHide : true,
				events:
				{
					onAfterPopupShow: BX.proxy(function()
					{
						BX.cleanNode(this.loadingScreen.popupContainer);
						this.loadingScreen.popupContainer.appendChild(
							BX.create('IMG', {props: {src: this.templateFolder + "/images/loader.gif"}})
						);
						this.loadingScreen.popupContainer.removeAttribute('style');
						this.loadingScreen.popupContainer.style.display = 'block';
					}, this)
				}
			});
		}

		return this.loadingScreen.show();
	};

	/**
	 * Hiding loader image with overlay.
	 */
	classDescription.prototype.endLoader = function(loaderTimer)
	{
		this.BXFormPosting = false;

		if (this.loadingScreen && this.loadingScreen.isShown())
		{
			this.loadingScreen.close();
		}

		clearTimeout(loaderTimer);
	};

	return classDescription;
})();