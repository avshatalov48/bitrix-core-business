// @flow
'use strict';

import { Cache, Dom, Event, Loc, Tag, Text, Type } from 'main.core';
import { Util } from 'calendar.util';
import { Entry } from 'calendar.entry';
import { EventEmitter } from 'main.core.events';

export default class SyncWizard extends EventEmitter
{
	TYPE = 'undefined';
	SLIDER_NAME = 'calendar:sync-wizard-slider';
	SLIDER_WIDTH = 450;
	LOADER_NAME = "calendar:loader";
	cache = new Cache.MemoryCache();
	syncStagesList = [];
	accountName = '';
	HELPDESK_CODE = 11828176;
	MIN_UPDATE_STATE_DELAY = 1500; // in ms
	CONFETTI_DELAY = 1000;

	constructor()
	{
		super();
		this.setEventNamespace('BX.Calendar.Sync.Interface.SyncWizard');

		this.BX = window.top.BX || window.BX;

		this.pullWizardEventHandler = this.handlePullNewEvent.bind(this);
		this.lastUpdateStateTimestamp = Date.now();
		this.logoIconClass = '';
	}

	openSlider()
	{
		BX.SidePanel.Instance.open(this.SLIDER_NAME, {
			contentCallback: slider => {
				return new Promise((resolve, reject) => {
					resolve(this.getContent());
				});
			},
			allowChangeHistory: false,
			events: {
				onLoad: () => {
					this.displaySyncStages();
					this.bindButtonsHandlers();
				},
				onDestroy: this.handleCloseWizard.bind(this)
			},
			cacheable: false,
			width: this.SLIDER_WIDTH,
			loader: this.LOADER_NAME,
		});

		this.slider = BX.SidePanel.Instance.getTopSlider();
		this.syncIsFinished = false;
		this.errorStatus = false;
	}

	getContent()
	{
		return Tag.render`
			<div class="calendar-sync__wrapper calendar-sync__scope">
				<div class="calendar-sync__content --border-radius">
					<div class="calendar-sync__content-block --space-bottom">
						${this.getTitleWrapper()}
						${this.getSyncStagesWrapper()}
						${this.getInfoStatusWrapper()}
						${this.getErrorWrapper()}
						${this.getFinalCheckWrapper()}
						${this.getHelpLinkWrapper()}
						${this.getButtonWrapper()}
					</div>
				</div>
			</div>
		`;
	}

	getTitleWrapper()
	{
		this.syncTitleWrapper = Tag.render`
			<div class="calendar-sync__account">
				<div class="calendar-sync__account-logo">
					<div class="calendar-sync__account-logo--image ${this.getLogoIconClass()}"></div>
				</div>
				<div class="calendar-sync__account-content">
					${this.getAccountNameNode()}
					<div class="calendar-sync__account-info">
						<div class="calendar-sync__account-info--icon --animate"></div>
						${this.getActiveStatusNode()}
					</div>
				</div>
			</div>
		`;
		return this.syncTitleWrapper;
	}

	getSyncStagesWrapper()
	{
		this.syncStagesWrapper = Tag.render`<div class="calendar-sync-stages-wrap"></div>`;
		return this.syncStagesWrapper;
	}

	getInfoStatusWrapper()
	{
		this.infoStatusWrapper = Tag.render`
			<div class="calendar-sync__content-block --space-bottom-xl" style="display: none;">
				<div class="calendar-sync__notification">
					<div class="calendar-sync__notification-title">${Loc.getMessage('CAL_INFO_STATUS_CONG_1')}</div>
					<div class="calendar-sync__notification-message">${Loc.getMessage('CAL_INFO_STATUS_CONG_2')}</div>
				</div>
			</div>
		`;
		return this.infoStatusWrapper;
	}

	getErrorWrapper()
	{
		this.errorWrapper = Tag.render`
			<div class="calendar-sync__content-block --space-bottom-xl" style="display: none;">
				<div class="calendar-sync__error">
					<div class="calendar-sync__notification-message">
						<div class="calendar-sync__notification-message-inner">
							${Loc.getMessage('CAL_ERROR_WARN_1')}
						</div>
						${Loc.getMessage('CAL_ERROR_WARN_2')}</div>
				</div>
			</div>
		`;
		return this.errorWrapper;
	}

	getHelpLinkWrapper()
	{
		this.helpLinkWrapper = Tag.render`
			<div class="calendar-sync__content-block" style="display: none;"></div>
		`;
		return this.helpLinkWrapper;
	}

	getFinalCheckWrapper()
	{
		this.finalCheckWrapper = Tag.render`
			<div class="calendar-sync__content-block" style="display: none;"></div>
		`;
		return this.finalCheckWrapper;
	}

	getButtonWrapper()
	{
		this.buttonWrapper = Tag.render`
			<div style="display: none" class="calendar-sync__content-block --align-center">
				<a class="ui-btn ui-btn-lg ui-btn-primary ui-btn-round" data-role="continue_btn">
					${Loc.getMessage('CAL_BUTTON_CONTINUE')}
				</a>
				<a style="display: none" class="ui-btn ui-btn-lg ui-btn-light-border ui-btn-round" data-role="everything_is_fine_btn">
					${Loc.getMessage('CAL_BUTTON_EVERYTHING_IS_FINE')}
				</a>
				<a style="display: none" class="ui-btn ui-btn-lg ui-btn-light-border ui-btn-round" data-role="close_button">
					${Loc.getMessage('CAL_ERROR_CLOSE')}
				</a>
			</div>
		`;

		return this.buttonWrapper;
	}

	getNewEventCardWrapper()
	{
		this.newEventCardWrapper = Tag.render`
			<div class="calendar-sync__content-block --space-bottom" style="display: none;"></div>
		`;
		return this.newEventCardWrapper;
	}

	getSkeletonWrapper()
	{
		this.skeletonWrapper = Tag.render`
			<div class="calendar-sync__content-block --space-bottom">
					<div class="calendar-sync__balloon --skeleton">
						<div class="calendar-sync__balloon__skeleton-box">
							<div class="calendar-sync__balloon__skeleton-inline-box">
								<div class="calendar-sync__balloon__skeleton-circle"></div>
								<div class="calendar-sync__balloon__skeleton-line"></div>
							</div>
							<div class="calendar-sync__balloon__skeleton-line"></div>
						</div>
						<div class="calendar-sync__content-text">${this.getSkeletonTitle()}</div>
					</div>
				</div>
		`;
		return this.skeletonWrapper;
	}

	getSkeletonTitle()
	{
		return '';
	}

	getExtraInfoWithCheckIcon()
	{
		const alreadyConnected = Object.values(this.connectionsProviders).filter(item => {
			return item.mainPanel && item.status;
		}).length > 0;

		return Tag.render`
			<div class="calendar-sync__content-text --icon-check${(alreadyConnected ? ' --disabled' : '')}">
				${Loc.getMessage('CAL_SYNC_INFO_PROMO')}
			</div>
		`;
	}

	getAccountNameNode()
	{
		if (!Type.isElementNode(this.accountNameNode))
		{
			this.accountNameNode = Tag.render`
			<div class="calendar-sync__account-title">${this.getAccountName()}</div>
		`;
		}
		return this.accountNameNode;
	}

	setAccountName(value)
	{
		this.accountName = value;
	}

	getAccountName()
	{
		return this.accountName;
	}

	getActiveStatusNode()
	{
		if (!Type.isElementNode(this.activeStatusNode))
		{
			this.activeStatusNode = Tag.render`
				<span class="calendar-active-status-node-carousel">
					<span class="calendar-active-status-node-phrase">
						${Loc.getMessage('CAL_STATUS_SYNC_IN_PROGRESS')}
					</span>
				</span>
			`;

			this.startStatusCarousel(this.activeStatusNode);
		}

		return this.activeStatusNode;
	}

	startStatusCarousel(statusNode)
	{
		const progressStatuses = [
			Loc.getMessage('CAL_STATUS_SYNC_IN_PROGRESS_STATUSES_FIRST'),
			Loc.getMessage('CAL_STATUS_SYNC_IN_PROGRESS_STATUSES_SECOND')
		];

		let dotCycle = 1;

		this.statusCarouselInterval = setInterval(() => {
			const currentPhraseNode = statusNode.firstElementChild;
			if (this.countDots(currentPhraseNode.innerText) < 3)
			{
				currentPhraseNode.innerText += '.';
				statusNode.style.width = (currentPhraseNode.offsetWidth + 1) + 'px';
				return;
			}
			if (dotCycle < 2)
			{
				dotCycle++;
				currentPhraseNode.innerText = currentPhraseNode.innerText.slice(0, -3);
				return;
			}
			dotCycle = 1;

			if (progressStatuses.length > 0)
			{
				const status = progressStatuses.shift();
				this.animateNextStatus(statusNode, status);
			}
			else
			{
				const almostDoneStatus = Loc.getMessage('CAL_STATUS_SYNC_IN_PROGRESS_ALMOST_DONE');
				this.animateNextStatus(statusNode, almostDoneStatus);
				statusNode.style.width = '';
				clearInterval(this.statusCarouselInterval);
			}
		}, 900);
	}

	animateNextStatus(carousel, phraseText)
	{
		const currentPhraseNode = carousel.firstElementChild;
		const nextPhraseNode = Tag.render`
			<span class="calendar-active-status-node-phrase">${phraseText}</span>
		`;
		carousel.append(nextPhraseNode);

		const maxWidth = Math.max(nextPhraseNode.offsetWidth, currentPhraseNode.offsetWidth);
		carousel.style.width = (maxWidth + 1) + 'px';

		currentPhraseNode.style.transition = ''; // turn on animation
		currentPhraseNode.style.transform = `translateX(-${currentPhraseNode.offsetWidth}px)`;
		nextPhraseNode.style.transform = `translateX(-${currentPhraseNode.offsetWidth}px)`;

		setTimeout(() => {
			currentPhraseNode.remove();
			nextPhraseNode.style.transition = 'none'; // turn off animation
			nextPhraseNode.style.transform = '';
		}, 300);
	}

	countDots(string)
	{
		return (string.match(/\./g) || []).length;
	}

	setSyncStages()
	{
		this.syncStagesList = [];
	}

	getSyncStages()
	{
		return this.syncStagesList;
	}

	getHelpDeskCode()
	{
		return this.HELPDESK_CODE;
	}

	displaySyncStages()
	{
		Dom.clean(this.syncStagesWrapper);
		this.getSyncStages().forEach(stage => {
			stage.renderTo(this.syncStagesWrapper);
		});
	}

	bindButtonsHandlers()
	{
		const continueButton = this.buttonWrapper.querySelector('.ui-btn[data-role="continue_btn"]');
		if (Type.isElementNode(continueButton))
		{
			Event.bind(continueButton, 'click', this.handleContinueButtonClick.bind(this));
		}

		const eifButton = this.buttonWrapper.querySelector('.ui-btn[data-role="everything_is_fine_btn"]');
		if (Type.isElementNode(eifButton))
		{
			Event.bind(eifButton, 'click', this.handleFinalCloseButtonClick.bind(this));
		}
	}

	handleContinueButtonClick()
	{
		this.showFinalStage();
	}

	showFinalStage()
	{
		this.syncIsFinished = true;

		const eifButton = this.buttonWrapper.querySelector('.ui-btn[data-role="everything_is_fine_btn"]');
		if (Type.isElementNode(eifButton))
		{
			eifButton.style.display = '';
		}

		const continueButton = this.buttonWrapper.querySelector('.ui-btn[data-role="continue_btn"]');
		if (Type.isElementNode(continueButton))
		{
			continueButton.style.display = 'none';
		}

		this.showFinalCheckWrapper();
		this.showHelpLinkWrapper();
		this.hideSyncStagesWrapper();
		this.hideInfoStatusWrapper();

		Util.getBX().Event.EventEmitter.subscribe(
			'onPullEvent-calendar',
			this.pullWizardEventHandler
		);

		this.emit('startWizardWaitingMode');
	}

	isSyncFinished()
	{
		return this.syncIsFinished;
	}

	handleFinalCloseButtonClick()
	{
		BX.SidePanel.Instance.getOpenSliders().forEach(slider =>
		{
			if (['calendar:sync-slider', 'calendar:section-slider', this.SLIDER_NAME].includes(slider.getUrl()))
			{
				slider.close();
			}
		});

		BX.ajax.runAction('calendar.api.calendarajax.analytical', {
			analyticsLabel: {
				calendarAction: 'complete_wizard_close',
				connection_type: this.TYPE
			}
		});
	}

	handleUpdateState(stateData)
	{
		const currentTimestamp = Date.now();
		if (currentTimestamp - this.lastUpdateStateTimestamp > this.MIN_UPDATE_STATE_DELAY)
		{
			this.updateState(stateData);
		}
		else
		{
			setTimeout(() => {
				this.handleUpdateState(stateData);
			}, this.MIN_UPDATE_STATE_DELAY);
		}
	}

	updateState(stateData)
	{
		if (this.errorStatus)
		{
			return;
		}

		if (stateData.stage === 'connection_created'
			&& stateData.accountName
			&& Type.isElementNode(this.accountNameNode)
		)
		{
			this.setAccountName(stateData.accountName);
			this.accountNameNode.innerHTML = Text.encode(stateData.accountName)
		}

		this.lastUpdateStateTimestamp = Date.now();
	}

	setActiveStatusFinished()
	{
		this.activeStatusNode.style.width = '';
		clearInterval(this.statusCarouselInterval);
		this.syncIsFinished = true;
		if (Type.isElementNode(this.activeStatusNode))
		{
			this.activeStatusNode.innerHTML = Text.encode(Loc.getMessage('CAL_STATUS_SYNC_SUCCESS').toUpperCase())
			Dom.remove(this.syncTitleWrapper.querySelector('.calendar-sync__account-info--icon'));
		}
	}

	showButtonWrapper()
	{
		if (Type.isElementNode(this.buttonWrapper))
		{
			this.buttonWrapper.style.display = '';
		}
	}

	hideButtonWrapper()
	{
		if (Type.isElementNode(this.buttonWrapper))
		{
			this.buttonWrapper.style.display = 'none';
		}
	}

	showInfoStatusWrapper()
	{
		if (Type.isElementNode(this.infoStatusWrapper))
		{
			this.infoStatusWrapper.style.display = '';
		}
	}

	hideInfoStatusWrapper()
	{
		if (Type.isElementNode(this.infoStatusWrapper))
		{
			this.infoStatusWrapper.style.display = 'none';
		}
	}

	showErrorWrapper()
	{
		if (Type.isElementNode(this.errorWrapper))
		{
			this.errorWrapper.style.display = '';
		}
	}

	hideErrorWrapper()
	{
		if (Type.isElementNode(this.errorWrapper))
		{
			this.errorWrapper.style.display = 'none';
		}
	}

	showFinalCheckWrapper()
	{
		if (Type.isElementNode(this.finalCheckWrapper))
		{
			this.finalCheckWrapper.style.display = '';
		}
	}

	hideFinalCheckWrapper()
	{
		if (Type.isElementNode(this.finalCheckWrapper))
		{
			this.finalCheckWrapper.style.display = 'none';
		}
	}

	showSyncStagesWrapper()
	{
		if (Type.isElementNode(this.syncStagesWrapper))
		{
			this.syncStagesWrapper.style.display = '';
		}
	}

	hideSyncStagesWrapper()
	{
		if (Type.isElementNode(this.syncStagesWrapper))
		{
			this.syncStagesWrapper.style.display = 'none';
		}
	}

	showHelpLinkWrapper()
	{
		if (Type.isElementNode(this.helpLinkWrapper))
		{
			this.helpLinkWrapper.style.display = '';
		}
	}

	hideHelpLinkWrapper()
	{
		if (Type.isElementNode(this.helpLinkWrapper))
		{
			this.helpLinkWrapper.style.display = 'none';
		}
	}

	handlePullNewEvent(event)
	{
		if (event && Type.isFunction(event.getData))
		{
			const data = {
				command: event.getData()[0],
				...event.getData()[1]
			};

			if (
				data.command === 'edit_event'
				&& data.newEvent
			)
			{
				if (Type.isElementNode(this.finalCheckWrapper))
				{
					const syncBalloon = this.finalCheckWrapper.querySelector('.calendar-sync__balloon');
					if (Type.isElementNode(syncBalloon) && Dom.hasClass(syncBalloon, '--progress'))
					{
						Dom.removeClass(syncBalloon, '--progress');
						Dom.addClass(syncBalloon, '--done');
					}
				}

				const entry = new Entry({ data: data.fields });
				this.displayNewEvent(entry);

				Util.getBX().Event.EventEmitter.unsubscribe(
					'onPullEvent-calendar',
					this.pullWizardEventHandler
				);

				const eifButton = this.buttonWrapper.querySelector('.ui-btn[data-role="everything_is_fine_btn"]');
				if (Type.isElementNode(eifButton))
				{
					eifButton.innerHTML = Text.encode(Loc.getMessage('CAL_BUTTON_KEEP_GOING'));
					Dom.addClass(eifButton, 'ui-btn-primary');
					Dom.removeClass(eifButton, 'ui-btn-light-border');
				}

				this.emit('endWizardWaitingMode');
			}
		}
	}

	displayNewEvent(entry)
	{
		// Hide skeleton
		if (Type.isElementNode(this.skeletonWrapper))
		{
			Dom.remove(this.skeletonWrapper);
		}

		if (Type.isElementNode(this.newEventCardWrapper))
		{
			this.newEventCardWrapper.style.display = '';
			Dom.clean(this.newEventCardWrapper);
			this.newEventCardWrapper.appendChild(this.getNewEventCard(entry));
		}
	}

	getNewEventCard(entry)
	{
		const from = new Date(entry.from.getTime() - (parseInt(entry.data['~USER_OFFSET_FROM']) || 0) * 1000);
		const to = new Date(entry.to.getTime() - (parseInt(entry.data['~USER_OFFSET_TO']) || 0) * 1000);
		const fromTimestamp = from.getTime();
		const dateFrom = BX.date.format(Util.getDayMonthFormat(), fromTimestamp / 1000);
		const timeFrom = Util.formatTime(from.getHours(), from.getMinutes());
		const timeTo = Util.formatTime(to.getHours(), to.getMinutes());
		const timeField = entry.isFullDay()
				? Loc.getMessage('CAL_WIZARD_FULL_DAY')
				: timeFrom + ' - ' + timeTo;

		this.newEventCard = Tag.render`
			<div class="calendar-sync__balloon --calendar ${entry.isFullDay() ? '--fullday-event' : ''}">
				<div class="calendar-sync__content-text">
					${dateFrom}
					<span class="calendar-date-year">
						${BX.date.format('Y', fromTimestamp / 1000)}
					</span>
				</div>
				<div class="calendar-sync__content-text">${BX.date.format('l', fromTimestamp / 1000)}</div>
				<div class="calendar-sync__time-box">
					<div class="calendar-sync__time">
						<div class="calendar-sync__time-date">${timeFrom}</div>
						<div class="calendar-sync__time-line"></div>
					</div>
					<div class="calendar-sync__time-notification-box">
						<div class="calendar-sync__content-text">${Text.encode(entry.getName())}</div>
						<div class="calendar-sync__content-text">${timeField}</div>
					</div>
					<div class="calendar-sync__time">
						<div class="calendar-sync__time-date">${timeTo}</div>
						<div class="calendar-sync__time-line"></div>
					</div>
				</div>
			</div>
		`;

		return this.newEventCard;
	}

	handleCloseWizard()
	{
		this.slider = null;

		clearInterval(this.statusCarouselInterval);

		Util.getBX().Event.EventEmitter.unsubscribe(
			'onPullEvent-calendar',
			this.pullWizardEventHandler
		);

		this.emit('onClose');
	}

	showConfetti()
	{
		setTimeout(() => {
			const bx = Util.getBX();
			bx.UI.Confetti.fire({
				particleCount: 240,
				spread: 170,
				origin: { y: 0.3, x: 0.9 },
				zIndex: (bx.SidePanel.Instance.getTopSlider().getZindex() + 1)
			});
		}, this.CONFETTI_DELAY);
	}

	getLogoIconClass()
	{
		return this.logoIconClass;
	}

	getSlider()
	{
		return this.slider;
	}

	setErrorState()
	{
		this.errorStatus = true;
		this.showErrorWrapper();
		this.hideInfoStatusWrapper();
		this.hideSyncStagesWrapper();
		this.showButtonWrapper();
		Dom.addClass(this.syncTitleWrapper, '--error');
		if (Type.isElementNode(this.activeStatusNode))
		{
			this.activeStatusNode.innerHTML = Text.encode(Loc.getMessage('CAL_STATUS_SYNC_ERROR').toUpperCase())
		}

		const closeButton = this.buttonWrapper.querySelector('.ui-btn[data-role="close_button"]');
		if (Type.isElementNode(closeButton))
		{
			closeButton.style.display = '';
			Event.bind(closeButton, 'click', () => {
				BX.SidePanel.Instance.getOpenSliders().forEach(slider =>
				{
					if (['calendar:sync-slider', 'calendar:section-slider', this.SLIDER_NAME].includes(slider.getUrl()))
					{
						slider.close();
					}
				});
				BX.reload();
			});
		}

		const continueButton = this.buttonWrapper.querySelector('.ui-btn[data-role="continue_btn"]');
		if (Type.isElementNode(continueButton))
		{
			continueButton.style.display = 'none';
		}
	}
}
