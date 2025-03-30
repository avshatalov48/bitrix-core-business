<?php

/* TODO: Comments are out of date and incomplete. */

/**
 * FILE:        windowslivelogin.php
 *
 * DESCRIPTION: Sample implementation of Web Authentication and Delegated
 *              Authentication protocol in PHP. Also includes trusted
 *              sign-in and application verification sample
 *              implementations.
 *
 * VERSION:     1.1
 *
 * Copyright (c) 2008 Microsoft Corporation.  All Rights Reserved.
 */

/**
 * Holds the user information after a successful sign-in.
 */
class WLL_User
{
	/**
    * Initialize the User with time stamp, userid, flags, context and token.
    */
	public function __construct($timestamp, $id, $flags, $context, $token)
	{
		$this->setTimestamp($timestamp);
		$this->setId($id);
		$this->setFlags($flags);
		$this->setContext($context);
		$this->setToken($token);
	}

	/*private*/
	var $_timestamp;

	/**
     * Returns the Unix timestamp as obtained from the SSO token.
     */
	/*public*/
	function getTimestamp()
	{
		return $this->_timestamp;
	}

	/**
     * Sets the Unix timestamp.
     */
	/*private*/
	function setTimestamp($timestamp)
	{
		if (!$timestamp) {
			//throw new Exception('Error: WLL_User: Null timestamp.');
			$this->setError('Error: WLL_User: Null timestamp.');
			return ;
		}

		if (!preg_match('/^\d+$/', $timestamp) || ($timestamp <= 0)) {
			//throw new Exception('Error: WLL_User: Invalid timestamp: ' . $timestamp);
			$this->setError('Error: WLL_User: Invalid timestamp: ' . $timestamp);
			return ;
		}

		$this->_timestamp = $timestamp;
	}

	/*private*/
	var $_id;

	/**
     * Returns the pairwise unique ID for the user.
     */
	/*public*/
	function getId()
	{
		return $this->_id;
	}

	/**
     * Sets the pairwise unique ID for the user.
     */
	/*private*/
	function setId($id)
	{
		if (!$id) {
			//throw new Exception('Error: WLL_User: Null id.');
			$this->setError('Error: WLL_User: Null id.');
			return ;
		}

		if (!preg_match('/^\w+$/', $id)) {
			//throw new Exception('Error: WLL_User: Invalid id: ' . $id);
			$this->setError('Error: WLL_User: Invalid id: ' . $id);
			return ;
		}

		$this->_id = $id;
	}

	/*private*/
	var $_usePersistentCookie;

	/**
     * Indicates whether the application is expected to store the
     * user token in a session or persistent cookie.
     */
	/*public*/
	function usePersistentCookie()
	{
		return $this->_usePersistentCookie;
	}

	/**
     * Sets the usePersistentCookie flag for the user.
     */
	/*private*/
	function setFlags($flags)
	{
		$this->_usePersistentCookie = false;
		if (preg_match('/^\d+$/', $flags)) {
			$this->_usePersistentCookie = (($flags % 2) == 1);
		}
	}

	/*private*/
	var $_context;

	/**
     * Returns the application context that was originally passed
     * to the sign-in request, if any.
     */
	/*public*/
	function getContext()
	{
		return $this->_context;
	}

	/**
     * Sets the the Application context.
     */
	/*private*/
	function setContext($context)
	{
		$this->_context = $context;
	}

	/*private*/
	var $_token;

	/**
     * Returns the encrypted Web Authentication token containing
     * the UID. This can be cached in a cookie and the UID can be
     * retrieved by calling the ProcessToken method.
     */
	/*public*/
	function getToken()
	{
		return $this->_token;
	}

	/**
     * Sets the the User token.
     */
	/*private*/
	function setToken($token)
	{
		$this->_token = $token;
	}


	var $_error = false;

	function setError($str)
	{
		$this->_error = $str;
	}

	function getError()
	{
		if ($this->_error !== false)
		{
			return $this->_error;
		}
	}
}

/**
 * Holds the Consent Token object corresponding to consent granted.
 */
class WLL_ConsentToken
{
	/**
     * Indicates whether the delegation token is set and has not expired.
     */
	/*public*/
	function isValid()
	{
		if (!$this->getDelegationToken()) {
			return false;
		}

		$now = time();
		return (($now-300) < $this->getExpiry());
	}

	/**
     * Refreshes the current token and replace it. If operation succeeds
     * true is returned to signify success.
     */
	/*public*/
	function refresh()
	{
		$wll = $this->_wll;
		$ct = $wll->refreshConsentToken($this);
		if (!$ct) {
			return false;
		}
		$this->copy($ct);
		return true;
	}

	/*private*/
	var $_wll;

	/**
     * Initialize the ConsentToken module with the WindowsLiveLogin,
     * delegation token, refresh token, session key, expiry, offers,
     * location ID, context, decoded token, and raw token.
     */
	public function __construct(
						$wll, $delegationtoken, $refreshtoken,
						$sessionkey, $expiry, $offers, $locationID, $context,
						$decodedtoken, $token
				)
	{
		$this->_wll = $wll;
		$this->setDelegationToken($delegationtoken);
		$this->setRefreshToken($refreshtoken);
		$this->setSessionKey($sessionkey);
		$this->setExpiry($expiry);
		$this->setOffers($offers);
		$this->setLocationID($locationID);
		$this->setContext($context);
		$this->setDecodedToken($decodedtoken);
		$this->setToken($token);
	}

	/*private*/
	var $_delegationtoken;

	/**
     * Gets the Delegation token.
     */
	/*public*/
	function getDelegationToken()
	{
		return $this->_delegationtoken;
	}

	/**
     * Sets the Delegation token.
     */
	/*private*/
	function setDelegationToken($delegationtoken)
	{
		if (!$delegationtoken) {
			//throw new Exception('Error: WLL_ConsentToken: Null delegation token.');
			$this->setError('Error: WLL_ConsentToken: Null delegation token.');
			return ;
		}
		$this->_delegationtoken = $delegationtoken;
	}

	/*private*/
	var $_refreshtoken;

	/**
     * Gets the refresh token.
     */
	/*public*/
	function getRefreshToken()
	{
		return $this->_refreshtoken;
	}

	/**
     * Sets the refresh token.
     */
	/*private*/
	function setRefreshToken($refreshtoken)
	{
		$this->_refreshtoken = $refreshtoken;
	}

	/*private*/
	var $_sessionkey;

	/**
     * Gets the session key.
     */
	/*public*/
	function getSessionKey()
	{
		return $this->_sessionkey;
	}

	/**
     * Sets the session key.
     */
	/*private*/
	function setSessionKey($sessionkey)
	{
		if (!$sessionkey) {
			//throw new Exception('Error: WLL_ConsentToken: Null session key.');
			$this->setError('Error: WLL_ConsentToken: Null session key.');
			return ;
		}
		$this->_sessionkey = base64_decode(urldecode($sessionkey));
	}

	/*private*/
	var $_expiry;

	/**
     * Gets the expiry time of delegation token.
     */
	/*public*/
	function getExpiry()
	{
		return $this->_expiry;
	}

	/**
     * Sets the expiry time of delegation token.
     */
	/*private*/
	function setExpiry($expiry)
	{
		if (!$expiry) {
			//throw new Exception('Error: WLL_ConsentToken: Null expiry time.');
			$this->setError('Error: WLL_ConsentToken: Null expiry time.');
			return ;
		}

		if (!preg_match('/^\d+$/', $expiry) || ($expiry <= 0)) {
			//throw new Exception('Error: WLL_ConsentToken: Invalid expiry time: ' . $expiry);
			$this->setError('Error: WLL_ConsentToken: Invalid expiry time: ' . $expiry);
			return ;
		}
		$this->_expiry = $expiry;
	}

	/*private*/
	var $_offers;

	/**
     * Gets the list of offers/actions for which the user granted consent.
     */
	/*public*/
	function getOffers()
	{
		return $this->_offers;
	}

	/*private*/
	var $_offers_string;

	/**
     * Gets the string representation of all the offers/actions for which
     * the user granted consent.
     */
	/*public*/
	function getOffersString()
	{
		return $this->_offers_string;
	}

	/**
     * Sets the offers/actions for which user granted consent.
     */
	/*private*/
	function setOffers($offers)
	{
		if (!$offers) {
			//throw new Exception('Error: WLL_ConsentToken: Null offers.');
			$this->setError('Error: WLL_ConsentToken: Null offers.');
			return ;
		}

		$offers = urldecode($offers);

		//Split $offers by ";" and then take only substring before first ":"
		if(preg_match_all("/(^|;)([^:;]*)/", $offers, $arMatch))
		{
			$this->_offers = $arMatch[2];
			$this->_offers_string = ltrim(implode(",", $arMatch[2]), ",");
		}
		else
		{
			$this->_offers = array();
			$this->_offers_string = "";
		}
	}

	/*private*/
	var $_locationID;
	/**
     * Gets the location ID.
     */
	/*public*/
	function getLocationID()
	{
		return $this->_locationID;
	}

	/**
     * Sets the location ID.
     */
	/*private*/
	function setLocationID($locationID)
	{
		if (!$locationID) {
			//throw new Exception('Error: WLL_ConsentToken: Null Location ID.');
			$this->setError('Error: WLL_ConsentToken: Null Location ID.');
			return ;
		}
		$this->_locationID = $locationID;
	}

	/*private*/
	var $_context;
	/**
     * Returns the application context that was originally passed
     * to the sign-in request, if any.
     */
	/*public*/
	function getContext()
	{
		return $this->_context;
	}

	/**
     * Sets the application context.
     */
	/*private*/
	function setContext($context)
	{
		$this->_context = $context;
	}

	/*private*/
	var $_decodedtoken;
	/**
     * Gets the decoded token.
     */
	/*public*/
	function getDecodedToken()
	{
		return $this->_decodedtoken;
	}

	/**
     * Sets the decoded token.
     */
	/*private*/
	function setDecodedToken($decodedtoken)
	{
		$this->_decodedtoken = $decodedtoken;
	}

	/*private*/
	var $_token;

	/**
     * Gets the raw token.
     */
	/*public*/
	function getToken()
	{
		return $this->_token;
	}

	/**
     * Sets the raw token.
     */
	/*private*/
	function setToken($token)
	{
		$this->_token = $token;
	}

	/**
     * Makes a copy of the ConsentToken object.
     */
	/*private*/
	function copy($ct)
	{
		$this->_delegationtoken = $ct->_delegationtoken;
		$this->_refreshtoken = $ct->_refreshtoken;
		$this->_sessionkey = $ct->_sessionkey;
		$this->_expiry = $ct->_expiry;
		$this->_offers = $ct->_offers;
		$this->_offers_string = $ct->_offers_string;
		$this->_locationID = $ct->_locationID;
		$this->_decodedtoken = $ct->_decodedtoken;
		$this->_token = $ct->_token;
	}

	var $_error = false;

	function setError($str)
	{
		$this->_error = $str;
	}

	function getError()
	{
		if ($this->_error !== false)
		{
			return $this->_error;
		}
	}
}

class WindowsLiveLogin
{
	/* Implementation of basic methods for Web Authentication support. */

	/*private*/
	var $_debug = false;

	/**
     * Stub implementation for logging errors. If you want to enable
     * debugging output, set this to true. In this implementation
     * errors will be logged using the PHP error_log function.
     */
	/*public*/
	function setDebug($debug)
	{
		$this->_debug = $debug;
	}

	/**
     * Stub implementation for logging errors. By default, this
     * function does nothing if the debug flag has not been set with
     * setDebug. Otherwise, errors are logged using the PHP error_log
     * function.
     */
	/*private*/
	function debug($string)
	{
		if ($this->_debug) {
			echo "$string<br>";
			error_log($string);
		}
	}

	/**
     * Stub implementation for handling a fatal error.
     */
	/*private*/
	function fatal($string)
	{
		$this->debug($string);
		//throw new Exception($string);
		$this->setError($string);
	}

	/**
     * Initialize the WindowsLiveLogin module with the application ID,
     *   secret key, and security algorithm.
     *
     *  We recommend that you employ strong measures to protect the
     *  secret key. The secret key should never be exposed to the Web
     *  or other users.
     *
     *  Be aware that if you do not supply these settings at
     *  initialization time, you may need to set the corresponding
     *  properties manually.
     *
     *  For Delegated Authentication, you may optionally specify the
     *  privacy policy URL and return URL. If you do not specify these
     *  values here, the default values that you specified when you
     *  registered your application will be used.
     *
     *  The 'force_delauth_nonprovisioned' flag also indicates whether
     *  your application is registered for Delegated Authentication
     *  (that is, whether it uses an application ID and secret key). We
     *  recommend that your Delegated Authentication application always
     *  be registered for enhanced security and functionality.
     */
	public function __construct(
					$appid=null, $secret=null, $securityalgorithm=null,
					$force_delauth_nonprovisioned=null,
					$policyurl=null, $returnurl=null
				)
	{
		$this->setForceDelAuthNonProvisioned($force_delauth_nonprovisioned);

		if ($appid) {
			$this->setAppId($appid);
		}
		if ($secret) {
			$this->setSecret($secret);
		}
		if ($securityalgorithm) {
			$this->setSecurityAlgorithm($securityalgorithm);
		}
		if ($policyurl) {
			$this->setPolicyUrl($policyurl);
		}
		if ($returnurl) {
			$this->setReturnUrl($returnurl);
		}
	}

	/**
     * Initialize the WindowsLiveLogin module from a settings file.
     *
     *  'settingsFile' specifies the location of the XML settings file
     *  that contains the application ID, secret key, and security
     *  algorithm. The file is of the following format:
     *
     *  <windowslivelogin>
     *    <appid>APPID</appid>
     *    <secret>SECRET</secret>
     *    <securityalgorithm>wsignin1.0</securityalgorithm>
     *  </windowslivelogin>
     *
     *  In a Delegated Authentication scenario, you may also specify
     *  'returnurl' and 'policyurl' in the settings file, as shown in the
     *  Delegated Authentication samples.
     *
     *  We recommend that you store the WindowsLiveLogin settings file
     *  in an area on your server that cannot be accessed through the
     *  Internet. This file contains important confidential information.
     */
	/*public static*/
	function initFromXml($settingsFile)
	{
		$o = new WindowsLiveLogin();
		$settings = $o->parseSettings($settingsFile);

		if (@$settings['debug'] == 'true') {
			$o->setDebug(true);
		}
		else {
			$o->setDebug(false);
		}

		if (@$settings['force_delauth_nonprovisioned'] == 'true') {
			$o->setForceDelAuthNonProvisioned(true);
		}
		else {
			$o->setForceDelAuthNonProvisioned(false);
		}

		$o->setAppId(@$settings['appid']);
		$o->setSecret(@$settings['secret']);
		$o->setOldSecret(@$settings['oldsecret']);
		$o->setOldSecretExpiry(@$settings['oldsecretexpiry']);
		$o->setSecurityAlgorithm(@$settings['securityalgorithm']);
		$o->setPolicyUrl(@$settings['policyurl']);
		$o->setReturnUrl(@$settings['returnurl']);
		$o->setBaseUrl(@$settings['baseurl']);
		$o->setSecureUrl(@$settings['secureurl']);
		$o->setConsentBaseUrl(@$settings['consenturl']);
		return $o;
	}

	/*private*/
	var $_appid;

	/**
     * Sets the application ID. Use this method if you did not specify
     * an application ID at initialization.
     **/
	/*public*/
	function setAppId($appid)
	{
		$_force_delauth_nonprovisioned = $this->_force_delauth_nonprovisioned;
		if (!$appid) {
			if ($_force_delauth_nonprovisioned) {
				return;
			}
			$this->fatal('Error: setAppId: Null application ID.');
		}
		if (!preg_match('/^\w+$/', $appid)) {
			$this->fatal("Error: setAppId: Application ID must be alpha-numeric: $appid");
		}
		$this->_appid = $appid;
	}

	/**
     * Returns the application ID.
     */
	/*public*/
	function getAppId()
	{
		if (!$this->_appid) {
			$this->fatal('Error: getAppId: Application ID was not set. Aborting.');
		}
		return $this->_appid;
	}

	/*private*/
	var $_signkey;
	/*private*/
	var $_cryptkey;

	/**
     * Sets your secret key. Use this method if you did not specify
     * a secret key at initialization.
     */
	/*public*/
	function setSecret($secret)
	{
		$_force_delauth_nonprovisioned = $this->_force_delauth_nonprovisioned;
		if (!$secret || (strlen($secret) < 16)) {
			if ($_force_delauth_nonprovisioned) {
				return;
			}
			$this->fatal("Error: setSecret: Secret key is expected to be non-null and longer than 16 characters.");
		}

		$this->_signkey  = $this->derive($secret, "SIGNATURE");
		$this->_cryptkey = $this->derive($secret, "ENCRYPTION");
	}

	/*private*/
	var $_oldsignkey;
	/*private*/
	var $_oldcryptkey;

	/**
     * Sets your old secret key.
     *
     * Use this property to set your old secret key if you are in the
     * process of transitioning to a new secret key. You may need this
     * property because the Windows Live ID servers can take up to
     * 24 hours to propagate a new secret key after you have updated
     * your application settings.
     *
     * If an old secret key is specified here and has not expired
     * (as determined by the oldsecretexpiry setting), it will be used
     * as a fallback if token decryption fails with the new secret
     * key.
     */
	/*public*/
	function setOldSecret($secret)
	{
		if (!$secret) {
			return;
		}
		if (strlen($secret) < 16) {
			$this->fatal("Error: setOldSecret: Secret key is expected to be non-null and longer than 16 characters.");
		}

		$this->_oldsignkey  = $this->derive($secret, "SIGNATURE");
		$this->_oldcryptkey = $this->derive($secret, "ENCRYPTION");
	}

	/*private*/
	var $_oldsecretexpiry;

	/**
     * Sets the expiry time for your old secret key.
     *
     * After this time has passed, the old secret key will no longer be
     * used even if token decryption fails with the new secret key.
     *
     * The old secret expiry time is represented as the number of seconds
     * elapsed since January 1, 1970.
     */
	/*public*/
	function setOldSecretExpiry($timestamp)
	{
		if (!$timestamp) {
			return;
		}

		if (!preg_match('/^\d+$/', $timestamp) || ($timestamp <= 0)) {
			$this->fatal('Error: setOldSecretExpiry Invalid timestamp: '
			. $timestamp);
		}

		$this->_oldsecretexpiry = $timestamp;
	}

	/**
     * Gets the old secret key expiry time.
     */
	/*public*/
	function getOldSecretExpiry()
	{
		return $this->_oldsecretexpiry;
	}

	/*private*/
	var $_securityalgorithm;

	/**
     * Sets the version of the security algorithm being used.
     */
	/*public*/
	function setSecurityAlgorithm($securityalgorithm)
	{
		$this->_securityalgorithm = $securityalgorithm;
	}

	/**
     * Gets the version of the security algorithm being used.
     */
	/*public*/
	function getSecurityAlgorithm()
	{
		$securityalgorithm = $this->_securityalgorithm;
		if (!$securityalgorithm) {
			return 'wsignin1.0';
		}
		return $securityalgorithm;
	}

	/*private*/
	var $_force_delauth_nonprovisioned;

	/**
     * Sets a flag that indicates whether Delegated Authentication
     * is non-provisioned (i.e. does not use an application ID or secret
     * key).
     */
	/*public*/
	function setForceDelAuthNonProvisioned($force_delauth_nonprovisioned)
	{
		$this->_force_delauth_nonprovisioned = $force_delauth_nonprovisioned;
	}

	/*private*/
	var $_policyurl;

	/**
     * Sets the privacy policy URL if you did not provide one at initialization time.
     */
	/*public*/
	function setPolicyUrl($policyurl)
	{
		$_force_delauth_nonprovisioned = $this->_force_delauth_nonprovisioned;
		if (!$policyurl) {
			if ($_force_delauth_nonprovisioned) {
				$this->fatal("Error: setPolicyUrl: Null policy URL given.");
			}
		}
		$this->_policyurl = $policyurl;
	}

	/**
     * Gets the privacy policy URL for your site.
     */
	/*public*/
	function getPolicyUrl()
	{
		$policyurl = $this->_policyurl;
		$_force_delauth_nonprovisioned = $this->_force_delauth_nonprovisioned;
		if (!$policyurl) {
			$this->debug("Warning: In the initial release of Delegated Auth, a Policy URL must be configured in the SDK for both provisioned and non-provisioned scenarios.");
			if ($_force_delauth_nonprovisioned) {
				$this->fatal("Error: getPolicyUrl: Policy URL must be set in a Del Auth non-provisioned scenario. Aborting.");
			}
		}
		return $policyurl;
	}

	/*private*/
	var $_returnurl;

	/**
     * Sets the return URL--the URL on your site to which the consent
     *  service redirects users (along with the action, consent token,
     *  and application context) after they have successfully provided
     *  consent information for Delegated Authentication. This value will
     *  override the return URL specified during registration.
     */
	/*public*/
	function setReturnUrl($returnurl)
	{
		$_force_delauth_nonprovisioned = $this->_force_delauth_nonprovisioned;
		if (!$returnurl) {
			if ($_force_delauth_nonprovisioned) {
				$this->fatal("Error: setReturnUrl: Null return URL given.");
			}
		}
		$this->_returnurl = $returnurl;
	}

	/**
     * Returns the return URL of your site.
     */
	/*public*/
	function getReturnUrl()
	{
		$_force_delauth_nonprovisioned = $this->_force_delauth_nonprovisioned;
		$returnurl = $this->_returnurl;
		if (!$returnurl) {
			if ($_force_delauth_nonprovisioned) {
				$this->fatal("Error: getReturnUrl: Return URL must be set in a Del Auth non-provisioned scenario. Aborting.");
			}
		}
		return $returnurl;
	}

	/*private*/
	var $_baseurl;

	/**
     * Sets the base URL to use for the Windows Live Login server.
     *  You should not have to change this property. Furthermore, we recommend
     *  that you use the Sign In control instead of the URL methods
     *  provided here.
     */
	/*public*/
	function setBaseUrl($baseurl)
	{
		$this->_baseurl = $baseurl;
	}

	/**
     * Gets the base URL to use for the Windows Live Login server.
     * You should not have to use this property. Furthermore, we recommend
     * that you use the Sign In control instead of the URL methods
     * provided here.
     */
	/*public*/
	function getBaseUrl()
	{
		$baseurl = $this->_baseurl;
		if (!$baseurl) {
			return "http://login.live.com/";
		}
		return $baseurl;
	}

	/*private*/
	var $_secureurl;

	/**
     * Sets the secure (HTTPS) URL to use for the Windows Live Login
     * server. You should not have to change this property.
     */
	/*public*/
	function setSecureUrl($secureurl)
	{
		$this->_secureurl = $secureurl;
	}

	/**
     * Gets the secure (HTTPS) URL to use for the Windows Live Login
     * server. You should not have to use this functon directly.
     */
	/*public*/
	function getSecureUrl()
	{
		$secureurl = $this->_secureurl;
		if (!$secureurl) {
			return "https://login.live.com/";
		}
		return $secureurl;
	}

	/*private*/
	var $_consenturl;

	/**
     * Sets the Consent Base URL to use for the Windows Live Consent
     * server. You should not have to use or change this property directly.
     */
	/*public*/
	function setConsentBaseUrl($consenturl)
	{
		$this->_consenturl = $consenturl;
	}

	/**
     * Gets the URL to use for the Windows Live Consent server. You
     * should not have to use or change this directly.
     */
	/*public*/
	function getConsentBaseUrl()
	{
		$consenturl = $this->_consenturl;
		if (!$consenturl) {
			return "https://consent.live.com/";
		}
		return $consenturl;
	}

	/* Methods for Web Authentication support. */

	/**
     * Returns the sign-in URL to use for the Windows Live Login server.
     * We recommend that you use the Sign In control instead.
     *
     * If you specify it, 'context' will be returned as-is in the sign-in
     * response for site-specific use.
     */
	/*public*/
	function getLoginUrl($context=null, $market=null)
	{
		$url  = $this->getBaseUrl();
		$url .= 'wlogin.srf?appid=' . $this->getAppId();
		$url .= '&alg=' . $this->getSecurityAlgorithm();
		$url .= ($context ? '&appctx=' . urlencode($context) : '');
		$url .= ($market ? '&mkt=' . urlencode($market) : '');
		return $url;
	}

	/**
     * Returns the sign-out URL to use for the Windows Live Login server.
     * We recommend that you use the Sign In control instead.
     */
	/*public*/
	function getLogoutUrl($market=null)
	{
		$url = $this->getBaseUrl();
		$url .= "logout.srf?appid=" . $this->getAppId();
		$url .= ($market ? '&mkt=' . urlencode($market) : '');
		return $url;
	}

	/**
     * Processes the sign-in response from Windows Live Login server.
     *
     * @param query contains the preprocessed POST query, a map of
     *              Strings to an an array of Strings, such as that
     *              returned by ServletRequest.getParameterMap().
     * @return      a User object on successful sign-in; otherwise null.
     */
	/*public*/
	function processLogin($query)
	{
		$action = @$query['action'];
		if ($action != 'login') {
			$this->debug("Warning: processLogin: query action ignored: $action");
			return;
		}
		$token  = @$query['stoken'];
		$context = urldecode(@$query['appctx']);
		return $this->processToken($token, $context);
	}

	/**
     * Decodes and validates a Web Authentication token. Returns a User
     * object on success. If a context is passed in, it will be returned
     * as the context field in the User object.
     */
	/*public*/
	function processToken($token, $context=null)
	{
		if (!$token) {
			$this->debug('Error: processToken: Invalid token specified.');
			return;
		}

		$decodedToken = $this->decodeAndValidateToken($token);
		if (!$decodedToken) {
			$this->debug("Error: processToken: Failed to decode/validate token: $token");
			return;
		}

		$parsedToken = $this->parse($decodedToken);
		if (!$parsedToken) {
			$this->debug("Error: processToken: Failed to parse token after decoding: $token");
			return;
		}

		$appid = $this->getAppId();
		$tokenappid = @$parsedToken['appid'];
		if ($appid != $tokenappid) {
			$this->debug("Error: processToken: Application ID in token did not match ours: $tokenappid, $appid");
			return;
		}

		$user = null;

		//try {
		$user = new WLL_User(@$parsedToken['ts'],
			@$parsedToken['uid'],
			@$parsedToken['flags'],
			$context, $token);
		//} catch (Exception $e) {
		if ($user->getError() !== false)
			$this->debug("Error: processToken: Contents of token considered invalid: " + $user->getError());
		//}

		return $user;
	}

	/**
     * Returns an appropriate content type and body response that the
     * application handler can return to signify a successful sign-out
     * from the application.
     *
     * When a user signs out of Windows Live or a Windows Live
     * application, a best-effort attempt is made at signing the user out
     * from all other Windows Live applications the user might be signed
     * in to. This is done by calling the handler page for each
     * application with 'action' set to 'clearcookie' in the query
     * string. The application handler is then responsible for clearing
     * any cookies or data associated with the sign-in. After successfully
     * signing the user out, the handler should return a GIF (any GIF)
     * image as response to the 'action=clearcookie' query.
     */
	/*public*/
	function getClearCookieResponse()
	{
		$type = "image/gif";
		$content = "R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7";
		$content = base64_decode($content);
		return array($type, $content);
	}

	/* Methods for Delegated Authentication. */

	/*
	* Returns the consent URL to use for Delegated Authentication for
	* the given comma-delimited list of offers.
	*
	* If you specify it, 'context' will be returned as-is in the consent
	* response for site-specific use.
	*
	* The registered/configured return URL can also be overridden by
	* specifying 'ru' here.
	*
	* You can change the language in which the consent page is displayed
	* by specifying a culture ID (For example, 'fr-fr' or 'en-us') in the
	* 'market' parameter.
	*/
	/*public*/
	function getConsentUrl($offers, $context=null, $ru=null, $market=null)
	{
		if (!$offers) {
			//throw new Exception('Error: getConsentUrl: Invalid offers list.');
			$this->setError('Error: getConsentUrl: Invalid offers list.');
			return false;
		}
		$url  = $this->getConsentBaseUrl();
		$url .= 'Delegation.aspx?ps=' . urlencode($offers);
		$ru = ($ru ? $ru : $this->getReturnUrl());
		$url .= ($ru ? '&ru=' . urlencode($ru) : '');
		$pl = $this->getPolicyUrl();
		$url .= ($pl ? '&pl=' . urlencode($pl) : '');
		$url .= ($market ? '&mkt=' . urlencode($market) : '');
		if (!$this->_force_delauth_nonprovisioned) {
			$url .= '&app=' . $this->getAppVerifier();
		}
		$url .= ($context ? '&appctx=' . urlencode($context) : '');
		return $url;
	}

	/*
	* Returns the URL to use to download a new consent token, given the
	* offers and refresh token.
	*
	* The registered/configured return URL can also be overridden by
	* specifying 'ru' here.
	*/
	/*public*/
	function getRefreshConsentTokenUrl($offers, $refreshtoken, $ru=null)
	{
		$_force_delauth_nonprovisioned = $this->_force_delauth_nonprovisioned;
		if (!$offers) {
			//throw new Exception('Error: getRefreshConsentTokenUrl: Invalid offers list.');
			$this->setError('Error: getRefreshConsentTokenUrl: Invalid offers list.');
			return false;
		}
		if (!$refreshtoken) {
			//throw new Exception('Error: getRefreshConsentTokenUrl: Invalid refresh token.');
			$this->setError('Error: getRefreshConsentTokenUrl: Invalid refresh token.');
			return false;
		}

		$url  = $this->getConsentBaseUrl();
		$url .= 'RefreshToken.aspx?ps=' . urlencode($offers);
		$url .= '&reft=' . $refreshtoken;
		$ru = ($ru ? $ru : $this->getReturnUrl());
		$url .= ($ru ? '&ru=' . urlencode($ru) : '');

		if (!$this->_force_delauth_nonprovisioned) {
			$url .= '&app=' . $this->getAppVerifier();
		}

		return $url;
	}

	/*
	* Returns the URL for the consent-management user interface.
	*
	* You can change the language in which the consent page is displayed
	* by specifying a culture ID (For example, 'fr-fr' or 'en-us') in the
	* 'market' parameter.
	*/
	/*public*/
	function getManageConsentUrl($market=null)
	{
		$url  = $this->getConsentBaseUrl();
		$url .= 'ManageConsent.aspx';
		$url .= ($market ? '?mkt=' . urlencode($market) : '');
		return $url;
	}

	/*
	* Processes the POST response from the Delegated Authentication
	* service after a user has granted consent. The processConsent
	* function extracts the consent token string and returns the result
	* of invoking the processConsentToken method.
	*/
	/*public*/
	function processConsent($query)
	{
		$action = @$query['action'];
		if ($action != 'delauth') {
			$this->debug("Warning: processConsent: query action ignored: $action");
			return;
		}
		$responsecode = @$query['ResponseCode'];
		if ($responsecode != 'RequestApproved') {
			$this->debug("Warning: processConsent: consent was not successfully granted: $responsecode");
			return;
		}
		$token  = @$query['ConsentToken'];
		$context = urldecode(@$query['appctx']);
		return $this->processConsentToken($token, $context);
	}

	/*
	* Processes the consent token string that is returned in the POST
	* response by the Delegated Authentication service after a
	* user has granted consent.
	*/
	/*public*/
	function processConsentToken($token, $context=null)
	{
		if (!$token) {
			$this->debug('Error: processConsentToken: Null token.');
			return;
		}

		$decodedToken = $token;
		$parsedToken = $this->parse(urldecode($decodedToken));
		if (!$parsedToken) {
			$this->debug("Error: processConsentToken: Failed to parse token: $token");
			return;
		}

		$eact = @$parsedToken['eact'];
		if ($eact) {
			$decodedToken = $this->decodeAndValidateToken($eact);
			if (!$decodedToken) {
				$this->debug("Error: processConsentToken: Failed to decode/validate token: $token");
				return;
			}
			$parsedToken = $this->parse($decodedToken);
			if (!$parsedToken) {
				$this->debug("Error: processConsentToken: Failed to parse token after decoding: $token");
				return;
			}
			$decodedToken = urlencode($decodedToken);
		}

		$consenttoken = null;

		//try {
		$consenttoken = new WLL_ConsentToken($this,
			@$parsedToken['delt'],
			@$parsedToken['reft'],
			@$parsedToken['skey'],
			@$parsedToken['exp'],
			@$parsedToken['offer'],
			@$parsedToken['lid'],
			$context, $decodedToken, $token);
		//} catch (Exception $e) {
		if($consenttoken->getError() !== false)
			$this->debug("Error: processConsentToken: Contents of token considered invalid: " + $consenttoken->getError());
		//}
		return $consenttoken;
	}

	/*
	* Attempts to obtain a new, refreshed token and return it. The
	* original token is not modified.
	*/
	/*public*/
	function refreshConsentToken($token, $ru=null)
	{
		if (!$token) {
			$this->debug("Error: refreshConsentToken: Null consent token.");
			return;
		}
		$this->refreshConsentToken2($token->getOffersString(), $token->getRefreshToken(), $ru);
	}

	/*
	* Helper function to obtain a new, refreshed token and return it.
	* The original token is not modified.
	*/
	/*public*/
	function refreshConsentToken2($offers_string, $refreshtoken, $ru=null)
	{
		$body = $this->fetch($this->getRefreshConsentTokenUrl($offers_string, $refreshtoken, $ru));
		if (!$body) {
			$this->debug("Error: refreshConsentToken2: Failed to obtain a new token.");
			return;
		}

		preg_match('/\{"ConsentToken":"(.*)"\}/', $body, $matches);
		if(count($matches) == 2) {
			return $matches[1];
		}
		else {
			$this->debug("Error: refreshConsentToken2: Failed to extract token: $body");
			return;
		}
	}

	/* Common methods. */

	/*
	* Decodes and validates the token.
	*/
	/*public*/
	function decodeAndValidateToken($token, $cryptkey=null, $signkey=null,
	$internal_allow_recursion=true)
	{
		if (!$cryptkey) {
			$cryptkey = $this->_cryptkey;
		}
		if (!$signkey) {
			$signkey = $this->_signkey;
		}

		$haveoldsecret = false;
		$oldsecretexpiry = $this->getOldSecretExpiry();
		$oldcryptkey = $this->_oldcryptkey;
		$oldsignkey = $this->_oldsignkey;

		if ($oldsecretexpiry and (time() < $oldsecretexpiry)) {
			if ($oldcryptkey and $oldsignkey) {
				$haveoldsecret = true;
			}
		}
		$haveoldsecret = ($haveoldsecret and $internal_allow_recursion);

		$stoken = $this->decodeToken($token, $cryptkey);

		if ($stoken) {
			$stoken = $this->validateToken($stoken, $signkey);
		}

		if (!$stoken and $haveoldsecret) {
			$this->debug("Warning: Failed to validate token with current secret, attempting old secret.");
			$stoken =
			$this->decodeAndValidateToken($token, $oldcryptkey, $oldsignkey, false);
		}

		return $stoken;
	}

	/**
     * Decodes the given token string; returns undef on failure.
     *
     * First, the string is URL-unescaped and base64 decoded.
     * Second, the IV is extracted from the first 16 bytes of the string.
     * Finally, the string is decrypted using the encryption key.
     */
	/*public*/
	function decodeToken($token, $cryptkey=null)
	{
		if (!$cryptkey) {
			$cryptkey = $this->_cryptkey;
		}
		if (!$cryptkey) {
			$this->fatal("Error: decodeToken: Secret key was not set. Aborting.");
		}

		$ivLen = 16;
		$token = $this->u64($token);
		$len = strlen($token);

		if (!$token || ($len <= $ivLen) || (($len % $ivLen) != 0)) {
			$this->debug("Error: decodeToken: Attempted to decode invalid token.");
			return;
		}

		$iv = substr($token, 0, 16);
		$crypted = substr($token, 16);

		return openssl_decrypt($crypted, "AES-128-CBC", $cryptkey, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
	}

	/**
     * Creates a signature for the given string by using the signature
     * key.
     */
	/*public*/
	function signToken($token, $signkey=null)
	{
		if (!$signkey) {
			$signkey = $this->_signkey;
		}
		if (!$signkey) {
			$this->fatal("Error: signToken: Secret key was not set. Aborting.");
		}

		if (!$token) {
			$this->debug("Attempted to sign null token.");
			return;
		}

		return hash_hmac("sha256", $token, $signkey, true);
	}

	/**
     * Extracts the signature from the token and validates it.
     */
	/*public*/
	function validateToken($token, $signkey=null)
	{
		if (!$signkey) {
			$signkey = $this->_signkey;
		}
		if (!$token) {
			$this->debug("Error: validateToken: Invalid token.");
			return;
		}

		$split = explode("&sig=", $token);
		if (count($split) != 2) {
			$this->debug("ERROR: validateToken: Invalid token: $token");
			return;
		}
		list($body, $sig) = $split;

		$sig = $this->u64($sig);
		if (!$sig) {
			$this->debug("Error: validateToken: Could not extract signature from token.");
			return;
		}

		$sig2 = $this->signToken($body, $signkey);
		if (!$sig2) {
			$this->debug("Error: validateToken: Could not generate signature for the token.");
			return;
		}


		if ($sig == $sig2) {
			return $token;
		}

		$this->debug("Error: validateToken: Signature did not match.");
		return;
	}

	/* Implementation of the methods needed to perform Windows Live
	application verification as well as trusted sign-in. */

	/**
     * Generates an application verifier token. An IP address can
     * optionally be included in the token.
     */
	/*public*/
	function getAppVerifier($ip=null)
	{
		$token  = 'appid=' . $this->getAppId() . '&ts=' . $this->getTimestamp();
		$token .= ($ip ? "&ip={$ip}" : '');
		$token .= '&sig=' . $this->e64($this->signToken($token));
		return urlencode($token);
	}

	/**
     * Returns the URL that is required to retrieve the application
     * security token.
     *
     * By default, the application security token is generated for
     * the Windows Live site; a specific Site ID can optionally be
     * specified in 'siteid'. The IP address can also optionally be
     * included in 'ip'.
     *
     * If 'js' is nil, a JavaScript Output Notation (JSON) response is
     * returned in the following format:
     *
     *  {"token":"<value>"}
     *
     * Otherwise, a JavaScript response is returned. It is assumed that
     * WLIDResultCallback is a custom function implemented to handle the
     * token value:
     *
     * WLIDResultCallback("<tokenvalue>");
     */
	/*public*/
	function getAppLoginUrl($siteid=null, $ip=null, $js=null)
	{
		$url  = $this->getSecureUrl();
		$url .= 'wapplogin.srf?app=' . $this->getAppVerifier($ip);
		$url .= '&alg=' . $this->getSecurityAlgorithm();
		$url .= ($siteid ? "&id=$siteid" : '');
		$url .= ($js ? '&js=1' : '');
		return $url;
	}

	/**
     * Retrieves the application security token for application
     * verification from the application sign-in URL.
     *
     * By default, the application security token will be generated for
     * the Windows Live site; a specific Site ID can optionally be
     * specified in 'siteid'. The IP address can also optionally be
     * included in 'ip'.
     *
     * Implementation note: The application security token is downloaded
     * from the application sign-in URL in JSON format:
     *
     * {"token":"<value>"}
     *
     * Therefore we must extract <value> from the string and return it as
     *  seen here.
     */
	/*public*/
	function getAppSecurityToken($siteid=null, $ip=null)
	{
		$body = $this->fetch($this->getAppLoginUrl($siteid, $ip));
		if (!$body) {
			$this->debug("Error: getAppSecurityToken: Could not fetch the application security token.");
			return;
		}

		preg_match('/\{"token":"(.*)"\}/', $body, $matches);
		if(count($matches) == 2) {
			return $matches[1];
		}
		else {
			$this->debug("Error: getAppSecurityToken: Failed to extract token: $body");
			return;
		}
	}

	/**
     * Returns a string that can be passed to the getTrustedParams
     * function as the 'retcode' parameter. If this is specified as the
     * 'retcode', the application will be used as return URL after it
     * finishes trusted sign-in.
     */
	/*public*/
	function getAppRetCode()
	{
		return 'appid=' . $this->getAppId();
	}

	/**
     * Returns a table of key-value pairs that must be posted to the
     * sign-in URL for trusted sign-in. Use HTTP POST to do this. Be aware
     * that the values in the table are neither URL nor HTML escaped and
     * may have to be escaped if you are inserting them in code such as
     * an HTML form.
     *
     * The user to be trusted on the local site is passed in as string
     * 'user'.
     *
     *  Optionally, 'retcode' specifies the resource to which successful
     *  sign-in is redirected, such as Windows Live Mail, and is typically
     *  a string in the format 'id=2000'. If you pass in the value from
     *  getAppRetCode instead, sign-in will be redirected to the
     *  application. Otherwise, an HTTP 200 response is returned.
     */
	/*public*/
	function getTrustedParams($user, $retcode=null)
	{
		$token  = $this->getTrustedToken($user);
		if (!$token) {
			return;
		}
		$token = "<wst:RequestSecurityTokenResponse xmlns:wst=\"http://schemas.xmlsoap.org/ws/2005/02/trust\"><wst:RequestedSecurityToken><wsse:BinarySecurityToken xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">$token</wsse:BinarySecurityToken></wst:RequestedSecurityToken><wsp:AppliesTo xmlns:wsp=\"http://schemas.xmlsoap.org/ws/2004/09/policy\"><wsa:EndpointReference xmlns:wsa=\"http://schemas.xmlsoap.org/ws/2004/08/addressing\"><wsa:Address>uri:WindowsLiveID</wsa:Address></wsa:EndpointReference></wsp:AppliesTo></wst:RequestSecurityTokenResponse>";

		$params = array();
		$params['wa']      = $this->getSecurityAlgorithm();
		$params['wresult'] = $token;

		if ($retcode) {
			$params['wctx'] = $retcode;
		}

		return $params;
	}

	/**
     * Returns the trusted sign-in token in the format that is needed by a
     * control doing trusted sign-in.
     *
     * The user to be trusted on the local site is passed in as string
     * 'user'.
     */
	/*public*/
	function getTrustedToken($user)
	{
		if (!$user) {
			$this->debug('Error: getTrustedToken: Null user specified.');
			return;
		}

		$token  = "appid=" . $this->getAppId() . "&uid=" . urlencode($user)
		. "&ts=". $this->getTimestamp();
		$token .= "&sig="  . $this->e64($this->signToken($token));
		return urlencode($token);
	}

	/**
     * Returns the trusted sign-in URL to use for Windows Live Login server.
     */
	/*public*/
	function getTrustedLoginUrl()
	{
		return $this->getSecureUrl() . 'wlogin.srf';
	}

	/**
     * Returns the trusted sign-in URL to use for Windows Live
     *  Login server.
     */
	/*public*/
	function getTrustedLogoutUrl()
	{
		return $this->getSecureUrl() . "logout.srf?appid=" + $this->getAppId();
	}

	/* Helper methods */

	/**
     * Function to parse the settings file.
     */
	/*private*/
	function parseSettings($settingsFile)
	{
		$settings = array(
			'appid' => '00163FFF8000E2C5',
			'secret' => '12345678901234567890',
			'securityalgorithm' => 'wsignin1.0',
		);

		return $settings;

		$doc = new DOMDocument();
		if (!$doc->load($settingsFile)) {
			$this->fatal("Error: parseSettings: Error while reading $settingsFile");
		}

		$nl = $doc->getElementsByTagName('windowslivelogin');
		if($nl->length != 1) {
			$this->fatal("error: parseSettings: Failed to parse settings file:"
			. $settingsFile);
		}

		$topnode = $nl->item(0);
		foreach ($topnode->childNodes as $node) {
			if ($node->nodeType == XML_ELEMENT_NODE) {
				$firstChild = $node->firstChild;
				if (!$firstChild) {
					$this->fatal("error: parseSettings: Failed to parse settings file:"
					. $settingsFile);
				}
				$settings[$node->nodeName] = $firstChild->nodeValue;
			}
		}

		return $settings;
	}

	/**
     * Derives the key, given the secret key and prefix as described in the
     * Web Authentication SDK documentation.
     */
	/*private*/
	function derive($secret, $prefix)
	{
		if (!$secret || !$prefix) {
			$this->fatal("Error: derive: secret or prefix is null.");
		}

		$keyLen = 16;
		$key = $prefix . $secret;

		$key = hash("sha256", $key, true);

		if (!$key || (strlen($key) < $keyLen)) {
			$this->debug("Error: derive: Unable to derive key.");
			return;
		}

		return substr($key, 0, $keyLen);
	}

	/**
     * Parses query string and returns a hash.
     *
     * If a hash ref is passed in from CGI->Var, it is dereferenced and
     * returned.
     */
	/*private*/
	function parse($input)
	{
		if (!$input) {
			$this->debug("Error: parse: Null input.");
			return;
		}

		$input = explode('&', $input);
		$pairs = array();

		foreach ($input as $pair) {
			$kv = explode('=', $pair);
			if (count($kv) != 2) {
				$this->debug("Error: parse: Bad input to parse: " . $pair);
				return;
			}
			$pairs[$kv[0]] = $kv[1];
		}

		return $pairs;
	}

	/**
     * Generates a time stamp suitable for the application verifier
     * token.
     */
	/*private*/
	function getTimestamp()
	{
		return time();
	}

	/**
     * Base64-encodes and URL-escapes a string.
     */
	/*private*/
	function e64($input)
	{
		if (is_null($input)) {
			return;
		}
		return urlencode(base64_encode($input));
	}

	/**
     * URL-unescapes and Base64-decodes a string.
     */
	/*private*/
	function u64($input)
	{
		if(is_null($input))
			return;
		return base64_decode(urldecode($input));
	}

	/**
     * Fetches the contents given a URL.
     */
	/*private*/
	function fetch($url)
	{
		/*
		if (!($handle = fopen($url, "rb"))) {
			WindowsLiveLogin::debug("error: fetch: Could not open url: $url");
			return;
		}

		if (!($contents = stream_get_contents($handle))) {
			WindowsLiveLogin::debug("Error: fetch: Could not read from url: $url");
		}

		fclose($handle);
		*/

		//$str = $url."\n\n".$contents."\n\n\n";
		//file_put_contents(__FILE__ . '.ftech.log', $str, FILE_APPEND);

		$http = new \Bitrix\Main\Web\HttpClient([
			"redirect" => false,
		]);
		$contents = $http->get($url);

		return $contents;
	}

	var $_error = false;

	function setError($str)
	{
		$this->_error = $str;
	}

	function getError()
	{
		if ($this->_error !== false)
		{
			return $this->_error;
		}
	}

	function OnExternalAuthList()
	{
		$arResult = Array();
		if (
			COption::GetOptionString('main', 'new_user_registration', 'Y') == 'Y' &&
			COption::GetOptionString('main', 'auth_liveid', 'N') == 'Y'
		)
		{
			$arResult[] = Array(
				'ID' => 'LIVEID',
				'NAME' => 'LiveID',
				);
		}
		return $arResult;
	}

	public static function IsAvailable()
	{
		return function_exists('hash');
	}
}
