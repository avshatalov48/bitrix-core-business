{"version":3,"sources":["content.bundle.js"],"names":["this","BX","Landing","UI","exports","main_core","landing_ui_panel_base","getDeltaFromEvent","event","deltaX","deltaY","Type","isUndefined","wheelDeltaX","wheelDeltaY","deltaMode","Number","isNaN","wheelDelta","x","y","calculateDurationTransition","diff","defaultDuration","Math","min","scrollTo","container","element","Promise","resolve","elementTop","duration","defaultMargin","elementMarginTop","max","parseInt","Dom","style","containerScrollTop","scrollTop","HTMLIFrameElement","offsetTop","contentWindow","scrollY","pos","top","abs","start","finish","easing","step","state","animate","setTimeout","_templateObject8","data","babelHelpers","taggedTemplateLiteral","_templateObject7","_templateObject6","_templateObject5","_templateObject4","_templateObject3","_templateObject2","_templateObject","Content","_BasePanel","inherits","createClass","key","value","createOverlay","Tag","render","createHeader","createTitle","createBody","createSidebar","createContent","createFooter","calculateTransitionDuration","arguments","length","undefined","scrollTo$1","getDeltaFromEvent$1","id","_this","classCallCheck","possibleConstructorReturn","getPrototypeOf","call","addClass","layout","Object","freeze","overlay","header","title","body","footer","sidebar","content","closeButton","Button","BaseButton","className","onClick","hide","bind","assertThisInitialized","attrs","Loc","getMessage","forms","Collection","FormCollection","buttons","ButtonCollection","sidebarButtons","wheelEventName","isNil","window","onwheel","onmousewheel","onMouseWheel","onMouseEnter","onMouseLeave","removeClass","append","isString","concat","subTitle","init","Event","onKeyDown","PageObject","getInstance","view","then","frame","console","warn","scrollAnimation","scrollObserver","IntersectionObserver","onIntersecting","_this2","document","setTitle","isArray","forEach","item","appendFooterButton","isDomNode","items","isIntersecting","target","keyCode","stopPropagation","contains","right","scrollTarget","currentTarget","unbind","_this3","preventDefault","delta","requestAnimationFrame","isShown","show","options","_this4","Utils","Show","_this5","Hide","appendForm","form","add","getNode","appendCard","card","observe","clear","clearContent","clearSidebar","clean","innerHTML","button","appendSidebarButton","BasePanel","Panel"],"mappings":"AAAAA,KAAKC,GAAKD,KAAKC,OACfD,KAAKC,GAAGC,QAAUF,KAAKC,GAAGC,YAC1BF,KAAKC,GAAGC,QAAQC,GAAKH,KAAKC,GAAGC,QAAQC,QACpC,SAAUC,EAASC,EAAWC,GAC9B,aAEA,SAASC,EAAkBC,GACzB,IAAIC,EAASD,EAAMC,OACnB,IAAIC,GAAU,EAAIF,EAAME,OAExB,GAAIL,EAAUM,KAAKC,YAAYH,IAAWJ,EAAUM,KAAKC,YAAYF,GAAS,CAC5ED,GAAU,EAAID,EAAMK,YAAc,EAClCH,EAASF,EAAMM,YAAc,EAG/B,GAAIN,EAAMO,YAAc,EAAG,CACzBN,GAAU,GACVC,GAAU,GAKZ,GAAIM,OAAOC,MAAMR,IAAWO,OAAOC,MAAMP,GAAS,CAChDD,EAAS,EACTC,EAASF,EAAMU,WAGjB,OACEC,EAAGV,EACHW,EAAGV,GAIP,SAASW,EAA4BC,GACnC,IAAIC,EAAkB,IACtB,OAAOC,KAAKC,IAAI,IAAM,IAAMH,EAAMC,GAGpC,SAASG,EAASC,EAAWC,GAC3B,OAAO,IAAIC,QAAQ,SAAUC,GAC3B,IAAIC,EAAa,EACjB,IAAIC,EAAW,EAEf,GAAIJ,EAAS,CACX,IAAIK,EAAgB,GACpB,IAAIC,EAAmBV,KAAKW,IAAIC,SAAS/B,EAAUgC,IAAIC,MAAMV,EAAS,eAAgBK,GACtF,IAAIM,EAAqBZ,EAAUa,UAEnC,KAAMb,aAAqBc,mBAAoB,CAC7CV,EAAaH,EAAQc,WAAaf,EAAUe,WAAa,GAAKR,MACzD,CACLK,EAAqBZ,EAAUgB,cAAcC,QAC7Cb,EAAa9B,GAAG4C,IAAIjB,GAASkB,IAAMZ,EAAmB,IAGxDF,EAAWX,EAA4BG,KAAKuB,IAAIhB,EAAaQ,IAC7D,IAAIS,EAAQxB,KAAKW,IAAII,EAAoB,GACzC,IAAIU,EAASzB,KAAKW,IAAIJ,EAAY,GAElC,GAAIiB,IAAUC,EAAQ,CACpB,IAAIhD,GAAGiD,QACLlB,SAAUA,EACVgB,OACER,UAAWQ,GAEbC,QACET,UAAWS,GAEbE,KAAM,SAASA,EAAKC,GAClB,KAAMzB,aAAqBc,mBAAoB,CAC7Cd,EAAUa,UAAYY,EAAMZ,cACvB,CACLb,EAAUgB,cAAcjB,SAAS,EAAGF,KAAKW,IAAIiB,EAAMZ,UAAW,QAGjEa,UACHC,WAAWxB,EAASE,OACf,CACLF,SAEG,CACLA,OAKN,SAASyB,IACP,IAAIC,EAAOC,aAAaC,uBAAuB,4DAA+D,mBAE9GH,EAAmB,SAASA,IAC1B,OAAOC,GAGT,OAAOA,EAGT,SAASG,IACP,IAAIH,EAAOC,aAAaC,uBAAuB,uGAE/CC,EAAmB,SAASA,IAC1B,OAAOH,GAGT,OAAOA,EAGT,SAASI,IACP,IAAIJ,EAAOC,aAAaC,uBAAuB,4EAE/CE,EAAmB,SAASA,IAC1B,OAAOJ,GAGT,OAAOA,EAGT,SAASK,IACP,IAAIL,EAAOC,aAAaC,uBAAuB,4EAE/CG,EAAmB,SAASA,IAC1B,OAAOL,GAGT,OAAOA,EAGT,SAASM,IACP,IAAIN,EAAOC,aAAaC,uBAAuB,qGAE/CI,EAAmB,SAASA,IAC1B,OAAON,GAGT,OAAOA,EAGT,SAASO,IACP,IAAIP,EAAOC,aAAaC,uBAAuB,qEAE/CK,EAAmB,SAASA,IAC1B,OAAOP,GAGT,OAAOA,EAGT,SAASQ,IACP,IAAIR,EAAOC,aAAaC,uBAAuB,uGAE/CM,EAAmB,SAASA,IAC1B,OAAOR,GAGT,OAAOA,EAGT,SAASS,IACP,IAAIT,EAAOC,aAAaC,uBAAuB,oHAE/CO,EAAkB,SAASA,IACzB,OAAOT,GAGT,OAAOA,EAMT,IAAIU,EAEJ,SAAUC,GACRV,aAAaW,SAASF,EAASC,GAC/BV,aAAaY,YAAYH,EAAS,OAChCI,IAAK,gBACLC,MAAO,SAASC,IACd,OAAOnE,EAAUoE,IAAIC,OAAOT,QAG9BK,IAAK,eACLC,MAAO,SAASI,IACd,OAAOtE,EAAUoE,IAAIC,OAAOV,QAG9BM,IAAK,cACLC,MAAO,SAASK,IACd,OAAOvE,EAAUoE,IAAIC,OAAOX,QAG9BO,IAAK,aACLC,MAAO,SAASM,IACd,OAAOxE,EAAUoE,IAAIC,OAAOZ,QAG9BQ,IAAK,gBACLC,MAAO,SAASO,IACd,OAAOzE,EAAUoE,IAAIC,OAAOb,QAG9BS,IAAK,gBACLC,MAAO,SAASQ,IACd,OAAO1E,EAAUoE,IAAIC,OAAOd,QAG9BU,IAAK,eACLC,MAAO,SAASS,IACd,OAAO3E,EAAUoE,IAAIC,OAAOf,QAG9BW,IAAK,8BACLC,MAAO,SAASU,IACd,IAAI3D,EAAO4D,UAAUC,OAAS,GAAKD,UAAU,KAAOE,UAAYF,UAAU,GAAK,EAC/E,OAAO7D,EAA4BC,MAGrCgD,IAAK,WACLC,MAAO,SAASc,EAAW1D,EAAWC,GACpC,OAAOF,EAASC,EAAWC,MAG7B0C,IAAK,oBACLC,MAAO,SAASe,EAAoB9E,GAClC,OAAOD,EAAkBC,OAI7B,SAAS0D,EAAQqB,GACf,IAAIC,EAEJ,IAAIhC,EAAO0B,UAAUC,OAAS,GAAKD,UAAU,KAAOE,UAAYF,UAAU,MAC1EzB,aAAagC,eAAezF,KAAMkE,GAClCsB,EAAQ/B,aAAaiC,0BAA0B1F,KAAMyD,aAAakC,eAAezB,GAAS0B,KAAK5F,KAAMuF,EAAI/B,IACzGnD,EAAUgC,IAAIwD,SAASL,EAAMM,OAAQ,4BACrCN,EAAMhC,KAAOuC,OAAOC,OAAOxC,GAC3BgC,EAAMS,QAAU/B,EAAQM,gBACxBgB,EAAMU,OAAShC,EAAQS,eACvBa,EAAMW,MAAQjC,EAAQU,cACtBY,EAAMY,KAAOlC,EAAQW,aACrBW,EAAMa,OAASnC,EAAQc,eACvBQ,EAAMc,QAAUpC,EAAQY,gBACxBU,EAAMe,QAAUrC,EAAQa,gBACxBS,EAAMgB,YAAc,IAAIvG,GAAGC,QAAQC,GAAGsG,OAAOC,WAAW,SACtDC,UAAW,iCACXC,QAASpB,EAAMqB,KAAKC,KAAKrD,aAAasD,sBAAsBvB,IAC5DwB,OACEb,MAAOlG,GAAGC,QAAQ+G,IAAIC,WAAW,oCAGrC1B,EAAM2B,MAAQ,IAAIlH,GAAGC,QAAQC,GAAGiH,WAAWC,eAC3C7B,EAAM8B,QAAU,IAAIrH,GAAGC,QAAQC,GAAGiH,WAAWG,iBAC7C/B,EAAMgC,eAAiB,IAAIvH,GAAGC,QAAQC,GAAGiH,WAAWG,iBACpD/B,EAAMiC,eAAiBpH,EAAUM,KAAK+G,MAAMC,OAAOC,SAAWD,OAAOC,QAAUD,OAAOE,aACtFrC,EAAMsC,aAAetC,EAAMsC,aAAahB,KAAKrD,aAAasD,sBAAsBvB,IAChFA,EAAMuC,aAAevC,EAAMuC,aAAajB,KAAKrD,aAAasD,sBAAsBvB,IAChFA,EAAMwC,aAAexC,EAAMwC,aAAalB,KAAKrD,aAAasD,sBAAsBvB,IAChFnF,EAAUgC,IAAI4F,YAAYzC,EAAMM,OAAQ,mBACxCzF,EAAUgC,IAAIwD,SAASL,EAAMS,QAAS,mBACtC5F,EAAUgC,IAAI6F,OAAO1C,EAAMc,QAASd,EAAMY,MAC1C/F,EAAUgC,IAAI6F,OAAO1C,EAAMe,QAASf,EAAMY,MAC1C/F,EAAUgC,IAAI6F,OAAO1C,EAAMU,OAAQV,EAAMM,QACzCzF,EAAUgC,IAAI6F,OAAO1C,EAAMW,MAAOX,EAAMU,QACxC7F,EAAUgC,IAAI6F,OAAO1C,EAAMY,KAAMZ,EAAMM,QACvCzF,EAAUgC,IAAI6F,OAAO1C,EAAMa,OAAQb,EAAMM,QACzCzF,EAAUgC,IAAI6F,OAAO1C,EAAMgB,YAAYV,OAAQN,EAAMM,QAErD,GAAIzF,EAAUM,KAAKwH,SAAS3E,EAAKmD,WAAY,CAC3CtG,EAAUgC,IAAIwD,SAASL,EAAMM,QAAStC,EAAKmD,UAAW,GAAGyB,OAAO5E,EAAKmD,UAAW,cAGlF,GAAItG,EAAUM,KAAKwH,SAAS3E,EAAK6E,WAAa7E,EAAK6E,WAAa,GAAI,CAClE7C,EAAM6C,SAAWhI,EAAUoE,IAAIC,OAAOnB,IAAoBC,EAAK6E,UAC/DhI,EAAUgC,IAAI6F,OAAO1C,EAAM6C,SAAU7C,EAAMU,QAC3C7F,EAAUgC,IAAIwD,SAASL,EAAMM,OAAQ,0CAGvCN,EAAM8C,OAENjI,EAAUkI,MAAMzB,KAAKa,OAAO7E,IAAK,UAAW0C,EAAMgD,UAAU1B,KAAKrD,aAAasD,sBAAsBvB,KACpGvF,GAAGC,QAAQuI,WAAWC,cAAcC,OAAOC,KAAK,SAAUC,UAChDA,GAASxI,EAAUkI,MAAMzB,KAAK+B,EAAMlG,cAAe,UAAW6C,EAAMgD,UAAU1B,KAAKrD,aAAasD,sBAAsBvB,OAC7HsD,QAAQC,MAEX,GAAIvD,EAAMhC,KAAKwF,gBAAiB,CAC9BxD,EAAMyD,eAAiB,IAAIC,qBAAqB1D,EAAM2D,eAAerC,KAAKrD,aAAasD,sBAAsBvB,KAG/G,OAAOA,EAGT/B,aAAaY,YAAYH,IACvBI,IAAK,OACLC,MAAO,SAAS+D,IACd,IAAIc,EAASpJ,KAEbK,EAAUgC,IAAI6F,OAAOlI,KAAKiG,QAASoD,SAASjD,MAC5C/F,EAAUkI,MAAMzB,KAAK9G,KAAKiG,QAAS,QAASjG,KAAK6G,KAAKC,KAAK9G,OAC3DK,EAAUkI,MAAMzB,KAAK9G,KAAK8F,OAAQ,aAAc9F,KAAK+H,cACrD1H,EAAUkI,MAAMzB,KAAK9G,KAAK8F,OAAQ,aAAc9F,KAAKgI,cACrD3H,EAAUkI,MAAMzB,KAAK9G,KAAKuG,QAAS,aAAcvG,KAAK+H,cACtD1H,EAAUkI,MAAMzB,KAAK9G,KAAKuG,QAAS,aAAcvG,KAAKgI,cACtD3H,EAAUkI,MAAMzB,KAAK9G,KAAKsG,QAAS,aAActG,KAAK+H,cACtD1H,EAAUkI,MAAMzB,KAAK9G,KAAKsG,QAAS,aAActG,KAAKgI,cACtD3H,EAAUkI,MAAMzB,KAAK9G,KAAKkG,OAAQ,aAAclG,KAAK+H,cACrD1H,EAAUkI,MAAMzB,KAAK9G,KAAKkG,OAAQ,aAAclG,KAAKgI,cACrD3H,EAAUkI,MAAMzB,KAAK9G,KAAKqG,OAAQ,aAAcrG,KAAK+H,cACrD1H,EAAUkI,MAAMzB,KAAK9G,KAAKqG,OAAQ,aAAcrG,KAAKgI,cAErD,GAAI,UAAWhI,KAAKwD,KAAM,CACxBxD,KAAKsJ,SAAStJ,KAAKwD,KAAK2C,OAG1B,GAAI,WAAYnG,KAAKwD,KAAM,CACzB,GAAInD,EAAUM,KAAK4I,QAAQvJ,KAAKwD,KAAK6C,QAAS,CAC5CrG,KAAKwD,KAAK6C,OAAOmD,QAAQ,SAAUC,GACjC,GAAIA,aAAgBxJ,GAAGC,QAAQC,GAAGsG,OAAOC,WAAY,CACnD0C,EAAOM,mBAAmBD,GAG5B,GAAIpJ,EAAUM,KAAKgJ,UAAUF,GAAO,CAClCpJ,EAAUgC,IAAI6F,OAAOuB,EAAML,EAAO/C,gBAQ5C/B,IAAK,iBACLC,MAAO,SAAS4E,EAAeS,GAC7BA,EAAMJ,QAAQ,SAAUC,GACtB,GAAIA,EAAKI,eAAgB,CACvBxJ,EAAUgC,IAAI4F,YAAYwB,EAAKK,OAAQ,6BACvCzJ,EAAUgC,IAAIwD,SAAS4D,EAAKK,OAAQ,6BAC/B,CACLzJ,EAAUgC,IAAIwD,SAAS4D,EAAKK,OAAQ,6BACpCzJ,EAAUgC,IAAI4F,YAAYwB,EAAKK,OAAQ,+BAK7CxF,IAAK,YACLC,MAAO,SAASiE,EAAUhI,GACxB,GAAIA,EAAMuJ,UAAY,GAAI,MACnB/J,KAAK6G,WAIdvC,IAAK,eACLC,MAAO,SAASwD,EAAavH,GAC3BA,EAAMwJ,kBACN3J,EAAUkI,MAAMzB,KAAK9G,KAAK8F,OAAQ9F,KAAKyH,eAAgBzH,KAAK8H,cAC5DzH,EAAUkI,MAAMzB,KAAK9G,KAAK8F,OAAQ,YAAa9F,KAAK8H,cAEpD,GAAI9H,KAAKsG,QAAQ2D,SAASzJ,EAAMsJ,SAAW9J,KAAKuG,QAAQ0D,SAASzJ,EAAMsJ,SAAW9J,KAAKkG,OAAO+D,SAASzJ,EAAMsJ,SAAW9J,KAAKqG,OAAO4D,SAASzJ,EAAMsJ,SAAW9J,KAAKkK,OAASlK,KAAKkK,MAAMD,SAASzJ,EAAMsJ,QAAS,CAC7M9J,KAAKmK,aAAe3J,EAAM4J,kBAI9B9F,IAAK,eACLC,MAAO,SAASyD,EAAaxH,GAC3BA,EAAMwJ,kBACN/J,GAAGoK,OAAOrK,KAAK8F,OAAQ9F,KAAKyH,eAAgBzH,KAAK8H,cACjD7H,GAAGoK,OAAOrK,KAAK8F,OAAQ,YAAa9F,KAAK8H,iBAG3CxD,IAAK,eACLC,MAAO,SAASuD,EAAatH,GAC3B,IAAI8J,EAAStK,KAEbQ,EAAM+J,iBACN/J,EAAMwJ,kBACN,IAAIQ,EAAQtG,EAAQ3D,kBAAkBC,GACtC,IAAIgC,EAAYxC,KAAKmK,aAAa3H,UAClCiI,sBAAsB,WACpBH,EAAOH,aAAa3H,UAAYA,EAAYgI,EAAMpJ,OAItDkD,IAAK,WACLC,MAAO,SAAS7C,EAASE,QAClBsC,EAAQxC,SAAS1B,KAAKuG,QAAS3E,MAGtC0C,IAAK,UACLC,MAAO,SAASmG,IACd,OAAO1K,KAAKoD,QAAU,WAIxBkB,IAAK,OACLC,MAAO,SAASoG,EAAKC,GACnB,IAAIC,EAAS7K,KAEb,IAAKA,KAAK0K,UAAW,MACdzK,GAAGC,QAAQ4K,MAAMC,KAAK/K,KAAKiG,SAChC,OAAOhG,GAAGC,QAAQ4K,MAAMC,KAAK/K,KAAK8F,QAAQ8C,KAAK,WAC7CiC,EAAOzH,MAAQ,UAInB,OAAOvB,QAAQC,QAAQ,SAGzBwC,IAAK,OACLC,MAAO,SAASsC,IACd,IAAImE,EAAShL,KAEb,GAAIA,KAAK0K,UAAW,MACbzK,GAAGC,QAAQ4K,MAAMG,KAAKjL,KAAKiG,SAChC,OAAOhG,GAAGC,QAAQ4K,MAAMG,KAAKjL,KAAK8F,QAAQ8C,KAAK,WAC7CoC,EAAO5H,MAAQ,WAInB,OAAOvB,QAAQC,QAAQ,SAGzBwC,IAAK,aACLC,MAAO,SAAS2G,EAAWC,GACzBnL,KAAKmH,MAAMiE,IAAID,GACf9K,EAAUgC,IAAI6F,OAAOiD,EAAKE,UAAWrL,KAAKuG,YAG5CjC,IAAK,aACLC,MAAO,SAAS+G,EAAWC,GACzB,GAAIvL,KAAKwD,KAAKwF,gBAAiB,CAC7B3I,EAAUgC,IAAIwD,SAAS0F,EAAKzF,OAAQ,6BACpC9F,KAAKiJ,eAAeuC,QAAQD,EAAKzF,QAGnCzF,EAAUgC,IAAI6F,OAAOqD,EAAKzF,OAAQ9F,KAAKuG,YAGzCjC,IAAK,QACLC,MAAO,SAASkH,IACdzL,KAAK0L,eACL1L,KAAK2L,eACL3L,KAAKmH,MAAMsE,WAGbnH,IAAK,eACLC,MAAO,SAASmH,IACdrL,EAAUgC,IAAIuJ,MAAM5L,KAAKuG,YAG3BjC,IAAK,eACLC,MAAO,SAASoH,IACdtL,EAAUgC,IAAIuJ,MAAM5L,KAAKsG,YAG3BhC,IAAK,WACLC,MAAO,SAAS+E,EAASnD,GACvBnG,KAAKmG,MAAM0F,UAAY1F,KAGzB7B,IAAK,qBACLC,MAAO,SAASmF,EAAmBoC,GACjC9L,KAAKsH,QAAQ8D,IAAIU,GACjBzL,EAAUgC,IAAI6F,OAAO4D,EAAOhG,OAAQ9F,KAAKqG,WAG3C/B,IAAK,sBACLC,MAAO,SAASwH,EAAoBD,GAClC9L,KAAKwH,eAAe4D,IAAIU,GACxBzL,EAAUgC,IAAI6F,OAAO4D,EAAOhG,OAAQ9F,KAAKsG,aAG7C,OAAOpC,EAxST,CAySE5D,EAAsB0L,WAExB5L,EAAQ8D,QAAUA,GAndnB,CAqdElE,KAAKC,GAAGC,QAAQC,GAAG8L,MAAQjM,KAAKC,GAAGC,QAAQC,GAAG8L,UAAahM,GAAIA,GAAGC,QAAQC,GAAG8L","file":"content.bundle.map.js"}