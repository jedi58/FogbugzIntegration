<?php
/**
 *
 */
class FogbugzIntegration {
	/**
	 * @var string The URL to use when connecting to the FogBugz API
	 * Example: example.fogbugz.com
	 */
	protected $api_url;
	/**
	 * @var string The token used to make authenticated requests
	 */
	protected $token;
	/**
	 * @var string Stores the last error received from the API
	 */
	protected $last_error;
	/**
	 * The default constructor for FogBugz logs in to the API and stores
	 * the token
	 * @param string @api_url The URL of the FogBugz API to connect to
	 * @param string $email The email address of the user logging in to FogBugz. Optional.
	 * @param string $password The plaintext password of the user. Optional
	 */
	public function __construct($api_url, $email = '', $password = '')
	{
		$this->setApiUrl($api_url);
		$this->login($email, $password);
	}
	/**
	 *
	 */
	public function setApiUrl($value)
	{
		$this->api_url = $value;
	}
	/**
	 *
	 */
	public function getApiUrl()
	{
		return $this->api_url;
	}
	/**
	 *
	 */
	public function setToken($value)
	{
		$this->token = $value;
	}
	/**
	 *
	 */
	public function getToken()
	{
		return $this->token;
	}
	/**
	 *
	 */
	public function setLastError($value)
	{
		$this->last_error = $value;
	}
	/**
	 *
	 */
	public function getLastError()
	{
		return $this->last_error;
	}
	/**
	 * Determines if the Fogbugz API is accessible
	 * @return bool Returns true if the server can be accessed
	 */
	public function serverStatus()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->getApiUrl());
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		return (bool) $response;
	}
	/**
	 * Retrieve the authentication token for the user with the specified email
	 * address. This will access the database to get their FogBugz password and
	 * send with their email address to get their token.
	 * @param string $email The email address of the user logging in to FogBugz
	 * @param string $password The plaintext password of the user
	 */
	public function login($email, $password)
	{
		if (empty($this->api_url)) {
			throw new \Exception('URL for FogBugz API not specified');
		} elseif (empty($email) || empty($password)) {
			throw new \Exception('Credentials for use with the FogBugz API must be specified');
		}
		return $this->_sendRequest(
			'cmd=logon&email=' . 
			urlencode($email) . 
			'&password=' . 
			urlencode($password)
		);
	}
	/**
	 * Closes the current connection to the Fogbuz API
	 * @return 
	 */
	public function logout()
	{
	    $result = $this->_sendRequest('cmd=logoff');
	    $this->setToken('');
	    return $result;
	}
	/**
	 * Opens a new ticket in FogBugz with the specified details and title. If the optional
	 * parameters have no values supplied then they will default to the last used values.
	 * @param string $title The title of the new ticket
	 * @param string $description The contents of the new ticket
	 * @param string[] $options Additional settings to apply to the ticket
	 * @return int The ID of the new FogBugz ticket
	 */
	public function openTicket (
		$title, 
		$description, 
		$options = array()
	) {
		if (empty($title) || empty($description) ) {
			throw new \Exception('title and description must be specified');
		}
		$path = 'cmd=new&sTitle=' . urlencode($title) . 
			'&sEvent=' . urlencode($description);
		if (!empty($options)) {
			foreach($options as $option=>$value) {
				$path .= '&' . $this->convertParameter($option) . 
					'=' . urlencode($value);
			}
		}
		$result = $this->_sendRequest($path);
		return (int) $result->case->attributes()->ixBug;
	}
	/**
	 * Updates the content of an existing FogBugz ticket (adds a new "event")
	 * @param int $caseID The ID of the case to update
	 * @param string $content The contents of the ticket event
	 * @param string[] $options Additional settings to apply to the ticket
	 * @return int If request succeeds then returns ticket ID to confirm
	 */
	public function updateTicket($caseID, $content, $options = array())
	{
		if (empty($caseID) || empty($content) ) {
			throw new \Exception('caseID and content must be specified');
		}
		$path = 'cmd=edit&ixBug=' . intval($caseID) . 
					'&sEvent=' . urlencode($content);
		if (!empty($options)) {
			foreach($options as $option=>$value) {
				$path .= '&' . $this->convertParameter($option) . 
					'=' . urlencode($value);
			}
		}
		$result = $this->_sendRequest($path);
		return (int) $result->case->attributes()->ixBug;
	}
	/**
	 * Reopens an existing FogBugz ticket
	 * @param int $caseID The ID of the case to update
	 * @param string $content The contents of the ticket event (optional)
	 * @param string $owner The person to re-assign the ticket to, if left empty the owner remains the same
	 * @param string $priority The priority of the ticket
	 * @return int If request succeeds then returns ticket ID to confirm
	 */
	public function reopenTicket(
		$caseID,
		$options = array()
	) {
		if (empty($caseID)) {
			throw new \Exception('caseID must be specified');
		}
		$path = 'cmd=reopen&ixBug=' . intval($caseID);
		if (!empty($options)) {
			foreach($options as $option=>$value) {
				$path .= '&' . $this->convertParameter($option) . 
					'=' . urlencode($value);
			}
		}
		$result = $this->_sendRequest($path);
		return (int) $result->case->ixBug;
	}
	/**
	 * Resolve the specified ticket
	 * @param int $caseID The ID of the FogBugz case to resolve
	 * @return int The ID of the case that has been resolved
	 */
	public function resolveTicket($caseID)
	{
		if (empty($caseID)) {
			throw new \Exception('caseID must be specified');
		}
		$result = $this->_sendRequest('cmd=resolve&ixBug=' . intval($caseID));
		return (int) $result->case->ixBug;
	}
	/**
	 * Closes the specified ticket
	 * @param int $caseID The ID of the FogBugz case to close
	 * @return int The ID of the case that has been closed
	 */
	public function closeTicket($caseID)
	{
		if (empty($caseID)) {
			throw new \Exception('caseID must be specified');
		}
		$result = $this->_sendRequest('cmd=close&ixBug=' . intval($caseID));
		return (int) $result->case->ixBug;
	}
	/**
	 * Returns the case specified by $caseID
	 * @param int The ID of the case to return
	 * @return 
	 */
	public function getTicket($caseID)
	{
		if (empty($caseID)) {
			throw new \Exception('caseID must be specified');
		}
		$result = $this->_sendRequest(
			'cmd=search&q=' . intval($caseID) . 
			'&cols=fOpen,latestEvent,ixBugEvent,ixStatus,' . 
			'ixPersonAssignedTo,sPersonAssignedTo'
		);
		return $result->cases->case->events->event;
	}
	/**
	 * Retrieves details about a status type
	 * @param string $statusID The ID of the status to get details for
	 * @return 
	 */
	public function getTicketStatus($statusID)
	{
		if (empty($statusID)) {
			throw new \Exception('statusID must be specified');
		}
		$result = $this->_sendRequest(
			'cmd=viewStatus&ixStatus=' . urlencode($statusID)
		);
		return $result->status;
	}
	/**
	 * Returns an array of all areas within a project
	 * @return string[] The array of all areas indexed by ID
	 */
	public function getAllAreas($projectID)
	{
		if (empty($projectID)) {
			throw new \Exception('projectID must be specified');
		}
		$result = $this->_sendRequest(
			'cmd=listAreas&ixProject=' . urlencode($projectID)
		);
		$areas = array();
		for ($i = 0; $i < sizeof($result->areas->area); $i++) {
			$areas[intval($result->areas->area[$i]->ixArea)] = (string) $result->areas->area[$i]->sArea;
		}
		return $areas;
	}
	/**
	 * Returns a list of all issue categories available to the current user
	 * @return string[] The array of all categories
	 */
	public function getAllCategories()
	{
		$result = $this->_sendRequest('cmd=listCategories');
		$categories = array();
		for ($i = 0; $i < sizeof($result->categories->category); $i++) {
			$categories[intval($result->categories->category[$i]->ixCategory)] = (string) $result->categories->category[$i]->sCategory;
		}
		return $categories;
	}
	/**
	 * Retrieves a list of filters available to the current user
	 * @return string[] The array of all filters available
	 */
	public function getAllFilters()
	{
		$result = $this->_sendRequest('cmd=listFilters');
		$filters = array();
		if (!empty($result->filters->filter)) {
			foreach ($result->filters->filter as $filter) {
				$filters[] = array(
					$filter[0],
					$filter->attributes()->type,
					$filter->attributes()->sFilter
				);
			}
		}
		return $result->filters;
	}
	/**
	 * Returns an array of users name and email address indexed by ID
	 * @return int[string, string] The array of users
	 */
	public function getAllFogbugzUsers(
		$includeNormal = true,
		$includeVirtual = false,
		$includeCommunity = false
	) {
		$result = $this->_sendRequest('cmd=listPeople' . 
			($includeNormal ? '&fIncludeNormal=1' : '') . 
			($includeVirtual ? '&fIncludeVirtual=1' : '') . 
			($includeCommunity ? '&fIncludeCommunity=1' : ''));
		$people = array();
		if (!empty($result->people->person)) {
			foreach ($result->people->person as $person) {
				$people[intval($person->ixPerson)] = array(
					(string) $person->sFullName,
					(string) $person->sEmail
				);
			}
		}
		return $people;
	}
	/**
	 * Returns an array of all priorities
	 * @return string[] The array of all priorities
	 */
	public function getAllPriorities($raw = false)
	{
		$result = $this->_sendRequest('cmd=listPriorities');
		if (!$raw) {
			$priorities = array();
			for ($i = 0; $i < sizeof($result->priorities->priority); $i++) {
				$priorities[intval($result->priorities->priority[$i]->ixPriority)] = array(
					(string) $result->priorities->priority[$i]->sPriority,
					(string) $result->priorities->priority[$i]->fDefault == 'true' ? true : false
				);
			}
			return $priorities;
		} else {
			return $result;
		}
	}
	/**
	 * Returns an array of all projects
	 * @return string[] The array of all projects
	*/
	public function getProjectList ($raw = false)
	{
		$result = $this->_sendRequest('cmd=listProjects'); 
		if (!$raw) {
			$projects = array();
			for ($i = 0; $i < sizeof($result->projects->project); $i++) {
				$projects[intval($result->projects->project[$i]->ixProject)] = array(
					(string) $result->projects->project[$i]->sProject, 
					intval($result->projects->project[$i]->ixPersonOwner)
				);
			}
			return $projects;
		} else {
			return $result;
		}
	}
	/**
	 * Changes which filter is in use
	 * @param int $filterID The ID of the filter to switch to
	 * @return SimpleXMLElement The result of setting the filter
	 */
	function setFilter ($filterID)
	{
		return $this->_sendRequest(
			'cmd=setCurrentFilter&sFilter=' . intval($filterID)
		);
	}
	/**
	 * Searches for cases by keyword
	 * @param string $query The keyword to filter cases by
 	 * @return SimpleXMLElement The cases found by the search
	 */
	function search ($query = '')
	{
		$result = $this->_sendRequest(
			'cmd=search' . 
			(!empty($query) ? '&q=' . $query : '') . 
			'&cols=fOpen,ixStatus,ixPersonAssignedTo,sPersonAssignedTo,sTitle'
		);
		return $result->cases;
	}
	/**
	 * Sends a request to the FogBugz API and returns the response
	 * @param string $path The details of the API request
	 * @return SimpleXMLElement The result of the API request
	 */
	private function _sendRequest($path)
	{
		if (!empty($this->token)) {
			$path = 'token=' . urlencode($this->token) . '&' . $path;
		} elseif (strpos($path, 'cmd=login') === false) {
			throw new \Exception('Use login() before attempting to make a request');
		}
		$context  = stream_context_create(array(
			'http' => array(
				'timeout' => 10
			)
		));
		$xml = @file_get_contents(
			$this->getApiUrl() . '?' . $path, false, $context
		);
		if (!empty($xml)) {
			$xml = simplexml_load_string(
				$xml,
				'SimpleXMLElement',
				LIBXML_NOCDATA
			);
			if (!empty($xml->error)) {
				$this->setLastError($xml->error);
			}
		} else {
			$this->setLastError('No XML returned');
		}
		return $xml;
	}
	/**
	 * Converts a friendly parameter name into a FogBugz API parameter name
	 * @param string $param The parameter to rename
	 * @return string The renamed parameter
	 */
	private function convertParameter($param)
	{
		switch ($param) {
			case 'area':
				$param = 'ixArea';
				break;

			case 'category':
				$param = 'ixCategory';
				break;

			case 'content':
			case 'description':
				$param = 'sEvent';
				break;

			case 'owner':
				$param = 'ixPersonAssignedTo';
				break;

			case 'priority':
				$param = 'ixPriority';
				break;

			case 'project':
				$param = 'ixProject';
				break;
		}
		return $param;
	}
}
