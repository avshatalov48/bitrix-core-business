import { Type, Tag, Loc, Runtime, Dom } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

export class CommentForm
{
	static onAfterShow(obj, text, data)
	{
		if (!Type.isPlainObject(data))
		{
			data = {};
		}

		const [ entityId, commentId ]  = obj.entitiesCorrespondence[obj.id.join('-')];

		document.getElementById(`feed_comments_block_${entityId}`).style.display = 'block';

		EventEmitter.emit('OnBeforeSocialnetworkCommentShowedUp', new BaseEvent({
			compatData: [ 'socialnetwork' ],
		}));

		obj.form.action = obj.url.replace(/\#eId\#/, entityId).replace(/\#id\#/, commentId);

		const postData = {
			ENTITY_XML_ID: obj.id[0],
			ENTITY_TYPE: obj.entitiesId[obj.id[0]][0],
			ENTITY_ID: obj.entitiesId[obj.id[0]][1],
			parentId: obj.id[1],
			comment_post_id: obj.entitiesId[obj.id[0]][1],
			edit_id: obj.id[1],
			act: (obj.id[1] > 0 ? 'edit' : 'add'),
			logId: obj.entitiesId[obj.id[0]][2]
		};

		Object.entries(postData).forEach(([key, value]) => {
			if (!obj.form[key])
			{
				obj.form.appendChild(Tag.render`<input type="hidden" name="${key}">`);
			}
			obj.form[key].value = value;
		});

		this.onLightEditorShow(text, data);
	}

	static onLightEditorShow(content, data)
	{
		if (!Type.isPlainObject(data))
		{
			data = {};
		}

		const result = {};

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

		LHEPostForm.reinitData(window.SLEC.editorId, content, result);
	}

	static onSubmit(obj, post_data)
	{
		post_data.r = Math.floor(Math.random() * 1000);
		post_data.sessid = Loc.getMessage('bitrix_sessid');
		post_data.log_id = obj.entitiesCorrespondence[obj.id.join('-')][0];
		post_data.p_smile = Loc.getMessage('sonetLPathToSmile');
		post_data.p_ubp = Loc.getMessage('sonetLPathToUserBlogPost');
		post_data.p_gbp = Loc.getMessage('sonetLPathToGroupBlogPost');
		post_data.p_umbp = Loc.getMessage('sonetLPathToUserMicroblogPost');
		post_data.p_gmbp = Loc.getMessage('sonetLPathToGroupMicroblogPost');
		post_data.p_user = Loc.getMessage('sonetLPathToUser');
		post_data.p_le = Loc.getMessage('sonetLEPath');
		post_data.f_id = Loc.getMessage('sonetLForumID');
		post_data.bapc = Loc.getMessage('sonetLBlogAllowPostCode');
		post_data.site = Loc.getMessage('SITE_ID');
		post_data.lang = Loc.getMessage('LANGUAGE_ID');
		post_data.nt = Loc.getMessage('sonetLNameTemplate');
		post_data.sl = Loc.getMessage('sonetLShowLogin');
		post_data.as = Loc.getMessage('sonetLAvatarSizeComment');
		post_data.dtf = Loc.getMessage('sonetLDateTimeFormat');
		post_data.message = post_data.REVIEW_TEXT;
		post_data.action = 'add_comment';
		post_data.RATING_TYPE = Loc.getMessage('sonetRatingType');
		post_data.pull = 'Y';
		post_data.crm = Loc.getMessage('sonetLIsCRM');

		obj.form['bx-action'] = obj.form.action;
		obj.form.action = Loc.getMessage('sonetLESetPath');
	}

	static onResponse(obj, data)
	{
		obj.form.action = obj.form['bx-action'];

		let returnData = {
			errorMessage: data,
		};

		if (!(!!data && Type.isPlainObject(data)))
		{

		}
		else if (data[0] === '*')
		{
			returnData = {errorMessage : Loc.getMessage('SONET_EXT_ERROR_SESSION')};
		}
		else if (data.status === 'error')
		{
			returnData.errorMessage = data.message;
		}
		else
		{
			if (!(data["commentID"] > 0) || !!data["strMessage"])
			{
				returnData['errorMessage'] = data["strMessage"];
			}
			else if (data['return_data'])
			{
				returnData = data['return_data'];
			}
			else
			{
				const formattedCommentFields = data.arCommentFormatted;
				const commentFields = data.arComment;
				const ratingNode = (!!window.__logBuildRating ? window.__logBuildRating(data['arComment'], data['arCommentFormatted']) : null);
				const thisId = (!!commentFields.SOURCE_ID ? commentFields.SOURCE_ID : commentFields.ID);

				const res = {
					ID: thisId, // integer
					ENTITY_XML_ID: obj.id[0], // string
					FULL_ID: [ obj.id[0], thisId ],
					NEW: 'N', //"Y" | "N"
					APPROVED: 'Y', //"Y" | "N"
					POST_TIMESTAMP: data.timestamp - Loc.getMessage('USER_TZ_OFFSET'),
					POST_TIME: formattedCommentFields.LOG_TIME_FORMAT,
					POST_DATE: formattedCommentFields.LOG_TIME_FORMAT,
					'~POST_MESSAGE_TEXT': formattedCommentFields.MESSAGE,
					POST_MESSAGE_TEXT: formattedCommentFields.MESSAGE_FORMAT,
					PANELS: {
						MODERATE: false,
					},
					URL: {
						LINK: (
							Type.isStringFilled(commentFields.URL)
								? commentFields.URL
								: `${Loc.getMessage('sonetLEPath').replace('#log_id#', commentFields.LOG_ID)}?commentId=${commentFields.ID}#com${thisId}`
						),
					},
					AUTHOR: {
						ID: formattedCommentFields.USER_ID,
						NAME: formattedCommentFields.CREATED_BY.FORMATTED,
						URL: formattedCommentFields.CREATED_BY.URL,
						AVATAR: formattedCommentFields.AVATAR_SRC,
					},
					BEFORE_ACTIONS: (!!ratingNode ? ratingNode : ''),
					AFTER: formattedCommentFields.UF,
				};

				if (
					Type.isStringFilled(data.hasEditCallback)
					&& data.hasEditCallback === 'Y'
				)
				{
					res.PANELS.EDIT = 'Y';
					re.URL.EDIT = `__logEditComment('${obj.id[0]}', '${commentFields.ID}', '${commentFields.LOG_ID}');`;
				}

				if (
					Type.isStringFilled(data.hasDeleteCallback)
					&& data.hasDeleteCallback === 'Y'
				)
				{
					res.PANELS.DELETE = 'Y';
					res.URL.DELETE = `${Loc.getMessage('sonetLESetPath')}?lang=${Loc.getMessage('LANGUAGE_ID')}&action=delete_comment&delete_comment_id=${commentFields.ID}&post_id=${commentFields.LOG_ID}&site=${Loc.getMessage('SITE_ID')}`;
				}

				returnData = {
					errorMessage: '',
					okMessage: '',
					status: true,
					message: '',
					messageCode: formattedCommentFields["MESSAGE"],
					messageId: [obj.id[0], thisId],
					'~message': '',
					messageFields: res,
				};
			}

			const entityId = obj.entitiesCorrespondence[obj.id.join('-')][0];
			const followNode = document.getElementById(`log_entry_follow_${entityId}`);
			const currentFollowValue = (!!followNode ? (followNode.getAttribute('data-follow') === 'Y' ? 'Y' : 'N') : false);

			if (currentFollowValue === 'N')
			{
				const followTitleNode = followNode.querySelector('a');
				if (followTitleNode)
				{
					followTitleNode.innerHTML = Loc.getMessage('sonetLFollowY');
				}
				followNode.setAttribute('data-follow', 'Y');
			}

			const commentsCounterNode = document.getElementById(`feed-comments-all-cnt-${entityId}`);
			const counterValue = (!!commentsCounterNode ? (commentsCounterNode.innerHTML.length > 0 ? parseInt(commentsCounterNode.innerHTML) : 0) : false);

			if (counterValue !== false)
			{
				commentsCounterNode.innerHTML = (counterValue + 1);
			}
		}

		obj.OnUCFormResponseData = returnData;
	}

	static onInit(obj)
	{
		Dom.remove(document.getElementById(`micro${obj.editorName}`));
	}
}
