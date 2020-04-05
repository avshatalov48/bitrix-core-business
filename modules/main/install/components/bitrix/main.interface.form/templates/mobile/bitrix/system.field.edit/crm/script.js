;(function(window) {
	var repo = {};
	BX.namespace("BX.CRM");
	if (BX.CRM.UFMobile)
		return;
	BX.CRM.UFMobile = (function () {
		var UF = function (params) {
			this.dialogName = "CRMDialog";
			var id = params['id'];

			this.controlName = params['controlName'];
			this.id = BX.util.getRandomString();
			this.container = BX(id + 'Container');
			this.dropNode = BX.delegate(this.dropNode, this);

			var types = BX.findChildren(this.container, {tagName : "DL"}, false),
				type,
				entity,
				t,
				dd,
				i, j,
				entities = {};
			this.types = [];
			for (i = 0; i < types.length; i++)
			{
				type = types[i].getAttribute("data-bx-type").toLowerCase();
				entity = this.entities[type];
				t = BX.findChild(types[i], {tagName : "DT"});
				if (entity)
				{
					entity = {
						type : type,
						prefix : entity.prefix,
						viewUrl : entity.viewUrl,
						selectUrl : entity.selectUrl.replace(/#id#/gi, this.id),
						title : (t ? t.innerHTML : type),
						container : types[i],
						value : []
					};
					entities[type] = entity;
					dd = BX.findChildren(types[i], {tagName : "DD"}, false);
					for (j = 0; j < dd.length; j++) {
						this.bindNode(dd[j]);
						entity.value.push(entity.prefix + dd[j].getAttribute("id"));
					}
				}
			}
			this.entities = entities;
			if (BX(id + 'Add'))
				BX.bind(BX(id + 'Add'), "click", BX.proxy(this.showAdd, this));
			this.eventName = "onCRMEntityWasChosen" + this.id;
			BXMobileApp.addCustomEvent(window, this.eventName, BX.proxy(this.buildNode, this));
			return this;
		};

		UF.prototype = {
			entities : {
				lead : {
					prefix : "L_",
					viewUrl : "/mobile/crm/lead/?page=view&lead_id=#id#",
					selectUrl : "/mobile/crm/search.php?entity=lead"
				},
				contact : {
					prefix : "C_",
					viewUrl : "/mobile/crm/contact/?page=view&contact_id=#id#",
					selectUrl : "/mobile/crm/search.php?entity=contact"
				},
				company : {
					prefix : "CO_",
					viewUrl : "/mobile/crm/company/?page=view&company_id=#id#",
					selectUrl : "/mobile/crm/search.php?entity=company"
				},
				deal : {
					prefix : "D_",
					viewUrl : "/mobile/crm/deal/?page=view&deal_id=#id#",
					selectUrl : "/mobile/crm/search.php?entity=deal"
				},
				quote : {
					prefix : "Q_",
					viewUrl : "/mobile/crm/quote/?page=view&quote_id=#id#",
					selectUrl : "/mobile/crm/search.php?entity=quote"
				}
			},
			showAdd : function(e) {
				BX.PreventDefault(e);
				this.showSelector();
				return false;
			},
			showSelector : function() {
				var buttons = [], i;
				for (i in this.entities)
				{
					if (this.entities.hasOwnProperty(i))
					{
						buttons.push({
							title : this.entities[i]["title"],
							callback : (function(url){
								return function() {
									BXMobileApp.PageManager.loadPageModal({
										url:url,
										bx24ModernStyle:true,
										cache : !window.app.enableInVersion(15)
									}); }
							})(this.entities[i]["selectUrl"] + "&event=" + this.eventName)
						})
					}
				}
				if (buttons.length > 0)
					(new window.BXMobileApp.UI.ActionSheet( { buttons: buttons }, "textPanelSheet" )).show();
			},
			bindNode : function(node) {
				if (BX(node) && node.parentNode)
				{
					var type = node.parentNode.getAttribute("data-bx-type").toLowerCase(),
						del = BX.findChild(node, {tagName : "DEL"});
					if (this.entities[type])
					{
						BX.bind(node, "click", (function(url){
							return function() {
								window.BXMobileApp.PageManager.loadPageUnique({url : url, bx24ModernStyle : true});
							};
						})(this.entities[type]["viewUrl"].replace(/#id#/gi, node.getAttribute("id"))));
						if (del)
							BX.bind(del, "click", this.dropNode);
					}
				}
			},
			buildNode : function(entity) {
				var type = entity["entityType"];
				if (this.entities[type])
				{
					var id = this.entities[type]["prefix"] + entity["id"], title = entity["name"];
					if (!BX.util.in_array(id, this.entities[type]["value"]))
					{
						var node = BX.create("DD", {attrs : { id : id }, html : [
							title,
							'<del></del>',
							'<input type="hidden" name="', this.controlName,'" value="' + id + '" />'
						].join("")});
						this.entities[type]["container"].appendChild(node);
						this.entities[type]["value"].push(id);
						this.bindNode(node);
						BX.onCustomEvent(this, "onChange", [this, node, "add"]);
					}
				}
			},
			dropNode : function(e) {
				var delNode = (BX.proxy_context||e.target),
					node = delNode.parentNode,
					entity = node.parentNode.getAttribute("data-bx-type").toLocaleLowerCase(),
					i;
				if (this.entities[entity])
				{
					entity = this.entities[entity];
					i = BX.util.array_search(node.getAttribute("id"), entity.value);
					if (i >= 0)
					{
						entity.value.splice(i, 1);
					}
				}
				node.parentNode.removeChild(node);
				BX.onCustomEvent(this, "onChange", [this, node, "delete"]);
				return BX.PreventDefault(e);
			}
		};
		return UF;
	})();
	BX.CRM.UFMobile.add = function(params) {
		repo[params['id']] = new BX.CRM.UFMobile(params);
	};
	BX.Disk.UFMobile.getByName = function(name) {
		for (var ii in repo)
		{
			if (repo.hasOwnProperty(ii))
			{
				if (repo[ii]["controlName"] == name || repo[ii]["controlName"] == name + '[]')
				{
					return repo[ii];
				}
			}
		}
		return null;
	};
})(window);
