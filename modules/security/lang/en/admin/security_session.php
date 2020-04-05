<?
$MESS ['SEC_SESSION_ADMIN_SAVEDB_TAB'] = "Sessions in Database";
$MESS ['SEC_SESSION_ADMIN_SAVEDB_TAB_TITLE'] = "Configure storing of session data in database";
$MESS ['SEC_SESSION_ADMIN_TITLE'] = "Session Protection";
$MESS ['SEC_SESSION_ADMIN_DB_ON'] = "Session data is stored in the Security module database.";
$MESS ['SEC_SESSION_ADMIN_DB_OFF'] = "Session data are not stored in the Security module database.";
$MESS ['SEC_SESSION_ADMIN_DB_BUTTON_OFF'] = "Don't Store Session Data in The Security Module Database";
$MESS ['SEC_SESSION_ADMIN_DB_BUTTON_ON'] = "Store Session Data in The Security Module Database";
$MESS ['SEC_SESSION_ADMIN_DB_NOTE'] = "<p>Most web attacks steal authorized user session data. Enabling the <b>session protection</b> makes makes session hijacking pointless.</p>
<p>In addition to the standard session protection options that you can set in the user group preferences, the <b>proactive session protection</b>:
<ul style='font-size:100%'>
<li>changes the session ID periodically, and the frequency can be set;</li>
<li>stores the session data in the module table.</li>
</ul>
<p>Storing session data in the module database prevents data from being stolen by running scripts on other virtual servers, which eliminates virtual hosting configuration errors, bad temporary folder permission settings and other problems related to the operating system. It also reduces file system stress by offloading operations to the database server.</p>
<p><i>Recommended for high level.</i></p>";
$MESS ['SEC_SESSION_ADMIN_SESSID_TAB'] = "ID Change";
$MESS ['SEC_SESSION_ADMIN_SESSID_TAB_TITLE'] = "Configure periodic changing of session ID";
$MESS ['SEC_SESSION_ADMIN_SESSID_ON'] = "Session ID change is enabled.";
$MESS ['SEC_SESSION_ADMIN_SESSID_OFF'] = "Session ID change is disabled.";
$MESS ['SEC_SESSION_ADMIN_SESSID_BUTTON_OFF'] = "Disable ID Change";
$MESS ['SEC_SESSION_ADMIN_SESSID_BUTTON_ON'] = "Enable ID Change";
$MESS ['SEC_SESSION_ADMIN_SESSID_TTL'] = "Session ID Lifetime, sec.";
$MESS ['SEC_SESSION_ADMIN_SESSID_NOTE'] = "<p>If this feature is enabled, the session ID will change after the specified period of time. This adds to the server load, but obviously makes ID hijacking without instantaneous usage absolutely senseless.</p>
<p><i>Recommended for high level.</i></p>";
$MESS ['SEC_SESSION_ADMIN_DB_WARNING'] = "Attention! Toggling the session mode on or off will cause currently authorized users to lose authorization (the session data will be destroyed).";
$MESS ['SEC_SESSION_ADMIN_SESSID_WARNING'] = "Session ID is not compatible with Proactive Protection module. Identifier returned with session_id() function must not have more than 32 characters and should contain only Latin letters or numbers.";
?>