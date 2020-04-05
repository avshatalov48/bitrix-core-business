;(function ()
{
	"use strict";

	BX.ready(function ()
	{
		var exportIds = BX.findChild(BX('table_EXPORT_PROFILES'), {class: 'vk_export__profile_id'}, true, true);
		exportIds.forEach(function (element)
		{
			var exportId = element.getAttribute('value');
			window["vkExportSections_" + exportId] = new VkExportSections(exportId);
		});
	});

	var VkExportSections = function (exportId)
	{
		this.exportId = exportId;
		this.sectionId = null;
		this.items = {};

		this.init();
	};

	VkExportSections.prototype = {
		init: function()
		{
			var exportId = this.exportId;

			this.items.vkExportInherit = BX('vk_export_inherit_' + exportId);
			this.items.vkExportEnable = BX('vk_export_enable_' + exportId);
			this.items.vkExportEnableParent = BX('vk_export_enable_parent_' + exportId);
			this.items.vkExportToAlbumCurrent = BX('vk_export_to_album_current_' + exportId);
			this.items.vkExportToAlbum = BX('vk_export_to_album_' + exportId);
			this.items.vkExportToAlbumParent = BX('vk_export_to_album_parent_' + exportId);
			this.items.vkExportToAlbumAlias = BX('vk_export_to_album_alias_' + exportId);
			this.items.vkExportToAlbumAliasParent = BX('vk_export_to_album_alias_parent_' + exportId);
			this.items.vkExportToAlbumAliasContainer = BX('vk_export_to_album_alias_container_' + exportId);
			this.items.vkExportIncludeChilds = BX('vk_export_include_childs_' + exportId);
			this.items.vkExportIncludeChildsParent = BX('vk_export_include_childs_parent_' + exportId);
			this.items.vkExportVkCategory = BX('vk_export_vk_category_' + exportId);
			this.items.vkExportVkCategoryParent = BX('vk_export_vk_category_parent_' + exportId);

			// save current section ID
			this.sectionId = this.items.vkExportToAlbumCurrent.value;

			/* if inherit - hide all, if not - show other */
			BX.bind(this.items.vkExportInherit, 'change', BX.delegate(this.onInheritClick, this));

			/* if disable - hide all, if enable - check visible */
			BX.bind(this.items.vkExportEnable, 'change', BX.delegate(this.onEnableClick, this));

			/* if export to current album - show alias field */
			/* if change album to add - we can adding childs products to this album */
			BX.bind(this.items.vkExportToAlbum, 'change', BX.delegate(this.onToAlbumChange, this));
		},

		// event handlers
		onToAlbumChange: function ()
		{
			var items = this.items;

			// only if change CURRENT section album
			if (!(items.vkExportToAlbum.value == this.sectionId && items.vkExportToAlbum.value > 0))
			{
				BX.hide(items.vkExportToAlbumAliasContainer);
			}
			else
			{
				// if alias not set - use section NAME
				var currAlias = items.vkExportToAlbumAlias.value;
				if(!currAlias)
				{
					var alias = this.items.vkExportToAlbum.options[this.items.vkExportToAlbum.options.selectedIndex];
					alias = alias.text.replace(/^(\. )+/,'');
					items.vkExportToAlbumAlias.value = alias;
					// BX.adjust(items.vkExportIncludeChilds, {
					// 	props: {disabled: (items.vkExportToAlbumAlias.value > 0 ? false : true)}
					// });
				}
				BX.show(items.vkExportToAlbumAliasContainer);
			}

			BX.adjust(items.vkExportIncludeChilds, {
				props: {disabled: (items.vkExportToAlbum.value > 0 ? false : true)}
			});
		},

		onEnableClick: function ()
		{
			this.checkSettingsVisible();
		},

		onInheritClick: function ()
		{
			this.checkSettingsVisible();
		},


		/**
		 * Match visibility of ALL settings for current export
		 * @param items
		 */
		checkSettingsVisible: function ()
		{
			var items = this.items;

			if (items.vkExportInherit.checked)
			{
				BX.adjust(items.vkExportEnable, {props: {disabled: true}});
				this.hideSettings(items);
				this.setParentValues(items);
			}
			else
			{
				BX.adjust(items.vkExportEnable, {props: {disabled: false}});
				if (!items.vkExportEnable.checked)
				{
					this.hideSettings(items);
				}
				else
				{
					BX.adjust(items.vkExportToAlbum, {props: {disabled: false}});
					BX.adjust(items.vkExportVkCategory, {props: {disabled: false}});
					this.onToAlbumChange(items);
				}

			}
		},

		/**
		 * If change INHERIT options - set values from parent settings
		 * @param items
		 */
		setParentValues: function ()
		{
			var items = this.items;

			//checkboxes
			BX.adjust(items.vkExportEnable, {props: {checked: items.vkExportEnableParent.value}});
			BX.adjust(items.vkExportIncludeChilds, {props: {checked: items.vkExportIncludeChildsParent.value}});
			//values fields
			items.vkExportToAlbum.value = items.vkExportToAlbumParent.value;
			items.vkExportToAlbumAlias.value = items.vkExportToAlbumAliasParent.value;
			items.vkExportVkCategory.value = items.vkExportVkCategoryParent.value;
		},

		/**
		 * Hide or disabled options
		 * @param items
		 */
		hideSettings: function ()
		{
			var items = this.items;

			BX.adjust(items.vkExportToAlbum, {props: {disabled: true}});
			BX.hide(items.vkExportToAlbumAliasContainer);
			BX.adjust(items.vkExportIncludeChilds, {props: {disabled: true}});
			BX.adjust(items.vkExportVkCategory, {props: {disabled: true}});
		},
	};
})();