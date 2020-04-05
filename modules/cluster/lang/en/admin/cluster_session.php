<?
$MESS["CLU_SESSION_SAVEDB_TAB"] = "Sessions in Database";
$MESS["CLU_SESSION_SAVEDB_TAB_TITLE"] = "Configure storing of session data in database";
$MESS["CLU_SESSION_DB_ON"] = "Session data is stored in the Security module database.";
$MESS["CLU_SESSION_DB_OFF"] = "Session data currently not being stored in the Security Module database.";
$MESS["CLU_SESSION_DB_BUTTON_OFF"] = "Don't Store Session Data in The Security Module Database";
$MESS["CLU_SESSION_DB_BUTTON_ON"] = "Store Session Data in The Security Module Database";
$MESS["CLU_SESSION_DB_WARNING"] = "Attention! Toggling the session mode on or off will cause currently authorized users to lose authorization (the session data will be destroyed).";
$MESS["CLU_SESSION_SESSID_WARNING"] = "Session ID is not compatible with Proactive Protection module. Identifier returned with session_id() function must not have more than 32 characters and should contain only Latin letters or numbers.";
$MESS["CLU_SESSION_NO_SECURITY"] = "The \"Proactive Protection\" module is required.";
$MESS["CLU_SESSION_TITLE"] = "Store Sessions in Database";
$MESS["CLU_SESSION_NOTE"] = "<p>Web server clustering requires that you properly configure the session support.</p>
<p>The most frequently used load distribution strategies are:</p>
<p>1) assigning a visitor session to a web server for the processing of all further requests.</p>
<p>2) allowing different hits of a web session to be processed by different web servers.<br>
The mandatory prerequisite for the strategy (2) is that the Security module must be configured to store the session data.</p>";
?>