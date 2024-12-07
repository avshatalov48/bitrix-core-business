;(function ()
{

	if (window.BXMailView)
	{
		return;
	}

	var BXMailView = function (options)
	{
		if (BXMailView.__views[options.messageId])
		{
			return BXMailView.__views[options.messageId];
		}

		this.mailboxId = options.mailboxId;
		this.id = options.messageId;
		this.options = options;
		this.progressPercent = 0;
		this.progressInterval = 0;

		BXMailView.__views[this.id] = this;
		BXMailView.__views[this.id].init();
	};

	BXMailView.__views = {};

	BXMailView.getView = function (id)
	{
		return BXMailView.__views[id];
	};

	BXMailView.prototype.init = function ()
	{
		if (this.options.isAjaxBody && this.options.messageBodyElementId)
		{
			this.ajaxLoadMessageBody();
		}
		else
		{
			this.ajaxLoadAttachments();
		}
		this.addPageSwapper();

		if (this.options.fileRefreshButtonId)
		{
			this.setRefreshFileButtonAction();
		}
	};

	BXMailView.prototype.ajaxLoadMessageBody = function ()
	{
		const messageId = this.options.messageId;
		if (!messageId)
		{
			return;
		}

		this.startProgress();
		this.bindErrorClose();

		BX.ajax.runComponentAction('bitrix:mail.client.message.view', 'getHtmlBody', {
			mode: 'class',
			data: {id: messageId},
		}).then((response) =>
		{
			this.handleSuccessResponse(response.data);
		}, () =>
		{
			this.handleFailedResponse();
		});
	}

	BXMailView.prototype.handleSuccessResponse = function (data)
	{
		this.stopProgress();
		if (BX.type.isNotEmptyObject(data) && BX.type.isString(data.messageHtml))
		{
			this.insertBodyText(data.messageHtml);

			if (BX.type.isString(data.quote))
			{
				this.insertQuoteText(data.quote);
			}
		}
		safeHide(this.options.warningWaitElementId);

		this.showControls();
		this.ajaxLoadAttachments();
	}

	BXMailView.prototype.insertQuoteText = function (quote)
	{
		const options = this.options;
		if (BX.type.isString(quote)
			&& BX.type.isString(options.formId)
			&& BX.type.isString(options.quoteFieldName)
			&& BX.type.isObject(BXMainMailForm)
			&& BX.type.isObject(BXMainMailForm.getForm(options.formId))
			&& BX.type.isArray(BXMainMailForm.getForm(options.formId).fields))
		{
			const fields = BXMainMailForm.getForm(options.formId).fields;
			for (const i in fields)
			{
				if (fields.hasOwnProperty(i))
				{
					if (BX.type.isObject(fields[i]) && fields[i].name === options.quoteFieldName)
					{
						fields[i].value = quote;
						break;
					}
				}
			}
		}
	}

	BXMailView.prototype.insertBodyText = function (html)
	{
		const options = this.options;
		const messageBodyElement = document.getElementById(options.messageBodyElementId);
		if (!messageBodyElement)
		{
			return;
		}
		messageBodyElement.innerHTML = '<div id="mail-message-wrapper">' + html + '</div>';
		if (BX.type.isObject(options.bxMailMessage))
		{
			BX.onCustomEvent(options.bxMailMessage, 'MailMessage:reInitMessageBody');
		}
	}

	BXMailView.prototype.ajaxLoadAttachments = function ()
	{
		const options = this.options;
		const self = this;
		if (!options.ajaxAttachmentElementId || !options.messageId)
		{
			return;
		}

		const ajaxAttachmentElement = document.getElementById(options.ajaxAttachmentElementId);
		if (!ajaxAttachmentElement)
		{
			return;
		}

		const ajaxAttachmentLoader = new BX.Loader({
			target: ajaxAttachmentElement,
			mode: 'inline',
			size: 20,
			color: '#828b95',
		});
		ajaxAttachmentLoader.show();

		BX.ajax.runComponentAction('bitrix:mail.client.message.view', 'getAttachments', {
			mode: 'class',
			data: {
				id: options.messageId,
				mail_uf_message_token: options.mailUfMessageToken,
			},
		}).then(function (response)
		{
			if (BX.type.isNotEmptyObject(response.data) && BX.type.isString(response.data.attachmentsHtml))
			{
				ajaxAttachmentLoader.hide();
				ajaxAttachmentElement.innerHTML = response.data.attachmentsHtml;
				if (options.fileRefreshButtonId)
				{
					self.setRefreshFileButtonAction();
				}
			}
		}, function ()
		{
			ajaxAttachmentLoader.hide();
			BX.hide(ajaxAttachmentElement.parentElement);
		});
	}

	BXMailView.prototype.showError = function ()
	{
		safeHide(this.options.warningWaitElementId);
		safeShow(this.options.warningFailElementId);
	}

	BXMailView.prototype.startProgress = function ()
	{
		const options = this.options;
		if (!options.bodyLoaderElementId || !options.bodyLoaderMaxTime)
		{
			return;
		}
		const progressContainer = document.getElementById(options.bodyLoaderElementId);
		if (!progressContainer)
		{
			return;
		}
		const myProgress = new BX.UI.ProgressBar({
			maxValue: 100,
			value: 0,
		});
		myProgress.renderTo(BX(options.bodyLoaderElementId));

		const stepTime = options.bodyLoaderMaxTime / 100 * 1000;
		this.progressInterval = setInterval(() =>
		{
			if (this.progressPercent >= 100)
			{
				this.stopProgress();
				this.progressPercent = 100;
			}
			else
			{
				this.progressPercent += 1;
			}
			myProgress.setValue(this.progressPercent);
			myProgress.update();
		}, stepTime);
	}

	BXMailView.prototype.stopProgress = function ()
	{
		clearInterval(this.progressInterval);
	}

	BXMailView.prototype.handleFailedResponse = function ()
	{
		this.stopProgress();
		this.showError();
		this.showControls();
		this.ajaxLoadAttachments();
	}

	BXMailView.prototype.showControls = function ()
	{
		safeShow(this.options.messageControlElementId);
		safeShow(this.options.fastReplyElementId);
	}

	BXMailView.prototype.bindErrorClose = function ()
	{
		if (!this.options.warningFailElementId)
		{
			return;
		}

		const errorContainer = document.getElementById(this.options.warningFailElementId);
		if (!errorContainer)
		{
			return;
		}

		const closeElement = errorContainer.querySelector('.ui-alert-close-btn');
		if (!closeElement)
		{
			return;
		}

		BX.bind(closeElement, 'click', function ()
		{
			BX.hide(errorContainer);
		});
	}

	BXMailView.prototype.addPageSwapper = function()
	{
		const slider = BX.SidePanel.Instance.getTopSlider();
		const container = slider.iframe.contentDocument.getElementById('header-page-swapper-container');
		if (!container)
		{
			return;
		}

		if (container.firstChild)
		{
			return;
		}

		const pagesHref = slider.getData().get('hrefList');
		if (!slider || !BX.UI.SidePanel.PageSwapper || !pagesHref)
		{
			container.remove();

			return;
		}

		this.pageSwapper = new BX.UI.SidePanel.PageSwapper({
			slider,
			container,
			pagesHref,
			pageType: 'mail',
		});
		this.pageSwapper.init();
		const openSliders = BX.SidePanel.Instance.getOpenSliders();
		const count = openSliders.length;
		const prevSliderWindow = openSliders[count - 2].getFrameWindow();
		const enableNextPage = slider.getData().get('enableNextPage');
		if (enableNextPage && !this.pageSwapper.hasPagesBeforeEnd(3))
		{
			prevSliderWindow.document.querySelector('.main-grid-more-btn').click();
		}
	};

	BXMailView.prototype.setRefreshFileButtonAction = function()
	{
		const button = document.getElementById(this.options.fileRefreshButtonId);
		if (!button)
		{
			return;
		}

		const icon = document.querySelector('.mail-msg-refresh-files-button-icon');
		if (!icon)
		{
			return;
		}

		const toggleButton = () => {
			const rotateButtonClass = 'mail-msg-refresh-files-button-icon-rotate';
			if (this.refreshFilesInProgress)
			{
				BX.Dom.removeClass(icon, rotateButtonClass);
				this.refreshFilesInProgress = false;

				return;
			}

			BX.Dom.addClass(icon, rotateButtonClass);
			this.refreshFilesInProgress = true;
		};

		BX.bind(button, 'click', () => {
			if (this.refreshFilesInProgress)
			{
				return;
			}

			toggleButton();
			BX.ajax.runAction('mail.syncingattachments.resyncAttachments', {
				data: {
					messageId: this.id,
					mailboxId: this.mailboxId,
				},
			}).then((response) => {
				if (response.status !== 'success')
				{
					toggleButton();

					return;
				}

				location.reload();
			}).catch(() => {
				toggleButton();
			});
		});
	};

	function safeHide(elementId)
	{
		if (elementId)
		{
			const element = document.getElementById(elementId);
			if (element)
			{
				BX.hide(element);
			}
		}
	}

	function safeShow(elementId)
	{
		if (elementId)
		{
			const element = document.getElementById(elementId);
			if (element && element.style && element.style.display === 'none')
			{
				element.style.display = '';
			}
		}
	}

	window.BXMailView = BXMailView;

})();
