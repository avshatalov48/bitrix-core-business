import { Dom, Event, Tag, Loc, Type } from 'main.core';
import { Scenario } from './scenario';
import { Step } from './step';
import { Guide } from 'ui.tour';
import { Loader } from "main.loader";
import 'ui.feedback.form';

export class Manager extends Event.EventEmitter
{
	constructor()
	{
		super();
		this.setEventNamespace('BX.UI.Tutor.Manager');
	}

	setOptions(options, domain, feedbackFormId)
	{
		options = options || {};

		this.tutorialData = options.tutorialData || {};
		this.eventService = options.eventService || {};
		this.lastCheckTime = options.lastCheckTime || 0;
		this.domain = options.domain || '';
		this.feedbackFormId = options.feedbackFormId || '';
		if(Type.isString(domain) && domain.length > 0)
		{
			this.domain = domain;
		}
		if(Type.isString(feedbackFormId) && feedbackFormId.length > 0)
		{
			this.feedbackFormId = feedbackFormId;
		}
	}

	getDomain()
	{
		return this.domain;
	}

	getCurrentTutorialData()
	{
		return this.tutorialData;
	}

	getCurrentEventService()
	{
		return this.eventService;
	}

	getCurrentLastCheckTime()
	{
		return this.lastCheckTime;
	}

	/**
	 * @return {Manager}
	 */
	static getInstance()
	{
		return this.instance;
	}

	/**
	 * @return {Scenario}
	 */
	static getScenarioInstance()
	{
		return this.scenarioInstance;
	}

	static init(options, domain, feedbackFormId)
	{
		let instance = this.getInstance();
		if (!(instance instanceof Manager))
		{
			this.instance = new Manager();
			instance = this.getInstance();
			this.emit('onInitManager');
		}
		else
		{
			instance = this.getInstance();
		}
		instance.setOptions(options, domain, feedbackFormId);

		return instance;
	}

	static initScenario(options)
	{
		let instance = this.getScenarioInstance();
		if (!(instance instanceof Scenario))
		{
			this.scenarioInstance = new Scenario();
			instance = this.getScenarioInstance();
			this.emit('onInitScenario');
		}
		else
		{
			instance = this.getScenarioInstance();
		}
		instance.setOptions(options);

		return instance;
	}

	static showButton(animation)
	{
		return this.getImButton(animation);
	}

	static getRootImButton()
	{
		return document.getElementById('ui-tutor-btn-wrap');
	}

	static hasImButton()
	{
		return !!this.getRootImButton();
	}

	static getImButton(animation)
	{
		if(!this.layout.imButton)
		{
			let buttonWrapper = this.getRootImButton();
			if(buttonWrapper)
			{
				let buttonInner = Tag.render`
					<div class="ui-tutor-btn"></div>
				`;
				if (animation)
				{
					Dom.addClass(buttonWrapper, 'ui-tutor-btn-wrap-animate');
				}
				Dom.append(buttonInner, buttonWrapper);
				Dom.addClass(buttonWrapper, 'ui-tutor-btn-wrap-show');
				this.layout.imButton = buttonWrapper;
				Event.bind(this.layout.imButton, "click", () => {
					this.emit('clickImButton');
				});

				let usersPanel = document.querySelector('.bx-im-users-wrap');
				if (document.querySelector('#bx-im-btn-call'))
				{
					usersPanel.style.bottom = '175px';
				}
				else
				{
					usersPanel.style.bottom = '120px';
				}
			}
		}

		return this.layout.imButton;
	}

	static showSmallPopup(text)
	{
		this.smallPopupText = text;
		this.getSmallPopup().style.display = 'block';
		this.smallPopupText = '';

		if (Dom.hasClass(this.getSmallPopup(), 'ui-tutor-popup-welcome-hide'))
		{
			Dom.removeClass(this.getSmallPopup(), 'ui-tutor-popup-welcome-hide');
		}
	}

	static hideSmallPopup(skipAnimation)
	{
		skipAnimation = skipAnimation === true;
		const removeHandler = function() {
			Dom.remove(this.getSmallPopup());
			if(this.hasOwnProperty('smallPopup'))
			{
				delete this.smallPopup;
			}
			this.emit('onCompleteHideSmallPopup');
		}.bind(this);
		Dom.removeClass(this.getSmallPopup(), 'ui-tutor-popup-welcome-show');
		Dom.addClass(this.getSmallPopup(), 'ui-tutor-popup-welcome-hide');
		if(skipAnimation)
		{
			removeHandler();
		}
		else
		{
			setTimeout(removeHandler, 300);
		}
	}

	static showWelcomePopup(text)
	{
		this.emit('onShowWelcomePopup');
		this.showSmallPopup(text);
	}

	static hideWelcomePopup()
	{
		this.emit('onBeforeHideWelcomePopup');
		this.hideSmallPopup();
		this.emit('onAfterHideWelcomePopup');
	}

	static showNoticePopup(text)
	{
		this.emit('onShowNoticePopup');
		this.showSmallPopup(text);
	}

	static hideNoticePopup()
	{
		this.emit('onBeforeHideNoticePopup');
		this.hideSmallPopup();
		this.emit('onAfterHideNoticePopup');
	}

	static getSmallPopup()
	{
		const clickSmallPopupHandler = () => {
			this.emit('onClickSmallPopupBtn');
		};
		if (!this.smallPopup)
		{
			this.smallPopup =
				Tag.render`
					<div class="ui-tutor-popup" onclick="${clickSmallPopupHandler.bind(this)}">
						<div class="ui-tutor-popup-header">
							<span class="ui-tutor-popup-header-icon"></span>
							<span class="ui-tutor-popup-header-title-wrap">
								<span class="ui-tutor-popup-header-title">${Loc.getMessage('JS_UI_TUTOR_TITLE')}</span> 
							</span>
						</div>
						<div class="ui-tutor-popup-content">
							<div class="ui-tutor-popup-text">${this.smallPopupText}</div>
						</div>
						<div class="ui-tutor-popup-icon-angle"></div>
					</div>
				`;
			this.emit('onCreateSmallPopupNode');
			Dom.addClass(this.smallPopup, 'ui-tutor-popup-welcome-show');
			this.emit('onBeforeAppendSmallPopupNode');
			Dom.append(this.smallPopup, document.body);
			this.emit('onAfterAppendSmallPopupNode');
		}

		return this.smallPopup;
	}

	static showStartPopup(title, text)
	{
		this.emit('onShowStartPopup');
		this.startTitle = title;
		this.startText = text;
		Dom.addClass(this.getStartPopup(), 'ui-tutor-popup-show');
		this.startPopup.style.display = 'flex';
		this.startTitle = '';
		this.startText = '';
	};

	static closeStartPopup()
	{
		Dom.remove(this.getStartPopup());
		delete this.startPopup;
	}

	static getStartPopup()
	{
		if (!this.startPopup)
		{
			this.startPopup =
				Tag.render`
					<div class="ui-tutor-popup ui-tutor-popup-start">
						<div class="ui-tutor-popup-header">
							<span class="ui-tutor-popup-header-icon"></span>
							<span class="ui-tutor-popup-header-title-wrap">
								<span class="ui-tutor-popup-header-title">${Loc.getMessage('JS_UI_TUTOR_TITLE')}</span>
							</span>
						</div>
						<div class="ui-tutor-popup-content">
							<div class="ui-tutor-popup-title">${this.startTitle}</div>
							<div class="ui-tutor-popup-text">${this.startText}</div>
						</div>
						<div class="ui-tutor-popup-footer">
							<div class="ui-tutor-popup-btn">
								${this.getBeginBtn()}
								${this.getDeferBtn()}
							</div>
						</div>
						<div class="ui-tutor-popup-icon-angle"></div>
					</div>
				`;
			this.emit('onCreateStartPopupNode');
			Dom.append(this.startPopup, document.body);
			this.emit('onAfterAppendStartPopupNode');
		}

		return this.startPopup;
	}

	static getBeginBtn()
	{
		if (!this.beginBtn)
		{
			this.beginBtn =
				Tag.render`
					<button class="ui-btn ui-btn-primary ui-btn-round">
						${Loc.getMessage('JS_UI_TUTOR_BTN_BEGIN')}
					</button>
				`;

			Event.bind(this.beginBtn, "click", ()=> {
				this.emit('clickBeginBtn');
			});
		}

		return this.beginBtn;
	}

	static getDeferBtn()
	{
		if (!this.deferBtn)
		{
			this.deferBtn =
				Tag.render`
					<button class="ui-btn ui-btn-link">
						${Loc.getMessage('JS_UI_TUTOR_CLOSE_POPUP_BTN')}
					</button>
				`;

			Event.bind(this.deferBtn, "click", () => {
				this.emit('clickDeferBtn');
			});
		}

		return this.deferBtn;
	}

	/**
	 * @private
	 */
	static getFullEventName(shortName)
	{
		return shortName;
	}

	/**
	 * @public
	 */
	static getInformer()
	{
		if (!this.informer)
		{
			this.informer =
				Tag.render`
					<div class="ui-tutor-informer" id="ui-tutor-informer"></div>
				`;
			let informerParentNode = this.getImButton();
			if(this.isCollapsedShow)
			{
				informerParentNode = this.getCollapseBlock();
			}
			if(informerParentNode)
			{
				Dom.append(this.informer, informerParentNode);
			}
		}

		return this.informer;
	}

	static setCount(num)
	{
		this.emit('onBeforeSetCount');
		if (num < 1)
		{
			this.removeInformer();
			delete this.informer;
			this.isInformerShow = false;
		}
		else
		{
			this.getInformer().textContent = num;
			this.isInformerShow = true;
		}
		this.emit('onAfterSetCount');
	}

	/**
	 * @private
	 */
	static removeInformer()
	{
		if(this.isInformerShow)
		{
			Dom.remove(this.getInformer());
		}
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	static showCollapsedBlock(step, withGuide, showAfterAnimation)
	{
		withGuide = withGuide !== false;
		showAfterAnimation = showAfterAnimation !== false;
		this.emit('onBeforeShowCollapsedBlock');
		if(!this.isCollapsedShow)
		{
			this.emit('onStartShowCollapsedBlock');
			if(!(step instanceof Step))
			{
				step = new Step(step);
			}
			this.collapsedStep = step;

			let collapsedBlock = this.getCollapseBlock();
			let showFunction = function() {
				collapsedBlock.style.display = 'flex';
			};
			if(showAfterAnimation)
			{
				setTimeout(showFunction.bind(this), 300);
			}
			else
			{
				showFunction.call(this);
			}
			this.getCollapseTitle().innerHTML = step.getTitle();
			if(this.isInformerShow)
			{
				Dom.append(this.getInformer(), collapsedBlock);
			}
			this.isCollapsedShow = true;
			this.emit('onShowCollapsedBlock');
		}

		if (withGuide)
		{
			this.showGuide();
		}
		else
		{
			this.checkButtonsState();
		}
	}

	static setCollapsedInvisible()
	{
		this.hideNode(this.getCollapseBlock());
	}

	static setCollapsedVisible()
	{
		this.showNode(this.getCollapseBlock());
	}

	static checkButtonsState()
	{
		this.emit('onCheckButtonsState');
		let step = this.collapsedStep;
		if(!step)
		{
			return;
		}

		if(step.getCompleted())
		{
			if(this.activeGuide)
			{
				this.hideNode(this.getRepeatBtn());
			}
			else
			{
				this.showNode(this.getRepeatBtn());
			}
			this.hideNode(this.getCompletedBtn());
			this.hideNode(this.getStartBtn());
		}
		else if(step.isActive)
		{
			this.showNode(this.getCompletedBtn());
			if(this.activeGuide || !this.isShowRepeatWithCompleted)
			{
				this.hideNode(this.getRepeatBtn());
			}
			else
			{
				this.showNode(this.getRepeatBtn());
			}
			this.hideNode(this.getStartBtn());
		}
		else
		{
			this.showNode(this.getStartBtn());
			this.hideNode(this.getRepeatBtn());
			this.hideNode(this.getCompletedBtn());
		}
	}

	static showGuide()
	{
		this.emit('onBeforeShowGuide');
		let step = this.collapsedStep;
		if (!this.activeGuide && step)
		{
			this.emit('onStartShowGuide');
			this.activeGuide = new Guide({
				simpleMode: true,
				steps: [
					step.getHighlightOptions()
				],
			});
			this.activeGuide.subscribe(Guide.getFullEventName("onFinish"), this.finishGuide.bind(this));
			this.activeGuide.start();
			Dom.remove(this.activeGuide.getPopup().closeIcon);
			this.emit('showCollapseWithGuide');
			this.checkButtonsState();
		}
	}

	static closeGuide()
	{
		if(this.activeGuide instanceof Guide)
		{
			this.activeGuide.close();
			this.emit('onAfterGuide');
		}
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	static getCollapseBlock()
	{
		if (!this.layout.collapseBlock)
		{
			this.layout.collapseBlock =
				Tag.render`
					<div class="ui-tutor-popup ui-tutor-popup-collapse" onclick="${this.clickCollapseBlockHandler.bind(this)}">
						<div class="ui-tutor-popup-content">
							<div class="ui-tutor-popup-step-subject">${Loc.getMessage('JS_UI_TUTOR_STEP_TITLE')}</div>
							${this.getCollapseTitle()}
							<div class="ui-tutor-popup-collapse-btn">
								${this.getStartBtn()}
								${this.getRepeatBtn()}
								${this.getCompletedBtn()}
							</div>
						</div>
					</div>
				`;
			this.emit('onCreateCollapsedBlockNode');
			Dom.append(this.layout.collapseBlock, document.body);
			this.emit('onAfterAppendCollapsedBlockNode');
		}

		return this.layout.collapseBlock;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	static getStartBtn()
	{
		if (!this.startBtn)
		{
			this.startBtn =
				Tag.render`
					<button class="ui-btn ui-btn-primary ui-btn-round ui-btn-xs">
						${Loc.getMessage('JS_UI_TUTOR_BTN_START')}
					</button>
				`;

			Event.bind(this.startBtn, "click", (event) => {
				event.stopPropagation();
				this.emit('clickStartBtn');
			});
		}

		return this.startBtn;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	static getRepeatBtn()
	{
		if (!this.repeatBtn)
		{
			this.repeatBtn =
				Tag.render`
					<button class="ui-btn ui-btn-primary ui-btn-round ui-btn-xs">
						${Loc.getMessage('JS_UI_TUTOR_BTN_REPEAT')}
					</button>
				`;

			Event.bind(this.repeatBtn, "click", (event) => {
				event.stopPropagation();
				this.emit('clickRepeatBtn');
			});
		}

		return this.repeatBtn;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	static getCompletedBtn()
	{
		if (!this.completedBtn)
		{
			this.completedBtn =
				Tag.render`
					<button class="ui-btn ui-btn-success ui-btn-round ui-btn-xs">
						${Loc.getMessage('JS_UI_TUTOR_BTN_COMPLETED_SHORT')}
					</button>
				`;

			Event.bind(this.completedBtn, "click", (event) => {
				event.stopPropagation();
				this.emit('clickCompletedBtn');
			});
		}

		return this.completedBtn;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	static getCollapseTitle()
	{
		if (!this.layout.collapseTitle)
		{
			this.layout.collapseTitle =
				Tag.render`
					<div class="ui-tutor-popup-step-title"></div>
				`;
		}

		return this.layout.collapseTitle;
	}

	/**
	 * @public
	 * @returns {HTMLElement}
	 */
	static closeCollapsePopup(event)
	{
		this.closeCollapseEntity();
		this.emit('clickCloseCollapseBlock');
	}

	/**
	 * @private
	 */
	static clickCollapseBlockHandler()
	{
		this.emit('clickCollapseBlock');
	}

	static finishGuide()
	{
		delete this.activeGuide;
		this.checkButtonsState();
		this.emit('completeCloseGuide');
	}

	static closeCollapseEntity()
	{
		this.emit('onBeforeHideCollapsedBlock');
		this.getCollapseBlock().style.display = 'none';
		this.getImButton().style.display = 'block';
		if(this.activeGuide instanceof Guide)
		{
			this.activeGuide.close();
		}
		if(this.isInformerShow)
		{
			Dom.append(this.getInformer(), this.getImButton());
		}
		delete this.collapsedStep;
		this.isCollapsedShow = false;
		this.emit('onHideCollapsedBlock');
	}

	static showLoader()
	{
		this.emit('onBeforeShowLoader');
		this.startTitle = '';
		this.startText = '';
		this.layout.loader = new Loader({
			target: this.getStartPopup(),
			size: 85
		});
		this.layout.loader.show();
		this.getStartPopup().style.display = 'flex';
		Dom.addClass(this.getStartPopup(), "ui-tutor-popup-load");
		this.emit('onAfterShowLoader');
	}

	static hideLoader()
	{
		if (this.layout.loader)
		{
			this.layout.loader.destroy();
			this.getStartPopup().style.display = 'none';
		}
	}

	static showCollapsedLoader()
	{
		this.emit('onBeforeShowCollapsedLoader');
		this.layout.collapseLoader = new Loader({
			target: this.getCollapseBlock(),
			size: 34
		});
		this.layout.collapseLoader.show();
		this.getCollapseBlock().style.display = 'flex';
		Dom.addClass(this.getCollapseBlock(), "ui-tutor-popup-collapse-load");
		this.emit('onAfterShowCollapsedLoader');
	}

	static hideCollapsedLoader()
	{
		this.emit('onBeforeHideCollapsedLoader');
		if (this.layout.collapseLoader)
		{
			this.layout.collapseLoader.destroy();
			Dom.removeClass(this.getCollapseBlock(), "ui-tutor-popup-collapse-load");
			this.getCollapseBlock().style.display = 'none';
		}
		this.emit('onAfterHideCollapsedLoader');
	}

	static showNode (node)
	{
		node.style.display = 'block';
	}

	static hideNode (node)
	{
		node.style.display = 'none';
	}

	static checkFollowLink(step, scenario)
	{
		this.emit('onStartCheckFollowLink');
		step = step || this.collapsedStep;
		if(step instanceof Step)
		{
			scenario = scenario || {};
			if (!(window.location.pathname === step.getUrl()))
			{
				let beforeEvent = 'onBeforeRedirectToActionPage';
				if(scenario instanceof Scenario)
				{
					Dom.addClass(scenario.getStartBtn(), 'ui-btn-wait');
					scenario.fireCurrentStepEvent(beforeEvent);
				}
				else
				{
					Dom.addClass(this.getStartBtn(), 'ui-btn-wait');
					this.emit(beforeEvent, {step});
				}
				window.location = step.getUrl();
			}
			else
			{
				if(scenario instanceof Scenario)
				{
					scenario.showCollapseBlock(step);
				}
				else
				{
					step.activate();
					this.showCollapsedBlock(step);
				}
			}
		}
		this.emit('onFinishCheckFollowLink');
	}

	static fireEvent(eventName)
	{
		this.emit(eventName);
	}
}

/**
 * @private
 */
Manager.instance = null;
Manager.scenarioInstance = null;
Manager.activeGuide = null;
Manager.isShowRepeatWithCompleted = true;
Manager.layout = {
	imButton: null,
	collapseBlock: null,
	collapseTitle: null,
};