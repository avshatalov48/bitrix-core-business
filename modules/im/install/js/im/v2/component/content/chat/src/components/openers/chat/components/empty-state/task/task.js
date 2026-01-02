import { Loc } from 'main.core';

import './css/task.css';

// @vue/component
export const TaskEmptyState = {
	name: 'TaskEmptyState',
	computed: {
		preparedTitle(): string
		{
			return Loc.getMessage('IM_CONTENT_TASK_START_TITLE', {
				'[color]': '<span class="--brand-accent-with-icon">',
				'[/color]': '</span>',
			});
		},
	},
	methods: {
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-task-start__container">
			<div class="bx-im-task-start__title" v-html="preparedTitle"></div>
			<div class="bx-im-task-start__content">
				<div class="bx-im-task-start__features">
					<div class="bx-im-task-start__features_content">
						<div class="bx-im-task-start__feature_item">
							<div class="bx-im-task-start__feature_icon --ai"></div>
							<div class="bx-im-task-start__feature_text">{{ loc('IM_CONTENT_TASK_START_FEATURE_AI') }}</div>
						</div>
						<div class="bx-im-task-start__feature_item">
							<div class="bx-im-task-start__feature_icon --result"></div>
							<div class="bx-im-task-start__feature_text">{{ loc('IM_CONTENT_TASK_START_FEATURE_RESULT') }}</div>
						</div>
						<div class="bx-im-task-start__feature_item">
							<div class="bx-im-task-start__feature_icon --call"></div>
							<div class="bx-im-task-start__feature_text">{{ loc('IM_CONTENT_TASK_START_FEATURE_CALL') }}</div>
						</div>
						<div class="bx-im-task-start__feature_item">
							<div class="bx-im-task-start__feature_icon --changes"></div>
							<div class="bx-im-task-start__feature_text">{{ loc('IM_CONTENT_TASK_START_FEATURE_CHANGES') }}</div>
						</div>
					</div>
				</div>
				<div class="bx-im-task-start__image"></div>
			</div>
		</div>
	`,
};
