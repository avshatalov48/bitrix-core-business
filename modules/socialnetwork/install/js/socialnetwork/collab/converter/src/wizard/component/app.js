import { Event } from 'main.core';

import { BIcon } from 'ui.icon-set.api.vue';
import { Outline } from 'ui.icon-set.api.core';
import { Button as UiButton, AirButtonStyle, ButtonSize } from 'ui.vue3.components.button';
import 'ui.icon-set.outline';

import { Loader } from './loader';
import { GroupAvatar } from './group-avatar';
import { Advantage } from './steps/advantage';
import { UserList } from './user/user-list';
import { StepAdvantages } from './steps/step-advantages';
import { StepAfter } from './steps/step-after';
import { StepFeatures } from './steps/step-features';
import { api } from '../../service/api';

import type { Member, Feature } from '../../service/types';

import '../wizard.css';

// @vue/component
export const App = {
	name: 'SocialnetworkCollabConverter',
	components: {
		BIcon,
		Loader,
		GroupAvatar,
		UserList,
		Advantage,
		UiButton,
		StepAdvantages,
		StepAfter,
		StepFeatures,
	},
	props: {
		groupId: {
			type: Number,
			required: true,
		},
		redirectAfterSuccess: {
			type: Boolean,
			default: false,
		}
	},
	setup(): Object
	{
		return {
			ButtonSize,
			AirButtonStyle,
			Outline,
		};
	},
	data(): Object
	{
		return {
			collab: null,
			isConversionStarted: false,
			group: null,
			step: 1,
		};
	},
	computed: {
		leftTopTitle(): string
		{
			return this.loc(
				'SN_COLLAB_CONVERTER_TITLE',
				{
					'#ACCENT_START#': '<span class="socialnetwork-collab-converter-wizard-left-top-title-accent">',
					'#ACCENT_END#': '</span>',
				},
			);
		},
		groupManagers(): Member[]
		{
			return this.group.members.filter((member: Member) => ['owner', 'moderator'].includes(member.role));
		},
		groupCommonMembers(): Member[]
		{
			return this.group.members.filter((member: Member) => ['member'].includes(member.role));
		},
		featuresToDisable(): boolean
		{
			const featuresToDisable = new Set(['forum', 'photo', 'group_lists', 'wiki', 'landing_knowledge', 'marketplace']);

			return this.group.features.filter((feature: Feature) => feature.isActive && featuresToDisable.has(feature.name));
		},
		fullScaleTitle(): string
		{
			if (this.collab)
			{
				return this.loc('SN_COLLAB_CONVERTER_DONE');
			}

			return this.loc('SN_COLLAB_CONVERTER_TITLE_CONVERTING');
		},
		fullScaleSubtitle(): string
		{
			return this.collab ? '' : this.loc('SN_COLLAB_CONVERTER_SUBTITLE_CONVERTING');
		},
	},
	async created(): Promise<void>
	{
		try
		{
			this.group = await api.getGroup(this.groupId);
		}
		catch (error)
		{
			console.error('Fetch group error', error);
			this.close();
		}
	},
	methods: {
		handleStepAdvantagesContinue(): void
		{
			this.step++;
		},
		handleStepAfterContinue(): void
		{
			if (this.featuresToDisable.length === 0)
			{
				void this.convert();

				return;
			}

			this.step++;
		},
		handleStepFeaturesContinue(): void
		{
			void this.convert();
		},
		async convert(): Promise<void>
		{
			this.isConversionStarted = true;
			try
			{
				this.collab = await api.convertToCollab(this.groupId);
				Event.EventEmitter.emit('socialnetwork:collab:converter:success', { collab: this.collab });
			}
			catch (error)
			{
				console.error('Conversion error', error);
				this.close();
			}
		},
		close(): void
		{
			Event.EventEmitter.emit('socialnetwork:collab:converter:close');
		},
		openCollab(): void
		{
			if (top.BX.Messenger.Public)
			{
				top.BX.Messenger.Public.openChat(this.collab.dialogId);
			}
			this.close();
		},
	},
	template: `
		<div
			v-if="group"
			class="socialnetwork-collab-converter-wizard"
		>
			<template v-if="isConversionStarted">
				<div class="socialnetwork-collab-converter-wizard-full-scale">
					<div class="socialnetwork-collab-converter-wizard-full-scale-content">
						<div class="socialnetwork-collab-converter-wizard-full-scale-header">
							<div class="socialnetwork-collab-converter-wizard-full-scale-title">
								{{ fullScaleTitle }}
							</div>
							<div class="socialnetwork-collab-converter-wizard-full-scale-subtitle">
								{{ fullScaleSubtitle }}
							</div>
						</div>
						<div class="socialnetwork-collab-converter-wizard-full-scale-group">
							<GroupAvatar :group="group" :size="72"/>
							<div class="socialnetwork-collab-converter-wizard-group-title">
								{{ group.name }}
							</div>
						</div>
					</div>
					<div v-if="collab && !redirectAfterSuccess" class="socialnetwork-collab-converter-wizard-full-scale-buttons">
						<UiButton
							:text="loc('SN_COLLAB_CONVERTER_OPEN_COLLAB')"
							:size="ButtonSize.LARGE"
							@click="openCollab"
							data-test-id="socialnetwork-collab-converter-open-collab-button"
						/>
						<UiButton
							:text="loc('SN_COLLAB_CONVERTER_GET_IT')"
							:size="ButtonSize.LARGE"
							:style="AirButtonStyle.OUTLINE"
							@click="close"
							data-test-id="socialnetwork-collab-converter-close-button"
						/>
					</div>
				</div>
			</template>
			<template v-else>
				<div class="socialnetwork-collab-converter-wizard-left">
					<div class="socialnetwork-collab-converter-wizard-left-top">
						<div class="socialnetwork-collab-converter-wizard-left-top-title" v-html="leftTopTitle"/>
						<div class="socialnetwork-collab-converter-wizard-group-info">
							<div class="socialnetwork-collab-converter-wizard-group-info-main">
								<GroupAvatar :group="group"/>
								<div class="socialnetwork-collab-converter-wizard-group-title">
									{{ group.name }}
								</div>
							</div>
							<UserList :hostUsers="groupManagers" :commonUsers="groupCommonMembers"/>
						</div>
					</div>
					<div class="socialnetwork-collab-converter-wizard-left-link">
						<BIcon :name="Outline.QUESTION" :size="16"/>
						<div class="socialnetwork-collab-converter-wizard-left-link-text">
							{{ loc('SN_COLLAB_CONVERTER_LINK_MORE') }}
						</div>
					</div>
				</div>
				<div class="socialnetwork-collab-converter-wizard-right">
					<StepAdvantages v-if="step === 1" @continue="handleStepAdvantagesContinue" @close="close"/>
					<StepAfter v-if="step === 2" @continue="handleStepAfterContinue" @close="close"/>
					<StepFeatures v-if="step === 3" :features="featuresToDisable" @continue="handleStepFeaturesContinue" @close="close"/>
				</div>
			</template>
		</div>
		<Loader v-else/>
	`,
};
