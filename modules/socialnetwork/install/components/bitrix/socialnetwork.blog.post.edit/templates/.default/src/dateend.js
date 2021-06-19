import { Loc } from 'main.core';
import { MenuManager } from 'main.popup';

import 'main.date';

export default class PostFormDateEnd
{
	isInitialized: boolean = false;
	popupShowingPeriods = null;
	menuItems = [];
	customDateStyleModifier: string = 'feed-add-post-expire-date-customize';

	customDatePopupOptionClass: string = 'js-custom-date-end';

	postExpireDateBlock: HTMLElement = null;
	formUfInputDateCustom: HTMLElement = null;
	formDateDuration: HTMLElement = null;
	formDateTimeEditing: HTMLElement = null;
	popupTrigger: HTMLElement = null;
	customDateSelectedTitle: HTMLElement = null;

	selectors = {
		postExpireDateBlock: '.js-post-expire-date-block',
		postEndTime: '.js-form-post-end-time',
		postEditingEndTime: '.js-form-editing-post-end-time',
		postEndPeriod: '.js-form-post-end-period',
		popupTrigger: '.js-important-till-popup-trigger',
		customDateFinal: '.js-date-post-showing-custom',
		durationOptionsContainer: '.js-post-showing-duration-options-container',
		durationOption: '.js-post-showing-duration-option'
	};

	constructor()
	{
		this.init();
	}

	init(): void
	{
		if (this.isInitialized)
		{
			return;
		}

		this.addEventHandlers();

		if (!this.formDateTimeEditing.value)
		{
			this.customDateSelectedTitle.innerText = this.getCurrentDate();
		}

		this.isInitialized = true;
	};

	addEventHandlers()
	{
		this.postExpireDateBlock = document.querySelector(this.selectors.postExpireDateBlock);
		this.formUfInputDateCustom = document.querySelector(this.selectors.postEndTime);
		this.formDateDuration = document.querySelector(this.selectors.postEndPeriod);
		this.formDateTimeEditing = document.querySelector(this.selectors.postEditingEndTime);
		this.popupTrigger = document.querySelector(this.selectors.popupTrigger);

		if (this.popupTrigger)
		{
			this.popupTrigger.addEventListener('click', () => {
				this.showPostEndPeriodsPopup();
			});
		}

		this.customDateSelectedTitle = document.querySelector(this.selectors.customDateFinal);
		if (this.customDateSelectedTitle)
		{
			this.customDateSelectedTitle.addEventListener('click', () => {
				let curDate = new Date();
				let curTimestamp = Math.round(curDate / 1000) - curDate.getTimezoneOffset() * 60;

				if (this.formDateTimeEditing.value)
				{
					curDate = BX.parseDate(this.formDateTimeEditing.value);
					curTimestamp = BX.date.convertToUTC(curDate);
				}

				BX.calendar({
					node: this.customDateSelectedTitle,
					form: 'blogPostForm',
					value: curTimestamp,
					bTime: false,
					callback: () => {
						return true;
					},
					callback_after: this.onEndDateSet.bind(this)
				});
			});
		}
	};

	showPostEndPeriodsPopup(): void
	{
		if (!this.popupShowingPeriods)
		{
			this.createPopupShowingPeriods();
		}
		this.popupShowingPeriods.popupWindow.show();
	};

	createPopupShowingPeriods(): void
	{
		if (this.menuItems.length <= 0)
		{
			this.menuItems = this.createPopupItems();
		}

		this.popupShowingPeriods = MenuManager.create(
			'feed-add-post-form-popup42',
			document.getElementById('js-post-expire-date-wrapper'),
			this.menuItems,
			{
				className: "feed-add-post-expire-date-options",
				closeByEsc: true,
				angle: true
			}
		);
	};

	createPopupItems()
	{
		const menuPostDurationItems = [];
		const selectOptions = document.querySelector(this.selectors.durationOptionsContainer).querySelectorAll(this.selectors.durationOption);

		if (!selectOptions)
		{
			return menuPostDurationItems;
		}

		selectOptions.forEach((element) => {
			menuPostDurationItems.push({
				onclick: this.onPopupItemClick.bind(this),
				dataset: {
					value: element.getAttribute('data-value'),
					class: element.getAttribute('data-class')
				},
				text: element.getAttribute('data-text'),
				className: `menu-popup-item menu-popup-no-icon ${element.getAttribute('data-class')}`
			});
		});

		return menuPostDurationItems;
	};

	onPopupItemClick(event)
	{
		const element = event.currentTarget;
		if (element.getAttribute('data-class') === this.customDatePopupOptionClass)
		{
			this.postExpireDateBlock.classList.add(this.customDateStyleModifier);
			if (this.formDateTimeEditing.value)
			{
				this.formUfInputDateCustom.value = this.formDateTimeEditing.value;
				this.customDateSelectedTitle.innerText = this.formDateTimeEditing.value;
			}
			else
			{
				this.formUfInputDateCustom.value = this.getCurrentDate();
			}
		}
		else
		{
			this.postExpireDateBlock.classList.remove(this.customDateStyleModifier);
			this.formUfInputDateCustom.value = null;
		}

		this.popupTrigger.innerText = element.innerText.toLowerCase();
		this.formDateDuration.value = element.getAttribute('data-value').toUpperCase();
		this.popupShowingPeriods.popupWindow.close();
	};

	onEndDateSet(value)
	{
		if (!value)
		{
			return;
		}

		this.formDateTimeEditing.value = this.getFormattedDate(value);
		this.formUfInputDateCustom.value = this.getFormattedDate(value);
		this.customDateSelectedTitle.innerText = this.getFormattedDate(value);
	};

	getFormattedDate(value)
	{
		return BX.date.format(BX.date.convertBitrixFormat(Loc.getMessage('FORMAT_DATE')), value);
	};

	getCurrentDate()
	{
		return this.getFormattedDate(new Date());
	};

}