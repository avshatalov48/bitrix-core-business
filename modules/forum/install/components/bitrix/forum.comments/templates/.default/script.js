;(function(window){
	window.__fcOnUCFormClear = function(obj) {
		window.LHEPostForm.reinitDataBefore(obj.editorId);
	};
	window.__fcOnUCFormAfterShow = function(obj, text, data) {
		var post_data = {MID : obj.id[1], ENTITY_XML_ID : obj.id[0], ENTITY_TYPE : obj.entitiesId[obj.id[0]][0], ENTITY_ID : obj.entitiesId[obj.id[0]][1]}, ii;
		for (ii in post_data)
		{
			if (post_data.hasOwnProperty(ii) && typeof ii == "string" && (ii.indexOf("MID") === 0 || ii.indexOf("ENTITY") === 0))
			{
				if (!obj.form[ii])
					obj.form.appendChild(BX.create('INPUT', {attrs : {name : ii, type: "hidden"}}));
				obj.form[ii].value = post_data[ii];
			}
		}
		var res = data ? data["UF"] : {};
		if (data && data["FILES"])
		{
			res["FILES"] = {
				USER_TYPE_ID : "file",
				FIELD_NAME : "FILE_NEW[]",
				VALUE : data["FILES"],
				CID : BX.message('FCCID')
			}
		}
		window.LHEPostForm.reinitData(obj.editorId, text, res);
	}
})(window);