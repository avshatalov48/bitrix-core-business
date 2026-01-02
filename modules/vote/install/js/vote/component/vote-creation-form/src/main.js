import { ready, Event, Dom, ajax } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { UI } from 'ui.notification';
import { BitrixVue } from 'ui.vue3';
import { VoteForm } from './components/form';
import { VoteAnalytics, AnalyticsEvents } from 'vote.analytics';

type FormOptions = {
	chatId: number;
	containerId: string;
	maxQuestionsCount: number;
	minAnswersCount: number;
	maxAnswersCount: number;
};

const indexStep = 10;

const calculateSortOrder = (questionIndex: number): string => {
	return String(questionIndex * indexStep + indexStep);
};

const parseData = (chatId: number): { [key: string]: string } => {
	const { questions, settings } = App.voteForm;
	const { anonymousVote, allowRevoking } = settings;
	const data = {
		chatId,
		'IM_MESSAGE_VOTE_DATA[ANONYMITY]': anonymousVote ? '2' : '1',
		'IM_MESSAGE_VOTE_DATA[OPTIONS]': allowRevoking ? '1' : '0',
	};

	return Object.values(questions).reduce((acc, question, questionIndex) => {
		const { questionText, allowMultipleAnswers, answers } = question;
		const questionKey = `IM_MESSAGE_VOTE_DATA[QUESTIONS][${questionIndex}]`;
		acc[`${questionKey}[QUESTION]`] = questionText;
		acc[`${questionKey}[C_SORT]`] = calculateSortOrder(questionIndex);
		acc[`${questionKey}[QUESTION_TYPE]`] = 'text';
		acc[`${questionKey}[FIELD_TYPE]`] = allowMultipleAnswers ? '1' : '0';

		Object.values(answers).forEach((answer, answerIndex) => {
			const answerKey = `${questionKey}[ANSWERS][${answerIndex}]`;
			acc[`${answerKey}[MESSAGE]`] = answer;
			acc[`${answerKey}[MESSAGE_TYPE]`] = 'text';
			acc[`${answerKey}[C_SORT]`] = String(answerIndex * 10 + 10);
			acc[`${answerKey}[FIELD_TYPE]`] = '0';
		});

		return acc;
	}, data);
};

const createVote = async (saveButton: HTMLElement, chatId: number): Promise<void> => {
	const parsedData = parseData(chatId);
	try
	{
		const { settings, questions } = App.voteForm;
		const { anonymousVote, allowRevoking } = settings;
		const { data } = await ajax.runAction('bitrix:vote.Integration.Im.send', { data: parsedData });
		const { messageId } = data;
		VoteAnalytics.publishVote(anonymousVote, messageId);
		const [{ answers, allowMultipleAnswers }] = Object.values(questions);
		const answersCount = Object.keys(answers).length;
		VoteAnalytics.setupVote(answersCount, AnalyticsEvents.setOptions, messageId);
		VoteAnalytics.setupVote(allowMultipleAnswers, AnalyticsEvents.isMultipleChoice, messageId);
		VoteAnalytics.setupVote(allowRevoking, AnalyticsEvents.setCancelVote, messageId);
		BX.SidePanel.Instance.close();
	}
	catch (response)
	{
		Dom.removeClass(saveButton, 'ui-btn-wait');
		const errors = response?.errors ?? [];
		const [firstError] = errors;
		if (firstError)
		{
			UI.Notification.Center.notify({ content: firstError.message });
		}
	}
};

const toggleSaveButton = (saveButton: HTMLElement): void => {
	Dom.toggleClass(saveButton, 'ui-btn-disabled');
};

const init = (chatId: number): void => {
	const saveButton = document.getElementById('vote-im-edit-slider-button-create');
	toggleSaveButton(saveButton);
	Event.bind(saveButton, 'click', () => createVote(saveButton, chatId));
	const validateHandler = () => toggleSaveButton(saveButton);
	EventEmitter.subscribe('vote-creation-form-validate', validateHandler);
	EventEmitter.subscribeOnce('SidePanel.Slider:onDestroy', () => {
		EventEmitter.unsubscribe('vote-creation-form-validate', validateHandler);
	});
};

export class App
{
	static voteForm: Object;

	static mount(formOptions: FormOptions): void
	{
		const { containerId, chatId, ...rest } = formOptions;
		const container = document.getElementById(containerId);
		const app = BitrixVue.createApp(VoteForm, { options: { ...rest, minQuestionsCount: 1 } });
		App.voteForm = app.mount(container);
		ready(() => init(chatId));
	}
}
