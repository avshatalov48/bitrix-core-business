import { Runtime } from 'main.core';

import { Core } from 'im.v2.application.core';

import {
	FormConfigAiAssistant,
	FormConfigCopilot,
	FormConfigGeneral,
	FormContext,
} from './const/const';

import type {
	FormConfigType,
	CopilotFormParams,
} from './types/types';

const FEEDBACK_EXTENSION = 'ui.feedback.form';

export class FeedbackManager
{
	async openAiAssistantForm(): Promise<void>
	{
		const { id, forms } = FormConfigAiAssistant;
		const formattedId = `${id}-${this.#generateFormIdSuffix()}`;
		const config = {
			id: formattedId,
			forms,
			presets: {
				sender_page: FormContext.aiAssistantBot,
			},
		};

		void this.#openForm(config);
	}

	async openCopilotForm(params: CopilotFormParams): Promise<void>
	{
		const { id, forms } = FormConfigCopilot;
		const formattedId = `${id}-${this.#generateFormIdSuffix()}`;
		const context = params.userCounter <= 2 ? FormContext.copilotBot : FormContext.copilotGroup;
		const config = {
			id: formattedId,
			forms,
			presets: {
				sender_page: context,
				language: Core.getLanguageId(),
				cp_answer: params.text,
			},
		};

		void this.#openForm(config);
	}

	async openGeneralForm(): Promise<void>
	{
		const { id, forms } = FormConfigGeneral;
		const config = {
			id,
			forms,
			presets: { sender_page: FormContext.general },
		};

		void this.#openForm(config);
	}

	async #openForm(config: FormConfigType): Promise<void>
	{
		await Runtime.loadExtension(FEEDBACK_EXTENSION);
		BX.UI.Feedback.Form.open(config);
	}

	#generateFormIdSuffix(): number
	{
		return Math.round(Math.random() * 1000);
	}
}
