BX.namespace("BX.Lists");
BX.Lists.ListsEditClass = (function ()
{
	var ListsEditClass = function (parameters)
	{
		this.randomString = parameters.randomString;
		this.iblockTypeId = parameters.iblockTypeId;
		this.iblockId = parameters.iblockId;
		this.socnetGroupId = parameters.socnetGroupId;
		this.jsClass = 'ListsEditClass_'+parameters.randomString;
		this.listsUrl = parameters.listsUrl || '';
		this.listTemplateEditUrl = parameters.listTemplateEditUrl;
		this.listElementUrl = parameters.listElementUrl;

		this.init();
	};

	ListsEditClass.prototype.init = function ()
	{
		this.ajaxUrl = '/bitrix/components/bitrix/lists.list.edit/ajax.php';
	};

	ListsEditClass.prototype.copyIblock = function()
	{
		BX.UI.Dialogs.MessageBox.confirm(
			BX.Loc.getMessage("CT_BLLE_COPY_POPUP_CONTENT"),
			BX.Loc.getMessage('CT_BLLE_COPY_POPUP_TITLE'),
			() =>
			{
				const actionPromise = BX.ajax.runAction("lists.controller.iblock.copy", {
					data: {
						iblock_type_id: this.iblockTypeId,
						iblock_id: this.iblockId,
						socnet_group_id: this.socnetGroupId,
						list_element_url: this.listElementUrl
					}
				});

				actionPromise.then(
					response => {
						this.listTemplateEditUrl = this.listTemplateEditUrl
							.replace("#list_id#", response.data)
							.replace("#group_id#", this.socnetGroupId);
						BX.UI.Notification.Center.notify({
							content: BX.Loc.getMessage(
								"CT_BLLE_COPY_POPUP_COPIED_SUCCESS",
								{"#URL#": BX.util.htmlspecialchars(this.listTemplateEditUrl)}
							),
							position: "top-right",
							closeButton: false
						});
					},
					response => {
						BX.UI.Notification.Center.notify({
							content: response.errors.pop().message,
							position: "top-right",
							closeButton: false
						});
					}
				);

				return actionPromise;
			},
			BX.Loc.getMessage("CT_BLLE_COPY_POPUP_ACCEPT_BUTTON"),
		);
	};

	ListsEditClass.prototype.deleteIblock = function(form_id, message)
	{
		var _form = BX(form_id);
		var _flag = BX('action');
		if(_form && _flag)
		{
			BX.UI.Dialogs.MessageBox.confirm(
				message,
				BX.Loc.getMessage('CT_BLLE_DELETE_POPUP_TITLE'),
				() =>
				{
					_flag.value = 'delete';
					_form.submit();
					return true;
				},
				BX.Loc.getMessage("CT_BLLE_DELETE_POPUP_ACCEPT_BUTTON"),
			);
		}
	};

	ListsEditClass.prototype.migrateList = function(formId, message)
	{
		var _form = BX(formId);
		var _flag = BX('action');
		if(_form && _flag)
		{
			BX.UI.Dialogs.MessageBox.confirm(
				message,
				BX.Loc.getMessage('CT_BLLE_MIGRATE_POPUP_TITLE'),
				() =>
				{
					_flag.value = 'migrate';
					_form.submit();

					return true;
				},
				BX.Loc.getMessage("CT_BLLE_MIGRATE_POPUP_ACCEPT_BUTTON"),
			);
		}
	};

	return ListsEditClass;
})();
