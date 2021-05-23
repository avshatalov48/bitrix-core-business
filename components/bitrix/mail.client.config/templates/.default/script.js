BX.namespace("BX.MailClientConfig");
BX.MailClientConfig.Edit = {

	isForbiddenToShare: false,

	init: function(params)
	{
		if (BX.type.isNotEmptyObject(params))
		{
			this.isForbiddenToShare = params.isForbiddenToShare || false;
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


	}

};