BX.namespace("BX.MailClientConfig");
BX.MailClientConfig.Edit = {

	isSuccessSyncStatus: null,
	oauthUserIsEmpty: true,
	isOauthMode: false,
	isForbiddenToShare: false,
	smtp: {
		switcherChecked: false,
		switcherDisabled: false,
	},
	crm: {
		integrationAvailable: false,
		switcherChecked: false,
	},
	isCrmIntegrationAvailable: false,
	isCrmSwitcherChecked: false,

	init: function(params)
	{
		if (BX.type.isNotEmptyObject(params))
		{
			this.isForbiddenToShare = params.isForbiddenToShare || false;
			this.smtp.switcherChecked = params.isSmtpSwitcherChecked || false;
			this.smtp.switcherDisabled = params.isSmtpSwitcherDisabled || false;
			this.crm.switcherChecked = params.isCrmSwitcherChecked || false;
			this.crm.integrationAvailable = params.isCrmIntegrationAvailable || false;
			this.oauthUserIsEmpty = params.oauthUserIsEmpty || false;
			this.isSuccessSyncStatus = null;
			this.isOauthMode = params.isOauthMode || false;

			if (params.isSuccessSyncStatus !== undefined)
			{
				this.isSuccessSyncStatus = params.isSuccessSyncStatus;
			}

			const {
				crmSyncIntervals = {},
				leadSourceList = {},
				crmEntityList = {},
				messageSyncIntervals = {},
				defaultMaxCrmSyncKey = '',
				defaultMaxAgeMessageSyncKey = '',
				defaultLeadSourceKey = '',
				defaultNewEntityInKey = '',
				defaultNewEntityOutKey = '',
			} = params;

			const connectCrmAllowEntityInSelectorWrapper = BX('mail-connect-crm-allow-entity-in');

			if (connectCrmAllowEntityInSelectorWrapper !== null)
			{
				const connectCrmAllowEntityInSelector = new BX.Mail.SettingSelector({
					settingsMap: crmEntityList,
					selectedOptionKey: defaultNewEntityInKey,
					inputName: 'fields[crm_entity_in]',
				});
				connectCrmAllowEntityInSelector.renderTo(connectCrmAllowEntityInSelectorWrapper);
			}

			const connectCrmAllowEntityOutSelectorWrapper = BX('mail-connect-crm-allow-entity-out');

			if (connectCrmAllowEntityOutSelectorWrapper !== null)
			{
				const connectCrmAllowEntityOutSelector = new BX.Mail.SettingSelector({
					settingsMap: crmEntityList,
					selectedOptionKey: defaultNewEntityOutKey,
					inputName: 'fields[crm_entity_out]',
				});
				connectCrmAllowEntityOutSelector.renderTo(connectCrmAllowEntityOutSelectorWrapper);
			}

			const connectCrmLeadSourceSelectorWrapper = BX('mail-connect-crm-lead-source');

			if (connectCrmLeadSourceSelectorWrapper !== null)
			{
				const connectCrmLeadSourceSelector = new BX.Mail.SettingSelector({
					settingsMap: leadSourceList,
					selectedOptionKey: defaultLeadSourceKey,
					inputName: 'fields[crm_lead_source]',
					dialogOptions: {
						width: 300,
						height: 300,
						enableSearch: true,
					},
				});
				connectCrmLeadSourceSelector.renderTo(connectCrmLeadSourceSelectorWrapper);
			}

			const mailMessageMaxAgeSelectorWrapper = BX('mail-message-max-age');

			if (mailMessageMaxAgeSelectorWrapper !== null)
			{
				const mailMessageMaxAgeSelector = new BX.Mail.SettingSelector({
					settingsMap: messageSyncIntervals,
					selectedOptionKey: defaultMaxAgeMessageSyncKey,
					inputName: 'fields[msg_max_age]',
				});
				mailMessageMaxAgeSelector.renderTo(mailMessageMaxAgeSelectorWrapper);
			}

			const mailCrmMaxAgeSelectorWrapper = BX('mail-crm-max-age');

			if (mailCrmMaxAgeSelectorWrapper !== null)
			{
				const mailCrmMaxAgeSelector = new BX.Mail.SettingSelector({
					settingsMap: crmSyncIntervals,
					selectedOptionKey: defaultMaxCrmSyncKey,
					inputName: 'fields[crm_max_age]',
				});
				mailCrmMaxAgeSelector.renderTo(mailCrmMaxAgeSelectorWrapper);
			}
		}

		if (this.isOauthMode && this.oauthUserIsEmpty)
		{
			this.showOauthAuthorizationBlock();
		}

		var selectInputs = BX.findChildrenByClassName(document, 'mail-set-singleselect', true);
		for (var i in selectInputs)
		{
			if (!selectInputs.hasOwnProperty(i))
			{
				continue;
			}
			this.singleselect(selectInputs[i]);
		}

		BX.bind(
			BX('mail_connect_mb_crm_new_lead_for_link'),
			'click',
			function (e)
			{
				var textarea = BX('mail_connect_mb_crm_new_lead_for');
				var hide = textarea.offsetHeight > 0;

				textarea.style.display = hide ? 'none' : '';
				BX[hide?'removeClass':'addClass'](this, 'mail-set-textarea-show-open');
			}
		);

		this.setSmtpSwitcher();
		this.setCrmSwitcher();
	},

	singleselect: function(input)
	{
		var options = BX.findChildren(input, {tag: 'input', attr: {type: 'radio'}}, true);
		for (var i in options)
		{
			if (!options.hasOwnProperty(i))
			{
				continue;
			}

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

		BX.bind(document, 'click', function(event)
		{
			event = event || window.event;
			if (event.skip_singleselect !== input)
			{
				BX(input.getAttribute('data-checked')).checked = true;
			}
		});
	},

	beforeOpenDialog: function()
	{
		return new Promise(function(resolve, reject)
		{
			if (BX.MailClientConfig.Edit.isForbiddenToShare)
			{
				BX.MailClientConfig.Edit.alertForbiddenToShare();
				return reject();
			}

			return resolve();
		});
	},

	alertForbiddenToShare: function()
	{

			B24.licenseInfoPopup.show(
				'mail-shared-mailbox-limit',
				BX.message('MAIL_MAILBOX_LICENSE_SHARED_LIMIT_TITLE'),
				BX.message('MAIL_MAILBOX_LICENSE_SHARED_LIMIT_BODY')
			);


	},

	setBlockSwitcher(
		switcherNodeId,
		oldSwitcherId,
		sectionBlockId,
		isSwitcherChecked,
		isSwitcherDisabled = false,
	)
	{
		const switcherNode = BX(switcherNodeId);
		const sectionBlockNode = BX(sectionBlockId);
		if (!switcherNode || !sectionBlockNode)
		{
			return;
		}

		const blockItemsNode = sectionBlockNode.querySelector('.mail-connect-form-items');
		const blockTitleNode = sectionBlockNode.querySelector('.mail-connect-title-block');
		if (!blockItemsNode || !blockTitleNode)
		{
			return;
		}

		const hideBlock = () => {
			BX.Dom.hide(blockItemsNode);
			BX.Dom.addClass(blockTitleNode, 'mail-connect-title-hidden-block');
			BX.Dom.removeClass(sectionBlockNode, 'mail-connect-section-block');
		};

		const showBlock = () => {
			BX.Dom.show(blockItemsNode);
			BX.Dom.removeClass(blockTitleNode, 'mail-connect-title-hidden-block');
			BX.Dom.addClass(sectionBlockNode, 'mail-connect-section-block');
		};

		const oldSwitcherNode = BX(oldSwitcherId);
		const switcher = new BX.UI.Switcher({
			node: switcherNode,
			checked: isSwitcherChecked,
			size: 'small',
			disabled: isSwitcherDisabled ?? false,
			handlers: {
				toggled: () => {
					if (switcher.checked === true)
					{
						showBlock();
					}
					else
					{
						hideBlock();
					}

					if (oldSwitcherNode)
					{
						oldSwitcherNode.click();
					}
				},
			},
		});

		if (!isSwitcherChecked)
		{
			hideBlock();
		}
	},

	showOauthErrorTour()
	{
		this.oauthErrorTour = new BX.UI.Tour.Guide({
			id: 'mail-config-oauth-error-tour',
			simpleMode: true,
			steps: [
				{
					target: '#mail_connect_mb_oauth_btn',
					title: BX.message('MAIL_CONFIG_OAUTH_ERROR_TOUR_TITLE'),
					text: BX.message('MAIL_CONFIG_OAUTH_ERROR_TOUR_TEXT'),
					position: 'bottom',
					article: '19083990',
				},
			],
		});

		this.oauthErrorTour.start();
	},

	showPasswordErrorTour()
	{
		this.oauthErrorTour = new BX.UI.Tour.Guide({
			id: 'mail-config-password-error-tour',
			simpleMode: true,
			steps: [
				{
					target: '#mail_password_form_wrapper',
					title: BX.message('MAIL_CONFIG_PASSWORD_ERROR_TOUR_TITLE'),
					text: BX.message('MAIL_CONFIG_PASSWORD_ERROR_TOUR_TEXT'),
					position: 'bottom',
					article: '19083990',
				},
			],
		});

		this.oauthErrorTour.start();
	},

	showOauthAuthorizationBlock()
	{
		var form = BX('mail_connect_form');
		var oauthFieldError = BX('mail-client-config-email-oauth-field-error');
		var oauthFieldSuccess = BX('mail-client-config-email-oauth-field-success');
		var emailOauthBlock = BX('mail-email-oauth');
		var mailConnectOauthField = BX('mail_connect_mb_oauth_field');

		if(emailOauthBlock)
		{
			BX.hide(emailOauthBlock);
			BX.hide(oauthFieldError);
			BX.hide(oauthFieldSuccess);
		}

		if (mailConnectOauthField)
		{
			mailConnectOauthField.value = 'N';
		}

		if (!form.elements['fields[mailbox_id]'])
		{
			var nameField = BX('mail_connect_mb_name_field');
			if (!nameField['__filled'])
			{
				nameField.value = '';
			}
		}

		BX.Dom.style(BX('mail_connect_mb_oauth_status'), 'display', 'none');
		BX.Dom.style(BX('mail_connect_mb_oauth_btn'), 'display', null);
	},

	setSmtpSwitcher()
	{
		this.setBlockSwitcher(
			'mail-connect-smtp-settings-title',
			'mail_connect_mb_server_smtp_switch',
			'mail-connect-section-smtp-block',
			this.smtp.switcherChecked,
			this.smtp.switcherDisabled,
		);
	},

	setCrmSwitcher() {
		if (!this.crm.integrationAvailable)
		{
			return;
		}

		this.setBlockSwitcher(
			'mail-connect-crm-settings-title',
			'mail_connect_mb_crm_switch',
			'mail-connect-section-crm-block',
			this.crm.switcherChecked,
		);
	},
};
