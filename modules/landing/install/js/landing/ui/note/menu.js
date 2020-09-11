;(function() {
	'use strict';

	BX.namespace('BX.Landing.UI.Note');

	var noteCreatePath = '/kb/notes/?create=Y';
	var noteListPath = '/kb/notes/';
	var entityId = null;
	var entityType = null;

	/**
	 * Creates page in new created site.
	 */
	BX.addCustomEvent('Landing:onDemoCreateStart',
		function()
		{
			top.BX.SidePanel.Instance.closeAll();
			top.BX.onCustomEvent('Landing:onNoteKnowledgeSelect', [0, 'knowledge']);
		}
	);

	/**
	 * Creates page in selected knowledge.
	 * @param {integer} id Selected knowledge id.
	 * @param {string} scope Selected knowledge scope code.
	 */
	BX.addCustomEvent('Landing:onNoteKnowledgeSelect',
		function(id, scope)
		{
			BX.ajax({
				url: '/bitrix/services/main/ajax.php?action=landing.api.note.createNote',
				method: 'POST',
				dataType: 'json',
				data: {
					kbId: id,
					sourceType: entityType,
					sourceId: entityId,
					scope: scope,
					sessid: BX.message('bitrix_sessid')
				},
				onsuccess: function(result)
				{
					if (result.status === 'success')
					{
						BX.UI.Notification.Center.notify({
							content: BX.message('LANDING_JS_NOTE_WIKI_CREATED_MESS')
								.replace('#LINK#', result.data['PUBLIC_URL'])
						});
					}
					else
					{
						alert(result.errors[0]['message']);
					}
				}
			});
		}
	);

	BX.Landing.UI.Note.Menu = function() {};

	/**
	 * Returns menu item for external interface.
	 * @param {string} type Entity type.
	 * @param {integer} id Entity id.
	 */
	BX.Landing.UI.Note.Menu.getMenuItem = function(type, id)
	{
		entityId = id;
		entityType = type;

		return null;

		return {
			text: BX.message('LANDING_JS_NOTE_WIKI_CREATE'),
			onclick: function(e, menuItem)
			{

				var menuItemNode = menuItem.getLayout().item;
				var loader = new BX.Loader({
					target: menuItemNode,
					size: 30
				});

				menuItem.closeSubMenu();
				loader.show();
				menuItemNode.classList.add('menu-popup-item-disabled');

				BX.ajax({
					url: '/bitrix/services/main/ajax.php?action=landing.api.note.getTargets',
					method: 'POST',
					dataType: 'json',
					onsuccess: function(result)
					{

						menuItemNode.classList.remove('menu-popup-item-disabled');
						loader.hide();

						if (result.data.list)
						{
							// show wiki list in sub menu + 2 extra menu items
							if (result.data.list.length > 0)
							{
								var items = [];
								result.data.list.map(function(item)
								{
									items.push({
										text: item['TITLE'],
										onclick: function(item)
										{
											menuItem.menuWindow.close();
											top.BX.onCustomEvent(
												'Landing:onNoteKnowledgeSelect',
												[item['ID'], item['TYPE']]
											);
										}.bind(this, item)
									});
								});
								items.push({
									delimiter: true
								});
								items.push({
									text: BX.message('LANDING_JS_NOTE_WIKI_CREATE_SELECT'),
									onclick: function()
									{
										menuItem.menuWindow.close();
										BX.SidePanel.Instance.open(noteListPath, {
											allowChangeHistory: false,
											cacheable: false
										});
									}
								});
								if (result.data.canCreateNew)
								{
									items.push({
										text: BX.message('LANDING_JS_NOTE_WIKI_CREATE_NEW'),
										onclick: function()
										{
											menuItem.menuWindow.close();
											BX.SidePanel.Instance.open(noteCreatePath, {
												allowChangeHistory: false,
												cacheable: false
											});
										}
									});
								}
								menuItem.addSubMenu(items);
								menuItem.showSubMenu();
							}
							// show wiki list in slider
							else
							{
								menuItem.menuWindow.close();
								BX.SidePanel.Instance.open(noteListPath, {
									allowChangeHistory: false,
									cacheable: false
								});
							}
						}
					}
				});
			}
		};
	};
})();