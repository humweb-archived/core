<?php namespace Humweb\Core\Html;


/**
* Html helpers
*/
class BS3
{

	/**
	 * App container
	 * 
	 * \Illuminate\Foundation\Application
	 */
	protected $app;
	protected $url;


	/**
	 * Creates new class instance
	 * 
	 * @param  \Illuminate\Foundation\Application  $app
	 */
	function __construct($app)
	{
		$this->app = $app;
		$this->url = $app->make('url');
	}

	function route($route, $parameters = array())
	{
		return $this->url->route($route, $parameters);
	}

	function btnRoute($route, $parameters = array())
	{
		$args = [
			'class' => 'btn btn-primary'
		];	
		return $this->route();
	}
}