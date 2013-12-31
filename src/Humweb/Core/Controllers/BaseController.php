<?php namespace Humweb\Core\Controllers;

use Illuminate\Routing\Controller;

class BaseController extends Controller {

	protected $currentUser;
	protected $theme = 'default';
	protected $layout = 'layouts.default';
	public $_metadata = '';

	public function __construct()
	{
		//Set language
		//App::setLocale('en');

		//Check CSRF token on POST

		//set Current user - to be used throughout the request cycle
		$this->currentUser = Sentry::getUser();

		//Researching Ideas??
		//$this->layout->_metadata = [];
		

		//Share the current users data across all views
		View::share(array('currentUser' => $this->currentUser));

		Event::fire('start.controller.all', [$this, $this->currentUser]);
	}

	/**
	  * Output view
	  *
	  * @param  string  $view
	  * @param  array   $data
	  * @param  array   $mergeData
	  * @return \Illuminate\View\View
	 */
	protected function setContent($view, $data = array(), $mergeData = array())
	{
		if ( ! is_null($view))
		{
			if (is_string($view))
			{
				$this->layout->content = View::make($view, $data, $mergeData);
			}
			else {
				$this->layout->content = $view;
			}
		}
	}	
	protected function setLayout($view)
	{
		if ( ! is_null($view))
		{
			if (is_string($view))
			{
				$this->layout = View::make($view);
			}
			else {
				$this->layout = $view;
			}

		}
	}
	
	protected function setTite($title)
	{
		if ( ! is_null($title))
		{
			$this->layout->title = $title;
		}
	}

	public function setMeta($name, $content, $type = 'meta')
	{


		$name = htmlspecialchars(strip_tags($name));
		$content = trim(htmlspecialchars(strip_tags($content)));

		// Keywords with no comments? ARG! comment them
		if ($name == 'keywords' and ! strpos($content, ','))
		{
			$content = preg_replace('/[\s]+/', ', ', trim($content));
		}

		switch($type)
		{
			case 'meta':
				$this->_metadata[$name] = '<meta name="'.$name.'" content="'.$content.'" />';
			break;

			case 'link':
				$this->_metadata[$content] = '<link rel="'.$name.'" href="'.$content.'" />';	
			break;

			case 'og':
				$this->_metadata[md5($name.$content)] = '<meta property="'.$name.'" content="'.$content.'" />';
			break;
		}
	}
	/**
	 * Generates meta tags from an array of key/values
	 *
	 * @param	array
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function ssetMeta($name = '', $content = '', $type = 'name', $newline = "\n\t")
	{
		// Since we allow the data to be passes as a string, a simple array
		// or a multidimensional one, we need to do a little prepping.
		if ( ! is_array($name))
		{
			$name = array(array('name' => $name, 'content' => $content, 'type' => $type, 'newline' => $newline));
		}
		elseif (isset($name['name']))
		{
			// Turn single array into multidimensional
			$name = array($name);
		}

		$str = '';
		foreach ($name as $meta)
		{
			$type		= ( ! isset($meta['type']) OR $meta['type'] === 'name') ? 'name' : 'http-equiv';
			$name		= isset($meta['name'])					? $meta['name'] : '';
			$content	= isset($meta['content'])				? $meta['content'] : '';
			$newline	= isset($meta['newline'])				? $meta['newline'] : "\n";

			$str .= '<meta '.$type.'="'.$name.'" content="'.$content.'" />'.$newline;
		}

		$this->_metadata .= $str;
	}

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		// We dont need a layout for certain request (ajax for instance)
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

}