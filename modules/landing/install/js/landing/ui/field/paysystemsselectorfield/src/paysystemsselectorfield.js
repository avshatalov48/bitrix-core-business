import { BaseField } from 'landing.ui.field.basefield';
import { Loader } from 'main.loader';
import { Dom, Event, Tag, Text } from 'main.core';
import { Loc } from 'landing.loc';

import defaultPaySystemImage from './image/default-pay-system-image.svg';
import './css/style.css';
import { SmallSwitch } from 'landing.ui.field.smallswitch';

type PaySystemSelectorOptions = {
	disabledPaySystems: Array<number>,
	onFetchPaySystemsError: Function,
	showMorePaySystemsBtn: boolean,
	morePaySystemsBtnSidePanelPath: string,
}

type ActivePaySystemData = {
	id: number,
	title: string,
	image: ?string,
}

type RecommendedPaySystemData = {
	id: string,
	title: string,
	image: string,
	editPath: string,
}

type FetchPaySystemListResult = {
	active: Array<ActivePaySystemData>,
	recommended: Array<RecommendedPaySystemData>,
};

type UpdatePaySystemsResult = {
	paySystems: FetchPaySystemListResult,
	isUpdated: boolean
}

export class PaySystemsSelectorField extends BaseField
{
	#disabledPaySystems: Array<number>;
	#allPaySystems: FetchPaySystemListResult = { active: [], recommended: [] };
	#onFetchPaySystemsError: Function;
	#showMorePaySystemsBtn: boolean;
	#morePaySystemsBtnSidePanelPath: string;
	// in milliseconds
	#minLoaderShowTime: number = 3000;

	constructor(options: PaySystemSelectorOptions = {})
	{
		super(options);
		this.#disabledPaySystems = Reflect.has(options, 'disabledPaySystems')
			? options.disabledPaySystems
			: [];
		this.#onFetchPaySystemsError = Reflect.has(options, 'onFetchPaySystemsError')
			? options.onFetchPaySystemsError
			: () => {
			};
		this.#showMorePaySystemsBtn = Reflect.has(options, 'showMorePaySystemsBtn')
			? options.showMorePaySystemsBtn
			: false;
		this.#morePaySystemsBtnSidePanelPath = Reflect.has(options, 'morePaySystemsBtnSidePanelPath')
			? options.morePaySystemsBtnSidePanelPath
			: '';

		Dom.clean(this.getLayout());
		this.#updateAndRenderPaySystems(true);
	}

	#getPaySystemsList(): Promise<Array<FetchPaySystemListResult>>
	{
		return BX.ajax.runAction('crm.api.form.paysystem.list', { json: {} }).then(
			(response) => {
				return response.data;
			},
		);
	}

	#updatePaySystems(): Promise<UpdatePaySystemsResult>
	{
		return this.#getPaySystemsList().then(
			(paySystems) => {
				const oldPaySystemIds = this.#allPaySystems.active.map((ps) => ps.id).sort();
				const newPaySystemIds = paySystems.active.map((ps) => ps.id).sort();

				this.#allPaySystems = paySystems;

				const result = {
					paySystems: paySystems,
					isUpdated: false,
				};

				if (oldPaySystemIds.length !== newPaySystemIds.length)
				{
					result.isUpdated = true;
					return result;
				}

				for (let index = 0; index < oldPaySystemIds.length; index++)
				{
					if (oldPaySystemIds[index] !== newPaySystemIds[index])
					{
						result.isUpdated = true;
						return result;
					}
				}

				return result;
			},
		).catch(
			(response) => {
				this.#onFetchPaySystemsError(response.errors);
			},
		);
	}

	#updateAndRenderPaySystems(useLoaderOnFetchStart: boolean = true, minLoaderShowTime: number = 0):
		Promise<UpdatePaySystemsResult>
	{
		if (useLoaderOnFetchStart)
		{
			Dom.clean(this.getLayout());
			this.#getLoader().show();
		}
		let loaderEndTime = useLoaderOnFetchStart ? Date.now() + minLoaderShowTime : null;

		return this.#updatePaySystems().then(
			async ({ paySystems, isUpdated }: UpdatePaySystemsResult) => {
				if (isUpdated)
				{
					Dom.clean(this.getLayout());
					this.#getLoader().show();

					loaderEndTime = loaderEndTime !== null ? loaderEndTime : Date.now() + minLoaderShowTime;
					await new Promise((resolve) => setTimeout(resolve, loaderEndTime - Date.now()));

					this.#getLoader().hide();
					this.#renderLayout();
				}

				return paySystems;
			},
		);
	}

	#renderLayout(): void
	{
		Dom.clean(this.getLayout());
		this.#renderActivePaySystems();
		this.#renderRecommendedPaySystems();
		if (this.#showMorePaySystemsBtn && this.#morePaySystemsBtnSidePanelPath)
		{
			this.#renderShowMorePaySystemsBtn();
		}
	}

	#renderRecommendedPaySystems(): void
	{
		this.#allPaySystems.recommended.forEach(
			(paySystem) => {
				Dom.append(
					this.#getRecommendedPaySystemsLayout(paySystem),
					this.getLayout(),
				);
			},
		);
	}

	#renderShowMorePaySystemsBtn(): void
	{
		Dom.append(
			this.#getShowMorePaySystemsBtn(),
			this.getLayout(),
		);
	}

	#getShowMorePaySystemsBtn(): HTMLDivElement
	{
		return this.cache.remember('showMorePaySystemsBtn', () => {
			const btnLayout = Tag.render`
			<button type="button" class="landing-ui-content-pay-system-more-ps">
				<span class="landing-ui-content-pay-system-more-ps-text">
					${Loc.getMessage('LANDING_FORM_PAY_SYSTEMS_CONNECT_OTHER_PAY_SYSTEM')}
				</span>
			</button>
			`;
			btnLayout.onclick = () => {
				BX.SidePanel.Instance.open(
					this.#morePaySystemsBtnSidePanelPath,
					{
						events: {
							onCloseComplete: (event) => this.#onMorePaySystemSliderClose(event),
						},
					},
				);
			};

			return btnLayout;
		});
	}

	#getRecommendedPaySystemsLayout(paySystemData: RecommendedPaySystemData): HTMLDivElement
	{
		return this.cache.remember('recommendedPaySystem:' + paySystemData.id,
			() => {
				const paySystemLayout = this.#getDefaultPaySystemLayout(paySystemData.title, paySystemData.image);
				const connectBtnLayout = Tag.render`
					<div class="landing-ui-field-pay-system-selector-connect-recommended">
						${Loc.getMessage('LANDING_FORM_PAY_SYSTEMS_CONNECT_RECOMMENDED_PAY_SYSTEM_TEXT')}
					</div>
				`;
				Event.bind(
					connectBtnLayout,
					'click',
					() =>
						BX.SidePanel.Instance.open(
							paySystemData.editPath,
							{
								events: {
									onCloseComplete: (event) => {
										this.#onRecommendedSliderClose(event);
									},
								},
							},
						),
				);
				Dom.append(
					connectBtnLayout,
					paySystemLayout,
				);

				return paySystemLayout;
			});
	}

	#getActivePaySystemLayout(paySystemData: ActivePaySystemData): HTMLDivElement
	{
		return this.cache.remember('formPaySystem:' + paySystemData.id, () => {
			const paySystemLayout = this.#getDefaultPaySystemLayout(
				paySystemData.title,
				paySystemData.image ?? defaultPaySystemImage,
			);
			const switcher = new SmallSwitch({
				value: this.#isPaySystemActiveInForm(paySystemData.id),
			});
			Dom.addClass(
				switcher.getLayout(), 'landing-ui-field-pay-system-selector-ps-switch',
			);
			Dom.append(
				switcher.getLayout(),
				paySystemLayout,
			);
			switcher.subscribe('onChange', () => this.#onPaySystemSwitchChange(paySystemData));

			return paySystemLayout;
		});
	}

	getValue(): FetchPaySystemListResult
	{
		return {
			allPaySystems: { ...this.#allPaySystems },
			disabledPaySystems: [...this.#disabledPaySystems],
		};
	}

	#onRecommendedSliderClose(event): void
	{
		this.#updateAndRenderPaySystems(false, this.#minLoaderShowTime);
	}

	#renderActivePaySystems(): void
	{
		const paySystemSortRule = (paySystem1: ActivePaySystemData, paySystem2: ActivePaySystemData) => {
			// sort by active status
			const paySystem1ActivationStatus = this.#isPaySystemActiveInForm(paySystem1.id);
			const paySystem2ActivationStatus = this.#isPaySystemActiveInForm(paySystem2.id);

			if (paySystem1ActivationStatus !== paySystem2ActivationStatus)
			{
				return paySystem1ActivationStatus ? -1 : 1;
			}

			// sort by id
			return paySystem2.id - paySystem1.id;
		};
		this.#allPaySystems.active.sort(paySystemSortRule).forEach(
			(paySystem) => {
				Dom.append(
					this.#getActivePaySystemLayout(paySystem),
					this.getLayout(),
				);
			},
		);
	}

	#isPaySystemActiveInForm(paySystemId: number): boolean
	{
		return !this.#disabledPaySystems.includes(paySystemId);
	}

	#onPaySystemSwitchChange(paySystemData: ActivePaySystemData): void
	{
		if (this.#isPaySystemActiveInForm(paySystemData.id))
		{
			this.#disabledPaySystems.push(paySystemData.id);
		}
		else
		{
			this.#disabledPaySystems.splice(
				this.#disabledPaySystems.indexOf(paySystemData.id),
				1,
			);
		}

		this.emit('onChange');
	}

	#getDefaultPaySystemLayout(title: string, image: string): HTMLDivElement
	{
		const paySystemLayout = Tag.render`
			<div class="landing-ui-field-pay-system-selector-ps-wrapper">
				<div class="landing-ui-field-pay-system-selector-ps-img"></div>
			</div>
		`;
		Dom.append(
			Tag.render`<img src="${image}">`,
			paySystemLayout.children[0],
		);
		Dom.append(
			Tag.render`<div class="landing-ui-field-pay-system-ps-title">${Text.encode(title)}</div>`,
			paySystemLayout,
		);
		return paySystemLayout;
	}

	#getLoader(): Loader
	{
		return this.cache.remember(
			'loader',
			() => new Loader({
				target: this.layout,
				size: 50,
				mode: 'inline',
				offset: {
					top: '5px',
					left: '250px',
				},
			}),
		);
	}

	#onMorePaySystemSliderClose(event): void
	{
		this.#updateAndRenderPaySystems(false, this.#minLoaderShowTime);
	}
}