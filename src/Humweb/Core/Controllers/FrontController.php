<?php namespace Humweb\Core\Controllers;

class FrontController extends BaseController {

	protected $theme = 'default';

	/**
	 * Instantiate a new BaseController
	 */
	public function __construct()
	{
		parent::__construct();

		Event::fire('start.controller.front', [$this, $this->currentUser]);

		View::addLocation(public_path().'/themes/'.$this->theme);

	}
	
}