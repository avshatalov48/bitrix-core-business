import { Type, Dom, Event, Tag, Loc } from 'main.core';
import {Step} from './step.js';
import {Manager} from './manager.js';

export class Scenario extends Event.EventEmitter {
	constructor(options = {}) {
		super();
		this.setEventNamespace('BX.UI.Tutor.Scenario');

		this.stepPopup = null;
		this.arrowTimer = null;
		this.guide = null;
		this.loader = null;

		this.arrowWrap = null;
		this.prevArrow = null;
		this.nextArrow = null;

		this.currentStepIndex = 0;
		this.currentStep = null;
		this.isAddedSteps = false;
		this.hasArrows = false;

		this.isLoading = true;

		this.setOptions(options);

		this.btn = document.getElementById('ui-tutor-btn-wrap');
		this.informer = document.getElementById('ui-tutor-informer');

		this.layout = {
			stepBlock: null,
			progress: null,
			counter: null,
			counterContainer: null,
			title: null,
			description: null,
			collapseBlock: null,
			collapseTitle: null,
			collapseDescription: null,
			content: null,
			contentInner: null,
			contentBlock: null,
			url: null,
			target: null,
			startBtn: null,
			nextBtn: null,
			repeatBtn: null,
			deferBtn: null,
			help: null,
			completedBtn: null,
			completedBlock: null,
			finishedBlock: null,
			supportLink: null
		};

		this.sections = [
			'settings',
			'scenario',
			'work'
		];

		this.loadYoutubeApiScript();

		this.subscribe("onYouTubeReady", () => {
			this.setVideoItems();
		});
	}

	loadYoutubeApiScript()
	{
		const onYouTubeReadyEvent = function() {
			this.emit("onYouTubeReady", { scenario: this});
		}.bind(this);

		if (!window.YT)
		{
			let isNeedCheckYT = true;
			const tag = document.createElement('script');
			tag.src = "https://www.youtube.com/iframe_api";
			const firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

			let ytCheckerTimer = setInterval(function() {
				if (isNeedCheckYT)
				{
					if (window.YT && Type.isFunction(window.YT.Player))
					{
						clearInterval(ytCheckerTimer);
						onYouTubeReadyEvent();
					}
				}
			}, 200);

			setTimeout(function() {
				clearInterval(ytCheckerTimer);
				isNeedCheckYT = false;
			}, 2000);
		}
		else {
			setTimeout(function() {
				onYouTubeReadyEvent();
			}.bind(this), 100);
		}

	}

	setOptions (options)
	{
		this.fireCurrentStepEvent('onBeforeSetOptions', false);
		options = Type.isPlainObject(options) ? options : {};
		let currentStep = this.getCurrentStep();

		/** @var {Step[]} */
		this.steps = [];
		if (Array.isArray(options.steps))
		{
			options.steps.forEach(step => {
				this.steps.push(new Step(step));
			});
		}
		if (currentStep instanceof Step)
		{
			let stepInList = this.findStepById(currentStep.getId());
			if (stepInList)
			{
				currentStep = stepInList;
			}
		}
		else if (Type.isString(options.currentStepId) && options.currentStepId.length > 0)
		{
			let stepInList = this.findStepById(options.currentStepId);
			if (stepInList)
			{
				currentStep = stepInList;
				if(options.currentStepIsActive === true)
				{
					currentStep.activate();
				}
			}
		}
		if (!currentStep)
		{
			let uncompletedStep = this.getFirstUncompletedStep();
			if (uncompletedStep)
			{
				currentStep = uncompletedStep;
			}
		}
		if (!currentStep && this.steps && this.steps[0])
		{
			currentStep = this.steps[0];
		}
		this.setCurrentStep(currentStep);

		if (options)
		{
			this.isLoading = false;
		}

		this.title = options.title || '';
		this.supportLink = options.supportLink || '';
		this.isFinished = options.isFinished || false;
		this.fireCurrentStepEvent('onAfterSetOptions', false);
	}

	/**
	 * @param {Step} step
	 */
	setCurrentStep(step)
	{
		if (step instanceof Step)
		{
			this.currentStep = step;
			let steps = this.steps;

			if (Type.isArray(steps))
			{
				this.currentStepIndex = steps.indexOf(step);
			}
			this.fireCurrentStepEvent('onStartStep');
		}
	}

	/**
	 * @public
	 */
	start(complexAnimation)
	{
		this.emit("onStart", { scenario: this});

		if (complexAnimation) // animate transition from collapsed popup to step popup
		{
			this.complexAnimation = true;
		}
		this.showPopup(this.getStepPopup());
		this.toggleCompletedState();
		this.toggleNavBtn();
		this.setPopupData();

		if (this.isAddedSteps)
		{
			this.hideFinalState();
		}

		if (!this.hasArrows)
		{
			this.initArrows();
		}

		this.complexAnimation = false;
		this.fireCurrentStepEvent('onShowComplete');
	}

	findStepById(stepId)
	{
		for (let i = 0; i < this.steps.length; i++)
		{
			const step = this.steps[i];
			if (step.getId() === stepId)
			{
				return step;
			}
		}

		return null;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getStepPopup()
	{
		const clickOnCloseIcon = () => {
			this.emit("onClickOnCloseIcon", { scenario: this});
		};

		if (!this.stepPopup)
		{
			this.stepPopup =
				Tag.render`
					<div class="ui-tutor-popup ui-tutor-popup-step">
						<div class="ui-tutor-popup-header">
							<span class="ui-tutor-popup-header-icon"></span>
							<span class="ui-tutor-popup-header-title">
								<span class="ui-tutor-popup-header-counter">
									${Loc.getMessage('JS_UI_TUTOR_TITLE')}.
									${this.getCounterContainer()}
								</span>
								<span class="ui-tutor-popup-header-subtitle">${this.title}</span>
							</span>
							${this.getDeferLink()}
						</div>
						<div class="ui-tutor-popup-content">
							${this.getContentBlock()}
						</div>
						<div class="ui-tutor-popup-step-wrap">
							<div class="ui-tutor-popup-step-inner">
								<div class="ui-tutor-popup-arrow-wrap"></div>
								<div class="ui-tutor-popup-step-list-wrap">
									${this.getStepBlock()}
								</div>
							</div>
						</div>
						<div class="ui-tutor-popup-icon-close" onclick="${clickOnCloseIcon.bind(this)}"></div>
						<div class="ui-tutor-popup-icon-angle"></div>
					</div>
				`;
			this.fireCurrentStepEvent('onCreateStepPopupNode');
			Dom.append(this.stepPopup, document.body);
			this.fireCurrentStepEvent('onAfterAppendStepPopupNode');
		}

		return this.stepPopup;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getContentBlock()
	{
		if (!this.layout.contentBlock)
		{
			this.layout.contentBlock =
				Tag.render`
					<div class="ui-tutor-popup-content-block">
						${this.getContentInner()}
						${this.getFooter()}
					</div>
				`;
		}

		return this.layout.contentBlock;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getFooter()
	{
		if (!this.layout.footer)
		{
			this.layout.footer =
				Tag.render`
					<div class="ui-tutor-popup-footer">
						${this.getNavigation()}
						${this.getBtnContainer()}
					</div>
				`;

			if (Manager.getInstance().feedbackFormId) {
				Dom.append(this.getSupportLink(), this.layout.footer);
			}

		}

		return this.layout.footer;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getContentInner()
	{
		if (!this.layout.contentInner)
		{
			this.layout.contentInner =
				Tag.render`
					<div class="ui-tutor-popup-content-inner">
						${this.getTitle()}
						${this.getDescription()}
						${this.getHelpBlock()}
					</div>
				`;
		}

		return this.layout.contentInner;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getBtnContainer()
	{
		if (!this.layout.btnContainer)
		{
			this.layout.btnContainer =
				Tag.render`
					<div class="ui-tutor-popup-btn">
						${this.getStartBtn()}
						${this.getRepeatBtn()}
						${this.getCompletedBtn()}
					</div>
				`;
		}

		return this.layout.btnContainer;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getNavigation()
	{
		if (!this.layout.navigation)
		{
			this.layout.navigation =
				Tag.render`
					<div class="ui-tutor-popup-nav"></div>
				`;

			this.layout.backBtn =
				Tag.render`
					<span class="ui-tutor-popup-nav-item ui-tutor-popup-nav-item-prev" onclick="${this.clickOnBackBtn.bind(this)}"></span>
				`;

			this.layout.nextBtn =
				Tag.render`
					<span class="ui-tutor-popup-nav-item ui-tutor-popup-nav-item-next" onclick="${this.clickOnNextBtn.bind(this)}"></span>
				`;

			Dom.append(this.layout.backBtn, this.layout.navigation);
			Dom.append(this.layout.nextBtn, this.layout.navigation);
		}

		return this.layout.navigation;
	}

	/**
	 * @private
	 * @param {HTMLElement} node
	 */
	setInformer(node)
	{
		this.setInformerCount(this.steps.length - this.getCompletedSteps());
	}

	/**
	 * @public
	 * @param {Number} num
	 */
	setInformerExternal(num)
	{
		this.setInformerCount(num);
	}

	/**
	 * @private
	 */
	setInformerCount(num)
	{
		Manager.setCount(num);
	}

	/**
	 * @public
	 * @param {Event} event
	 * @param {Boolean} complexAnimation
	 */
	closeStepPopup(event, complexAnimation)
	{
		if (!this.stepPopup)
		{
			return;
		}
		if (event)
		{
			event.stopPropagation();
		}
		this.fireCurrentStepEvent('onCloseStepPopup');
		if (complexAnimation) // animate transition from collapsed popup to step popup
		{
			this.complexAnimation = true;
		}
		this.fadeAnimation(this.getStepPopup());
		setTimeout(function () {
			this.hideNode(this.getStepPopup());
		}.bind(this), 310);
		this.complexAnimation = false;
	}

	/**
	 * @public
	 * @returns {number}
	 */
	getCompletedSteps()
	{
		let total = 0;
		for (let i = 0; i < this.steps.length; i += 1)
		{
			if (this.steps[i].isCompleted)
			{
				total += 1;
			}
		}
		return total;
	}

	/**
	 * @private
	 */
	setStepCounter()
	{
		this.getCounter().textContent = Loc.getMessage('JS_UI_TUTOR_COUNTER_NUMBER')
			.replace('#NUMBER#', this.steps.indexOf(this.getCurrentStep()) + 1)
			.replace('#NUMBER_TOTAL#', this.steps.length);
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getCounterContainer()
	{
		if (!this.layout.counterContainer)
		{
			this.layout.counterContainer =
				Tag.render`
					<span class="ui-tutor-popup-header-counter-step">
						${this.getCounter()}
					</span>
				`;
		}

		return this.layout.counterContainer;
	}
	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getCounter()
	{
		if (!this.layout.counter)
		{
			this.layout.counter =
				Tag.render`
					<span class="ui-tutor-popup-header-counter-number"></span>
				`;
		}

		return this.layout.counter;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getDeferLink()
	{
		if (!this.layout.deferLink)
		{
			this.layout.deferLink =
				Tag.render`
					<span class="ui-tutor-popup-defer-link">
						${Loc.getMessage('JS_UI_TUTOR_BTN_DEFER')}
					</span>
				`;

			const deferMenu = new BX.PopupMenuWindow({
				angle: true,
				offsetLeft: 15,
				className: 'ui-tutor-popup-defer-menu',
				bindElement: this.layout.deferLink,
				items: [
					{ text: Loc.getMessage('JS_UI_TUTOR_DEFER_MENU_HOUR'),
						onclick: function() {
							this.emit("onDeferOneHour", { scenario: this});
							deferMenu.close();
						}.bind(this)
					},
					{ text: Loc.getMessage('JS_UI_TUTOR_DEFER_MENU_TOMORROW'),
						onclick: function() {
							this.emit("onDeferTomorrow", { scenario: this});
							deferMenu.close();
						}.bind(this)
					},
					{
						text: Loc.getMessage('JS_UI_TUTOR_DEFER_MENU_WEEK'),
						onclick: function() {
							this.emit("onDeferWeek", { scenario: this});
							deferMenu.close();
						}.bind(this)
					},
					{
						text: Loc.getMessage('JS_UI_TUTOR_DEFER_MENU_FOREVER'),
						onclick: function() {
							this.emit("onDeferForever", { scenario: this});
							deferMenu.close();
						}.bind(this)
					}
				]
			});

			Event.bind(this.layout.deferLink, "click", () => {
				deferMenu.show();
			});
		}

		return this.layout.deferLink;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getStartBtn()
	{
		if (!this.layout.startBtn)
		{
			this.layout.startBtn =
				Tag.render`
					<button class="ui-btn ui-btn-primary ui-btn-round" onclick="${this.clickStartHandler.bind(this)}">
						${Loc.getMessage('JS_UI_TUTOR_BTN_START')}
					</button>
				`;
		}

		return this.layout.startBtn;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getCompletedBtn()
	{
		if (!this.layout.completedBtn)
		{
			this.layout.completedBtn =
				Tag.render`
					<button class="ui-btn ui-btn-success ui-btn-round" onclick="${this.showSuccessState.bind(this)}">
						${Loc.getMessage('JS_UI_TUTOR_BTN_COMPLETED')}
					</button>
				`;
		}

		return this.layout.completedBtn;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getRepeatBtn()
	{
		if (!this.layout.repeatBtn)
		{
			this.layout.repeatBtn =
				Tag.render`
					<button class="ui-btn ui-btn-primary ui-btn-round" onclick="${this.repeatStep.bind(this)}">
						${Loc.getMessage('JS_UI_TUTOR_BTN_REPEAT')}
					</button>
				`;
		}

		return this.layout.repeatBtn;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getDeferBtn()
	{
		if (!this.layout.deferBtn)
		{
			this.layout.deferBtn =
				Tag.render`
					<button class="ui-btn ui-btn-link ui-btn-round" onclick="${this.closeStepPopup.bind(this)}">
						${Loc.getMessage('JS_UI_TUTOR_BTN_DEFER')}
					</button>
				`;
		}

		return this.layout.deferBtn;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getCompletedBLock()
	{
		if (!this.layout.completedBlock)
		{
			this.layout.completedBlock =
				Tag.render`
					<div class="ui-tutor-popup-completed">
						<div class="ui-tutor-popup-completed-icon"></div>
						<div class="ui-tutor-popup-completed-text">${Loc.getMessage('JS_UI_TUTOR_STEP_COMPLETED')}</div>
					</div>
				`;
		}

		return this.layout.completedBlock;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getTitle()
	{
		if (!this.layout.title)
		{
			this.layout.title =
				Tag.render`
					<div class="ui-tutor-popup-step-title"></div>
				`;
		}

		return this.layout.title;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getDescription()
	{
		if (!this.layout.description)
		{
			this.layout.description =
				Tag.render`
					<div class="ui-tutor-popup-step-decs"></div>
				`;
		}

		return this.layout.description;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getSupportLink()
	{
		if (!this.layout.supportLink)
		{
			this.layout.supportLink =
				Tag.render`
					<a class="ui-tutor-popup-support-link" onclick="${this.supportLinkHandler.bind(this)}">
						${Loc.getMessage('JS_UI_TUTOR_BTN_SUPPORT')}
					</a>
				`;
		}

		return this.layout.supportLink;
	}

	setInvisible()
	{
		this.hideNode(this.getStepPopup());
	}

	setVisible()
	{
		this.showNode(this.getStepPopup());
	}

	supportLinkHandler()
	{
		this.emit('supportLinkClick');
		Manager.getInstance().showFeedbackForm();
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getStepBlock()
	{
		if (!this.layout.stepBlock)
		{
			this.layout.stepBlock =
				Tag.render`
					<div class="ui-tutor-popup-step-list"></div>
				`;

			this.layout.stepItems = [];

			for (let i = 0; i < this.steps.length; i += 1)
			{
				const currentStepIndex =
					Tag.render`
						<span class="ui-tutor-popup-step-item" data-step=${i} onclick="${this.switchStep.bind(this)}">
							<span class="ui-tutor-popup-step-item-number">${i + 1}</span>
						</span>
					`;

				this.layout.stepItems.push(currentStepIndex);
				Dom.append(currentStepIndex, this.layout.stepBlock);
			}
			this.setStepItems();
		}

		return this.layout.stepBlock;
	}

	/**
	 * @private
	 */
	setStepItems()
	{
		if (this.layout && this.layout.stepItems)
		{
			for (let i = 0; i < this.steps.length; i += 1)
			{
				if (this.layout.stepItems[i])
				{
					Dom.removeClass(this.layout.stepItems[i], 'ui-tutor-popup-step-item-current');
					if (i === this.currentStepIndex)
					{
						Dom.addClass(this.layout.stepItems[i], 'ui-tutor-popup-step-item-current');
					}
					if (this.steps[i].isCompleted)
					{
						Dom.addClass(this.layout.stepItems[i], 'ui-tutor-popup-step-item-completed');
					}
				}
			}
		}

	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getHelpBlock()
	{
		if (!this.layout.help)
		{
			this.layout.help =
				Tag.render`
					<div class="ui-tutor-popup-help">
						${this.getHelpLink()}
					</div>
				`;
		}

		return this.layout.help;
	}

	setVideoItems()
	{
		for (let i = 0; i < this.steps.length; i += 1)
		{
			const currentVideo =
				Tag.render`
					<div class="ui-tutor-popup-video" data-step=${i}></div>
				`;
			Dom.prepend(currentVideo, this.getHelpBlock());

			if (window.YT && Type.isObject(window.YT) && Type.isFunction(window.YT.Player) && this.steps[i].video !== null)
			{
				const playerData = {
					videoId: this.steps[i].video,
					events: {
						'onReady': function(event) {
							event.target.mute();
							event.target.pauseVideo();
							event.target.setPlaybackQuality('hd720');

							if (+event.target.getIframe().getAttribute('data-step') === this.currentStepIndex)
							{
								Dom.addClass(event.target.getIframe(), 'ui-tutor-popup-video-show');
								event.target.playVideo();
							}
						}.bind(this)
					},
					playerVars: {
						cc_load_policy: 1,
						cc_lang_pref: 'ru',
						rel: 0
					},
				};
				this.fireCurrentStepEvent('onBeforeCreateVideo', true, {
					playerData
				});
				this.steps[i].videoObj = new YT.Player(currentVideo, playerData);
				this.fireCurrentStepEvent('onAfterCreateVideo');
			}
		}
	}

	pauseCurrentVideo()
	{
		let step = this.getCurrentStep();

		if (window.YT && step instanceof Step)
		{
			let video = step.getVideoObj();
			if (Type.isObject(video) && video.pauseVideo)
			{
				video.pauseVideo();
			}
		}
	}

	playCurrentVideo()
	{
		let step = this.getCurrentStep();

		if (window.YT && step instanceof Step)
		{
			let video = step.getVideoObj();
			if (Type.isObject(video) && video.playVideo)
			{
				video.playVideo();
			}
		}
	}

	getHelpLink()
	{
		if (!this.layout.link)
		{
			this.layout.link =
				Tag.render`
					<span class="ui-tutor-popup-help-link" onclick="${this.handleClickLinkHandler.bind(this)}">
						<span class="ui-tutor-popup-help-link-text">${Loc.getMessage('JS_UI_TUTOR_ARTICLE_HELP_TOPIC')}</span>
					</span>
				`;
		}

		return this.layout.link;
	}

	/**
	 * @private
	 */
	handleClickLinkHandler()
	{
		this.emit('helpLinkClick');
	}

	/**
	 * @public
	 * @param {HTMLElement} node
	 */
	showPopup(node)
	{
		this.showAnimation(node);
		node.style.display = 'block';
	}

	/**
	 * @public
	 * @param {HTMLElement} node
	 */
	showNode (node)
	{
		node.style.display = 'block';
	}

	/**
	 * @public
	 * @param {HTMLElement} node
	 */
	hideNode (node)
	{
		node.style.display = 'none';
	}

	/**
	 * @public
	 * @param {HTMLElement} node
	 */
	removePopup(node)
	{
		Dom.remove(node);
	}

	/**
	 * @private
	 */
	clickOnNextBtn()
	{
		this.fireCurrentStepEvent('onBeforeClickNavNextBtn');
		if (this.getCompletedSteps() === this.steps.length && !this.isFinished)
		{
			this.isAddedSteps = false;
			Dom.remove(this.getNewStepsSection());
			Dom.removeClass(this.getFinishedBlock(), 'ui-tutor-popup-finished-new');
			this.showFinalState();

			return;
		}

		if (this.getCompletedSteps() === this.steps.length && this.currentStepIndex + 1 === this.steps.length)
		{
			this.currentStepIndex = -1;
		}

		Dom.removeClass(this.getStartBtn(), 'ui-btn-wait');
		this.increaseCurrentIndex();
		this.showStep();
		this.toggleNavBtn();
		this.fireCurrentStepEvent('onAfterClickNavNextBtn');
	}

	clickOnBackBtn()
	{
		this.fireCurrentStepEvent('onBeforeClickNavBackBtn');
		this.reduceCurrentIndex();
		this.toggleNavBtn();
		this.showStep();
		this.fireCurrentStepEvent('onAfterClickNavBackBtn');
	}

	toggleNavBtn()
	{
		Dom.removeClass(this.layout.backBtn, 'ui-tutor-popup-nav-item-disabled');
		Dom.removeClass(this.layout.nextBtn, 'ui-tutor-popup-nav-item-disabled');

		if (this.currentStepIndex === 0)
		{
			Dom.addClass(this.layout.backBtn, 'ui-tutor-popup-nav-item-disabled');
		}
		if (this.currentStepIndex + 1 === this.steps.length)
		{
			Dom.addClass(this.layout.nextBtn, 'ui-tutor-popup-nav-item-disabled');
		}
	}

	showStep()
	{
		// when last step is completed, but some steps are not
		if (this.clickOnCompletedBtn && this.currentStepIndex === this.steps.length)
		{
			let nextUncompletedStep = this.getFirstUncompletedStep();
			if (nextUncompletedStep)
			{
				this.setCurrentStep(nextUncompletedStep);
			}
		}

		this.scrollToStep();
		this.toggleCompletedState();
		this.setPopupData();

		this.clickOnCompletedBtn = false;
		this.fireCurrentStepEvent('onAfterShowStep');
	}


	/**
	 * @private
	 */
	switchStep()
	{
		this.fireCurrentStepEvent('onBeforeSwitchStep');
		this.setCurrentStep(this.steps[+window.event.target.getAttribute('data-step')]);
		this.fireCurrentStepEvent('onAfterSwitchStep');

		if (this.layout.finishedBlock)
		{
			this.hideFinalState();
		}

		this.showStep();
		this.toggleNavBtn();
		this.fireCurrentStepEvent('onEndSwitchStep');
	}

	/**
	 * @private
	 */
	getFirstUncompletedStep()
	{
		for (let i = 0; i < this.steps.length; i += 1)
		{
			if (!this.steps[i].isCompleted)
			{
				return this.steps[i];
			}
		}

		return null;
	}

	/**
	 * @private
	 */
	toggleCompletedState()
	{
		const currentStep = this.getCurrentStep();
		if (currentStep)
		{
			if (currentStep.getCompleted())
			{
				this.showNode(this.getRepeatBtn());
				this.hideNode(this.getStartBtn());
				this.hideNode(this.getCompletedBtn());
			}
			else if (currentStep.isActive)
			{
				this.showNode(this.getCompletedBtn());
				this.hideNode(this.getStartBtn());
				this.showNode(this.getRepeatBtn());
			}
			else
			{
				this.showNode(this.getStartBtn());
				this.hideNode(this.getCompletedBtn());
				this.hideNode(this.getRepeatBtn());
			}
		}
	}

	/**
	 * @private
	 */
	setPopupData()
	{
		this.fireCurrentStepEvent('onBeforeSetPopupData');
		const currentStep = this.getCurrentStep();
		if (currentStep)
		{
			this.getTitle().innerHTML = currentStep.getTitle();
			this.getDescription().innerHTML = currentStep.getDescription();
			Manager.getCollapseTitle().innerHTML = currentStep.getTitle();

			if (this.getCurrentStep().getVideo() && window.YT)
			{
				this.setCurrentVideo();
			}

			this.setStepCounter();
			this.setStepItems();
		}
		this.fireCurrentStepEvent('onAfterSetPopupData');
	}

	setCurrentVideo()
	{
		this.fireCurrentStepEvent('onSetCurrentVideo');
		for (let i = 0; i < this.steps.length; i += 1)
		{
			let video = this.steps[i].getVideoObj();
			if (window.YT && i === this.currentStepIndex && video && video.playVideo)
			{
				Dom.addClass(video.getIframe(), 'ui-tutor-popup-video-show');
				video.playVideo();
			}
			else
			{
				if (video) {
					Dom.removeClass(video.getIframe(), 'ui-tutor-popup-video-show');

					if (video.pauseVideo)
					{
						video.pauseVideo();
					}
				}
			}
		}
	}

	/**
	 * @public
	 * @returns {Step}
	 */
	getCurrentStep()
	{
		return this.currentStep;
	}

	/**
	 * @private
	 */
	increaseCurrentIndex()
	{
		if (this.currentStepIndex === this.steps.length)
		{
			return;
		}

		this.currentStepIndex += 1;
		this.setCurrentStep(this.steps[this.currentStepIndex]);
	}

	/**
	 * @private
	 */
	reduceCurrentIndex()
	{
		if (this.currentStepIndex === 0)
		{
			return;
		}

		this.currentStepIndex -= 1;
		this.setCurrentStep(this.steps[this.currentStepIndex]);
	}

	/**
	 * @private
	 */
	showCollapseBlock(step, withGuide)
	{
		withGuide = withGuide !== false;
		this.closeStepPopup(null, true);
		Manager.showCollapsedBlock(step, withGuide);
	}

	/**
	 * @private
	 */
	minimize()
	{
		this.pauseCurrentVideo();
		this.fireCurrentStepEvent('onMinimize');
		this.showCollapseBlock(this.getCurrentStep(), false);
	}

	repeatStep()
	{
		this.followLink();
	}

	clickStartHandler()
	{
		this.followLink();
	}
	/**
	 * @private
	 */
	followLink(step)
	{
		let currentStep = this.getCurrentStep();
		if (step instanceof Step)
		{
			currentStep = step;
		}
		this.pauseCurrentVideo();

		this.setActiveStep(currentStep);
		Manager.checkFollowLink(currentStep, this);
	}

	setActiveStep(step)
	{
		this.fireCurrentStepEvent('onBeforeSetActiveStep');
		if (this.currentActiveStep instanceof Step)
		{
			this.currentActiveStep.deactivate();
		}
		step.activate();
		this.currentActiveStep = step;
		this.fireCurrentStepEvent('onAfterSetActiveStep');
	}

	/**
	 * @private
	 */
	showSuccessState()
	{
		const currentStep = this.getCurrentStep();
		this.clickOnCompletedBtn = true;
		currentStep.isCompleted = true;
		this.fireCurrentStepEvent('onFinishStep');

		if (currentStep.getCompleted())
		{
			Dom.addClass(this.layout.stepItems[this.currentStepIndex], 'ui-tutor-popup-step-item-completed');
		}
		Dom.addClass(this.getContentBlock(), 'ui-tutor-popup-content-block-animate');

		setTimeout(function () {
			Dom.replace(this.getHelpBlock(), this.getCompletedBLock());
			this.getFooter().style.display = "none";
			this.getDescription().style.display = "none";
			this.getTitle().style.display = "none";
		}.bind(this), 300);

		setTimeout(function () {
			Dom.addClass(this.getCompletedBLock(), 'ui-tutor-popup-completed-animate')
		}.bind(this), 800);

		setTimeout(function () {
			Dom.replace(this.getCompletedBLock(), this.getHelpBlock());
			this.getTitle().style.display = "block";
			this.getDescription().style.display = "block";
			this.getFooter().style.display = "flex";

			this.clickOnNextBtn();
		}.bind(this), 1500);

		setTimeout(function () {
			Dom.removeClass(this.getCompletedBLock(), 'ui-tutor-popup-completed-animate');
			Dom.removeClass(this.getContentBlock(), 'ui-tutor-popup-content-block-animate');

			let counter = this.stepPopup.querySelector(".ui-tutor-popup-header-counter-number");
			counter.innerHTML = Loc.getMessage('JS_UI_TUTOR_COUNTER_NUMBER')
				.replace('#NUMBER#', this.steps.indexOf(this.getCurrentStep()) + 1)
				.replace('#NUMBER_TOTAL#', this.steps.length);
			this.fireCurrentStepEvent('onAfterShowSuccessState');
		}.bind(this), 1700);

	}

	fireCurrentStepEvent(eventName, fireStepEvent, extra)
	{
		fireStepEvent = fireStepEvent !== false;
		const currentStep = this.getCurrentStep();

		const data = {
			step : currentStep,
			scenario: this
		};
		if (extra)
		{
			data.extra = extra;
		}
		if (currentStep && fireStepEvent)
		{
			currentStep.emit(eventName, data);
		}
		this.emit(eventName, data);
	}

	/**
	 * @private
	 */
	showFinalState()
	{
		this.fireCurrentStepEvent('onFinalState');

		if (this.layout.stepItems)
		{
			Dom.removeClass(this.layout.stepItems[this.currentStepIndex], 'ui-tutor-popup-step-item-current');
		}

		Dom.append(this.getFinishedBlock(), this.getContentInner());
		Dom.replace(this.getStartBtn(), this.getFinishedNotice());
		Dom.remove(this.getCompletedBtn());

		Dom.remove(this.getSupportLink());
		Dom.remove(this.getNavigation());
		Dom.remove(this.getHelpBlock());

		Dom.remove(this.getRepeatBtn());
		Dom.remove(this.getTitle());
		Dom.remove(this.getDescription());
		Dom.remove(this.getDeferLink());

		this.isFinished = true;
		this.fireCurrentStepEvent('onAfterFinalState');
	}

	/**
	 * @private
	 */
	hideFinalState()
	{
		this.fireCurrentStepEvent('onBeforeHideFinalState');
		if (this.getCurrentStep().getCompleted())
		{
			Dom.replace(this.getFinishedNotice(), this.getRepeatBtn());
		}
		else
		{
			Dom.replace(this.getFinishedNotice(), this.getStartBtn());
		}

		Dom.replace(this.getFinishedBlock(), this.getHelpBlock());

		if (Manager.getInstance().feedbackFormId) {
			Dom.append(this.getSupportLink(), this.getFooter());
		}
		Dom.prepend(this.getNavigation(), this.getFooter());
		Dom.prepend(this.getDescription(), this.getContentInner());
		Dom.prepend(this.getTitle(), this.getContentInner());

		if (this.layout.deferBtn)
		{
			Dom.remove(this.getDeferBtn());
			Dom.prepend(this.getStartBtn(), this.getBtnContainer());
		}

		const header = this.getStepPopup().querySelector('.ui-tutor-popup-header');
		Dom.append(this.getDeferLink(), header);
		this.fireCurrentStepEvent('onAfterHideFinalState');
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getFinishedBlock()
	{
		if (!this.layout.finishedBlock)
		{
			this.layout.finishedBlock =
				Tag.render`
					<div class="ui-tutor-popup-finished">
						<div class="ui-tutor-popup-finished-title">${Loc.getMessage('JS_UI_TUTOR_FINAL_CONGRATULATIONS')}</div>
						<div class="ui-tutor-popup-finished-icon"></div>
						<div class="ui-tutor-popup-finished-text">${Loc.getMessage('JS_UI_TUTOR_FINAL_TEXT')}</div>
					</div>
				`;
		}

		return this.layout.finishedBlock;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getFinishedNotice()
	{
		if (!this.layout.finishedNotice)
		{
			this.layout.finishedNotice =
				Tag.render`
					<div class="ui-tutor-popup-finished-notice">${Loc.getMessage('JS_UI_TUTOR_FINAL_NOTICE')}</div>
				`;
		}

		return this.layout.finishedNotice;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	getNewStepsSection()
	{
		if (!this.layout.newStepsSection)
		{
			this.layout.newStepsSection =
				Tag.render`
					<div class="ui-tutor-popup-new-text">${Loc.getMessage('JS_UI_TUTOR_STEP_NEW')}</div>
				`;
		}

		return this.layout.newStepsSection;
	}

	/**
	 * @public
	 */
	showNewSteps()
	{
		Dom.addClass(this.getFinishedBlock(), 'ui-tutor-popup-finished-new');
		this.showPopup(this.getStepPopup());
		this.showFinalState();

		Dom.append(this.getNewStepsSection(), this.getFinishedBlock());
		Dom.replace(this.getFinishedNotice(), Manager.getBeginBtn());
		Dom.append(this.getDeferBtn(), this.getBtnContainer());

		this.setStepCounter();
		this.initArrows();
		this.scrollToStep();

		this.isAddedSteps = true;
		this.isFinished = false;
	}

	/**
	 * @private
	 */
	initArrows()
	{
		this.stepListWrap = document.querySelector('.ui-tutor-popup-step-list-wrap');
		this.arrowWrap = document.querySelector('.ui-tutor-popup-arrow-wrap');

		if (this.stepListWrap && this.stepListWrap.scrollWidth > this.stepListWrap.offsetWidth)
		{
			Dom.append(this.getPrevArrow(), this.arrowWrap);
			Dom.append(this.getNextArrow(), this.arrowWrap);

			this.stepListWrap.addEventListener('scroll', this.toggleArrows.bind(this));

			this.prevArrow.addEventListener('mouseenter', this.scrollToLeft.bind(this));
			this.prevArrow.addEventListener('mouseleave', this.stopAutoScroll.bind(this));
			this.nextArrow.addEventListener('mouseenter', this.scrollToRight.bind(this));
			this.nextArrow.addEventListener('mouseleave', this.stopAutoScroll.bind(this));

			this.toggleNextArrow();
			this.getStepBlock().classList.add("ui-tutor-popup-step-list-wide");

			this.hasArrows = true;
		}
	}

	getPrevArrow()
	{
		if (!this.prevArrow)
		{
			this.prevArrow =
				Tag.render`
					<div class="ui-tutor-popup-arrow ui-tutor-popup-arrow-prev"></div>
				`;
		}

		return this.prevArrow;
	}

	getNextArrow()
	{
		if (!this.nextArrow)
		{
			this.nextArrow =
				Tag.render`
					<div class="ui-tutor-popup-arrow ui-tutor-popup-arrow-next"></div>
				`;
		}

		return this.nextArrow;
	}

	/**
	 * @private
	 */
	scrollToLeft()
	{
		this.arrowTimer = setInterval(function() {
			this.stepListWrap.scrollLeft -= 5;
		}.bind(this), 20);
	}

	/**
	 * @private
	 */
	scrollToRight()
	{
		this.arrowTimer = setInterval(function() {
			this.stepListWrap.scrollLeft += 5;
		}.bind(this), 20);
	}

	/**
	 * @private
	 */
	stopAutoScroll()
	{
		clearInterval(this.arrowTimer);
	}

	/**
	 * @private
	 */
	toggleArrows()
	{
		this.togglePrevArrow();
		this.toggleNextArrow();
	}

	/**
	 * @private
	 */
	toggleNextArrow()
	{
		if (this.stepListWrap.scrollWidth > this.stepListWrap.offsetWidth
			&& (this.stepListWrap.offsetWidth + this.stepListWrap.scrollLeft) < this.stepListWrap.scrollWidth)
		{
			Dom.addClass(this.nextArrow, 'ui-tutor-popup-arrow-show');
		}
		else
		{
			Dom.removeClass(this.nextArrow, 'ui-tutor-popup-arrow-show');
		}
	}

	/**
	 * @private
	 */
	togglePrevArrow()
	{
		if (this.stepListWrap.scrollLeft > 0)
		{
			Dom.addClass(this.prevArrow, 'ui-tutor-popup-arrow-show');
		}
		else
		{
			Dom.removeClass(this.prevArrow, 'ui-tutor-popup-arrow-show');
		}
	}

	/**
	 * @private
	 */
	showAnimation(popup)
	{
		Dom.removeClass(popup, 'ui-tutor-popup-hide-complex');
		Dom.removeClass(popup, 'ui-tutor-popup-hide');

		if (this.complexAnimation)
		{
			Dom.addClass(popup, 'ui-tutor-popup-show-complex');
		}
		else
		{
			Dom.addClass(popup, 'ui-tutor-popup-show');
		}

	}

	/**
	 * @private
	 */
	fadeAnimation(popup)
	{
		Dom.removeClass(popup, 'ui-tutor-popup-show-complex');
		Dom.removeClass(popup, 'ui-tutor-popup-show');

		if (this.complexAnimation)
		{
			Dom.addClass(popup, 'ui-tutor-popup-hide-complex');
		}
		else
		{
			Dom.addClass(popup, 'ui-tutor-popup-hide');
		}
	}

	/**
	 * @private
	 */
	scrollToStep()
	{
		let posList = null;
		let posStep = null;
		if (this.stepListWrap)
		{
			posList = Dom.getPosition(this.stepListWrap);
			posStep = Dom.getPosition(this.stepListWrap.querySelector('[data-step="' + this.currentStepIndex + '"]'));
		}
		const offset = 7; // padding 2px and margin 5px

		if (!Type.isNull(posStep) && posStep.left + posStep.width > posList.left + posList.width )
		{
			this.stepListWrap.scrollLeft += posStep.left - (posList.left + posList.width) + posStep.width + offset;
		}

		if (!Type.isNull(posStep) && posStep.left < posList.left)
		{
			this.stepListWrap.scrollLeft -= posList.left - posStep.left + offset;
		}
	}

	/**
	 * @private
	 */
	static getFullEventName(shortName)
	{
		return shortName;
	}

	static getInstance()
	{
		return Manager.getScenarioInstance();
	}

	static init(options)
	{
		return Manager.initScenario(options);
	}
}