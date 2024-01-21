/* eslint-disable no-param-reassign */
import { BitrixVue, VueCreateAppResult } from 'ui.vue3';
import { createStore, Store, mapGetters, mapMutations } from 'ui.vue3.vuex';
import { Content } from './components/content';
import { Footer } from './components/footer';
import { WarehouseAppProps } from './types';
import { ButtonClickHandler } from './button-click-handler';
import { EventEmitter } from 'main.core.events';
import { Text } from 'main.core';
import { EventType, DialogCostPriceAccountingMethodSelection, Controller } from 'catalog.store-use';

export class App
{
	#application: VueCreateAppResult;
	rootNode: HTMLElement;

	constructor(props: WarehouseAppProps)
	{
		this.rootNode = document.getElementById(props.rootNodeId);

		this.store = this.#initStore(props);
	}

	#initStore(props): Store
	{
		const settingsStore = {
			state(): Object
			{
				return {
					isLoading: false,
					...props,
				};
			},

			getters: {
				isUsed(state): boolean
				{
					return state.isUsed;
				},
				isLoading(state): boolean
				{
					return state.isLoading;
				},

				getSelectedCostPriceAccountingMethod(state): string
				{
					return state.selectedCostPriceAccountingMethod;
				},
				isPlanRestricted(state): boolean
				{
					return state.isPlanRestricted;
				},
				isUsed1C(state): boolean
				{
					return state.isUsed1C;
				},
				isWithOrdersMode(state): boolean
				{
					return state.isWithOrdersMode;
				},
				isRestrictedAccess(state): boolean
				{
					return state.isRestrictedAccess;
				},
				getInventoryManagementSource(state): string
				{
					return state.inventoryManagementSource;
				},
				getPreviewLang(state): string
				{
					return state.previewLang;
				},

				getButtonClickHandler(state): ButtonClickHandler
				{
					return new ButtonClickHandler(state);
				},
			},

			mutations: {
				setIsLoading(state, value)
				{
					state.isLoading = value;
				},
				setSelectedCostPriceAccountingMethod(state, value)
				{
					state.selectedCostPriceAccountingMethod = value;
				},
			},
		};

		return createStore(settingsStore);
	}

	attachTemplate(): void
	{
		this.#application = BitrixVue.createApp({
			components: {
				Content,
				Footer,
			},

			computed: {
				...mapGetters([
					'getSelectedCostPriceAccountingMethod',
					'getButtonClickHandler',
					'getInventoryManagementSource',
				]),
			},

			created()
			{
				this.controller = new Controller();
			},

			mounted()
			{
				EventEmitter.subscribe(EventType.popup.disable, this.disable);
				EventEmitter.subscribe(
					EventType.popup.enableWithResetDocuments,
					this.enableWithResetDocuments,
				);
				EventEmitter.subscribe(EventType.popup.enableWithoutReset, this.enableWithoutReset);
				EventEmitter.subscribe(
					EventType.popup.selectCostPriceAccountingMethod,
					this.handleAccountingMethodSelected,
				);
			},

			unmounted()
			{
				EventEmitter.unsubscribe(EventType.popup.disable, this.disable);
				EventEmitter.unsubscribe(
					EventType.popup.enableWithResetDocuments,
					this.enableWithResetDocuments,
				);
				EventEmitter.unsubscribe(EventType.popup.enableWithoutReset, this.enableWithoutReset);
				EventEmitter.unsubscribe(
					EventType.popup.selectCostPriceAccountingMethod,
					this.handleAccountingMethodSelected,
				);
			},

			methods: {
				...mapMutations([
					'setIsLoading',
					'setSelectedCostPriceAccountingMethod',
				]),

				handleOnButtonClick()
				{
					/**
					 * @see ButtonClickHandler.handle()
					 */
					this.getButtonClickHandler.handle();
				},

				handleAccountingMethodSelected(item)
				{
					const value = (item.data.method === DialogCostPriceAccountingMethodSelection.METHOD_FIFO)
						? DialogCostPriceAccountingMethodSelection.METHOD_FIFO
						: DialogCostPriceAccountingMethodSelection.METHOD_AVERAGE;

					this.setSelectedCostPriceAccountingMethod(value);
				},

				closeSlider()
				{
					const slider = BX.SidePanel.Instance.getTopSlider();
					if (slider)
					{
						slider.close();
					}
				},

				disable()
				{
					this.setIsLoading(true);

					this.controller.inventoryManagementDisabled()
						.then(this.handleSuccessfulChanging)
						.catch(this.handleUnsuccessfulChanging);
				},

				enable()
				{
					this.enableBy(() => this.controller.inventoryManagementEnabled());
				},

				enableWithResetDocuments()
				{
					this.enableBy(() => this.controller.inventoryManagementEnableWithResetDocuments({
						costPriceAccountingMethod: this.getSelectedCostPriceAccountingMethod,
					}));
				},

				enableWithoutReset()
				{
					this.enableBy(() => this.controller.inventoryManagementEnableWithoutReset({
						costPriceAccountingMethod: this.getSelectedCostPriceAccountingMethod,
					}));
				},

				enableBy(method: Function)
				{
					this.setIsLoading(true);

					method()
						.then(this.handleSuccessfulChanging)
						.catch(this.handleUnsuccessfulChanging);
				},

				handleSuccessfulChanging()
				{
					this.setIsLoading(false);
					const slider = BX.SidePanel.Instance.getTopSlider();
					if (slider)
					{
						slider.getData().set('isInventoryManagementEnabled', true);
					}

					this.closeSlider();
				},

				handleUnsuccessfulChanging(response)
				{
					if (response.errors.length)
					{
						top.BX.UI.Notification.Center.notify({
							content: Text.encode(response.errors[0].message),
						});
					}

					this.setIsLoading(false);
				},
			},

			// language = Vue
			template: `
				<Content/>
				<Footer @onButtonClick="handleOnButtonClick"/>
			`,
		});

		this.#application.use(this.store);
		this.#application.mount(this.rootNode);
	}
}
