import { ajax as Ajax } from 'main.core';
import type { EntityOptions, Item, ItemId } from 'ui.entity-selector';
import { mapState } from 'ui.vue3.vuex';
import { type SelectorService } from '../service/selector-service';
import { ServiceLocator } from '../service/service-locator';
import type { SelectedMember } from '../store/model/user-groups-model';
import { Chip } from 'ui.system.chip.vue';
import { SELECTED_ALL_USER_ID } from '../store/model/user-groups-model';

export const MemberSelector = {
	name: 'MemberSelector',
	components: { Chip },
	data(): Object {
		return {
			accessCodesCache: {},
		};
	},
	computed: {
		selectedMember: {
			get(): SelectedMember {
				return this.$store.state.userGroups.selectedMember;
			},
			set(member) {
				this.$store.dispatch('userGroups/selectMember', { member });
			},
		},
		selectedMemberName(): string {
			if (
				!this.selectedMember.id
				|| this.selectedMember.id === SELECTED_ALL_USER_ID
				|| !this.selectedMember?.member?.name
			)
			{
				return this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_USER_SELECTOR_ALL_USERS');
			}

			return this.selectedMember.member.name;
		},
		selectedMemberAvatar(): ?string {
			if (!this.selectedMember.id || this.selectedMember.id === SELECTED_ALL_USER_ID)
			{
				return null;
			}

			return this.selectedMember?.member?.avatar ?? '/bitrix/js/ui/accessrights/v2/images/user-avatar.svg';
		},
		avatarBackgroundImage(): string {
			return `url(${encodeURI(this.selectedMemberAvatar)})`;
		},
		selectedItems(): ItemId[] {
			if (this.selectedMember && this.selectedMember.id && this.selectedMember.id !== SELECTED_ALL_USER_ID)
			{
				return [[this.selectedMember.entityType, this.selectedMember.entityId]];
			}

			return [['meta-user', SELECTED_ALL_USER_ID]];
		},
		...mapState({
			memberOptions: (state) => state.application.options.additionalMembersParams,
		}),
	},
	methods: {
		openUserSelector() {
			this.getSelectorService()
				.createDialog({
					targetNode: this.$refs.userSelector,
					preselectedItems: this.selectedItems,
					multiple: false,
					hideOnSelect: true,
					hideOnDeselect: true,
					events: {
						'Item:onSelect': this.onMemberSelect,
						'Item:onDeselect': this.onMemberDeselect,
					},
					entities: this.getEntities(),
				})
				.show();
		},
		getEntities(): EntityOptions[] {
			const entities = this.getSelectorService().entities();
			entities.push({
				id: 'meta-user',
				options: {
					'all-users': true,
				},
			});

			return entities;
		},
		onMemberSelect(event: BaseEvent): void {
			const { item } = event.getData();
			const cacheKey = item.id === SELECTED_ALL_USER_ID ? SELECTED_ALL_USER_ID : item.entityId + item.id;
			const newSelectedMember = {
				id: cacheKey,
				entityId: item.id,
				entityType: item.entityId,
				member: this.getSelectorService().getMemberByItem(item),
			};

			const cachedAccessCodes = this.getAccessCodesFromCache(cacheKey);
			if (cachedAccessCodes.length > 0 || item.id === SELECTED_ALL_USER_ID)
			{
				this.selectedMember = {
					...newSelectedMember,
					accessCodes: cachedAccessCodes,
				};

				return;
			}

			if (!this.shouldUseAjax(item))
			{
				this.selectedMember = {
					...newSelectedMember,
					accessCodes: this.getDefaultAccessCodesByItem(item),
				};

				return;
			}

			this.$store.commit('application/setProgress', true);
			Ajax.runAction('ui.accessrights.getAccessCodes', {
				data: {
					id: newSelectedMember.member.id,
					params: this.$store.state.application.options.additionalSaveParams,
					moduleId: this.$store.state.application.options.moduleId,
				},
			})
				.then((response) => {
					let accessCodes = response.data ?? [];
					if (accessCodes.length === 0)
					{
						accessCodes = this.getDefaultAccessCodesByItem(item);
					}
					this.selectedMember = {
						...newSelectedMember,
						accessCodes,
					};
					this.accessCodesCache[cacheKey] = new Set(accessCodes);
					this.$store.commit('application/setProgress', false);
				})
				.catch(() => {
					const accessCodes = this.getDefaultAccessCodesByItem(item);
					this.selectedMember = {
						...newSelectedMember,
						accessCodes,
					};
					this.accessCodesCache[cacheKey] = accessCodes;
					this.$store.commit('application/setProgress', false);
				});
		},
		shouldUseAjax(item): boolean {
			const entityTypes = [
				'user',
				'department',
				'structure-node',
			];

			return entityTypes.includes(item.entityId);
		},
		onMemberDeselect(event: BaseEvent): void {
			this.selectedMember = {
				id: SELECTED_ALL_USER_ID,
				entityId: SELECTED_ALL_USER_ID,
				entityType: 'meta-user',
				accessCodes: [],
			};
		},
		getDefaultAccessCodesByItem(item: Item): string[] {
			if (item.entityId === 'structure-role')
			{
				const itemId = item.id.match(/^(?:ATD|ATE|ATT|AD|AE|AT)([1-9]\d*)$/)?.[1];
				const accessCodes = [this.getSelectorService().getAccessCodeByItem(item), `AE${itemId}`];

				return [...new Set(accessCodes)];
			}

			if (item.entityId === 'project-access-codes')
			{
				const itemId = item.id.match(/^SG(\d+)_([AEK])$/)?.[1];
				const accessCodes = [this.getSelectorService().getAccessCodeByItem(item), `SG${itemId}_K`];

				return [...new Set(accessCodes)];
			}

			return [this.getSelectorService().getAccessCodeByItem(item)];
		},
		getAccessCodesFromCache(id): [] {
			return this.accessCodesCache[id] || [];
		},
		getSelectorService(): SelectorService
		{
			return ServiceLocator.getSelectorService(this.memberOptions);
		},
	},
	template: `
		<div ref="userSelector" class="ui-access-rights-v2-user-selector">
			<Chip
				:image="selectedMemberAvatar ? { src: selectedMemberAvatar, alt: selectedMemberName } : ''"
				:dropdown="true"
				:text=selectedMemberName
				@click="openUserSelector"
			/>
		</div>
	`,
};
