import { Core } from 'im.v2.application.core';

import { Messenger } from 'im.public';
import { Color, ActionByUserType, UserType } from 'im.v2.const';
import { Button as MessengerButton, ButtonSize } from 'im.v2.component.elements';
import { Analytics } from 'im.v2.lib.analytics';
import { PermissionManager } from 'im.v2.lib.permission';
import { CreatableChat } from 'im.v2.component.content.chat-forms.forms';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { ThemeManager, SpecialBackground } from 'im.v2.lib.theme';

import { FeatureBlock } from './components/feature-block';

import './css/collab.css';

import type { CustomColorScheme } from 'im.v2.component.elements';
import type { BackgroundStyle } from 'im.v2.lib.theme';

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
		createButtonColorScheme(): CustomColorScheme
		{
			return {
				borderColor: Color.transparent,
				backgroundColor: Color.white,
				iconColor: Color.gray90,
				textColor: Color.gray90,
				hoverColor: Color.white,
				textHoverColor: Color.collab70,
			};
		},
		isCurrentUserCollaber(): boolean
		{
			const currentUser = this.$store.getters['users/get'](Core.getUserId(), true);

			return currentUser.type === UserType.collaber;
		},
		backgroundStyle(): BackgroundStyle
		{
			return ThemeManager.getBackgroundStyleById(SpecialBackground.collab);
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
		<div class="bx-im-content-collab-start__container" :style="backgroundStyle">
			<div class="bx-im-content-collab-start__content">
				<div class="bx-im-content-collab-start__image"></div>
				<div class="bx-im-content-collab-start__title">
					{{ loc('IM_CONTENT_COLLAB_START_TITLE_V2') }}
				</div>
				<div v-if="isCurrentUserCollaber" class="bx-im-content-collab-start__blocks">
					<FeatureBlock
						:title="loc('IM_CONTENT_COLLAB_START_BLOCK_COLLABER_TITLE_1')"
						:subtitle="loc('IM_CONTENT_COLLAB_START_BLOCK_SUBTITLE_1')"
						name="collaboration"
					/>
					<FeatureBlock
						:title="loc('IM_CONTENT_COLLAB_START_BLOCK_COLLABER_TITLE_2')"
						:subtitle="loc('IM_CONTENT_COLLAB_START_BLOCK_COLLABER_SUBTITLE_2')"
						name="business"
					/>
					<FeatureBlock
						:title="loc('IM_CONTENT_COLLAB_START_BLOCK_TITLE_3')"
						:subtitle="loc('IM_CONTENT_COLLAB_START_BLOCK_SUBTITLE_3')"
						name="result"
					/>
				</div>
				<div v-else class="bx-im-content-collab-start__blocks">
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
				<MessengerButton
					v-if="canCreateCollab"
					:size="ButtonSize.XXL"
					:customColorScheme="createButtonColorScheme"
					:text="loc('IM_CONTENT_COLLAB_START_CREATE_BUTTON')"
					:isRounded="true"
					@click="onCreateClick"
				/>
			</div>
		</div>
	`,
};
