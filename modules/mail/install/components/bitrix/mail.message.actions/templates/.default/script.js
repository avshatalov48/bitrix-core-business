
;(function() {

	if (window.BXMailMessageActions)
		return;

	var BXMailMessageActions = {};

	BXMailMessageActions.init = function (options)
	{
		var self = this;

		self.options = options;

		var handler = self.createAction.bind(self);

		var createBtn = BX('mail-msg-' + self.options.messageId + '-actions-create-btn');
		BX.bind(
			createBtn,
			'click',
			function (event)
			{
				handler(
					event,
					{
						value: self.options.createMenu.__default.id,
						disable: BX.addClass.bind(BX, createBtn.parentNode, 'ui-btn-main-disabled'),
						enable: BX.removeClass.bind(BX, createBtn.parentNode, 'ui-btn-main-disabled')
					}
				);
			}
		);

		var createMenuBtn = BX('mail-msg-' + self.options.messageId + '-actions-create-menu-btn');
		BX.bind(
			createMenuBtn,
			'click',
			function ()
			{
				var items = ['TASKS_TASK'];
				if (self.options.isCrmEnabled)
				{
					items.push(self.options.createMenu['CRM_ACTIVITY'].binded ? 'CRM_EXCLUDE' : 'CRM_ACTIVITY');
				}
				items = items.concat([
					'BLOG_POST',
					'IM_CHAT',
					'CALENDAR_EVENT'
				]);
				for (var i = 0, id; i < items.length; i++)
				{
					id = items[i];

					if (id == self.options.createMenu.__default.id)
					{
						items.splice(i, 1);
						i--;

						continue;
					}

					items[i] = {
						text: self.options.createMenu[id].title,
						value: self.options.createMenu[id].id,
						onclick: handler,
						disabled: self.options.createMenu[id].disabled,
					};
				}

				BX.Main.MenuManager.show(
					'mail-msg-' + self.options.messageId + '-create-menu',
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
	};

	BXMailMessageActions.createAction = function (event, item)
	{
		var self = this;

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
				).join('<br>');

				top.BX.UI.Notification.Center.notify({
					autoHideDelay: 5000,
					content: error
				});
			}
		};

		switch (item.value)
		{
			case 'TASKS_TASK':
				top.BX.SidePanel.Instance.open(self.options.createMenu['TASKS_TASK'].href, {'cacheable': false, 'loader': 'task-new-loader'});
				break;
			case 'BLOG_POST':
				top.BX.SidePanel.Instance.open(self.options.createMenu['BLOG_POST'].href, {'cacheable': false, 'loader': 'socialnetwork:userblogposteditex'});
				break;
			case 'IM_CHAT':
				BX.Mail.Secretary.getInstance(self.options.messageId).openChat();
				break;
			case 'CALENDAR_EVENT':
				BX.Mail.Secretary.getInstance(self.options.messageId).openCalendarEvent();
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
							messageId: self.options.messageId
						}
					}
				);
				pr.then(
					function (json)
					{
						item.enable();

						top.BX.UI.Notification.Center.notify({
							autoHideDelay: 2000,
							content: BX.message('MAIL_MESSAGE_ACTIONS_NOTIFY_ADDED_TO_CRM')
						});

						self.options.createMenu['CRM_ACTIVITY'].binded = true;

						BX.Main.MenuManager.destroy('mail-msg-' + self.options.messageId + '-create-menu');

						BX.Event.EventEmitter.emit(
							'BXMailMessageActions:CRM_ACTIVITY',
							{
								'messageId': self.options.messageId
							}
						);
					},
					failHandler
				);
				break;
			case 'CRM_EXCLUDE':
				// @TODO: loader
				item.disable();

				var messageId = item.messageId || self.options.messageId;

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
							content: BX.message('MAIL_MESSAGE_ACTIONS_NOTIFY_EXCLUDED_FROM_CRM')
						});

						BX.Event.EventEmitter.emit(
							'BXMailMessageActions:CRM_EXCLUDE',
							{
								'messageId': self.options.messageId
							}
						);

						item.enable();

						if (messageId == self.options.messageId)
						{
							self.options.createMenu['CRM_ACTIVITY'].binded = false;
						}

						BX.Main.MenuManager.destroy('mail-msg-' + self.options.messageId + '-create-menu');
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

	window.BXMailMessageActions = BXMailMessageActions;

})();
