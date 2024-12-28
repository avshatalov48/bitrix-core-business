import { CollabList } from 'im.v2.component.list.items.collab';
import { CreateChatPromo } from 'im.v2.component.elements';
import { Layout, ChatType, ActionByUserType } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { Logger } from 'im.v2.lib.logger';
import { CreateChatManager } from 'im.v2.lib.create-chat';

import './css/collab-container.css';
import { PermissionManager } from 'im.v2.lib.permission';

// @vue/component
export const CollabListContainer = {
	name: 'CollabListContainer',
	components: { CollabList, CreateChatPromo },
	emits: ['selectEntity'],
	computed:
	{
		ChatType: () => ChatType,
		canCreate(): boolean
		{
			const creationAvailable = FeatureManager.isFeatureAvailable(Feature.collabCreationAvailable);
			const hasAccess = PermissionManager.getInstance().canPerformActionByUserType(ActionByUserType.createCollab);

			return creationAvailable && hasAccess;
		},
	},
	created()
	{
		Logger.warn('List: Collab container created');
	},
	methods:
	{
		onChatClick(dialogId: string): void
		{
			this.$emit('selectEntity', { layoutName: Layout.collab.name, entityId: dialogId });
		},
		onCreateClick(): void
		{
			Analytics.getInstance().chatCreate.onStartClick(ChatType.collab);
			this.startCollabCreation();
		},
		startCollabCreation()
		{
			CreateChatManager.getInstance().startChatCreation(ChatType.collab);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-list-container-collab__container">
			<div class="bx-im-list-container-collab__header_container">
				<div class="bx-im-list-container-collab__header_title">
					{{ loc('IM_LIST_CONTAINER_COLLAB_HEADER_TITLE') }}
				</div>
				<div
					v-if="canCreate"
					@click="onCreateClick" 
					class="bx-im-list-container-collab__header_create-collab"
				></div>
			</div>
			<div class="bx-im-list-container-collab__elements_container">
				<div class="bx-im-list-container-collab__elements">
					<CollabList @chatClick="onChatClick" />
				</div>
			</div>
		</div>
	`,
};
