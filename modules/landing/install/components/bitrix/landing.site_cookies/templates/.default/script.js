(function() {

	'use strict';

	BX.namespace('BX.Landing');

	/**
	 * Constructor for helper.
	 * @param params
	 * @constructor
	 */
	BX.Landing.SiteCookies = function(params)
	{
		this.bbFormAjaxPath = params.bbFormAjaxPath;
		this.idAgreementNew = params.idAgreementNew ? BX(params.idAgreementNew) : null;
		this.classNameAgreementBlock = params.classNameAgreementBlock || 'landing-agreement-block';
		this.classNameAgreementDelete = params.classNameAgreementDelete || 'landing-agreement-delete';
		this.classNameAgreementAdd = params.classNameAgreementAdd || 'landing-agreement-add';
		this.classNameEditIcon = params.classNameEditIcon || 'landing-agreement-edit';
		this.classCloseWarningIcon = params.classCloseWarningIcon || 'landing-agreement-warning-close';
		this.classInputBlock = params.classInputBlock || 'landing-agreement-input-block';
		this.classEditTitle = params.classEditTitle || 'landing-agreement-cookies-name-edit';
		this.classBlockAreaShow = params.classBlockAreaShow || 'landing-agreement-block-inner-show';
		this.inputBlocks = document.querySelectorAll('.landing-agreement-input');
		this.messages = params.messages || {};
		this.input = null;

		this.binding();
		this.bindEvents();
	};

	BX.Landing.SiteCookies.prototype =
	{
		/**
		 * Binding some actions.
		 */
		binding: function()
		{
			// remove cookie buttons
			var deleteButtons = [].slice.call(
				document.querySelectorAll('.' + this.classNameAgreementDelete)
			);
			deleteButtons.map(function(deleteButton)
			{
				if (!BX.hasClass(deleteButton, 'landing-binding'))
				{
					BX.addClass(deleteButton, 'landing-binding');
					BX.bind(deleteButton, 'click', function(event)
					{
						BX.UI.Dialogs.MessageBox.confirm(
							this.messages.removeAlertText,
							this.messages.removeAlertTitle,
							function(messageBox) {
								this.removeAgreement(deleteButton);
								messageBox.close();
							}.bind(this)
						);
					}.bind(this));
				}
			}.bind(this));

			// add cookie button
			var addButton = document.querySelector('.' + this.classNameAgreementAdd);
			if (addButton)
			{
				if (!BX.hasClass(addButton, 'landing-binding'))
				{
					BX.addClass(addButton, 'landing-binding');
					BX.bind(addButton, 'click', function(event)
					{
						this.addNewAgreement(addButton);
					}.bind(this));
				}
			}
		},

		bindEvents: function()
		{
			// edit blocks
			[].forEach.call(this.inputBlocks, function(item) {
				item.addEventListener('click', BX.delegate(this.toggleSection, this));

				var block = item.parentNode.nextElementSibling;
				if (block.classList.contains(this.classBlockAreaShow))
				{
					setTimeout(function() {
						block.style.height = block.children[0].offsetHeight + 'px';
					}, 1200);
				}
			}.bind(this));

			var editIcons = document.querySelectorAll('.' + this.classNameEditIcon);
			[].forEach.call(editIcons, function(icon) {
				icon.addEventListener('click', this.showInput.bind(this))
			}.bind(this));

			var closeButton = document.getElementById(this.classCloseWarningIcon);
			if (closeButton)
			{
				closeButton.addEventListener('click', this.closeWarning.bind(this))
			}
		},

		/**
		 * Turns to edit mode on of block title.
		 */
		showInput: function(event)
		{
			this.parent = event.currentTarget.parentNode;
			this.input = event.currentTarget.previousElementSibling;
			this.input.value = this.input.previousElementSibling.textContent;
			this.input.focus();
			this.parent.classList.add(this.classEditTitle);

			BX.bind(document, 'mousedown', this.hideInput.bind(this));
		},

		/**
		 * Turns to edit mode off of block title.
		 */
		hideInput: function(event)
		{
			if (event.target === this.input)
			{
				return;
			}

			this.input.previousElementSibling.textContent = this.input.value;
			this.input.setAttribute('value', this.input.previousElementSibling.textContent);
			this.parent.classList.remove(this.classEditTitle);
			BX.unbind(document, 'mousedown', this.hideInput);
		},

		/**
		 * Hides warning block.
		 */
		closeWarning: function(event)
		{
			var warningBlock = event.currentTarget.closest('.ui-alert');
			warningBlock.remove();
		},

		/**
		 * Toggles block.
		 */
		toggleSection: function(event)
		{
			var hiddenBlock = event.currentTarget.parentNode.nextElementSibling;
			var checkbox = event.currentTarget;

			if (hiddenBlock.classList.contains(this.classBlockAreaShow))
			{
				checkbox.checked = false;
				hiddenBlock.style.height = 0;
				hiddenBlock.classList.remove(this.classBlockAreaShow);
			}
			else
			{
				checkbox.checked = true;
				hiddenBlock.style.height = hiddenBlock.children[0].offsetHeight + 'px';
				hiddenBlock.classList.add(this.classBlockAreaShow);
			}
		},

		/**
		 * Creates new agreement form.
		 * @param {HTMLElement} addButton Add button node.
		 */
		addNewAgreement: function(addButton)
		{
			if (!this.bbFormAjaxPath)
			{
				return;
			}
			BX.ajax({
				url: this.bbFormAjaxPath,
				method: 'GET',
				onsuccess: function(data)
				{
					if (this.idAgreementNew)
					{
						var newForm = BX.create('div', {
							html: data
						});
						this.idAgreementNew.append(newForm);
					}
					this.binding();
				}.bind(this)
			});
		},

		/**
		 * Removes agreement block.
		 * @param {HTMLElement} removeNode Remove button node.
		 */
		removeAgreement: function(removeNode)
		{
			var node = removeNode.closest('.' + this.classNameAgreementBlock);
			while (node)
			{
				if (BX.hasClass(node, this.classNameAgreementBlock))
				{
					BX.remove(node);
					break;
				}
			}
		}
	};

})();