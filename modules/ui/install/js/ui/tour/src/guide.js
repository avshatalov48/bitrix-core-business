import { Type, Dom, Reflection, Event, Tag, Text, userOptions, Loc } from 'main.core';

import {Step} from './step.js';

export class Guide extends Event.EventEmitter
{
	constructor(options = {})
	{
		super(options);
		options = Type.isPlainObject(options) ? options : {};

		/** @var {Step[]}*/
		this.steps = [];
		if (Array.isArray(options.steps))
		{
			options.steps.forEach(step => {
				this.steps.push(new Step(step));
			});
		}

		if (this.steps.length < 1)
		{
			throw new Error("BX.UI.Tour.Guide: 'steps' argument is required.");
		}

		this.id = "ui-tour-guide-" + Text.getRandom();
		this.setId(options.id);

		this.autoSave = false;

		this.popup = null;
		this.layout = {
			overlay: null,
			element: null,
			title: null,
			text: null,
			link: null,
			btnContainer: null,
			nextBtn: null,
			backBtn: null,
			content: null,
			finalContent: null,
			counter: null,
			currentCounter: null,
			counterItems: []
		};
		this.buttons = options.buttons || "";
		this.currentStepIndex = 0;
		this.targetPos = null;
		this.clickOnBackBtn = false;

		this.finalStep = options.finalStep || false;
		this.finalText = options.finalText || "";
		this.finalTitle = options.finalTitle || "";

		this.simpleMode = options.simpleMode || false;

		this.setAutoSave(options.autoSave);

		const events = Type.isPlainObject(options.events) ? options.events : {};
		for (let eventName in events)
		{
			let cb = Type.isFunction(events[eventName]) ? events[eventName] : Reflection.getClass(events[eventName]);
			if (cb)
			{
				this.subscribe(this.constructor.getFullEventName(eventName), () => {
					cb();
				});
			}
		}

		Event.bind(window, "resize", this.handleResizeWindow.bind(this));
	}

	/**
	 * @public
	 * @returns {string}
	 */
	getId()
	{
		return this.id;
	}

	setId(id)
	{
		if (Type.isString(id) && id !== '')
		{
			this.id = id;
		}
	}

	/**
	 * @public
	 * @returns {Boolean}
	 */
	getAutoSave()
	{
		return this.autoSave;
	}

	setAutoSave(mode)
	{
		if (Type.isBoolean(mode))
		{
			this.autoSave = mode;
		}
	}

	save()
	{
		const optionName = "view_date_" + this.getId();
		userOptions.save("ui-tour", optionName, null, Math.floor(Date.now() / 1000));
		userOptions.send(null);
	}

	/**
	 * @public
	 */
	start()
	{
		this.emit(this.constructor.getFullEventName("onStart"), {guide: this});

		if (this.getAutoSave())
		{
			this.save();
		}

		this.setOverlay();

		const popup = this.getPopup();
		popup.show();

		if (this.popup.getPopupContainer())
		{
			Dom.removeClass(this.popup.getPopupContainer(), "popup-window-ui-tour-opacity");
		}


		this.showStep();

		Dom.addClass(this.layout.backBtn, "ui-tour-popup-btn-disabled");

		if (this.getCurrentStep().getTarget())
		{
			Dom.addClass(this.getCurrentStep().getTarget(), "ui-tour-selector");
		}

	}

	/**
	 * @public
	 */
	close()
	{
		this.emit(this.constructor.getFullEventName("onFinish"), { guide: this});

		this.getPopup().destroy();
		Dom.remove(this.layout.overlay);
		Dom.removeClass(document.body, "ui-tour-body-overflow");

		if (this.getCurrentStep() && this.getCurrentStep().getTarget())
		{
			this.getCurrentStep().getTarget().classList.remove("ui-tour-selector");
		}

		this.layout.overlay = null;
		this.layout.element = null;
		this.layout.title = null;
		this.layout.text = null;
		this.layout.link = null;
		this.layout.btnContainer = null;
		this.layout.nextBtn = null;
		this.layout.backBtn = null;
		this.layout.content = null;
		this.layout.finalContent = null;
		this.layout.counter = null;
		this.layout.currentCounter = null;
		this.layout.counterItems = [];
	}

	/**
	 * @private
	 */
	showStep()
	{
		let currentStep = this.getCurrentStep();
		currentStep.emit(currentStep.constructor.getFullEventName("onShow"), {
			step : currentStep,
			guide: this
		});

		if (currentStep.getTarget())
		{
			let close = this.close.bind(this);
			Event.bind(currentStep.getTarget(), 'click', close);

			this.subscribe("UI.Tour.Guide:onFinish", () => {
				Event.unbind(currentStep.getTarget(), 'click', close);
			});

			const targetPos = currentStep.getTarget().getBoundingClientRect();
			const targetPosWindow = Dom.getPosition(currentStep.getTarget());
			if (!this.isTargetVisible(targetPos))
			{
				this.scrollToTarget(targetPosWindow);
			}
		}

		Dom.removeClass(this.popup.getPopupContainer(), "popup-window-ui-tour-opacity");

		if (this.layout.element)
		{
			Dom.removeClass(this.layout.element, "ui-tour-overlay-element-opacity");
		}

		setTimeout(function() {
			this.layout.backBtn.style.display = "block";
		}.bind(this), 10);

		this.setOverlayElementForm();

		this.setCoords(this.getCurrentStep().getTarget());
		this.setPopupData();
	}

	/**
	 * @private
	 */
	closeStep()
	{
		const currentStep = this.getCurrentStep();
		if(currentStep)
		{
			currentStep.emit(currentStep.constructor.getFullEventName("onClose"), {
				step : currentStep,
				guide: this
			});

			const target = currentStep.getTarget();
			if (target)
			{
				Dom.removeClass(target, "ui-tour-selector")
			}
		}
	}

	setPopupPosition()
	{
		if (!this.getCurrentStep().getTarget() || this.targetPos === null)
		{
			this.getPopup().setBindElement(null);
			this.getPopup().setOffset({ offsetLeft: 0, offsetTop: 0});
			this.getPopup().setAngle(false);
			this.getPopup().adjustPosition();

			return;
		}

		let offsetLeft = 0;
		let offsetTop = -15;
		let angleOffset = 0;
		let anglePosition = "top";

		const bindOptions = {
			forceTop: true,
			forceLeft: true,
			forceBindPosition: true
		};

		const popupWidth = this.getPopup().getPopupContainer().offsetWidth;
		const clientWidth = document.documentElement.clientWidth;

		if (this.getCurrentStep().getPosition() === "right")
		{
			anglePosition = "left";
			offsetLeft = this.targetPos.width + 30;
			offsetTop = this.targetPos.height + this.getAreaPadding();

			if ((this.targetPos.left + offsetLeft + popupWidth) > clientWidth)
			{
				let left = this.targetPos.left - popupWidth;
				if (left > 0)
				{
					offsetLeft = -popupWidth - 30;
					anglePosition = "right";
				}
			}
		}
		else if (this.getCurrentStep().getPosition() === "left")
		{
			anglePosition = "right";
			offsetLeft = - this.targetPos.width - (popupWidth - this.targetPos.width) - 40;
			offsetTop = this.targetPos.height + this.getAreaPadding();

			if ((this.targetPos.right + offsetLeft + popupWidth) < clientWidth)
			{
				let left =  this.targetPos.left - popupWidth;
				if (left < 0)
				{
					offsetLeft = this.targetPos.width  + 40;
					anglePosition = "left";
				}
			}
		}
		else // top || bottom
		{
			bindOptions.forceLeft = false;
			bindOptions.forceTop = false;

			if (this.getCurrentStep().getRounded())
			{
				offsetTop = - (this.layout.element.getAttribute("r") - this.targetPos.height / 2 + 10);
				angleOffset = 0;
				offsetLeft = this.targetPos.width / 2;
			}
			else if (this.targetPos.width < 30)
			{
				offsetLeft = this.targetPos.width / 2;
				offsetTop = -15;
				angleOffset = 0;
			}
			else
			{
				offsetLeft = 25;
				offsetTop = - (this.layout.element.getAttribute("height") / 2 - this.targetPos.height / 2 + 10);
				angleOffset = 0;
			}
		}

		this.getPopup().setBindElement(this.getCurrentStep().getTarget());
		this.getPopup().setOffset({offsetLeft: offsetLeft, offsetTop: -offsetTop});
		this.getPopup().setAngle({position: anglePosition, offset: angleOffset});
		this.getPopup().adjustPosition(bindOptions);

	}

	/**
	 * @private
	 */
	setOverlay()
	{
		this.layout.overlay = Tag.render`
			<svg class="ui-tour-overlay" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" preserveAspectRatio="none">
				<mask id="hole">
					<defs>
						<filter id="ui-tour-filter">
							<feGaussianBlur stdDeviation="0"/>
						</filter>
					</defs>
					<rect x="0" y="0" width="100%" height="100%" fill="white"></rect>
					<rect id="rect" class="ui-tour-overlay-element ui-tour-overlay-element-rect" x="1035.5" y="338" width="422" rx="2" ry="2" height="58" filter="url(#ui-tour-filter)"></rect>
					<circle id="circle" class="ui-tour-overlay-element ui-tour-overlay-element-circle" cx="10" cy="10" r="10" filter="url(#ui-tour-filter)"></circle>
				</mask>
				<rect x="0" y="0" width="100%" height="100%" fill="#000" mask="url(#hole)"></rect>
			</svg>
		`;

		Dom.addClass(document.body, 'ui-tour-body-overflow');
		Dom.append(this.layout.overlay, document.body);

		this.setOverlayElementForm();
	}

	setOverlayElementForm()
	{
		if (this.getCurrentStep().getRounded())
		{
			this.layout.overlay.querySelector(".ui-tour-overlay-element-rect").style.display = "none";
			this.layout.overlay.querySelector(".ui-tour-overlay-element-circle").style.display = "block";
			this.layout.element = this.layout.overlay.querySelector(".ui-tour-overlay-element-circle");
		}
		else
		{
			this.layout.overlay.querySelector(".ui-tour-overlay-element-circle").style.display = "none";
			this.layout.overlay.querySelector(".ui-tour-overlay-element-rect").style.display = "block";
			this.layout.element = this.layout.overlay.querySelector(".ui-tour-overlay-element-rect");
		}

		return this.layout.element;
	}

	handleResizeWindow()
	{
		if (this.layout.element)
		{
			this.setCoords(this.getCurrentStep().getTarget());
		}

	}

	/**
	 * @private
	 * @param {Element} node
	 */
	setCoords(node)
	{
		if (!node)
		{
			this.layout.element.style.display = "none";
			return;
		}

		this.layout.element.style.display = "block";
		this.targetPos = node.getBoundingClientRect();

		if (this.getCurrentStep().getRounded())
		{
			this.layout.element.setAttribute('cx', this.targetPos.left + this.targetPos.width / 2);
			this.layout.element.setAttribute('cy', this.targetPos.top + this.targetPos.height / 2);
			this.layout.element.setAttribute('r', this.targetPos.width / 2 + this.getAreaPadding());
		}
		else
		{
			this.layout.element.setAttribute('x', this.targetPos.left - this.getAreaPadding());
			this.layout.element.setAttribute('y', this.targetPos.top - this.getAreaPadding());
			this.layout.element.setAttribute('width', this.targetPos.width + this.getAreaPadding()*2);
			this.layout.element.setAttribute('height', this.targetPos.height + this.getAreaPadding()*2);
		}
	}

	getAreaPadding()
	{
		let padding = 15;
		if (this.getCurrentStep().getAreaPadding() >= 0)
		{
			padding = this.getCurrentStep().getAreaPadding();
		}

		return padding;
	}

	/**
	 * @private
	 */
	increaseCurrentStepIndex()
	{
		this.currentStepIndex++;

		if (this.currentStepIndex + 1 === this.steps.length && !this.finalStep)
		{
			setTimeout(function() {
				this.layout.nextBtn.textContent = Loc.getMessage("JS_UI_TOUR_BUTTON_CLOSE");
			}.bind(this), 200);

		}
	}

	/**
	 * @private
	 */
	reduceCurrentStepIndex()
	{
		if (this.currentStepIndex === 0)
		{
			return;
		}

		if (this.currentStepIndex < this.steps.length && !this.finalStep)
		{
			this.layout.nextBtn.textContent = Loc.getMessage("JS_UI_TOUR_BUTTON");
		}

		this.currentStepIndex--;
	}

	/**
	 * @public
	 */
	getPopup()
	{
		if (this.popup === null)
		{
			this.popup = new BX.PopupWindow({
				content: this.getContent(),
				bindElement: this.getCurrentStep().getTarget(),
				className: 'popup-window-ui-tour popup-window-ui-tour-opacity',
				offsetTop: 15,
				offsetLeft: 30,
				maxWidth: 420,
				minWidth: 420,
				closeIcon: true,
				bindOptions: {
					forceTop: true,
					forceLeft: true,
					forceBindPosition: true
				},
				events: {
					onPopupClose : () => {
						this.close();
					}
				}
			});
		}

		return this.popup;
	}

	/**
	 * @private
	 */
	getContent()
	{
		if (!this.layout.content)
		{
			this.layout.content = Tag.render`
				<div class="ui-tour-popup ${this.simpleMode ? 'ui-tour-popup-simple' : ''}" >
					${this.getTitle()}
					<div class="ui-tour-popup-content">
						${this.getText()}
						${this.getLink()}
					</div>
					<div class="ui-tour-popup-footer">
						<div class="ui-tour-popup-index">
							${this.getCounterItems()}
							${this.getCurrentCounter()}
						</div>
						${this.getBtnContainer()}
					</div>
				</div>
			`;
		}

		return this.layout.content;
	}

	/**
	 * @private
	 */
	setPopupData()
	{
		Event.unbindAll(this.layout.link, 'click');

		this.getTitle().innerHTML = this.getCurrentStep().getTitle();
		this.getText().innerHTML = this.getCurrentStep().getText();

		if (this.getCurrentStep().getArticle() || this.getCurrentStep().getLink())
		{
			Dom.removeClass(this.layout.link,  "ui-tour-popup-link-hide");

			if (this.getCurrentStep().getArticle())
			{
				Event.bind(this.layout.link, "click", this.handleClickLink.bind(this));
			}

			if (this.getCurrentStep().getLink())
			{
				this.getLink().setAttribute('href', this.getCurrentStep().getLink());
			}

		}
		else {
			Dom.addClass(this.layout.link,  "ui-tour-popup-link-hide");
		}

		this.getCurrentCounter().textContent = Loc.getMessage("JS_UI_TOUR_STEP_INDEX_TEXT")
			.replace('#NUMBER#', this.currentStepIndex + 1)
			.replace('#NUMBER_TOTAL#', this.steps.length);

		for (let i = 0; i < this.steps.length; i++)
		{
			if (this.layout.counterItems[i])
			{
				Dom.removeClass(this.layout.counterItems[i], 'ui-tour-popup-index-item-current');
				Dom.removeClass(this.layout.counterItems[i], 'ui-tour-popup-index-item-passed');
			}

			if (i === this.currentStepIndex)
			{
				Dom.addClass(this.layout.counterItems[i], 'ui-tour-popup-index-item-current');
			}
			else if (i < this.currentStepIndex)
			{
				Dom.addClass(this.layout.counterItems[i], 'ui-tour-popup-index-item-passed')
			}
		}

		this.setPopupPosition();
	}

	/**
	 * @public
	 */
	handleClickLink()
	{
		event.preventDefault();
		BX.Helper.show("redirect=detail&code=" + this.getCurrentStep().getArticle());
	}

	/**
	 * @public
	 */
	getTitle()
	{
		if (this.layout.title === null)
		{
			this.layout.title = Tag.render`
				<div class="ui-tour-popup-title"></div>
			`;
		}

		return this.layout.title;
	}

	/**
	 * @public
	 */
	getText()
	{
		if (this.layout.text === null)
		{
			this.layout.text = Tag.render`
				<div class="ui-tour-popup-text"></div>
			`;
		}

		return this.layout.text;
	}

	/**
	 * @public
	 */
	getLink()
	{
		if (this.layout.link === null)
		{
			this.layout.link = Tag.render`
				<a target="_blank" href="" class="ui-tour-popup-link">
					${Loc.getMessage("JS_UI_TOUR_LINK")}
				</a>
			`;
		}

		return this.layout.link;
	}

	/**
	 * @public
	 */
	getCurrentCounter()
	{
		if (this.layout.currentCounter === null)
		{
			this.layout.currentCounter = Tag.render`
				<span class="ui-tour-popup-counter">
					${Loc.getMessage("JS_UI_TOUR_STEP_INDEX_TEXT")
						.replace('#NUMBER#', this.currentStepIndex + 1)
						.replace('#NUMBER_TOTAL#', this.steps.length)}
				</span>
			`;
		}

		return this.layout.currentCounter;
	}

	/**
	 * @private
	 */
	getBtnContainer()
	{
		if (this.layout.btnContainer === null)
		{
			this.layout.btnContainer = Tag.render`
				<div class="ui-tour-popup-btn-block"></div>
			`;

			this.layout.nextBtn = Tag.render`
				<button id="next" class="ui-tour-popup-btn-next">
					${this.simpleMode ? Loc.getMessage("JS_UI_TOUR_BUTTON_SIMPLE") : Loc.getMessage("JS_UI_TOUR_BUTTON")}
				</button>
			`;

			this.layout.backBtn = Tag.render`
				<button id="back" class="ui-tour-popup-btn-back">
				</button>
			`;

			Dom.append(this.layout.backBtn, this.layout.btnContainer);
			Dom.append(this.layout.nextBtn, this.layout.btnContainer);

			Event.bind(this.layout.nextBtn, "click", this.handleClickOnNextBtn.bind(this));
			Event.bind(this.layout.backBtn, "click", this.handleClickOnBackBtn.bind(this));

		}

		return this.layout.btnContainer;
	}

	getCounterItems()
	{
		if (this.layout.counter === null)
		{
			this.layout.counter = Tag.render`
				<span class="ui-tour-popup-index-items">
				</span>
			`;
		}

		this.layout.counterItems = [];

		for (let i = 0; i < this.steps.length; i++)
		{
			const currentStepIndex = Tag.render`
				<span class="ui-tour-popup-index-item">
				</span>
			`;

			this.layout.counterItems.push(currentStepIndex);
			Dom.append(currentStepIndex, this.layout.counter);
		}

		return this.layout.counter;
	}

	/**
	 * @returns {Step}
	 */
	getCurrentStep()
	{
		return this.steps[this.currentStepIndex];
	}

	/**
	 * @returns {Step}
	 */
	getPreviousStep()
	{
		if (this.steps[this.currentStepIndex - 1])
		{
			return this.steps[this.currentStepIndex - 1];
		}
	}

	handleClickOnNextBtn()
	{
		Dom.addClass(this.layout.element, "ui-tour-overlay-element-opacity");
		Dom.addClass(this.popup.getPopupContainer(), "popup-window-ui-tour-opacity");

		this.clickOnBackBtn = false;
		if (this.getCurrentStep())
		{
			this.closeStep();
		}

		this.increaseCurrentStepIndex();

		if (this.getCurrentStep() && this.getCurrentStep().getTarget())
		{
			Dom.addClass(this.getCurrentStep().getTarget(), 'ui-tour-selector');
		}

		if (this.currentStepIndex === this.steps.length)
		{
			if (this.finalStep)
			{
				this.setFinalStep()
			}
			else
			{
				this.close();
			}
		}
		else
		{
			setTimeout(function() {
				this.showStep();
			}.bind(this), 200);

			if (Dom.hasClass(this.layout.backBtn, 'ui-tour-popup-btn-disabled'))
			{
				Dom.removeClass(this.layout.backBtn, 'ui-tour-popup-btn-disabled');
			}
		}

	}

	handleClickOnBackBtn()
	{
		Dom.addClass(this.layout.element, "ui-tour-overlay-element-opacity");
		Dom.addClass(this.popup.getPopupContainer(), "popup-window-ui-tour-opacity");

		this.closeStep();
		this.reduceCurrentStepIndex();

		if (this.currentStepIndex === 0)
		{
			Dom.addClass(this.layout.backBtn, 'ui-tour-popup-btn-disabled');
		}

		this.clickOnBackBtn = true;
		setTimeout(function() {
			this.layout.backBtn.style.display = "none";
			this.showStep();
		}.bind(this), 200);

		if (this.getCurrentStep().getTarget())
		{
			Dom.addClass(this.getCurrentStep().getTarget(), 'ui-tour-selector');
		}
	}

	setFinalStep()
	{
		this.layout.element.style.display = "none";
		this.getPopup().destroy();

		const finalPopup = this.getFinalPopup();
		finalPopup.show();
		Dom.addClass(finalPopup.getPopupContainer(), "popup-window-ui-tour-final-show");
	}

	/**
	 * @public
	 */
	getFinalPopup()
	{
		this.popup = new BX.PopupWindow({
			content: this.getFinalContent(),
			className: 'popup-window-ui-tour-final',
			offsetTop: 15,
			offsetLeft: 35,
			maxWidth: 430,
			minWidth: 300
		});

		return this.popup;
	}

	getFinalContent()
	{
		if (!this.layout.finalContent)
		{
			this.layout.finalContent = Tag.render`
				<div class="ui-tour-popup">
					<div class="ui-tour-popup-title">
						${this.finalTitle}
					</div>
					<div class="ui-tour-popup-content">
						<div class="ui-tour-popup-text">
							${this.finalText}
						</div>
					</div>
					<div class="ui-tour-popup-footer-btn">
						${this.getFinalBtn()}
					</div>
				</div>
			`;
		}

		return this.layout.finalContent;
	}

	getFinalBtn()
	{
		const buttons = [];

		if (this.buttons !== "")
		{
			for (let i = 0; i < this.buttons.length; i++)
			{
				let btn = Tag.render`
					<button class="${this.buttons[i].class}" onclick="${this.buttons[i].events.click}">
					${this.buttons[i].text}
					</button>
				`;

				buttons.push(btn);
			}
		}
		else
		{
			let btn = Tag.render`
				<button class="ui-btn ui-btn-sm ui-btn-primary ui-btn-round" onclick="${this.close.bind(this)}">
				${Loc.getMessage("JS_UI_TOUR_BUTTON_CLOSE")}
				</button>
			`;

			buttons.push(btn);
		}

		return buttons;
	}

	/**
	 * @private
	 */
	isTargetVisible(node)
	{
		return (
			node.top >= 0 &&
			node.left >= 0 &&
			node.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
			node.right <= (window.innerWidth || document.documentElement.clientWidth)
		);
	}

	/**
	 * @private
	 */
	scrollToTarget(target)
	{
		window.scrollTo(0, target.y - this.getAreaPadding());
	}

	/**
	 * @private
	 */
	static getFullEventName(shortName)
	{
		return "UI.Tour.Guide:" + shortName;
	}
}