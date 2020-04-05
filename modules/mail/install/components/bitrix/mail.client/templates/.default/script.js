
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

			this.initCreateMenu();
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
			url: this.options.ajaxUrl,
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
					url: this.options.ajaxUrl,
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

	BXMailMessageController.initCreateMenu = function ()
	{
		var ctrl = this;

		var handler = ctrl.createAction.bind(ctrl);

		var createBtn = BX('mail-msg-view-create-btn');
		BX.bind(
			createBtn,
			'click',
			function (event)
			{
				handler(
					event,
					{
						value: ctrl.options.createMenu.__default.id,
						disable: BX.addClass.bind(BX, createBtn.parentNode, 'ui-btn-main-disabled'),
						enable: BX.removeClass.bind(BX, createBtn.parentNode, 'ui-btn-main-disabled')
					}
				);
			}
		);

		var createMenuBtn = BX('mail-msg-view-create-menu-btn');
		BX.bind(
			createMenuBtn,
			'click',
			function ()
			{
				var items = ['TASKS_TASK'];
				if (ctrl.options.isCrmEnabled)
				{
					items.push(ctrl.options.createMenu['CRM_ACTIVITY'].binded ? 'CRM_EXCLUDE' : 'CRM_ACTIVITY');
				}
				items = items.concat([
					'BLOG_POST',
					'IM_CHAT',
					'CALENDAR_EVENT'
				]);
				for (var i = 0, id; i < items.length; i++)
				{
					id = items[i];

					if (id == ctrl.options.createMenu.__default.id)
					{
						items.splice(i, 1);
						i--;

						continue;
					}

					items[i] = {
						text: ctrl.options.createMenu[id].title,
						value: ctrl.options.createMenu[id].id,
						onclick: handler,
						disabled: ctrl.options.createMenu[id].disabled,
					};
				}

				BX.PopupMenu.show(
					'mail-msg-view-create-menu',
					createMenuBtn,
					items,
					{
						offsetLeft: 16,
						angle: true,
						closeByEsc: true
					}
				);
			}
		);
	}

	BXMailMessageController.createAction = function (event, item)
	{
		var ctrl = this;

		var failHandler = function (json)
		{
			item.enable();

			if (json.errors && json.errors.length > 0)
			{
				var error = json.errors.map(
					function (item)
					{
						return item.message;
					}
				).join('\n');

				// @TODO: error
			}
		};

		switch (item.value)
		{
			case 'TASKS_TASK':
				top.BX.SidePanel.Instance.open(this.options.createMenu['TASKS_TASK'].href);
				break;
			case 'CRM_ACTIVITY':
				// @TODO: loader
				item.disable();

				var pr = BX.ajax.runComponentAction(
					'bitrix:mail.client',
					'createCrmActivity',
					{
						mode: 'ajax',
						data: {
							messageId: this.options.messageId
						}
					}
				);
				pr.then(
					function (json)
					{
						item.enable();

						if (json.data && json.data.length > 0)
						{
							top.BX.UI.Notification.Center.notify({
								autoHideDelay: 2000,
								content: BX.message('MAIL_MESSAGE_LIST_NOTIFY_ADDED_TO_CRM')
							});

							ctrl.options.createMenu['CRM_ACTIVITY'].binded = true;

							BX.PopupMenu.destroy('mail-msg-view-create-menu');

							BX.show(
								BX.findChildByClassName(
									BX('mail-msg-view-details-'+ctrl.options.messageId),
									'js-msg-view-control-skip',
									true
								)
							);
						}
						else
						{
							top.BX.UI.Notification.Center.notify({
								autoHideDelay: 2000,
								content: BX.message('MAIL_MESSAGE_LIST_NOTIFY_NOT_ADDED_TO_CRM')
							});
							// @TODO: error
						}
					},
					failHandler
				);
				break;
			case 'CRM_EXCLUDE':
				// @TODO: loader
				item.disable();

				var messageId = item.messageId || this.options.messageId;

				var pr = BX.ajax.runComponentAction(
					'bitrix:mail.client',
					'removeCrmActivity',
					{
						mode: 'ajax',
						data: {
							messageId: messageId
						}
					}
				);
				pr.then(
					function (json)
					{
						top.BX.UI.Notification.Center.notify({
							autoHideDelay: 2000,
							content: BX.message('MAIL_MESSAGE_LIST_NOTIFY_EXCLUDED_FROM_CRM')
						});

						BX.hide(
							BX.findChildByClassName(
								BX('mail-msg-view-details-' + messageId),
								'js-msg-view-control-skip',
								true
							)
						);

						item.enable();

						if (messageId == ctrl.options.messageId)
						{
							ctrl.options.createMenu['CRM_ACTIVITY'].binded = false;
						}

						BX.PopupMenu.destroy('mail-msg-view-create-menu');
					},
					failHandler
				);
				break;
		}

		if (item.menuWindow)
		{
			item.menuWindow.close();
		}
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
					self.ctrl.createAction(
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

		BX.addCustomEvent(mailForm, 'MailForm:field:rcptSelectorClose', BXMailMessage.handleRcptSelectorClose.bind(this));
		BX.addCustomEvent(mailForm, 'MailForm:footer:buttonClick', BXMailMessage.handleFooterButtonClick.bind(this));
		BX.addCustomEvent(mailForm, 'MailForm:submit', BXMailMessage.handleFormSubmit.bind(this));
		BX.addCustomEvent(mailForm, 'MailForm:submit:ajaxSuccess', BXMailMessage.handleFormSubmitSuccess.bind(this));
		BX.PULL.extendWatch('mail_mailbox_' + this.options.messageId);
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

	BXMailMessage.handleRcptSelectorClose = function (form, field)
	{
		if (!field.params.name.match(/^data\[(to|cc|bcc)\]$/))
			return;

		for (var i = 0, target; i < form.fields.length; i++)
		{
			target = form.fields[i];
			if (target.selector == field.selector || !target.params.name.match(/^data\[(to|cc|bcc)\]$/))
				continue;

			BX.SocNetLogDestination.obItems[target.selector] = BX.SocNetLogDestination.obItems[field.selector];
			BX.SocNetLogDestination.obItemsLast[target.selector] = BX.SocNetLogDestination.obItemsLast[field.selector];
		}
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
		var uploads;
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
		}

		/*
		if ('edit' == this.ctrl.options.type)
		{
			var hiddenWrapper = BX('crm_act_email_create_hidden');
			hiddenWrapper.innerHTML = '';

			var fields = BX.findChildren(document, {'tag': 'input'}, true);
			for (var i = 0, clone; i < fields.length; i++)
			{
				if (fields[i].name && fields[i].name.indexOf('__crm_activity_planner[') >= 0)
				{
					clone = fields[i].cloneNode(true);
					clone.removeAttribute('id');
					clone.setAttribute('name', 'data'+fields[i].name.substr('__crm_activity_planner'.length));

					hiddenWrapper.appendChild(clone);
				}
			}
		}
		*/
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

	var BXMailMailbox = {};

	BXMailMailbox.init = function (mailbox)
	{
		this.mailbox = mailbox || {};

		return this;
	};

	BXMailMailbox.sync = function (button, stepper, gridId)
	{
		var self = this;

		if (self.syncLock)
		{
			return;
		}

		self.syncLock = true;

		if (!BX.type.isDomNode(button))
		{
			button = document.createElement('DIV');
		}

		BX.addClass(button, 'ui-btn-wait');

		var pr = BX.ajax.runComponentAction(
			'bitrix:mail.client',
			'syncMailbox',
			{
				mode: 'ajax',
				data: {
					id: self.mailbox.ID
				}
			}
		);

		pr.then(
			function (json)
			{
				self.syncLock = false;
				BX.removeClass(button, 'ui-btn-wait');

				BX.addClass(button, 'ui-btn-icon-buisness');
				BX.removeClass(button, 'ui-btn-icon-business-warning');

				button.setAttribute('title', BX.message('MAIL_MESSAGE_SYNC_BTN_HINT'));

				BXMailMailbox.updateStepper(stepper, json.data.complete, json.data.status);

				if (json.data.new > 0)
				{
					BX.Main.gridManager.getInstanceById(gridId).reload();
				}
			},
			function (json)
			{
				self.syncLock = false;
				BX.removeClass(button, 'ui-btn-wait');

				BX.addClass(button, 'ui-btn-icon-business-warning');
				BX.removeClass(button, 'ui-btn-icon-buisness');

				var error = BX.message('MAIL_CLIENT_AJAX_ERROR');
				if (json.errors && json.errors.length > 0)
				{
					error += '\n';
					error += json.errors.map(
						function (item)
						{
							return item.message;
						}
					).join('\n');
				}

				button.setAttribute('title', error);
			}
		);
	};

	BXMailMailbox.updateStepper = function(stepper, completed, status)
	{
		if (completed)
		{
			stepper.style.display = 'none';
		}
		else
		{
			var stepperLine = BX.findChildByClassName(stepper, 'main-stepper-bar-line');
			var stepperSteps = BX.findChildByClassName(stepper, 'main-stepper-steps');

			if (status >= 0)
			{
				var status = Math.min(Math.max(Math.round(parseFloat(status) * 100), 1), 99);

				if (stepperLine)
				{
					stepperLine.style.width = status+'%';
				}

				if (stepperSteps)
				{
					stepperSteps.innerHTML = status+'%';
				}
			}
			else
			{
				if (stepperLine)
				{
					stepperLine.style.width = '0%';
				}

				if (stepperSteps)
				{
					stepperSteps.innerHTML = '';
				}
			}

			stepper.style.display = '';
		}

		var event = document.createEvent('Event');
		event.initEvent('resize', true, true);
		window.dispatchEvent(event);
	};

	BXMailMailbox.onFolderCheckboxClickHandler = function (event)
	{
		var selectedFolders = document.querySelectorAll('.mail-connect-form-input-check:checked');
		if (selectedFolders.length === 0)
		{
			event.stopPropagation();
			event.preventDefault();
		}
	};

	BXMailMailbox.setupDirs = function (callback)
	{
		var imapOptions = {}

		try
		{
			imapOptions = this.mailbox.OPTIONS.imap;
		}
		catch (err) {}

		var dirs = imapOptions.dirs;
		var dirsList = imapOptions.dirsList;

		var ignore = imapOptions.ignore || [];
		var disabled = imapOptions.disabled || [];

		var outcome = BX.type.isArray(imapOptions.outcome) && imapOptions.outcome[0] ? imapOptions.outcome[0] : 'INBOX';
		var trash = BX.type.isArray(imapOptions.trash) && imapOptions.trash[0] ? imapOptions.trash[0] : '';
		var spam = BX.type.isArray(imapOptions.spam) && imapOptions.spam[0] ? imapOptions.spam[0] : trash;

		if (dirs)
		{
			var dirsTree = [];

			var item, path, level;
			for (var i = 0; i < dirsList.length; i++)
			{
				path = dirsList[i];
				if (dirs.hasOwnProperty(path))
				{
					item = dirs[path];
					level = item.length - 1;

					dirsTree.push({
						item: item,
						path: path,
						name: item[level],
						level: level,
						ignore: BX.util.in_array(path, ignore),
						disabled: BX.util.in_array(path, disabled)
					});
				}
			}

			top.BX.SidePanel.Instance.open(
				'mail:mailbox-setup-dirs',
				{
					width: 640,
					cacheable: false,
					contentCallback: function(slider)
					{
						var promise = new top.BX.Promise();

						var html = '', subhtml, placeholder, checkedSingle;
						var count = dirsTree.length, i, flag;

						html += '<div class="mail-connect-section-block">';

						html += '<div class="mail-connect-title-block">';
						html += '<div class="mail-connect-title">' + BX.message('MAIL_CLIENT_CONFIG_IMAP_DIRS_SYNC') + '</div>';
						html += '</div>';

						for (i = 0; i < count; i++)
						{
							flag = dirsTree[i].disabled ? 'disabled' : (!dirsTree[i].ignore ? 'checked' : '');

							html += '<div class="mail-connect-option-email mail-connect-form-check-hidden" style="padding-left: '+(25*dirsTree[i].level)+'px">';
							html += '<input onclick="BXMailMailbox.onFolderCheckboxClickHandler(event)" class="mail-connect-form-input mail-connect-form-input-check" id="imap-dir-sync-n'+i+'" type="checkbox" name="imap_dirs[sync]['+i+']" value="'+dirsTree[i].path+'" '+flag+'>';
							html += '<label class="mail-connect-form-label mail-connect-form-label-check" for="imap-dir-sync-n'+i+'" '+(dirsTree[i].disabled ? 'style="color: #a0a0a0;"' : '')+'>'+dirsTree[i].name+'</label>';
							html += '</div>';
						}

						html += '</div>';

						html += '<div class="mail-connect-section-block">';

						html += '<div class="mail-connect-title-block">';
						html += '<div class="mail-connect-title">';
						html += BX.message('MAIL_CLIENT_CONFIG_IMAP_DIRS_FOR');
						html += '</div></div>';

						subhtml = '';
						checkedSingle = '';
						placeholder = '<input id="mail_connect_setup_dirs_outcome_placeholder" type="radio" name="imap_dirs[outcome]" value="" checked>';
						placeholder += '<label for="mail_connect_setup_dirs_outcome_placeholder">' + BX.message('MAIL_CLIENT_CONFIG_IMAP_DIRS_EMPTY_DEFAULT') + '</label>';
						for (i = 0; i < count; i++)
						{
							if (dirsTree[i].disabled)
							{
								continue;
							}

							flag = '';
							if (dirsTree[i].path == outcome)
							{
								flag = 'checked';
								placeholder = '';
								checkedSingle = 'mail_connect_setup_dirs_outcome_' + (i + 1);
							}

							subhtml += '<input type="radio" name="imap_dirs[outcome]" value="' + BX.util.htmlspecialchars(dirsTree[i].path) + '" id="mail_connect_setup_dirs_outcome_' + (i + 1) + '" ' + flag + '>';
							subhtml += '<label for="mail_connect_setup_dirs_outcome_' + (i + 1) + '">' + BX.util.htmlspecialchars(dirsTree[i].item.join(' / ')) + '</label>';
						}

						html += '<div class="mail-connect-option-email mail-connect-form-check-hidden">'
							+ BX.message('MAIL_CLIENT_CONFIG_IMAP_DIRS_OUTCOME') +
							'<label class="mail-set-singleselect mail-set-singleselect-line" data-checked="' + checkedSingle + '">\
								<input id="mail_connect_setup_dirs_outcome_0" type="radio" name="imap_dirs[outcome]" value="0">\
								<div class="mail-set-singleselect-wrapper">'
									+ subhtml +
								'</div>'
								+ placeholder +
							'</label>\
						</div>';

						subhtml = '';
						checkedSingle = '';
						placeholder = '<input id="mail_connect_setup_dirs_trash_placeholder" type="radio" name="imap_dirs[trash]" value="" checked>';
						placeholder += '<label for="mail_connect_setup_dirs_trash_placeholder">' + BX.message('MAIL_CLIENT_CONFIG_IMAP_DIRS_EMPTY_DEFAULT') + '</label>';
						for (i = 0; i < count; i++)
						{
							if (dirsTree[i].disabled)
							{
								continue;
							}

							flag = '';
							if (dirsTree[i].path == trash)
							{
								flag = 'checked';
								placeholder = '';
								checkedSingle = 'mail_connect_setup_dirs_trash_' + (i + 1);
							}

							subhtml += '<input type="radio" name="imap_dirs[trash]" value="' + BX.util.htmlspecialchars(dirsTree[i].path) + '" id="mail_connect_setup_dirs_trash_' + (i + 1) + '" ' + flag + '>';
							subhtml += '<label for="mail_connect_setup_dirs_trash_' + (i + 1) + '">' + BX.util.htmlspecialchars(dirsTree[i].item.join(' / ')) + '</label>';
						}

						html += '<div class="mail-connect-option-email mail-connect-form-check-hidden">'
							+ BX.message('MAIL_CLIENT_CONFIG_IMAP_DIRS_TRASH') +
							'<label class="mail-set-singleselect mail-set-singleselect-line" data-checked="' + checkedSingle + '">\
								<input id="mail_connect_setup_dirs_trash_0" type="radio" name="imap_dirs[trash]" value="0">\
								<div class="mail-set-singleselect-wrapper">'
									+ subhtml +
								'</div>'
								+ placeholder +
							'</label>\
						</div>';

						subhtml = '';
						checkedSingle = '';
						placeholder = '<input id="mail_connect_setup_dirs_spam_placeholder" type="radio" name="imap_dirs[spam]" value="" checked>';
						placeholder += '<label for="mail_connect_setup_dirs_spam_placeholder">' + BX.message('MAIL_CLIENT_CONFIG_IMAP_DIRS_EMPTY_DEFAULT') + '</label>';
						for (i = 0; i < count; i++)
						{
							if (dirsTree[i].disabled)
							{
								continue;
							}

							flag = '';
							if (dirsTree[i].path == spam)
							{
								flag = 'checked';
								placeholder = '';
								checkedSingle = 'mail_connect_setup_dirs_spam_' + (i + 1);
							}

							subhtml += '<input type="radio" name="imap_dirs[spam]" value="' + BX.util.htmlspecialchars(dirsTree[i].path) + '" id="mail_connect_setup_dirs_spam_' + (i + 1) + '" ' + flag + '>';
							subhtml += '<label for="mail_connect_setup_dirs_spam_' + (i + 1) + '">' + BX.util.htmlspecialchars(dirsTree[i].item.join(' / ')) + '</label>';
						}

						html += '<div class="mail-connect-option-email mail-connect-form-check-hidden">'
							+ BX.message('MAIL_CLIENT_CONFIG_IMAP_DIRS_SPAM') +
							'<label class="mail-set-singleselect mail-set-singleselect-line" data-checked="' + checkedSingle + '">\
								<input id="mail_connect_setup_dirs_spam_0" type="radio" name="imap_dirs[spam]" value="0">\
								<div class="mail-set-singleselect-wrapper">'
									+ subhtml +
								'</div>'
								+ placeholder +
							'</label>\
						</div>';

						html += '</div>';

						promise.fulfill(
							'<form class="mail-connect-setup-dirs-form" style="display: flex; flex-direction: column; height: 100%; ">\
								<div style="padding: 0 20px 20px 20px; flex: 1; overflow: auto; ">\
									<div class="mail-msg-sidepanel-header">\
										<div class="mail-msg-sidepanel-title">' + BX.message('MAIL_CLIENT_CONFIG_IMAP_DIRS_TITLE') + '</div>\
									</div>\
									<div class="mail-connect mail-connect-slider">' + html + '</div>\
								</div>\
								<div class="mail-connect-footer mail-connect-footer-fixed" style="position: static; ">\
									<div class="mail-connect-footer-container">\
										<button class="ui-btn ui-btn-md ui-btn-success ui-btn-success mail-connect-btn-connect" type="submit">'
											+ BX.message('MAIL_CLIENT_CONFIG_IMAP_DIRS_BTN_SAVE') +
										'</button>\
										<button class="ui-btn ui-btn-md ui-btn-link mail-connect-btn-cancel" type="reset">'
											+ BX.message('MAIL_CLIENT_CONFIG_IMAP_DIRS_BTN_CANCEL') +
										'</button>\
									</div>\
								</div>\
							</form>'
						);

						return promise;
					},
					events: {
						onLoad: function(event)
						{
							var form = BX.findChildByClassName(
								event.slider.layout.content,
								'mail-connect-setup-dirs-form',
								true
							);

							top.BX.bind(
								form,
								'submit',
								function (e)
								{
									e.preventDefault();

									var data = BX.ajax.prepareForm(form).data;

									if (data.imap_dirs)
									{
										imapOptions.outcome = data.imap_dirs.outcome ? [data.imap_dirs.outcome] : imapOptions.outcome;
										imapOptions.trash = data.imap_dirs.trash ? [data.imap_dirs.trash] : imapOptions.trash;
										imapOptions.spam = data.imap_dirs.spam ? [data.imap_dirs.spam] : imapOptions.spam;

										if (data.imap_dirs.sync)
										{
											imapOptions.ignore = [];

											for (path in dirs)
											{
												if (dirs.hasOwnProperty(path))
												{
													imapOptions.ignore.push(path);
												}
											}

											var i, k;
											for (i in data.imap_dirs.sync)
											{
												if (data.imap_dirs.sync.hasOwnProperty(i))
												{
													k = BX.util.array_search(data.imap_dirs.sync[i], imapOptions.ignore);

													if (!(k < 0))
													{
														imapOptions.ignore.splice(k, 1);
													}
												}
											}
										}
									}

									callback(data);

									event.slider.close();
								}
							);
							top.BX.bind(
								form,
								'reset',
								function (e)
								{
									event.slider.close();
								}
							);

							var singleselect = function(input)
							{
								var options = BX.findChildren(input, {tag: 'input', attr: {type: 'radio'}}, true);
								for (var i in options)
								{
									BX.bind(options[i], 'change', function()
									{
										if (this.checked)
										{
											if (this.value == 0)
											{
												var input1 = BX(input.getAttribute('data-checked'));
												if (input1)
												{
													var label0 = BX.findNextSibling(this, {tag: 'label', attr: {'for': this.id}});
													var label1 = BX.findNextSibling(input1, {tag: 'label', attr: {'for': input1.id}});
													if (label0 && label1)
														BX.adjust(label0, {text: label1.innerHTML});
												}
											}
											else
											{
												input.setAttribute('data-checked', this.id);
											}
										}
									});
								}

								BX.bind(input, 'click', function(event)
								{
									event = event || window.event;
									event.skip_singleselect = input;
								});

								BX.bind(event.getSlider().getContentContainer(), 'click', function(event)
								{
									event = event || window.event;
									if (event.skip_singleselect !== input)
									{
										if(top.BX(input.getAttribute('data-checked')))
										{
											top.BX(input.getAttribute('data-checked')).checked = true;
										}
									}
								});
							};

							var selectInputs = BX.findChildrenByClassName(event.getSlider().getContentContainer(), 'mail-set-singleselect', true);
							for (var i in selectInputs)
								singleselect(selectInputs[i]);
						}
					}
				}
			);
		}
	};

	window.BXMailMessageController = BXMailMessageController;
	window.BXMailMessage = BXMailMessage;
	window.BXMailMailbox = BXMailMailbox;

})();

if (window === window.top)
{
	top.BX.SidePanel.Instance.bindAnchors({
		rules: [
			{
				condition: [
					'^/mail/message/',
				],
				options: {
					width: 1080,
					cacheable: true
				}
			},
			{
				condition: [
					'^/mail/config/(new|edit)',
				],
				options: {
					width: 760,
					cacheable: false,
					allowChangeHistory: false
				}
			},
			{
				condition: [
					'^/mail/config/',
				],
				options: {
					width: 1080,
					cacheable: true,
					allowChangeHistory: false
				}
			},
			{
				condition: [
					'^/mail/blacklist'
				],
				options: {
					width: 1080,
					cacheable: true
				}
			},
			{
				condition: [
					'^/mail/signature'
				],
				options: {
					width: 1080,
					cacheable: true
				}
			}
		]
	});
}
