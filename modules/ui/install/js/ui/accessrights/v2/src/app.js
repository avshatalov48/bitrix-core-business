import { ajax as Ajax, type AjaxResponse, Dom, type JsonObject, Loc, Runtime, Text, Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Button, ButtonColor, ButtonSize, CancelButton } from 'ui.buttons';
import { MessageBox } from 'ui.dialogs.messagebox';
import { BitrixVue, type VueCreateAppResult } from 'ui.vue3';
import type { Store } from 'ui.vue3.vuex';
import { Grid } from './components/grid';
import 'ui.notification';
import { createStore } from './store/index';
import type { AccessRightsCollection, AccessRightsModel } from './store/model/access-rights-model';
import type { Options } from './store/model/application-model';
import { AllUserGroupsExporter } from './store/model/transformation/backend-exporter/user-groups/all-user-groups-exporter';
import { OnlyChangedUserGroupsExporter } from './store/model/transformation/backend-exporter/user-groups/only-changed-user-groups-exporter';
import type { ExternalAccessRightSection } from './store/model/transformation/internalizer/access-rights-internalizer';
import { AccessRightsInternalizer } from './store/model/transformation/internalizer/access-rights-internalizer';
import { ApplicationInternalizer } from './store/model/transformation/internalizer/application-internalizer';
import type { ExternalUserGroup } from './store/model/transformation/internalizer/user-groups-internalizer';
import { UserGroupsInternalizer } from './store/model/transformation/internalizer/user-groups-internalizer';
import { ShownUserGroupsCopier } from './store/model/transformation/shown-user-groups-copier';
import type { UserGroupsCollection, UserGroupsModel } from './store/model/user-groups-model';

export type AppConstructOptions = Options & {
	renderTo: HTMLElement;
	userGroups: ExternalUserGroup[];
	accessRights: ExternalAccessRightSection[];
};

type SaveAjaxResponse = AjaxResponse<{ACCESS_RIGHTS: JsonObject, USER_GROUPS: JsonObject}>;

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
	#store: Store;
	#resetState: () => void;
	#unwatch: () => void;
	#userGroupsModel: UserGroupsModel;
	#accessRightsModel: AccessRightsModel;

	constructor(options: AppConstructOptions)
	{
		this.#options = options || {};
		this.#renderTo = this.#options.renderTo;
		this.#buttonPanel = BX.UI.ButtonPanel || null;

		this.#guid = Text.getRandom(16);

		this.#bindEvents();
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

	sendActionRequest(): void
	{
		if (this.#store.state.application.isSaving || !this.#store.getters['userGroups/isModified'])
		{
			return;
		}

		this.#store.commit('application/setSaving', true);

		this.#runSaveAjaxRequest()
			.then(({ userGroups, accessRights }) => {
				this.#userGroupsModel.setInitialUserGroups(userGroups);
				this.#accessRightsModel.setInitialAccessRights(accessRights);

				// reset modification flags and stuff
				this.#resetState();

				this.#showNotification(Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SETTINGS_HAVE_BEEN_SAVED'));
			})
			.catch((response: SaveAjaxResponse) => {
				console.warn('ui.accessrights.v2: error during save', response);

				if (this.#tryShowFeaturePromoter(response))
				{
					return;
				}

				this.#showNotification(response?.errors?.[0]?.message || 'Something went wrong');
			})
			.finally(() => {
				const waitContainer = this.#buttonPanel?.getContainer().querySelector('.ui-btn-wait');
				Dom.removeClass(waitContainer, 'ui-btn-wait');
				this.#store.commit('application/setSaving', false);
			})
		;
	}

	#runSaveAjaxRequest(): Promise<{ userGroups: UserGroupsCollection, accessRights: AccessRightsCollection }>
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
					},
				},
			)
				.then((response: SaveAjaxResponse) => {
					const maxVisibleUserGroups = this.#store.state.application.options.maxVisibleUserGroups;

					const newUserGroups = (new UserGroupsInternalizer(maxVisibleUserGroups))
						.transform(response.data.USER_GROUPS)
					;

					(new ShownUserGroupsCopier(internalUserGroups, maxVisibleUserGroups)).transform(newUserGroups);

					resolve({
						userGroups: newUserGroups,
						accessRights: (new AccessRightsInternalizer()).transform(response.data.ACCESS_RIGHTS),
					});
				})
				.catch(reject)
			;
		});
	}

	#confirmBeforeClosingModifiedSlider(sliderEvent: BX.SidePanel.Event): void
	{
		if (!this.#store.getters['userGroups/isModified'] || this.#isUserConfirmedClose)
		{
			return;
		}

		sliderEvent.denyAction();

		const box = MessageBox.create({
			title: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CLOSE_WARNING_TITLE'),
			message: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CLOSE_WARNING'),
			modal: true,
			buttons: [
				new Button({
					color: ButtonColor.PRIMARY,
					size: ButtonSize.SMALL,
					text: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_MODIFIED_CLOSE_YES_CLOSE'),
					onclick: () => {
						this.#isUserConfirmedClose = true;
						box.close();
						sliderEvent.getSlider().close();
					},
				}),
				new CancelButton({
					size: ButtonSize.SMALL,
					onclick: () => {
						box.close();
					},
				}),
			],
		});

		box.show();
	}

	draw(): void
	{
		const applicationOptions = (new ApplicationInternalizer()).transform(this.#options);

		const { store, resetState, accessRightsModel, userGroupsModel } = createStore(
			applicationOptions,
			(new UserGroupsInternalizer(applicationOptions.maxVisibleUserGroups)).transform(this.#options.userGroups),
			(new AccessRightsInternalizer()).transform(this.#options.accessRights),
			this.#guid,
		);

		this.#store = store;
		this.#resetState = resetState;
		this.#userGroupsModel = userGroupsModel;
		this.#accessRightsModel = accessRightsModel;

		this.#unwatch = this.#store.watch(
			(state, getters) => getters['userGroups/isModified'],
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

		Dom.clean(this.#renderTo);
		this.#app.mount(this.#renderTo);
	}

	destroy(): void
	{
		this.#app.unmount();
		this.#app = null;

		this.#unbindEvents();

		this.#unwatch();
		this.#unwatch = null;

		this.#store = null;
		this.#resetState = null;
		this.#userGroupsModel = null;
		this.#accessRightsModel = null;
		this.#options = null;
		this.#buttonPanel = null;

		Dom.clean(this.#renderTo);
		this.#renderTo = null;
	}
}
