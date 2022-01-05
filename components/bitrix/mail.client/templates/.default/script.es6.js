import { MessageGrid } from 'mail.messagegrid';

;(function() {

	if (window.BXMailMessageController)
		return;

	var BXMailMessageController = {};

	BXMailMessageController.init = function (options)
	{
		if (this.__inited)
			return;

		this.options = options;

		this.__dummyNode = document.createElement('DIV');

		if ('edit' != this.options.type)
		{
			if (this.options.pageSize < 1 || this.options.pageSize > 100)
				this.options.pageSize = 5;

			this.__log = {'a': 0, 'b': 0};

			var details = BX('mail-msg-view-details-'+this.options.messageId);

			var moreA = BX.findChildByClassName(details.parentNode, 'mail-msg-view-log-more-a', true);
			BX.bind(moreA, 'click', this.handleLogClick.bind(this, 'a'));

			var moreB = BX.findChildByClassName(details.parentNode, 'mail-msg-view-log-more-b', true);
			BX.bind(moreB, 'click', this.handleLogClick.bind(this, 'b'));

			var items = BX.findChildrenByClassName(details.parentNode, 'mail-msg-view-log-item', true);
			for (var i in items)
			{
				var log = items[i].getAttribute('data-log').toLowerCase();
				if (typeof this.__log[log] != 'undefined')
					this.__log[log]++;

				BX.bind(items[i], 'click', this.handleLogItemClick.bind(this, items[i].getAttribute('data-id')));
			}

			BX.Event.EventEmitter.subscribe(
				'BXMailMessageActions:CRM_ACTIVITY',
				function (event)
				{
					BX.show(
						BX.findChildByClassName(
							BX('mail-msg-view-details-' + event.getData().messageId),
							'js-msg-view-control-skip',
							true
						)
					);
				}
			);

			BX.Event.EventEmitter.subscribe(
				'BXMailMessageActions:CRM_EXCLUDE',
				function (event)
				{
					BX.hide(
						BX.findChildByClassName(
							BX('mail-msg-view-details-' + event.getData().messageId),
							'js-msg-view-control-skip',
							true
						)
					);
				}
			);
		}

		this.__inited = true;
	};

	BXMailMessageController.initScrollable = function()
	{
		if (!this.__scrollable)
		{
			if (document.scrollingElement)
				this.__scrollable = document.scrollingElement;
		}

		if (!this.__scrollable)
		{
			if (document.documentElement.scrollTop > 0 || document.documentElement.scrollLeft > 0)
				this.__scrollable = document.documentElement;
			else if (document.body.scrollTop > 0 || document.body.scrollLeft > 0)
				this.__scrollable = document.body;
		}

		if (!this.__scrollable)
		{
			window.scrollBy(1, 1);

			if (document.documentElement.scrollTop > 0 || document.documentElement.scrollLeft > 0)
				this.__scrollable = document.documentElement;
			else if (document.body.scrollTop > 0 || document.body.scrollLeft > 0)
				this.__scrollable = document.body;

			window.scrollBy(-1, -1);
		}

		return this.__scrollable;
	}

	BXMailMessageController.scrollWrapper = function (pos)
	{
		var ctrl = this;

		if (!this.initScrollable())
			return;

		if (this.__scrollable.__animation)
		{
			clearInterval(this.__scrollable.__animation);
			this.__scrollable.__animation = null;
		}

		var start = this.__scrollable.scrollTop;
		var delta = pos - start;
		var step = 0;
		this.__scrollable.__animation = setInterval(function()
		{
			step++;
			ctrl.__scrollable.scrollTop = start + delta * step/8;

			if (step >= 8)
			{
				clearInterval(ctrl.__scrollable.__animation);
				ctrl.__scrollable.__animation = null;
			}
		}, 20);
	};

	BXMailMessageController.scrollTo = function (node1, node2)
	{
		if (!this.initScrollable())
			return;

		var pos0 = BX.pos(this.__scrollable);

		pos0.top    += this.__scrollable.scrollTop;
		pos0.bottom += this.__scrollable.scrollTop;

		var pos1 = BX.pos(node1);
		var pos2 = typeof node2 == 'undefined' || node2 === node1 ? pos1 : BX.pos(node2);

		if (pos1.top < pos0.top)
		{
			this.scrollWrapper(this.__scrollable.scrollTop - (pos0.top - pos1.top));
		}
		else if (pos2.bottom > pos0.bottom)
		{
			this.scrollWrapper(Math.min(
				this.__scrollable.scrollTop - (pos0.top - pos1.top),
				this.__scrollable.scrollTop + (pos2.bottom - pos0.bottom)
			));
		}
	};

	BXMailMessageController.handleLogClick = function (log, event)
	{
		BX.PreventDefault(event);

		var button = BX.findChildByClassName(
			BX('mail-msg-view-details-'+this.options.messageId).parentNode,
			'mail-msg-view-log-more-'+log,
			true
		);
		this.loadLog(log, button);
	};

	BXMailMessageController.loadLog = function (log, button)
	{
		var ctrl = this;

		var separator = button.parentNode;

		if (this['__loadingLog'+log])
			return;

		this['__loadingLog'+log] = true;

		var data = {
			sessid: BX.bitrix_sessid(),
			action: 'log',
			id: this.options.messageId,
			log: log + this.__log[log],
			size: this.options.pageSize
		};

		if (this.options.mail_uf_message_token)
		{
			data.mail_uf_message_token = this.options.mail_uf_message_token;
		}

		BX.ajax({
			method: 'POST',
			url: BX.util.add_url_param(this.options.ajaxUrl, {'action': 'log'}),
			data: data,
			dataType: 'json',
			onsuccess: function(json)
			{
				ctrl['__loadingLog'+log] = false;

				if (json.status == 'success')
				{
					ctrl.__dummyNode.innerHTML = json.data.html;

					var marker = log == 'a' ? BX.findNextSibling(separator, {'tag': 'div'}) : separator;
					while (ctrl.__dummyNode.childNodes.length > 0)
					{
						var item = separator.parentNode.insertBefore(ctrl.__dummyNode.childNodes[0], marker);
						if (item.nodeType == 1 && BX.hasClass(item, 'mail-msg-view-log-item'))
						{
							ctrl.__log[log]++;

							BX.addClass(item, 'mail-msg-show-animation-rev');
							BX.bind(item, 'click', ctrl.handleLogItemClick.bind(ctrl, item.getAttribute('data-id')));
						}
					}

					if (json.data.count < ctrl.options.pageSize)
						separator.style.display = 'none';

					if (log == 'b' && ctrl.initScrollable())
					{
						ctrl.scrollWrapper(ctrl.__scrollable.scrollHeight);
					}

					ctrl.__dummyNode.innerHTML = '';
				}
			},
			onfailure: function()
			{
				ctrl['__loadingLog'+log] = false;
			}
		});
	};

	BXMailMessageController.handleLogItemClick = function (messageId, event)
	{
		event = event || window.event;
		if (event.target && event.target.tagName && event.target.tagName.toUpperCase() == 'A')
			return;

		if (window.getSelection)
		{
			if (window.getSelection().toString().trim() != '')
				return;
		}
		else if (document.selection)
		{
			if (document.selection.createRange().htmlText.trim() != '')
				return;
		}

		BX.PreventDefault(event);

		this.toggleLogItem(messageId);
	};

	BXMailMessageController.toggleLogItem = function (messageId)
	{
		var ctrl = this;

		var wrapper = BX('mail-msg-view-details-'+this.options.messageId).parentNode;

		var logItem = BX.findChildByClassName(wrapper, 'mail-msg-view-logitem-'+messageId, false);
		var details = BX.findChildByClassName(wrapper, 'mail-msg-view-details-'+messageId, false);

		var opened  = BX.hasClass(logItem, 'mail-msg-view-item-open');

		BX.removeClass(logItem, 'mail-msg-show-animation-rev');
		BX.toggleClass(logItem, 'mail-msg-view-item-open');

		if (opened)
		{
			details.style.display = 'none';

			BX.addClass(logItem, 'mail-msg-show-animation-rev');
			logItem.style.display = '';
		}
		else
		{
			BX.removeClass(details, 'mail-msg-show-animation-rev');
			BX.addClass(details, 'mail-msg-show-animation');
			details.style.display = '';

			if (details.getAttribute('data-empty'))
			{
				var data = {
					sessid: BX.bitrix_sessid(),
					action: 'logitem',
					id: messageId
				};

				if (this.options.mail_uf_message_token)
				{
					data.mail_uf_message_token = this.options.mail_uf_message_token;
				}

				BX.ajax({
					method: 'POST',
					url: BX.util.add_url_param(this.options.ajaxUrl, {'action': 'logitem'}),
					data: data,
					dataType: 'json',
					onsuccess: function (json)
					{
						if (json.status != 'success')
						{
							json.errors = json.errors.map(
								function (item)
								{
									return item.message;
								}
							);
							details.innerHTML = '<div class="mail-msg-view-log-item-error mail-msg-view-border-bottom">'
								+ json.errors.join('<br>')
								+ '</div>';

							return;
						}

						var response = BX.processHTML(json.data);

						BX.removeClass(details, 'mail-msg-show-animation');
						BX.removeClass(details, 'mail-msg-show-animation-rev');
						setTimeout(function ()
						{
							details.style.textAlign = '';
							details.innerHTML = response.HTML;

							if (details.offsetHeight > 0)
								logItem.style.display = 'none';

							BX.ajax.processScripts(response.SCRIPT);

							BX.addClass(details, 'mail-msg-show-animation-rev');

							var button = BX.findChildByClassName(details, 'mail-msg-view-header', true);
							BX.bind(button, 'click', ctrl.handleLogItemClick.bind(ctrl, messageId));

							ctrl.scrollTo(details);
						}, 10);

						details.removeAttribute('data-empty');
					}
				});

				this.scrollTo(logItem, details);
			}
			else
			{
				logItem.style.display = 'none';

				this.scrollTo(details);
			}
		}
	};

	BXMailMessageController.removeLogItem = function (messageId)
	{
		var wrapper = BX('mail-msg-view-details-'+this.options.messageId).parentNode;

		var logItem = BX.findChildByClassName(wrapper, 'mail-msg-view-logitem-'+messageId, false);
		var details = BX.findChildByClassName(wrapper, 'mail-msg-view-details-'+messageId, false);

		var log = logItem.getAttribute('data-log').toLowerCase();
		if (typeof this.__log[log] != 'undefined')
			this.__log[log]--;

		setTimeout(function()
		{
			wrapper.removeChild(details);
			wrapper.removeChild(logItem);
		}, 200);

		details.style.maxHeight = (details.offsetHeight*1.5)+'px';
		details.style.transition = 'max-height .2s ease-in';
		details.offsetHeight;
		details.style.maxHeight = '0px';

		BX.removeClass(details, 'mail-msg-show-animation');
		BX.removeClass(details, 'mail-msg-show-animation-rev');
		BX.addClass(details, 'mail-msg-close-animation');
	};

	BXMailMessageController.close = function (destroy)
	{
		var slider = top.BX.SidePanel.Instance.getSliderByWindow(window);
		if (slider)
		{
			slider.setCacheable(!destroy);
			slider.close();
		}
		else
		{
			window.location.href = BX.util.add_url_param(
				this.options.pathList,
				{ 'strict': 'N' }
			);
		}
	};

	var BXMailMessage = function (options)
	{
		var self = this;

		this.ctrl = BXMailMessageController;
		this.options = options;

		this.__dummyNode = document.createElement('DIV');

		this.htmlForm = BX(this.options.formId);
		this.htmlForm.__wrapper = this.htmlForm.parentNode;

		if (this.htmlForm.__inited)
			return;

		if ('edit' != this.ctrl.options.type)
		{
			this.__wrapper = BX('mail-msg-view-details-'+this.ctrl.options.messageId);
			if (this.options.messageId != this.ctrl.options.messageId)
				this.__wrapper = BX.findChildByClassName(this.__wrapper.parentNode, 'mail-msg-view-details-'+this.options.messageId, false);

			BX.addCustomEvent(
				'MailMessage:replyButtonClick',
				function (source)
				{
					if (source !== self)
						self.hideReplyForm();
				}
			);
			top.BX.SidePanel.Instance.postMessage(
				window,
				'mail-message-view',
				{
					id: this.options.messageId
				}
			);
			var emailContainerId = 'mail_msg_'+this.options.messageId+'_body';

			// target links
			var emailLinks = typeof document.querySelectorAll != 'undefined'
				? document.querySelectorAll('#'+emailContainerId+' a')
				: BX.findChildren(BX(emailContainerId), {tag: 'a'}, true);
			for (var i in emailLinks)
			{
				if (!emailLinks.hasOwnProperty(i))
					continue;

				if (emailLinks[i] && emailLinks[i].setAttribute)
					emailLinks[i].setAttribute('target', '_blank');
			}

			// unfold quotes
			var quotesList = typeof document.querySelectorAll != 'undefined'
				? document.querySelectorAll('#'+emailContainerId+' blockquote')
				: BX.findChildren(BX(emailContainerId), {tag: 'blockquote'}, true);
			for (var i in quotesList)
			{
				if (!quotesList.hasOwnProperty(i))
					continue;

				BX.bind(quotesList[i], 'click', function ()
				{
					BX.addClass(this, 'mail-msg-view-quote-unfolded');
				});
			}

			// show hidden rcpt items
			var rcptMore = BX.findChildrenByClassName(this.__wrapper, 'mail-msg-view-rcpt-more');
			for (var i in rcptMore)
			{
				BX.bind(rcptMore[i], 'click', function (event)
				{
					BX.findChildByClassName(this.parentNode, 'mail-msg-view-rcpt-list-hidden', false).style.display = 'inline';
					this.style.display = 'none';

					BX.PreventDefault(event);
				});
			}

			var replyButton  = BX.findChildByClassName(this.__wrapper, 'js-msg-view-reply-panel', true);
			var replyLink    = BX.findChildByClassName(this.__wrapper, 'js-msg-view-control-reply', true);
			var replyAllLink = BX.findChildByClassName(this.__wrapper, 'js-msg-view-control-replyall', true);
			var forwardLink  = BX.findChildByClassName(this.__wrapper, 'js-msg-view-control-forward', true);
			var skipLink     = BX.findChildByClassName(this.__wrapper, 'js-msg-view-control-skip', true);
			var spamLink     = BX.findChildByClassName(this.__wrapper, 'js-msg-view-control-spam', true);
			var deleteLink   = BX.findChildByClassName(this.__wrapper, 'js-msg-view-control-delete', true);

			BX.bind(replyButton, 'click', this.showReplyForm.bind(this));
			BX.bind(replyAllLink, 'click', this.showReplyForm.bind(this));
			BX.bind(replyLink, 'click', this.showReplyForm.bind(this, true));

			BX.bind(forwardLink, 'click', function ()
			{
				var params = {
					forward: self.options.messageId
				};
				if (self.ctrl.options.mail_uf_message_token)
				{
					params.mail_uf_message_token = self.ctrl.options.mail_uf_message_token;
				}

				window.location.href = BX.util.add_url_param(self.ctrl.options.pathNew, params);
			});

			BX.bind(
				skipLink,
				'click',
				function (event)
				{
					BXMailMessageActions && BXMailMessageActions.createAction(
						event,
						{
							messageId: self.options.messageId,
							value: 'CRM_EXCLUDE',
							disable: BX.addClass.bind(BX, skipLink, 'mail-msg-view-control-disabled'),
							enable: BX.removeClass.bind(BX, skipLink, 'mail-msg-view-control-disabled')
						}
					);
				}
			);

			var uidKeyData = document.querySelector('[data-uid-key]');
			var uid = 0;
			if (uidKeyData)
			{
				uid = uidKeyData.dataset.uidKey;
			}
			BX.bind(spamLink, 'click', this.markAsSpam.bind(this, spamLink, uid));
			BX.bind(deleteLink, 'click', this.delete.bind(this, deleteLink, uid));
		}

		var mailForm = BXMainMailForm.getForm(this.options.formId);

		BX.addCustomEvent(mailForm, 'MailForm:footer:buttonClick', BXMailMessage.handleFooterButtonClick.bind(this));
		BX.addCustomEvent(mailForm, 'MailForm:submit', BXMailMessage.handleFormSubmit.bind(this));
		BX.addCustomEvent(mailForm, 'MailForm:submit:ajaxSuccess', BXMailMessage.handleFormSubmitSuccess.bind(this));
		BX.PULL && BX.PULL.extendWatch('mail_mailbox_' + this.options.messageId);
		BX.addCustomEvent("onPullEvent-mail", BX.proxy(function(command, params)
		{
			//outgoing message read confirmed handler
			if (command !== 'onMessageRead')
			{
				return;
			}
			var wrapper = BX('mail-msg-view-details-' + params.messageId);
			if(!wrapper)
			{
				wrapper = BX.findChildByClassName(document, 'mail-msg-view-logitem-' + params.messageId, true);
			}
			if(!wrapper)
			{
				return;
			}
			var items = BX.findChildrenByClassName(wrapper, 'read-confirmed-datetime', true);
			if (items && items.length > 0)
			{
				for (var i in items)
					BX.adjust(items[i], {text: BX.message('MAIL_MESSAGE_READ_CONFIRMED_SHORT')});
			}
		}, this));

		this.htmlForm.__inited = true;
	};

	BXMailMessage.handleFooterButtonClick = function (form, button)
	{
		if (BX.hasClass(button, 'main-mail-form-cancel-button'))
		{
			if ('edit' == this.ctrl.options.type)
			{
				this.ctrl.close();
			}
			else
			{
				this.hideReplyForm();
			}
		}
	};

	BXMailMessage.handleFormSubmit = function (form, event)
	{
		var fields = this.htmlForm.elements;
		var emptyRcpt = true;
		for (var i = 0; i < fields.length; i++)
		{
			if ('data[to][]' == fields[i].name && fields[i].value.length > 0)
				emptyRcpt = false;
		}
		if (emptyRcpt)
		{
			// @TODO: hide on select
			form.showError(BX.message('MAIL_MESSAGE_NEW_EMPTY_RCPT'));
			return BX.PreventDefault(event);
		}

		// @TODO: use events
		var uploads, items, totalSize = 0;
		for (var i in form.postForm.controllers)
		{
			if (!form.postForm.controllers.hasOwnProperty(i))
				continue;

			if (form.postForm.controllers[i].storage != 'disk')
				continue;

			try
			{
				uploads = 0;
				uploads = form.postForm.controllers[i].handler.agent.upload.filesCount;
			}
			catch (err) {}

			if (uploads > 0)
			{
				// @TODO: hide on complete
				form.showError(BX.message('MAIL_MESSAGE_NEW_UPLOADING'));
				return BX.PreventDefault(event);
			}

			if (BX.message('MAIL_MESSAGE_MAX_SIZE') > 0)
			{
				try
				{
					items = form.postForm.controllers[i].handler.agent.queue.items.items;
					totalSize = Object.keys(items).reduce(
						function (sum, k)
						{
							return sum + (items[k].file ? parseInt(items[k].file.sizeInt || items[k].file.size) : 0);
						},
						totalSize
					);
				}
				catch (err) {}
			}
		}

		if (BX.message('MAIL_MESSAGE_MAX_SIZE') > 0 && BX.message('MAIL_MESSAGE_MAX_SIZE') <= Math.ceil(totalSize / 3) * 4) // base64 coef.
		{
			form.showError(BX.message('MAIL_MESSAGE_MAX_SIZE_EXCEED'));
			return BX.PreventDefault(event);
		}
	};

	BXMailMessage.handleFormSubmitSuccess = function (form, data)
	{
		if (data.status != 'success')
		{
			var errorNode = document.createElement('DIV');

			if (!data.errors || !BX.type.isArray(data.errors))
			{
				data.errors = [{
					message: BX.message('MAIL_CLIENT_AJAX_ERROR'),
					code: 0
				}];
			}
			for (var i = 0; i < data.errors.length; i++)
			{
				errorNode.appendChild(document.createTextNode(data.errors[i].message));
				errorNode.appendChild(document.createElement('BR'));
			}

			form.showError(errorNode.innerHTML);
		}
		else
		{
			if ('edit' != this.ctrl.options.type)
			{
				this.hideReplyForm();
			}

			top.BX.SidePanel.Instance.postMessage(window, 'Mail.Client.MessageCreatedSuccess', data);

			top.BX.UI.Notification.Center.notify({
				autoHideDelay: 2000,
				content: BX.message('MAIL_MESSAGE_SEND_SUCCESS')
			});

			this.ctrl.close(true);
		}
	};

	BXMailMessage.prototype.showReplyForm = function (min)
	{
		var mailForm = BXMainMailForm.getForm(this.options.formId);
		var replyButton = BX.findChildByClassName(this.__wrapper, 'js-msg-view-reply-panel', true);

		if (this.htmlForm.parentNode === this.__dummyNode)
			this.htmlForm.__wrapper.appendChild(this.htmlForm);

		mailForm.init();

		if (min === true)
		{
			mailForm.getField('data[to]').setValue(this.options.rcptSelected);
			mailForm.getField('data[cc]').setValue();
		}
		else
		{
			mailForm.getField('data[to]').setValue(this.options.rcptAllSelected);
			mailForm.getField('data[cc]').setValue(this.options.rcptCcSelected);
		}

		mailForm.getField('data[bcc]').setValue();

		BX.onCustomEvent('MailMessage:replyButtonClick', [this]);

		BX.addClass(this.htmlForm, 'mail-msg-show-animation');
		this.htmlForm.style.display = '';

		replyButton.style.display = 'none';

		BX.onCustomEvent(mailForm, 'MailForm:show', []);

		this.ctrl.scrollTo(this.htmlForm);
	};

	BXMailMessage.prototype.hideReplyForm = function ()
	{
		var mailForm = BXMainMailForm.getForm(this.options.formId);
		var replyButton = BX.findChildByClassName(this.__wrapper, 'js-msg-view-reply-panel', true);

		BX.addClass(replyButton, 'mail-msg-show-animation-rev');
		replyButton.style.display = '';

		this.htmlForm.style.display = 'none';

		BX.onCustomEvent(mailForm, 'MailForm:hide', []);

		this.__dummyNode.appendChild(this.htmlForm);
	};

	BXMailMessage.prototype.markAsSpam = function (btn, uid)
	{
		btn.classList.add('mail-msg-view-control-disabled');
		BX.ajax.runComponentAction('bitrix:mail.client', 'markAsSpam', {
			mode: 'ajax',
			data: {ids: [uid]}
		}).then(
			this.onMessageActionSuccess.bind(this, btn),
			function (response)
			{
				this.onMessageActionError.bind(this, response)()
			}.bind(this)
		);
	};

	BXMailMessage.prototype.delete = function (btn, uid)
	{
		if (btn.dataset && btn.dataset.isTrash)
		{
			if (!this.popupDeleteConfirm)
			{
				var buttons = [
					new BX.PopupWindowButton({
						text: BX.message("MAIL_MESSAGE_LIST_CONFIRM_CANCEL_BTN"),
						className: "popup-window-button-cancel",
						events: {
							click: BX.delegate(function ()
							{
								this.popupDeleteConfirm.close();
							}, this)
						}
					}),
					new BX.PopupWindowButton({
						text: BX.message("MAIL_MESSAGE_LIST_CONFIRM_DELETE_BTN"),
						className: "popup-window-button-decline",
						events: {
							click: BX.delegate(function ()
							{
								this.processDelete(btn, uid);
								this.popupDeleteConfirm.close();
							}, this)
						}
					})];
				this.popupDeleteConfirm = new BX.PopupWindow('bx-mail-message-list-popup-delete-confirm', null, {
					zIndex: 1000,
					autoHide: true,
					buttons: buttons,
					closeByEsc: true,
					titleBar: {
						content: BX.create('div', {
							html: '<span class="popup-window-titlebar-text">' + BX.message("MAIL_MESSAGE_LIST_CONFIRM_TITLE") + '</span>'
						})
					},
					events: {
						onPopupClose: function ()
						{
							this.destroy()
						},
						onPopupDestroy: BX.delegate(function ()
						{
							this.popupDeleteConfirm = null
						}, this)
					},
					content: BX.create("div", {
						html: BX.message('MAIL_MESSAGE_LIST_CONFIRM_DELETE')
					})
				});
			}
			this.popupDeleteConfirm.show();
		}
		else
		{
			this.processDelete(btn, uid);
		}
	};

	BXMailMessage.prototype.processDelete = function (btn, uid)
	{
		btn.classList.add('mail-msg-view-control-disabled');
		BX.ajax.runComponentAction('bitrix:mail.client', 'delete', {
			mode: 'ajax',
			data: {ids: [uid]}
		}).then(
			this.onMessageActionSuccess.bind(this, btn),
			function (response)
			{
				this.onMessageActionError.bind(this, response)()
			}.bind(this)
		);
	};

	BXMailMessage.prototype.onMessageActionError = function (response)
	{
		alert(response.errors[0].message);
		// todo show errors
	};

	BXMailMessage.prototype.onMessageActionSuccess = function (btn)
	{
		top.BX.SidePanel.Instance.postMessage(
			window,
			'mail-message-reload-grid',
			{}
		);

		this.ctrl.close(true);
	};

	var BXMailMailbox = {
		syncData: {}
	};

	BXMailMailbox.init = function (mailbox)
	{
		this.mailbox = mailbox || {};

		return this;
	};

	BXMailMailbox.sync = function (stepper, gridId, onlySyncCurrent, showProgressBar)
	{
		var self = this;

		//if synchronization is requested by the user - show the bar even if synchronization is already in progress
		if(showProgressBar)
		{
			BXMailMailbox.updateStepper(stepper, 0, -1);
		}

		if (self.syncLock)
		{
			return;
		}

		self.syncLock = true;

		var filter = BX.Main.filterManager.getById(gridId);
		var dir = filter.getFilterFieldsValues()['DIR'];

		var pr = BX.ajax.runComponentAction(
			'bitrix:mail.client',
			'syncMailbox',
			{
				mode: 'ajax',
				data: {
					id: self.mailbox.ID,
					dir: dir || self.mailbox.OPTIONS.inboxDir,
					onlySyncCurrent: onlySyncCurrent === undefined ? false : onlySyncCurrent,
				}
			}
		);

		pr.then(
			function (json)
			{
				BXMailMailbox.syncProgress(stepper, gridId, json.data);
			},
			function (json)
			{
				BXMailMailbox.syncProgress(
					stepper,
					gridId,
					{
						'complete': -1,
						'status': -1,
						'errors': json.errors
					}
				);
			}
		);
	};

	BXMailMailbox.syncProgress = function (stepper, gridId, params)
	{
		var self = this;

		if (params.timestamp < self.syncData.timestamp)
		{
			return;
		}

		self.syncData.timestamp = params.timestamp;

		if (!BX.type.isNotEmptyString(params.sessid))
		{
			params.sessid = 'dummy';
		}

		if (typeof self.syncData[params.sessid] == 'undefined')
		{
			self.syncData[params.sessid] = {};
		}

		if (params.new > 0)
		{
			self.syncData[params.sessid].new = params.new;
		}

		if (params.complete > 0 && !self.syncData[params.sessid].complete)
		{
			if (self.syncData[params.sessid].new > 0 || params.updated > 0 || params.deleted > 0)
			{
				BX.onCustomEvent('BX.Mail.Sync:newLettersArrived');

				var messageGrid = new MessageGrid();
				messageGrid.setGridId(gridId);
				messageGrid.reloadTable();
			}

			if (params.final > 0)
			{
				delete self.syncData[params.sessid];
			}
			else
			{
				self.syncData[params.sessid].complete = true;
			}
		}

		BXMailMailbox.updateStepper(stepper, params.complete, params.status, params.errors);

		if (params.complete < 0 || params.complete > 0)
		{
			this.syncLock = false;
		}

		if (params.complete < 0 && params.status >= 0)
		{
			//sync incomplete to end
			BXMailMailbox.sync(stepper, gridId, true);
		}
	}

	BXMailMailbox.toggleStepper = function(stepper, show)
	{
		if (show)
		{
			stepper.show();
		}
		else
		{
			stepper.hide();
		}
	}

	BXMailMailbox.updateStepper = function(stepper, complete, status, errors)
	{
		stepper.hideTimeout = clearTimeout(stepper.hideTimeout);

		status = parseFloat(status);

		var stepperInfo = stepper.getErrorTitleNode();
		var stepperError = stepper.getErrorTextNode();

		//in case of synchronization error:
		if (complete < 0 && status < 0)
		{
			stepperInfo && (stepperInfo.innerText = BX.message('MAIL_CLIENT_MAILBOX_SYNC_BAR_INTERRUPTED'));

			if (stepperError)
			{
				var details = [];

				if (errors && errors.length > 0)
				{
					for (var i = 0; i < errors.length; i++)
					{
						if (errors[i].code < 0)
						{
							details.push(errors[i].message);
							errors.splice(i--, 1);
						}
						else
						{
							errors[i] = errors[i].message;
						}
					}

					var error = (errors.length > 0 ? errors : details).join(': ');
				}
				else
				{
					var error = BX.message('MAIL_CLIENT_AJAX_ERROR');
				}

				stepperError.innerText = error;

				if (details.length > 0 && errors.length > 0)
				{
					stepperError.appendChild(
						BX.UI.Hint.createNode(details.join(': '))
					);
				}

				stepper.showErrorBox();
			}
			else
			{
				BXMailMailbox.toggleStepper(stepper, false);
			}
		}
		else
		{
			stepper.hideErrorBox();

			if (complete > 0)
			{
				stepperInfo && (stepperInfo.innerHTML = BX.message('MAIL_CLIENT_MAILBOX_SYNC_BAR_COMPLETED'));

				stepper.hideTimeout = setTimeout(BXMailMailbox.toggleStepper.bind(this, stepper, false), 2000);
			}
			else
			{
				BXMailMailbox.toggleStepper(stepper, true);
			}
		}

		var event = document.createEvent('Event');
		event.initEvent('resize', true, true);
		window.dispatchEvent(event);
	};

	window.BXMailMessageController = BXMailMessageController;
	window.BXMailMessage = BXMailMessage;
	window.BXMailMailbox = BXMailMailbox;

})();

(function() {

	if (window !== window.top)
	{
		return;

		BX.bind(
			window,
			'beforeunload',
			function ()
			{
				document.body.style.opacity = '0.4';
			}
		);
	}

	var siteDir = ('/' + (BX.message.SITE_DIR || '/').replace(/[\\*+?.()|[\]{}]/g, '\\$&') + '/').replace(/\/+/g, '/');

	top.BX.SidePanel.Instance.bindAnchors({
		rules: [
			{
				condition: [
					siteDir + 'mail/',
				],
				options: {
					cacheable: false,
					customLeftBoundary: 0,
				}
			},
			{
				condition: [
					'^' + siteDir + 'mail/config/(new|edit)',
				],
				options: {
					width: 760,
					cacheable: false
				}
			},
			{
				condition: [
					'^' + siteDir + 'mail/(blacklist|signature|config|message)'
				],
				options: {
					width: 1080
				}
			}
		]
	});

})();
