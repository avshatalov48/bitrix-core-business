import { Event, Type, Dom, Loc } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import PostFormEditor from './editor';

export default class PostFormAutoSave
{
	constructor(autoSaveRestoreMethod, initRestore)
	{
		this.init(autoSaveRestoreMethod, initRestore);
	}

	init(autoSaveRestoreMethod, initRestore)
	{
		const formId = 'blogPostForm';
		const form = document.getElementById(formId);
		const titleID = 'POST_TITLE';
		const title = document.getElementById(titleID);
		const tags = form.TAGS;

		if (!form)
		{
			return;
		}

		initRestore = (!Type.isUndefined(initRestore) ? !!initRestore : true);

		EventEmitter.subscribe(form, 'onAutoSavePrepare', (event: BaseEvent) => {
			const [ ob, handler ] = event.getData();

			ob.DISABLE_STANDARD_NOTIFY = true;
			const _ob = ob;
			setTimeout(() => { this.bindLHEEvents(_ob) }, 100);
		});

		EventEmitter.subscribe(form, 'onAutoSave', (event: BaseEvent) => {
			const [ ob, formData ] = event.getData();

			formData.TAGS = tags.value;
			delete formData.POST_MESSAGE;
		});

		if (autoSaveRestoreMethod == 'Y')
		{
			EventEmitter.subscribe(form, 'onAutoSaveRestoreFound', (event: BaseEvent) => {
				const [ ob, data ] = event.getData();

				let text = data[`text${formId}`];
				text = (Type.isStringFilled(text) ? text.trim() : '');

				let title = data[titleID];
				title = (Type.isStringFilled(title) ? title.trim() : '');

				if (
					!Type.isStringFilled(text)
					&& !Type.isStringFilled(title)
				)
				{
					return;
				}

				ob.Restore();
			});
		}
		else
		{
			EventEmitter.subscribe(form, 'onAutoSaveRestoreFound', (event: BaseEvent) => {
				const [ ob, data ] = event.getData();

				let text = data[`text${formId}`];
				text = (Type.isStringFilled(text) ? text.trim() : '');

				let title = data[titleID];
				title = (Type.isStringFilled(title) ? title.trim() : '');

				if (
					!Type.isStringFilled(text)
					&& !Type.isStringFilled(title)
				)
				{
					return;
				}

				const messageBody = document.getElementById('microoPostFormLHE_blogPostForm');
				const textNode = Dom.create('DIV', {
					attrs: {
						className: 'feed-add-successfully'
					},
					children : [
						Dom.create('SPAN', {
							attrs: {
								className : 'feed-add-info-icon'
							}
						}),
						Dom.create('A', {
							attrs : {
								className: 'feed-add-info-text',
								href : '#'
							},
							events: {
								click : () => {
									ob.Restore();
									textNode.parentNode.removeChild(textNode);
									return false;
								}
							},
							text: Loc.getMessage('BLOG_POST_AUTOSAVE2')
						})
					]
				});

				if (messageBody)
				{
					messageBody.parentNode.insertBefore(textNode, messageBody);
				}
			});
		}

		if (initRestore)
		{
			EventEmitter.subscribe(form, 'onAutoSaveRestore', (event: BaseEvent) => {
				const [ ob, data ] = event.getData();

				title.value = data[titleID];
				if(
					Type.isStringFilled(data[titleID])
					&& data[titleID] !== title.getAttribute('placeholder')
				)
				{
					if(document.getElementById('divoPostFormLHE_blogPostForm').style.display !== 'none')
					{
						PostFormEditor.getInstance(formId).showPanelTitle(true);
					}
					else
					{
						window.bShowTitle = true;
					}

					if (Type.isFunction(title.__onchange))
					{
						title.__onchange();
					}
				}

				const formTags = window[`BXPostFormTags_${formId}`];

				if(
					data.TAGS.length > 0
					&& formTags
				)
				{
					const tags = formTags.addTag(data.TAGS);
					if (tags.length > 0)
					{
						BX.show(formTags.tagsArea);
					}
				}

				EventEmitter.emit('onAutoSaveRestoreDestination', new BaseEvent({
					compatData: [{
						formId: formId,
						data: data
					}]
				}));

				this.bindLHEEvents(ob);
			});
		}
	};

	bindLHEEvents(_ob)
	{
		const form = document.getElementById('blogPostForm');
		const title = document.getElementById('POST_TITLE');
		const tags = form.TAGS;

		Event.bind(title, 'keydown', _ob.Init.bind(_ob));
		Event.bind(tags, 'keydown', _ob.Init.bind(_ob));
	};
}