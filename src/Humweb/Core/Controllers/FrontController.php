<?php namespace Humweb\Core\Controllers;

use View, Event;
use App\Modules\Menus\Models\MenuLinkModel;

class FrontController extends BaseController {

	protected $theme = 'default';

	/**
	 * Instantiate a new BaseController
	 */
	public function __construct()
	{
		parent::__construct();
		$menu =  MenuLinkModel::build_navigation(1);
		//dd($menu);
		View::share('menu', $menu);
		Event::fire('start.controller.front', [$this, $this->currentUser]);

		//View::addLocation(public_path().'/themes/'.$this->theme);

	}
	
}