;BX.namespace('BX.Bizproc');
if (typeof BX.Bizproc.doInlineTask === 'undefined')
{
	BX.Bizproc.doInlineTask = function (parameters, callback, scope)
	{
		if (scope)
		{
			if (scope.__waiting)
				return false;
			scope.__waiting = true;
			if (BX.hasClass(scope, 'bp-button'))
			{
				BX.addClass(scope, 'bp-button-wait');
			}
			else if (BX.hasClass(scope, 'ui-btn'))
			{
				BX.addClass(scope, 'ui-btn-wait');
			}
		}
		if (!parameters || !parameters['TASK_ID'])
			return false;
		parameters['sessid'] = BX.bitrix_sessid();
		BX.ajax({
			method:'POST',
			dataType: 'json',
			url:'/bitrix/tools/bizproc_do_task_ajax.php',
			data: parameters,
			onsuccess: function(response)
			{
				if (response.ERROR)
				{
					window.alert(response.ERROR);
				}

				if (scope)
				{
					scope.__waiting = false;
					BX.removeClass(scope, ['bp-button-wait', 'ui-btn-wait']);
				}
				if (response.SUCCESS && callback)
				{
					callback(arguments);
				}
			}
		});

		return false;
	};
	BX.Bizproc.taskPopupInstance = null;
	BX.Bizproc.taskPopupCallback = null;
	BX.Bizproc.showTaskPopup = function (taskId, callback, userId, scope, useIframe)
	{
		if (scope)
		{
			if (scope.__waiting)
				return false;
			scope.__waiting = true;
			if (BX.hasClass(scope, 'bp-button'))
			{
				BX.addClass(scope, 'bp-button-wait');
			}
			else if (BX.hasClass(scope, 'ui-btn'))
			{
				BX.addClass(scope, 'ui-btn-wait');
			}
		}
		BX.Bizproc.taskPopupInstance = null;
		BX.Bizproc.taskPopupCallback = null;
		BX.ajax({
			method: 'GET',
			dataType: 'html',
			url: '/bitrix/components/bitrix/bizproc.task/popup.php?site_id='+BX.message('SITE_ID')+'&TASK_ID='
				+ taskId + (userId ? '&USER_ID=' + userId : '')
				+ (useIframe ? '&IFRAME=Y' : ''),
			onsuccess: function (HTML)
			{
				if (scope)
				{
					scope.__waiting = false;
					BX.removeClass(scope, ['bp-button-wait', 'ui-btn-wait']);
				}
				var wrapper = BX.create('div', {
					style: {width: '100%'}
				});
				wrapper.innerHTML = HTML;

				var title = '', titleNode = BX.findChild(wrapper, {className: 'bp-popup-title'}, true);
				if (titleNode)
				{
					title = titleNode.textContent;
					BX.remove(titleNode);
				}

				BX.Bizproc.taskPopupInstance = new BX.PopupWindow("bp-task-popup-" + taskId + Math.round(Math.random() * 100000), null, {
					content: wrapper,
					closeIcon: true,
					titleBar: title,
					contentColor: 'white',
					contentNoPaddings : true,
					zIndex: -100,
					offsetLeft: 0,
					offsetTop: 0,
					width: 980,
					closeByEsc: true,
					draggable: {restrict: false},
					overlay: {backgroundColor: 'black', opacity: 30},
					events: {
						onPopupClose: function (popup)
						{
							popup.destroy();
							if (BX.Bizproc.delegationPopup)
								BX.Bizproc.delegationPopup.destroy();
							BX.Bizproc.delegationPopup = null;
						}
					}

				});
				// BX.Bizproc.taskPopupInstance.show();
				BX.Bizproc.taskPopupCallback = callback;

				BX.load(['/bitrix/components/bitrix/bizproc.task/templates/.default/style.css'], function()
				{
					BX.Bizproc.taskPopupInstance.show();
				});
			}
		});

		return false;
	};

	BX.Bizproc.delegationPopup = null;
	BX.Bizproc.delegationSelected = null;
	BX.Bizproc.showDelegationPopup = function(scope, taskId, userId)
	{
		if (BX.Bizproc.delegationPopup)
		{
			BX.Bizproc.delegationPopup.show();

			return false;
		}

		if (BX.Reflection.getClass('BX.UI.EntitySelector.Dialog'))
		{
			BX.Bizproc.delegationSelected = null;
			BX.Bizproc.delegationPopup = new BX.UI.EntitySelector.Dialog({
				targetNode: scope,
				id: "bp-task-delegation-" + Math.round(Math.random() * 100000),
				context: 'bp-task-delegation',
				entities: [
					{
						id: 'user',
						options: {
							intranetUsersOnly: true,
							emailUsers: false,
							inviteEmployeeLink: false,
							inviteGuestLink: false,
						},
					},
					{
						id: 'department',
						options: {
							selectMode: 'usersOnly',
						},
					}
				],
				popupOptions: {
					bindOptions: { forceBindPosition: true },
				},
				enableSearch: true,
				events: {
					'Item:onSelect': (event) => {
						const item = event.getData().item;
						const dialog = event.getTarget();

						BX.Bizproc.delegationOnSelect(item);
						BX.Bizproc.delegateTask(taskId, userId, BX.Bizproc.delegationSelected);

						dialog.deselectAll();
					},
				},
				hideOnSelect: true,
				offsetTop: 3,
				clearUnavailableItems: true,
				multiple: false,
			});
			BX.Bizproc.delegationPopup.show();

			return false;
		}

		BX.ajax({
			method: 'GET',
			dataType: 'html',
			url: '/bitrix/components/bitrix/bizproc.task/delegate.php?SITE_ID=' + BX.message('SITE_ID')
				+'&sessid='+BX.bitrix_sessid(),
			onsuccess: function (HTML)
			{
				BX.Bizproc.delegationSelected = null;
				BX.Bizproc.delegationPopup = new BX.PopupWindow("bp-task-delegation-" + Math.round(Math.random() * 100000), scope, {
					content: HTML,
					lightShadow : true,
					//offsetLeft: -51,
					offsetTop: 3,
					zIndex: 0,
					autoHide: true,
					closeByEsc: true,
					bindOptions: { forceBindPosition: true },
					angle: {position:'top', offset: 20},
					buttons: [
						new BX.PopupWindowButton({
							text      :  BX.message('BPAT_DELEGATE_SELECT'),
							className : 'popup-window-button-accept',
							events    : {
								click : function(e)
								{
									BX.Bizproc.delegateTask(taskId, userId, BX.Bizproc.delegationSelected);
									BX.Bizproc.delegationPopup.close();
								}
							}
						}),

						new BX.PopupWindowButtonLink({
							text      :  BX.message('BPAT_DELEGATE_CANCEL'),
							className : 'popup-window-button-link-cancel',
							events    : {
								click : function(e)
								{
									if (!e)
										e = window.event;

									BX.Bizproc.delegationPopup.close();

									if (e)
										BX.PreventDefault(e);
								}
							}
						})
					]
				});
				BX.Bizproc.delegationPopup.show();
			}
		});

		return false;
	};

	BX.Bizproc.delegationOnSelect = function(user)
	{
		BX.Bizproc.delegationSelected = user.id;
	};

	BX.Bizproc.delegateTask = function(taskId, fromUserId, toUserId)
	{
		var parameters = {
			SITE_ID: BX.message('SITE_ID'),
			action: 'delegate',
			sessid: BX.bitrix_sessid(),
			task_id: taskId,
			from_user_id: fromUserId,
			to_user_id: toUserId
		};

		BX.ajax({
			action: 'delegate',
			method:'POST',
			dataType: 'json',
			url:'/bitrix/components/bitrix/bizproc.task/delegate.php',
			data: parameters,
			onsuccess: function(response)
			{
				window.alert(response.message);
				if (response.success)
				{
					if (!!BX.Bizproc.taskPopupInstance)
						BX.Bizproc.taskPopupInstance.close();
					if (BX.Bizproc.taskPopupCallback)
						BX.Bizproc.taskPopupCallback();
					else
						window.location.reload()
				}
			}
		});
	};

	BX.Bizproc.showWorkflowInfoPopup = function (workflowId)
	{
		BX.ajax({
			method: 'GET',
			dataType: 'html',
			url: '/bitrix/components/bitrix/bizproc.workflow.info/popup.php?site_id='+BX.message('SITE_ID')+'&WORKFLOW_ID=' + workflowId,
			onsuccess: function (HTML)
			{
				BX.load(['/bitrix/components/bitrix/bizproc.workflow.info/templates/.default/style.css'], function()
				{
					var wrapper = BX.create('div', {
						style: {width: '800px'}
					});
					wrapper.innerHTML = HTML;

					var title = '', titleNode = BX.findChild(wrapper, {className: 'bp-popup-title'}, true);
					if (titleNode)
					{
						title = titleNode.textContent;
						BX.remove(titleNode);
					}

					var popup = new BX.PopupWindow("bp-wfi-popup-" + workflowId + Math.round(Math.random() * 100000), null, {
						content: wrapper,
						closeIcon: true,
						titleBar: title,
						contentColor: 'white',
						contentNoPaddings : true,
						zIndex: -100,
						offsetLeft: 0,
						offsetTop: 0,
						closeByEsc: true,
						draggable: {restrict: false},
						overlay: {backgroundColor: 'black', opacity: 30},
						events: {
							onPopupClose: function (popup)
							{
								popup.destroy();
							}
						}

					});
					popup.show();
				});
			}
		});

		return false;
	};

	BX.Bizproc.showWorkflowLogPopup = function (workflowId, params)
	{
		if (!BX.type.isPlainObject(params))
		{
			params = {};
		}

		BX.ajax({
			method: 'GET',
			dataType: 'html',
			url: '/bitrix/components/bitrix/bizproc.log/popup.php?site_id='+BX.message('SITE_ID')+'&WORKFLOW_ID=' + workflowId,
			onsuccess: function (HTML)
			{
				var wrapper = BX.create('div', {
					style: {width: '800px'}
				});
				wrapper.innerHTML = HTML;

				var popup = new BX.PopupWindow("bp-wfi-popup-" + workflowId + Math.round(Math.random() * 100000), null, {
					content: wrapper,
					closeIcon: true,
					titleBar: params.title || '',
					contentColor: 'white',
					contentNoPaddings : true,
					zIndex: -100,
					offsetLeft: 0,
					offsetTop: 0,
					closeByEsc: true,
					draggable: {restrict: false},
					overlay: {backgroundColor: 'black', opacity: 30},
					events: {
						onPopupClose: function (popup)
						{
							popup.destroy();
						}
					}

				});
				popup.show();
			}
		});

		return false;
	};

	BX.Bizproc.postTaskForm = function (form, e)
	{
		if (form.BPRUNNING)
		{
			return;
		}
		BX.PreventDefault(e);

		form.action = '/bitrix/tools/bizproc_do_task_ajax.php';
		form.BPRUNNING = true;

		var actionName, actionValue, btn = document.activeElement;
		if ((!btn || !btn.type) && e.explicitOriginalTarget)
		{
			btn = e.explicitOriginalTarget;
		}

		if (!!btn && btn.type && btn.type.toLowerCase() == 'submit' && !!btn.name && !!btn.value)
		{
			actionName = btn.name;
			actionValue = btn.value;
		}

		if (!form.target)
		{
			if (null == form.BXFormTarget)
			{
				var frame_name = 'formTarget_' + Math.random();
				form.BXFormTarget = document.body.appendChild(BX.create('IFRAME', {
					props: {
						name: frame_name,
						id: frame_name,
						src: 'javascript:void(0)'
					},
					style: {
						display: 'none'
					}
				}));
			}

			form.target = form.BXFormTarget.name;
		}

		var scope = null;
		if (actionName)
		{
			scope = BX.findChild(form, {property: {type: 'submit', name: actionName}}, true);
		}
		if (scope)
		{
			if (BX.hasClass(scope, 'bp-button'))
			{
				BX.addClass(scope, 'bp-button-wait');
			}
			else if (BX.hasClass(scope, 'ui-btn'))
			{
				BX.addClass(scope, 'ui-btn-wait');
			}
		}

		form.BXFormCallback = function (response)
		{
			form.BPRUNNING = false;
			if (scope)
			{
				BX.removeClass(scope, ['bp-button-wait', 'ui-btn-wait']);
			}
			response = BX.parseJSON(response);
			if (response && response.ERROR)
				window.alert(response.ERROR);
			else
			{
				if (!!BX.Bizproc.taskPopupInstance)
					BX.Bizproc.taskPopupInstance.close();
				if (!!BX.Bizproc.taskPopupCallback)
					BX.Bizproc.taskPopupCallback();
			}
		};
		BX.bind(form.BXFormTarget, 'load', BX.proxy(BX.ajax._submit_callback, form));
		BX.submit(form, actionName, actionValue);
	};
}
if (typeof BX.Bizproc.WorkflowFaces === 'undefined')
{
	BX.Bizproc.WorkflowFaces = {};

	BX.Bizproc.WorkflowFaces.showFaces = function(tasks, scope, simple, taskBased)
	{
		if (typeof scope.__popup === 'undefined')
		{
			scope.__popup = new BX.PopupWindow('bp-wf-faces-'+Math.round(Math.random() * 100000), scope, {
				lightShadow : true,
				offsetLeft: -51,
				offsetTop: 3,
				zIndex: 0,
				autoHide: true,
				closeByEsc: true,
				bindOptions: {position: "bottom"},
				angle: {position:'top', offset: 78},
				content : BX.Bizproc.WorkflowFaces.createMenu(tasks, simple, taskBased)
			});
		}
		if (scope.__popup.isShown())
			scope.__popup.close();
		else
			scope.__popup.show();
		return false;
	};

	BX.Bizproc.WorkflowFaces.createMenu = function(tasks, simple, taskBased)
	{
		var i, k, s = tasks.length, l;
		var	tasksContent = [];

		const escapeImg = (path) => {
			return BX.Text.encode(encodeURI(path));
		};

		for (i = 0;i < s; ++i)
		{
			var cls, task = tasks[i],
				uContent = [];

			for (k = 0, l = task.USERS.length; k < l; ++k)
			{
				cls = 'bp-popup-parallel-avatar-ready';
				if (task.USERS[k].STATUS == '0')
					cls = '';
				else if (task.USERS[k].STATUS == '2' || task.USERS[k].STATUS == '4')
					cls = 'bp-popup-parallel-avatar-cancel';

				var tpl = [
					'<a>',
					'<span class="bp-popup-parallel-avatar '+cls+'"><span class="ui-icon ui-icon-common-user">'+(task.USERS[k].PHOTO_SRC? '<i style="background-image: url(\''+escapeImg(task.USERS[k].PHOTO_SRC)+'\')" alt=""></i>':'<i></i>')+'</span></span>',
					'<span class="bp-popup-parallel-name" title="'+task.USERS[k].FULL_NAME+'">'+task.USERS[k].FULL_NAME+'</span>',
					'</a>'
				];
				uContent.push(tpl.join(''));
			}
			var usersMenu = uContent.join('');
			if (s == 1 && !taskBased)
				tasksContent.push(usersMenu);
			else
			{
				cls = 'bp-popup-parallel-avatar-ready';
				if (task.USERS[0].STATUS == '0')
					cls = '';
				else if (task.USERS[0].STATUS == '2' || task.USERS[0].STATUS == '4')
					cls = 'bp-popup-parallel-avatar-cancel';

				var taskHead = [
					'<a class="'+(uContent.length > 1 || simple ? 'bp-popup-parallel-parent' : '')+'">',
					!simple? '<span class="bp-popup-parallel-avatar '+cls+'"><span class="ui-icon ui-icon-common-user">'+(task.USERS[0].PHOTO_SRC? '<i style="background-image:url(\''+escapeImg(task.USERS[0].PHOTO_SRC)+'\')" alt=""></i>':'<i></i>')+'</span></span>' : '',
					'<span class="bp-popup-parallel-name" title="'+task.NAME+'">'+(!simple? task.USERS[0].FULL_NAME : task.NAME)+'</span>',
					'</a>'
				];

				tasksContent.push('<div class="bp-popup-parallel-sub">'
					+taskHead.join('')
					+(uContent.length > 1 || simple ? '<div class="bp-popup-parallel">'+usersMenu+'</div></div>' : ''));
			}
		}
		return '<div class="bp-popup-parallel">'+tasksContent.join('')+'</div>';
	}
}