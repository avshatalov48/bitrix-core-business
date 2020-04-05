function ScormApi()
{
    var Initialized = false;
    var Terminated = false;
    var diagnostic = "";
    var errorCode = 0;
    var storedValues = {
    		"cmi.completion_status" : "incomplete"
    }
    
	function Initialize(param)
	{
		Initialized = true;
		errorCode = 0;
		//TODO implement method
		return true;
	}

	function Terminate(param)
	{
		Initialized = false;
        Terminated = true;
 		errorCode = 0;
		//TODO implement method
		return true;
	}

	function GetValue(element)
	{
		//TODO implement method
		if (storedValues[element] != undefined)
			return storedValues[element];
		else {
			return "";
		}
	}

	function SetValue(element,value)
	{
		//TODO implement method
		storedValues[element] = value;
		return true;
	}

	function Commit(param)
	{
		//TODO implement method
		return true;
	}

	function GetLastError()
	{
		return errorCode;
	}

    function GetErrorString(param) {
        if (param != "") {
            var errorString = "";
            switch(param) {
                case 0:
                    errorString = "No error";
                break;
                case 101:
                    errorString = "General exception";
                break;
                case 102:
                    errorString = "General Inizialization Failure";
                break;
                case 103:
                    errorString = "Already Initialized";
                break;
                case 104:
                    errorString = "Content Instance Terminated";
                break;
                case 111:
                    errorString = "General Termination Failure";
                break;
                case 112:
                    errorString = "Termination Before Inizialization";
                break;
                case 113:
                    errorString = "Termination After Termination";
                break;
                case 122:
                    errorString = "Retrieve Data Before Initialization";
                break;
                case 123:
                    errorString = "Retrieve Data After Termination";
                break;
                case 132:
                    errorString = "Store Data Before Inizialization";
                break;
                case 133:
                    errorString = "Store Data After Termination";
                break;
                case 142:
                    errorString = "Commit Before Inizialization";
                break;
                case 143:
                    errorString = "Commit After Termination";
                break;
                case 201:
                    errorString = "General Argument Error";
                break;
                case 301:
                    errorString = "General Get Failure";
                break;
                case 351:
                    errorString = "General Set Failure";
                break;
                case 391:
                    errorString = "General Commit Failure";
                break;
                case 401:
                    errorString = "Undefinited Data Model";
                break;
                case 402:
                    errorString = "Unimplemented Data Model Element";
                break;
                case 403:
                    errorString = "Data Model Element Value Not Initialized";
                break;
                case 404:
                    errorString = "Data Model Element Is Read Only";
                break;
                case 405:
                    errorString = "Data Model Element Is Write Only";
                break;
                case 406:
                    errorString = "Data Model Element Type Mismatch";
                break;
                case 407:
                    errorString = "Data Model Element Value Out Of Range";
                break;
                case 408:
                    errorString = "Data Model Dependency Not Established";
                break;
            }
            return errorString;
        } else {
            return "";
        }
    }

    function GetDiagnostic(param)
    {
		//TODO implement method
		return param;
    }

    this.Initialize = Initialize;
    this.Terminate = Terminate;
    this.GetValue = GetValue;
    this.SetValue = SetValue;
    this.Commit = Commit;
    this.GetLastError = GetLastError;
    this.GetErrorString = GetErrorString;
    this.GetDiagnostic = GetDiagnostic;
    this.version = '1.0';
}

var API_1484_11 = new ScormApi();

/*var nFindAPITries = 0;
var API = null;
var maxTries = 10;
var APIVersion = "";

function ScanForAPI(win)
{
	while ((win.API_1484_11 == null) && (win.parent != null) && (win.parent != win))
	{
		nFindAPITries++;
		if (nFindAPITries > maxTries)
		{
			return null;
		}
		win = win.parent;
	}

	return win.API_1484_11;
}

function GetAPI(win)
{
	if ((win.parent != null) && (win.parent != win))
	{
		API = ScanForAPI(win.parent);
	}

	if ((API == null) && (win.opener != null))
	{
		API = ScanForAPI(win.opener);
	}

	if (API != null)
	{
		APIVersion = API.version;
	}
}*/