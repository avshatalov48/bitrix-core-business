{"version":3,"sources":["simple_vad.js"],"names":["BX","SimpleVAD","VOLUME_THRESHOLD","INACTIVITY_TIME","config","this","mediaStream","audioContext","mediaStreamNode","analyserNode","audioTimeDomainData","voiceState","measureInterval","inactivityTimeout","callbacks","voiceStarted","type","isFunction","onVoiceStarted","DoNothing","voiceStopped","onVoiceStopped","isSupported","init","window","AudioContext","webkitAudioContext","AnalyserNode","prototype","MediaStream","createAnalyser","fftSize","createMediaStreamSource","connect","Float32Array","setInterval","analyzeAudioStream","bind","getFloatTimeDomainData","volume","getAverageVolume","setVoiceState","clearTimeout","setTimeout","onInactivityTimeout","sum","i","length","Math","sqrt","destroy","disconnect","close","clearInterval"],"mappings":"CAAC,WAEA,GAAGA,GAAGC,UACN,CACC,OAGD,IAAIC,EAAmB,GACvB,IAAIC,EAAkB,IAUtBH,GAAGC,UAAY,SAASG,GAEvBC,KAAKC,YAAcF,EAAOE,YAC1BD,KAAKE,aAAe,KACpBF,KAAKG,gBAAkB,KACvBH,KAAKI,aAAe,KAEpBJ,KAAKK,oBAAsB,KAC3BL,KAAKM,WAAa,MAElBN,KAAKO,gBAAkB,EACvBP,KAAKQ,kBAAoB,EAEzBR,KAAKS,WACJC,aAAcf,GAAGgB,KAAKC,WAAWb,EAAOc,gBAAkBd,EAAOc,eAAiBlB,GAAGmB,UACrFC,aAAcpB,GAAGgB,KAAKC,WAAWb,EAAOiB,gBAAkBjB,EAAOiB,eAAiBrB,GAAGmB,WAGtF,GAAGnB,GAAGC,UAAUqB,cAChB,CACCjB,KAAKkB,SAIPvB,GAAGC,UAAUqB,YAAc,WAE1B,OAAQE,OAAOC,cAAgBD,OAAOE,qBAAuBF,OAAOG,qBAAuBH,OAAOG,aAAaC,UAAU,4BAA+B,YAGzJ5B,GAAGC,UAAU2B,UAAUL,KAAO,WAE7B,KAAKlB,KAAKC,uBAAuBuB,aACjC,CACC,OAAO,MAGRxB,KAAKE,aAAe,IAAKiB,OAAOC,cAAgBD,OAAOE,oBACvDrB,KAAKI,aAAeJ,KAAKE,aAAauB,iBACtCzB,KAAKI,aAAasB,QAAU,IAC5B1B,KAAKG,gBAAkBH,KAAKE,aAAayB,wBAAwB3B,KAAKC,aACtED,KAAKG,gBAAgByB,QAAQ5B,KAAKI,cAElCJ,KAAKK,oBAAsB,IAAIwB,aAAa7B,KAAKI,aAAasB,SAC9D1B,KAAKO,gBAAkBuB,YAAY9B,KAAK+B,mBAAmBC,KAAKhC,MAAO,MAGxEL,GAAGC,UAAU2B,UAAUQ,mBAAqB,WAE3C/B,KAAKI,aAAa6B,uBAAuBjC,KAAKK,qBAC9C,IAAI6B,EAASlC,KAAKmC,iBAAiBnC,KAAKK,qBAExCL,KAAKoC,cAAcF,GAAUrC,IAG9BF,GAAGC,UAAU2B,UAAUa,cAAgB,SAAS9B,GAE/C,GAAGN,KAAKM,YAAcA,EACtB,CACC,OAGD,GAAGA,EACH,CACCN,KAAKS,UAAUC,eACf2B,aAAarC,KAAKQ,mBAClBR,KAAKQ,kBAAoB,EACzBR,KAAKM,WAAa,SAGnB,CACC,IAAIN,KAAKQ,kBACT,CACCR,KAAKQ,kBAAoB8B,WAAWtC,KAAKuC,oBAAoBP,KAAKhC,MAAOF,MAK5EH,GAAGC,UAAU2B,UAAUgB,oBAAsB,WAE5CvC,KAAKQ,kBAAoB,EACzBR,KAAKM,WAAa,MAClBN,KAAKS,UAAUM,gBAGhBpB,GAAGC,UAAU2B,UAAUY,iBAAmB,SAAS9B,GAElD,IAAImC,EAAM,EAEV,IAAI,IAAIC,EAAI,EAAGA,EAAIpC,EAAoBqC,OAAQD,IAC/C,CACCD,GAAOnC,EAAoBoC,GAAKpC,EAAoBoC,GAGrD,OAAOE,KAAKC,KAAKJ,EAAMnC,EAAoBqC,SAG5C/C,GAAGC,UAAU2B,UAAUsB,QAAU,WAEhC,GAAG7C,KAAKI,aACR,CACCJ,KAAKI,aAAa0C,aAGnB,GAAG9C,KAAKG,gBACR,CACCH,KAAKG,gBAAgB2C,aAGtB,GAAG9C,KAAKE,aACR,CACCF,KAAKE,aAAa6C,QAGnBC,cAAchD,KAAKO,iBAEnBP,KAAKI,aAAe,KACpBJ,KAAKG,gBAAkB,KACvBH,KAAKC,YAAc,KACnBD,KAAKE,aAAe,KAEpBF,KAAKS,WACJC,aAAcf,GAAGmB,UACjBC,aAAcpB,GAAGmB,aA5InB","file":""}