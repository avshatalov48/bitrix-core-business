import { ajax as Ajax, type AjaxResponse, Dom, type JsonObject, Loc, Runtime, Text, Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Button, ButtonColor, ButtonSize, CancelButton } from 'ui.buttons';
import { MessageBox } from 'ui.dialogs.messagebox';
import { BitrixVue, type VueCreateAppResult } from 'ui.vue3';
import type { Store } from 'ui.vue3.vuex';
import { Grid } from './components/grid';
import 'ui.notification';
import { AnalyticsManager } from './integration/analytics-manager';
import { createStore } from './store/index';
import type { Options } from './store/model/application-model';
import { AccessRightsExporter } from './store/model/transformation/backend-exporter/access-rights-exporter';
import { AllUserGroupsExporter } from './store/model/transformation/backend-exporter/user-groups/all-user-groups-exporter';
import { OnlyChangedUserGroupsExporter } from './store/model/transformation/backend-exporter/user-groups/only-changed-user-groups-exporter';
import type { ExternalAccessRightSection } from './store/model/transformation/internalizer/access-rights-internalizer';
import { AccessRightsInternalizer } from './store/model/transformation/internalizer/access-rights-internalizer';
import { ApplicationInternalizer } from './store/model/transformation/internalizer/application-internalizer';
import type { ExternalUserGroup } from './store/model/transformation/internalizer/user-groups-internalizer';
import { UserGroupsInternalizer } from './store/model/transformation/internalizer/user-groups-internalizer';
import { ShownUserGroupsCopier } from './store/model/transformation/shown-user-groups-copier';
import { SELECTED_ALL_USER_ID, SelectedMember } from './store/model/user-groups-model';
import type { AccessRightsModel } from './store/model/access-rights-model';
import { Loader } from 'main.loader';
import { saveSortConfigForAllUserGroups } from './utils';
import type { UserGroupsCollection, UserGroupsModel } from './store/model/user-groups-model';

export type AppConstructOptions = Options & {
	renderTo?: HTMLElement;
	renderToContainerId?: string;
	userGroups: ExternalUserGroup[];
	accessRights: ExternalAccessRightSection[];
	sortConfigForAllUserGroups: Object;
	userSortConfigName: string;
	selectedMember: Object;
};

type SaveAjaxResponse = AjaxResponse<{ USER_GROUPS: JsonObject, ACCESS_RIGHTS: ?JsonObject }>;

/**
 * @memberOf BX.UI.AccessRights.V2
 */
export class App
{
	#options: AppConstructOptions = {};
	#renderTo: HTMLElement;
	#buttonPanel: BX.UI.ButtonPanel;

	#guid: string;
	#isUserConfirmedClose: boolean = false;

	#handleSliderClose: (BaseEvent<BX.SidePanel.Event[]>) => void;

	#app: VueCreateAppResult;
	#rootComponent: Element;
	#store: Store;
	#resetState: () => void;
	#unwatch: () => void;
	#userGroupsModel: UserGroupsModel;
	#accessRightsModel: AccessRightsModel;
	#analyticsManager: AnalyticsManager;
	#selectedMember: Object;
	#sortConfigForAllUserGroups: ?Record<string, number>;
	#confirmationPopup: MessageBox | null = null;

	constructor(options: AppConstructOptions)
	{
		this.#options = options || {};
		this.#renderTo = this.#getRenderTo();
		this.#buttonPanel = BX.UI.ButtonPanel || null;
		this.#sortConfigForAllUserGroups = options.sortConfigForAllUserGroups;
		this.#selectedMember = options.selectedMember;
		this.#options.userSortConfigName = options.userSortConfigName ?? options.component;

		this.#guid = Text.getRandom(16);

		this.#bindEvents();
	}

	#getRenderTo(): ?HTMLElement
	{
		if (Type.isElementNode(this.#options.renderTo))
		{
			return this.#options.renderTo;
		}

		return document.getElementById(this.#options.renderToContainerId);
	}

	#bindEvents(): void
	{
		this.#handleSliderClose = (event: BaseEvent<BX.SidePanel.Event[]>): void => {
			const [sliderEvent] = event.getData();

			const isSliderBelongsToThisApp = BX.SidePanel?.Instance?.getSliderByWindow(window) === sliderEvent?.getSlider();

			if (!isSliderBelongsToThisApp)
			{
				return;
			}

			this.#confirmBeforeClosingModifiedSlider(sliderEvent);
		};

		EventEmitter.subscribe('SidePanel.Slider:onClose', this.#handleSliderClose);
	}

	#unbindEvents(): void
	{
		EventEmitter.unsubscribe('SidePanel.Slider:onClose', this.#handleSliderClose);

		this.#handleSliderClose = null;
	}

	fireEventReset(): void
	{
		const box = MessageBox.create({
			message: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CANCEL_WARNING'),
			modal: true,
			buttons: [
				new Button({
					color: ButtonColor.PRIMARY,
					size: ButtonSize.SMALL,
					text: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CANCEL_YES_CANCEL'),
					onclick: () => {
						this.#analyticsManager.onCancelChanges();
						this.#resetState();
						box.close();
					},
				}),
				new Button({
					color: ButtonColor.LINK,
					size: ButtonSize.SMALL,
					text: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CANCEL_NO_CANCEL'),
					onclick: () => {
						box.close();
					},
				}),
			],
		});

		box.show();
	}

	#tryShowFeaturePromoter(response: SaveAjaxResponse): boolean
	{
		if (!Type.isArrayFilled(response?.errors))
		{
			return false;
		}

		for (const error of response.errors)
		{
			if (Type.isStringFilled(error?.customData?.sliderCode))
			{
				Runtime.loadExtension('ui.info-helper').then(({ FeaturePromotersRegistry }) => {
					/** @see BX.UI.FeaturePromotersRegistry */
					FeaturePromotersRegistry.getPromoter({ code: error.customData.sliderCode }).show();
				}).catch((loadError) => {
					console.error('ui.accessrights.v2: could not load ui.info-helper', loadError);
				});

				return true;
			}
		}

		return false;
	}

	#showNotification(title): void
	{
		BX.UI.Notification.Center.notify({
			content: title,
			position: 'top-right',
			autoHideDelay: 3000,
		});
	}

	sendActionRequest(): Promise
	{
		return new Promise((resolve, reject) => {
			if (this.#store.state.application.isProgress || !this.#store.getters['application/isModified'])
			{
				resolve();

				return;
			}

			this.#store.commit('application/setProgress', true);

			this.#runSaveAjaxRequest()
				.then(({ userGroups, accessRights, sortConfig }) => {
					this.#analyticsManager.onSaveSuccess();
					this.#userGroupsModel.setInitialUserGroups(userGroups)
						.setSortConfig(sortConfig)
						.setSelectedMember(this.getSelectedMember());
					if (accessRights)
					{
						this.#accessRightsModel.setInitialAccessRights(accessRights);
					}

					const guid: string = this.#guid;
					EventEmitter.emit('BX.UI.AccessRights.V2:afterSave', { userGroups, accessRights, guid });

					// reset modification flags and stuff
					this.#resetState();
					this.#saveUserSortConfig(sortConfig[SELECTED_ALL_USER_ID]);

					this.#showNotification(Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SETTINGS_HAVE_BEEN_SAVED'));
				})
				.catch((response: SaveAjaxResponse) => {
					this.#analyticsManager.onSaveError(response);

					console.warn('ui.accessrights.v2: error during save', response);

					if (this.#tryShowFeaturePromoter(response))
					{
						reject(response);

						return;
					}

					this.#showNotification(response?.errors?.[0]?.message || 'Something went wrong');
					EventEmitter.emit('ui:accessRights:v2:onSaveError', { response });

					reject(response);
				})
				.finally(() => {
					const waitContainer = this.#buttonPanel?.getContainer().querySelector('.ui-btn-wait');
					Dom.removeClass(waitContainer, 'ui-btn-wait');
					this.#store.commit('application/setProgress', false);

					resolve();
				});
		});
	}

	#saveUserSortConfig(userSortConfig): Promise
	{
		if (!Type.isObject(userSortConfig))
		{
			return;
		}

		const userGroups = this.#store.state.userGroups.collection;
		const validUserSortConfig = {};

		for (const [groupId, sortValue] of Object.entries(userSortConfig))
		{
			if (userGroups.has(groupId))
			{
				validUserSortConfig[groupId] = sortValue;
			}
		}

		this.#sortConfigForAllUserGroups = validUserSortConfig;

		return saveSortConfigForAllUserGroups(this.#options.userSortConfigName, this.#sortConfigForAllUserGroups);
	}

	#runSaveAjaxRequest(): Promise<{ userGroups: UserGroupsCollection }>
	{
		const internalUserGroups = this.#store.state.userGroups.collection;

		let userGroups = null;
		if (this.#store.state.application.options.isSaveOnlyChangedRights)
		{
			userGroups = (new OnlyChangedUserGroupsExporter()).transform(internalUserGroups);
		}
		else
		{
			userGroups = (new AllUserGroupsExporter()).transform(internalUserGroups);
		}

		const bodyType = this.#store.state.application.options.bodyType;

		let accessRights = null;
		let deletedAccessRights = null;
		if (this.#store.state.application.options.isSaveAccessRightsList)
		{
			accessRights = (new AccessRightsExporter()).transform(
				this.#store.state.accessRights.collection,
				this.#guid,
			);
			deletedAccessRights = [...this.#store.state.accessRights.deleted.values()];
		}

		// wrap ajax in native promise
		return new Promise((resolve, reject) => {
			Ajax.runComponentAction(
				this.#store.state.application.options.component,
				this.#store.state.application.options.actionSave,
				{
					mode: this.#store.state.application.options.mode,
					[bodyType]: {
						userGroups,
						deletedUserGroups: [...this.#store.state.userGroups.deleted.values()],
						parameters: this.#store.state.application.options.additionalSaveParams,
						accessRights,
						deletedAccessRights,
					},
				},
			)
				.then((response: SaveAjaxResponse) => {
					const maxVisibleUserGroups = this.#store.state.application.options.maxVisibleUserGroups;
					const sortConfig = this.#store.state.userGroups.sortConfig;

					const newUserGroups = (new UserGroupsInternalizer(maxVisibleUserGroups))
						.transform(response.data.USER_GROUPS)
					;

					const transformer = new ShownUserGroupsCopier(internalUserGroups, maxVisibleUserGroups, sortConfig);
					transformer.transform(newUserGroups);
					const newSortConfig = transformer.getSortConfig();

					let newAccessRights = null;
					if (response.data.ACCESS_RIGHTS)
					{
						newAccessRights = (new AccessRightsInternalizer()).transform(response.data.ACCESS_RIGHTS);
					}

					resolve({
						userGroups: newUserGroups,
						sortConfig: newSortConfig,
						accessRights: newAccessRights,
					});
				})
				.catch(reject)
			;
		});
	}

	#confirmBeforeClosingModifiedSlider(sliderEvent: BX.SidePanel.Event): void
	{
		if (!this.#store.getters['application/isModified'] || this.#isUserConfirmedClose)
		{
			return;
		}

		sliderEvent.denyAction();

		if (this.#confirmationPopup && this.#confirmationPopup.getPopupWindow().isShown())
		{
			return;
		}

		this.#confirmationPopup = MessageBox.create({
			mediumButtonSize: false,
			title: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CLOSE_WARNING_TITLE'),
			message: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CLOSE_WARNING'),
			modal: true,
			buttons: [
				new Button({
					color: ButtonColor.PRIMARY,
					size: ButtonSize.SMALL,
					text: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CLOSE_YES_CLOSE'),
					onclick: () => {
						this.#analyticsManager.onCloseWithoutSave();
						this.#isUserConfirmedClose = true;
						this.#confirmationPopup.close();
						this.#confirmationPopup = null;

						setTimeout(() => {
							sliderEvent.getSlider().close();
						});
					},
				}),
				new CancelButton({
					size: ButtonSize.SMALL,
					text: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_CANCEL'),
					onclick: () => {
						this.#confirmationPopup.close();
						this.#confirmationPopup = null;
					},
				}),
			],
			popupOptions: {
				fixed: true,
			},
		});

		this.#confirmationPopup.show();
	}

	#getSortConfigForAllUserGroups(): Promise
	{
		if (this.#sortConfigForAllUserGroups)
		{
			return Promise.resolve(this.#sortConfigForAllUserGroups);
		}

		return new Promise((resolve) => {
			Ajax.runAction('ui.accessrights.getUserSortConfig', {
				data: {
					name: this.#options.userSortConfigName,
				},
			})
				.then((response) => resolve(response.data ? { ...response.data } : null))
				.catch(() => resolve(null));
		});
	}

	draw(): void
	{
		const loader = new Loader({
			target: this.#renderTo,
		});
		loader.show();
		this.#getSortConfigForAllUserGroups()
			.then((result) => {
				this.#sortConfigForAllUserGroups = result;
			})
			.catch(() => {
				this.#sortConfigForAllUserGroups = null;
			})
			.finally(() => {
				const applicationOptions = (new ApplicationInternalizer()).transform(this.#options);
				const userGroupsOptions = {
					sortConfig: {},
					selectedMember: this.#selectedMember,
				};
				if (Type.isObject(this.#sortConfigForAllUserGroups))
				{
					userGroupsOptions.sortConfig[SELECTED_ALL_USER_ID] = this.#sortConfigForAllUserGroups;
				}

				const { store, resetState, userGroupsModel, accessRightsModel } = createStore(
					applicationOptions,
					(new UserGroupsInternalizer(applicationOptions.maxVisibleUserGroups)).transform(this.#options.userGroups),
					(new AccessRightsInternalizer()).transform(this.#options.accessRights),
					this.#guid,
					userGroupsOptions,
				);

				this.#store = store;
				this.#resetState = resetState;
				this.#userGroupsModel = userGroupsModel;
				this.#accessRightsModel = accessRightsModel;

				this.#unwatch = this.#store.watch(
					(state, getters) => getters['application/isModified'],
					(newValue) => {
						if (newValue)
						{
							this.#buttonPanel?.show();
						}
						else
						{
							this.#buttonPanel?.hide();
						}
					},
				);

				this.#app = BitrixVue.createApp(Grid);
				this.#app.use(this.#store);

				loader.hide();
				Dom.clean(this.#renderTo);
				this.#rootComponent = this.#app.mount(this.#renderTo);
				this.#analyticsManager = new AnalyticsManager(this.#store, this.#options.analytics);
			});
	}

	destroy(): void
	{
		this.#analyticsManager = null;

		this.#app.unmount();
		this.#app = null;

		this.#unbindEvents();

		this.#unwatch();
		this.#unwatch = null;

		this.#store = null;
		this.#resetState = null;
		this.#userGroupsModel = null;
		this.#options = null;
		this.#buttonPanel = null;

		Dom.clean(this.#renderTo);
		this.#renderTo = null;

		if (this.#confirmationPopup)
		{
			this.#confirmationPopup.close();
			this.#confirmationPopup.getPopupWindow().destroy();
			this.#confirmationPopup = null;
		}
	}

	hasUnsavedChanges(): boolean
	{
		return !(!this.#store.getters['application/isModified'] || this.#isUserConfirmedClose);
	}

	scrollToSection(sectionCode)
	{
		this.#rootComponent.scrollToSection(sectionCode);
	}

	getSelectedMember(): SelectedMember
	{
		return this.#store.state.userGroups.selectedMember;
	}

	getGuid(): string
	{
		return this.#guid;
	}
}
