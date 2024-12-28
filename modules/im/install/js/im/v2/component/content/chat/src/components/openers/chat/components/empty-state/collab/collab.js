import { Loc } from 'main.core';

import { Messenger } from 'im.public';
import { Color, ActionByUserType } from 'im.v2.const';
import { Button as MessengerButton, ButtonSize } from 'im.v2.component.elements';
import { Analytics } from 'im.v2.lib.analytics';
import { PermissionManager } from 'im.v2.lib.permission';
import { CreatableChat } from 'im.v2.component.content.chat-forms.forms';
import { Feature, FeatureManager } from 'im.v2.lib.feature';

import { FeatureBlock } from './components/feature-block';

import './css/collab.css';

import type { CustomColorScheme } from 'im.v2.component.elements';

// @vue/component
export const CollabEmptyState = {
	name: 'CollabEmptyState',
	components: { FeatureBlock, MessengerButton },
	computed:
	{
		ButtonSize: () => ButtonSize,
		canCreateCollab(): boolean
		{
			const isAvailable = FeatureManager.isFeatureAvailable(Feature.collabCreationAvailable);
			const canCreate = PermissionManager.getInstance().canPerformActionByUserType(ActionByUserType.createCollab);

			return isAvailable && canCreate;
		},
		preparedTitle(): string
		{
			return Loc.getMessage('IM_CONTENT_COLLAB_START_TITLE', {
				'[highlight]': '<span class="bx-im-content-collab-start__title_highlight">',
				'[/highlight]': '</span>',
			});
		},
		createButtonColorScheme(): CustomColorScheme
		{
			return {
				borderColor: Color.transparent,
				backgroundColor: Color.collab60,
				iconColor: Color.white,
				textColor: Color.white,
				hoverColor: Color.collab50,
			};
		},
	},
	methods:
	{
		onCreateClick()
		{
			Analytics.getInstance().chatCreate.onCollabEmptyStateCreateClick();
			Messenger.openChatCreation(CreatableChat.collab);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-content-collab-start__container">
			<div class="bx-im-content-collab-start__title" v-html="preparedTitle"></div>
			<div class="bx-im-content-collab-start__content">
				<div class="bx-im-content-collab-start__blocks">
					<FeatureBlock
						:title="loc('IM_CONTENT_COLLAB_START_BLOCK_TITLE_1')"
						:subtitle="loc('IM_CONTENT_COLLAB_START_BLOCK_SUBTITLE_1')"
						name="collaboration"
					/>
					<FeatureBlock
						:title="loc('IM_CONTENT_COLLAB_START_BLOCK_TITLE_2')"
						:subtitle="loc('IM_CONTENT_COLLAB_START_BLOCK_SUBTITLE_2')"
						name="business"
					/>
					<FeatureBlock
						:title="loc('IM_CONTENT_COLLAB_START_BLOCK_TITLE_3')"
						:subtitle="loc('IM_CONTENT_COLLAB_START_BLOCK_SUBTITLE_3')"
						name="result"
					/>
				</div>
				<div class="bx-im-content-collab-start__image"></div>
			</div>
			<MessengerButton
				v-if="canCreateCollab"
				:size="ButtonSize.XXL"
				:customColorScheme="createButtonColorScheme"
				:text="loc('IM_CONTENT_COLLAB_START_CREATE_BUTTON')"
				:isRounded="true"
				@click="onCreateClick"
			/>
		</div>
	`,
};
