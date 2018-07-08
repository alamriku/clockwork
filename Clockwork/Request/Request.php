<?php namespace Clockwork\Request;

/**
 * Data structure representing a single application request
 */
class Request
{
	/**
	 * Unique request ID
	 */
	public $id;

	/**
	 * Data protocol version
	 */
	public $version = 1;

	/**
	 * Request time
	 */
	public $time;

	/**
	 * Request method
	 */
	public $method;

	/**
	 * Request URL
	 */
	public $url;

	/**
	 * Request URI
	 */
	public $uri;

	/**
	 * Request headers
	 */
	public $headers = [];

	/**
	 * Textual representation of executed controller
	 */
	public $controller;

	/**
	 * GET data array
	 */
	public $getData = [];

	/**
	 * POST data array
	 */
	public $postData = [];

	/**
	 * Session data array
	 */
	public $sessionData = [];

	/**
	 * Cookies array
	 */
	public $cookies = [];

	/**
	 * Response time
	 */
	public $responseTime;

	/**
	 * Response status code
	 */
	public $responseStatus;

	// Peak memory usage in bytes
	public $memoryUsage;

	/**
	 * Database queries array
	 */
	public $databaseQueries = [];

	/**
	 * Cache queries array
	 */
	public $cacheQueries = [];

	/**
	 * Cache reads count
	 */
	public $cacheReads;

	/**
	 * Cache hits count
	 */
	public $cacheHits;

	/**
	 * Cache writes count
	 */
	public $cacheWrites;

	/**
	 * Cache deletes count
	 */
	public $cacheDeletes;

	/**
	 * Cache time
	 */
	public $cacheTime;

	/**
	 * Timeline data array
	 */
	public $timelineData = [];

	/**
	 * Log messages array
	 */
	public $log = [];

	/**
	 * Fired events array
	 */
	public $events = [];

	/**
	 * Application routes array
	 */
	public $routes = [];

	/**
	 * Emails data array
	 */
	public $emailsData = [];

	/**
	 * Views data array
	 */
	public $viewsData = [];

	/**
	 * Custom user data (not used by Clockwork app)
	 */
	public $userData = [];

	public $subrequests = [];

	/**
	 * Create a new request, if optional data array argument is provided, it will be used to populate the request object,
	 * otherwise empty request with autogenerated ID will be created
	 */
	public function __construct(array $data = null)
	{
		if ($data) {
			foreach ($data as $key => $val) {
				$this->$key = $val;
			}
		} else {
			$this->id = $this->generateRequestId();
		}
	}

	/**
	 * Compute and return sum of duration of all database queries
	 */
	public function getDatabaseDuration()
	{
		return array_reduce($this->databaseQueries, function ($total, $query) {
			return isset($query['duration']) ? $total + $query['duration'] : $total;
		}, 0);
	}

	/**
	 * Compute and return response duration in milliseconds
	 */
	public function getResponseDuration()
	{
		return ($this->responseTime - $this->time) * 1000;
	}

	/**
	 * Return request data as an array
	 */
	public function toArray()
	{
		return [
			'id'               => $this->id,
			'version'          => $this->version,
			'time'             => $this->time,
			'method'           => $this->method,
			'url'              => $this->url,
			'uri'              => $this->uri,
			'headers'          => $this->headers,
			'controller'       => $this->controller,
			'getData'          => $this->getData,
			'postData'         => $this->postData,
			'sessionData'      => $this->sessionData,
			'cookies'          => $this->cookies,
			'responseTime'     => $this->responseTime,
			'responseStatus'   => $this->responseStatus,
			'responseDuration' => $this->getResponseDuration(),
			'memoryUsage'      => $this->memoryUsage,
			'databaseQueries'  => $this->databaseQueries,
			'databaseDuration' => $this->getDatabaseDuration(),
			'cacheQueries'     => $this->cacheQueries,
			'cacheReads'       => $this->cacheReads,
			'cacheHits'        => $this->cacheHits,
			'cacheWrites'      => $this->cacheWrites,
			'cacheDeletes'     => $this->cacheDeletes,
			'cacheTime'        => $this->cacheTime,
			'timelineData'     => $this->timelineData,
			'log'              => array_values($this->log),
			'events'           => $this->events,
			'routes'           => $this->routes,
			'emailsData'       => $this->emailsData,
			'viewsData'        => $this->viewsData,
			'userData'         => array_map(function ($data) {
				return $data instanceof UserData ? $data->toArray() : $data;
			}, $this->userData),
			'subrequests'      => $this->subrequests
		];
	}

	/**
	 * Return request data as a JSON string
	 */
	public function toJson()
	{
		return json_encode($this->toArray(), \JSON_PARTIAL_OUTPUT_ON_ERROR);
	}

	/**
	 * Record executed subrequest, takes the requested url, returned Clockwork ID and optional path if non-default
	 */
	public function addSubrequest($url, $id, $path = null)
	{
		$this->subrequests[] = [
			'url'  => urlencode($url),
			'id'   => $id,
			'path' => $path
		];
	}

	// Add custom user data (presented as additional tabs in the official app)
	public function userData($key = null)
	{
		if ($key && isset($this->userData[$key])) {
			return $this->userData[$key];
		}

		$userData = (new UserData)->title($key);

		return $key ? $this->userData[$key] = $userData : $this->userData[] = $userData;
	}

	/**
	 * Generate unique request ID in form <current time>-<random number>
	 */
	protected function generateRequestId()
	{
		return str_replace('.', '-', sprintf('%.4F', microtime(true))) . '-' . mt_rand();
	}
}
