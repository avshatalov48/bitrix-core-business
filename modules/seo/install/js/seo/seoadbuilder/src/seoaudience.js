import { Helper } from './helper';
import { Event, Loc } from "main.core";
import {type PostSelectorOptions} from './types/postselectoroptions'
import { TagSelector } from 'ui.entity-selector';

export class SeoAudience
{
	_accountId: string;
	_clientId: string;
	_type: string;

	constructor(options: PostSelectorOptions)
	{
		this.helper = Helper.getCreated();
		this.last = null;
		this._accountId = options.accountId;
		this._clientId = options.clientId;
		this._type = options.type;
		this.signedParameters = options.signedParameters;
		this.emptyBlock = document.querySelector('.seo-ads-empty-post-list-block');
		this.listContent = document.querySelector('.crm-order-instagram-view-list');
		this.dataContent = [];
		this.selectedInterest = {};
		this.loader = new BX.Loader({
			target: document.querySelector(".crm-order-instagram-view")
		});

		this.rangeInput = document.querySelector('.crm-ads-new-campaign-item-runner-value');
		this.inputMax = BX('max');
		this.inputMin = BX('min');
		this.MAX_VALUE = 65;
		this.MIN_VALUE = 13;
		this.init();
	}

	init()
	{
		document.querySelectorAll('.crm-ads-new-campaign-item-runner-input').forEach(element => {
			const block = element.closest('.crm-ads-new-campaign-item-runner-block--double');
			if (block)
			{
				this.setDoubleInputPosition();
				this.setDoubleLabelPosition(element);
				Event.bind(element, 'change', this.onDoubleInputRange.bind(this));
				Event.bind(element, 'input', this.onDoubleInputRange.bind(this));
			}
			else
			{
				Event.bind(element, 'change', this.onInputRange.bind(this));
				Event.bind(element, 'input', this.onInputRange.bind(this));
			}
		});

		this.buildSelector();
	}

	checkSex()
	{

	}

	onInputRange(event)
	{
		const label = event.target.closest('.crm-ads-new-campaign-item-runner-block').children[0].children[0];
		const value = event.target.value;

		if(value < this.MIN_VALUE)
		{
			event.target.value = this.MIN_VALUE;
		}

		label.textContent = event.target.value;
		this.rangeInput.style.width = event.target.offsetWidth * event.target.value/65 +"px";
	}

	onDoubleInputRange(event)
	{
		this.setDoubleLabelPosition(event.target);
		this.setDoubleInputPosition();

	}

	setDoubleLabelPosition (element)
	{
		const value = element.value;
		const label = element.previousElementSibling;

		if (value < this.MIN_VALUE)
		{
			element.value = this.MIN_VALUE;
		}

		label.children[0].textContent = element.value;
		label.style.left = (((value - this.MIN_VALUE) / (this.MAX_VALUE - this.MIN_VALUE)) * (element.offsetWidth - 70)) + 20 + 'px';

	}

	setDoubleInputPosition()
	{
		const labelMaxLeft = BX('label-max').getBoundingClientRect().left;
		const labelMinLeft = BX('label-min').getBoundingClientRect().left;

		const min = Math.min(labelMaxLeft, labelMinLeft);

		if (labelMaxLeft === min)
		{
			this.rangeInput.style.width = ((((this.inputMin.value - this.MIN_VALUE)/ (this.MAX_VALUE - this.MIN_VALUE)) * (this.inputMin.offsetWidth - 40)) + 20)
				- ((((this.inputMax.value - this.MIN_VALUE)/ (this.MAX_VALUE - this.MIN_VALUE)) * (this.inputMax.offsetWidth - 40)) + 20) + 'px';
			this.rangeInput.style.left = (((this.inputMax.value - this.MIN_VALUE)/ (this.MAX_VALUE- this.MIN_VALUE)) * (this.inputMax.offsetWidth - 40)) + 20 + 'px';
		}
		else
		{
			this.rangeInput.style.width = ((((this.inputMax.value - this.MIN_VALUE)/ (this.MAX_VALUE - this.MIN_VALUE)) * (this.inputMax.offsetWidth - 40)) + 20)
				- ((((this.inputMin.value - this.MIN_VALUE)/ (this.MAX_VALUE - this.MIN_VALUE)) * (this.inputMin.offsetWidth - 40)) + 20) + 'px';

			this.rangeInput.style.left = (((this.inputMin.value - this.MIN_VALUE) / (this.MAX_VALUE - this.MIN_VALUE)) * (this.inputMin.offsetWidth - 40)) + 20 + 'px';
		}
	}

	buildSelector()
	{
		const selector = new TagSelector({
			id: 'seo-ads-interests',
			dialogOptions: {
				id: 'seo-ads-interests',
				context: 'SEO_ADS_INTERESTS',
				dropdownMode: true,
				searchOptions: {
					allowCreateItem: false
				},
				width: 350,
				height: 250,
				recentTabOptions: {
					stub: true,
					stubOptions: {
						title: Loc.getMessage('UI_TAG_SELECTOR_START_INPUT'),
					}
				},
				events: {
					'Item:onSelect': event => {
						const data = event.data.item;
						this.selectedInterest[data.id] = data;
						let sum = 0;

						for (let key in this.selectedInterest)
						{
							sum += this.selectedInterest[key].customData.get('audienceSize');
						}

						document.querySelector('.crm-ads-new-campaign-item-cost-value').textContent = sum;
						return;
					}
				},
				entities: [
					{
						id: 'facebook_interests',
						searchable: true,
						dynamicSearch: true,
						options: {
							clientId: this._clientId
						}
					},
				],
			}
		});

		selector.renderTo(document.getElementById('seo-ads-interests'));
	}

	showEmptyListBlock()
	{
		this.emptyBlock.style.display = 'block';
	}

	hideEmptyListBlock()
	{
		this.emptyBlock.style.display = 'none';
	}

	showListContentBlock()
	{
		this.listContent.parentNode.style.display = 'block';
	}

	hideListContentBlock()
	{
		this.listContent.parentNode.style.display = 'none';
	}

	apply()
	{
		const applyBtn = document.getElementById('ui-button-panel-apply');

		BX.SidePanel.Instance.close();
		let genders = [];
		let genderTitles = [];

		if(document.getElementById('male').checked)
		{
			genders.push(1);
			genderTitles.push(document.getElementById('male').parentNode.querySelector('span').innerText);
		}

		if(document.getElementById('female').checked)
		{
			genders.push(2);
			genderTitles.push(document.getElementById('female').parentNode.querySelector('span').innerText);
		}

		let interests = []
		Object.entries(this.selectedInterest).forEach(entry => {
			const [key, value] = entry;
			interests.push({
				id: value.id,
				name: value.title,
			})
		});

		BX.SidePanel.Instance.postMessage(
			window,
			'seo-fb-audience-configured',
			{
				interests: interests,
				ageFrom: this.inputMin.value,
				ageTo: this.inputMax.value,
				genderTitles: genderTitles,
				genders: genders
			}
		);
		document.getElementById('ui-button-panel-apply').classList.remove('ui-btn-wait');
	}
}