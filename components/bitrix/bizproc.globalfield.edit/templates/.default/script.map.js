{"version":3,"sources":["script.js"],"names":["exports","main_core","ui_dialogs_messagebox","bp_field_type","bizproc_globals","_templateObject","namespace","Reflection","GlobalFieldEditComponent","options","babelHelpers","classCallCheck","this","Type","isPlainObject","oldProperty","property","documentType","signedDocumentType","mode","availableTypes","types","inputValueId","multipleNode","saveButtonNode","form","slider","createClass","key","value","init","sliderDict","getData","correspondenceModeToIdName","constant","variable","editInputValue","Event","bind","saveHandler","type","prop","BX","clone","defaultProperty","Multiple","Default","undefined","Object","keys","isString","objectSpread","Options","_this$oldProperty$Opt","control","Bizproc","FieldType","renderControl","className","id","inputValueNode","document","getElementById","replaceWith","remove","wrapper","Tag","render","taggedTemplateLiteral","optionControl","createControlOptions","setSelectOptionFromForm","appendChild","getElementsByTagName","style","paddingTop","before","getElementsByName","placeholder","Loc","getMessage","values","i","hasOwnProperty","length","buttonAdd","parent","parentNode","insertBefore","formElements","elements","Name","Description","getValues","Visibility","Required","CreatedBy","CreatedDate","validateName","date","Date","getTime","toString","me","Globals","Manager","Instance","upsertGlobalsAction","then","response","data","error","MessageBox","alert","removeClass","set","close","radioNodeList","isElementNode","tagName","selectedOptions","push","_i","name","window","UI","Dialogs"],"mappings":"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","file":"script.map.js"}