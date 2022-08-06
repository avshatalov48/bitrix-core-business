import { Type, Tag, Runtime } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { ResultManager } from 'tasks.result';

export class CommentForm
{
	static resultFieldTaskIdList = [];
	static taskResultCommentsData = {};

	static appendResultFieldTaskIds(taskIdList)
	{
		if (!Type.isArray(taskIdList))
		{
			return;
		}

		taskIdList = taskIdList.map((value) => { return parseInt(value); })
		this.resultFieldTaskIdList = [...this.resultFieldTaskIdList, ...taskIdList];
	}

	static appendTaskResultComments(data)
	{
		if (Type.isUndefined(ResultManager))
		{
			return;
		}

		this.taskResultCommentsData = Object.assign(this.taskResultCommentsData, data);

		Object.entries(this.taskResultCommentsData).forEach(([taskId, commentsIdList]) => {
			ResultManager.getInstance().initResult({
				context: 'task',
				taskId: parseInt(taskId),
				comments: commentsIdList,
			});
		});
	}

	static onAfterShow(obj, text, data)
	{
		if (!Type.isPlainObject(data))
		{
			data = {};
		}

		EventEmitter.emit('OnBeforeSocialnetworkCommentShowedUp', new BaseEvent({
			compatData: ['socialnetwork'],
		}));

		const postData = {
			ENTITY_XML_ID: obj.currentEntity.ENTITY_XML_ID,
			ENTITY_TYPE: obj.currentEntity.ENTITY_XML_ID.split('_')[0],
			ENTITY_ID: obj.currentEntity.ENTITY_XML_ID.split('_')[1],
			parentId: obj.id[1],
			comment_post_id: obj.currentEntity.ENTITY_XML_ID.split('_')[1],
			edit_id: obj.id[1],
			act: (obj.id[1] > 0 ? 'edit' : 'add'),
		};

		Object.entries(postData).forEach(([key, value]) =>
		{
			if (!obj.form[key])
			{
				obj.form.appendChild(Tag.render`<input type="hidden" name="${key}">`);
			}
			obj.form[key].value = value;
		});

		this.onLightEditorShow(text, data);

		if (!BX.Type.isUndefined(BX.Tasks))
		{
			const matches = obj.currentEntity.ENTITY_XML_ID.match(/^TASK_(\d+)$/i);
			if (
				matches
				&& this.resultFieldTaskIdList.includes(parseInt(matches[1]))
			)
			{
				BX.Tasks.ResultManager.showField();
			}
			else
			{
				BX.Tasks.ResultManager.hideField();
			}
		}
	}

	static onLightEditorShow(content, data)
	{
		if (!Type.isPlainObject(data))
		{
			data = {};
		}

		let result = {};

		if (Type.isPlainObject(data.UF))
		{
			result = data.UF;
		}
		else
		{
			if (data.arFiles)
			{
				const value = {};

				data.arFiles.forEach((fileId, index) => {
					const container = document.getElementById(`wdif-doc-${fileId}`);

					const name = container.querySelector('.feed-com-file-name');
					const size = container.querySelector('.feed-con-file-size');

					value[`F${index}`] = {
						FILE_ID: fileId,
						FILE_NAME: (name ? name.innerHTML : 'noname'),
						FILE_SIZE: (size ? size.innerHTML : 'unknown'),
						CONTENT_TYPE: 'notimage/xyz'
					};
				})

				result.UF_SONET_COM_DOC = {
					USER_TYPE_ID: 'file',
					FIELD_NAME: 'UF_SONET_COM_FILE[]',
					VALUE: value,
				};
			}

			if (data.arDocs)
			{
				result.UF_SONET_COM_FILE = {
					USER_TYPE_ID: 'webdav_element',
					FIELD_NAME: 'UF_SONET_COM_DOC[]',
					VALUE: Runtime.clone(data.arDocs),
				};
			}

			if (data.arDFiles)
			{
				result.UF_SONET_COM_FILE = {
					USER_TYPE_ID: 'disk_file',
					FIELD_NAME: 'UF_SONET_COM_DOC[]',
					VALUE: Runtime.clone(data.arDFiles)
				};
			}
		}

		LHEPostForm.reinitData(window.SLEC.editorId, content, result);
	}
}
