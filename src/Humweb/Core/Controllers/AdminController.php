<?php namespace Humweb\Core\Controllers;


use Humweb\Module\Controllers;

use View, Request, Modules;

class AdminController extends BaseController {

	protected $theme_slug = 'default';
	protected $section = 'index';
	protected $modules = [];
	protected $current_module = 'dashboard';
	
    public function __construct()
    {
        parent::__construct();
		
		//Check for admin access
		$this->beforeFilter('admin_auth', array('except' => array('getLogin', 'postLogin')));

		//Set admin layout
        $this->layout = 'layouts.admin';

        //Set some usefull variables to be used by extended controllers
		$this->current_module = Request::segment(2)?:'dashboard';
		$this->modules        = Modules::getProviders();
		$menu_array           = [];

		//Create menu array for admin panel
		foreach ($this->modules as $name => $module)
		{

			if (is_callable([$module, 'admin_menu']))
			{
				$menu_array = array_merge_recursive($menu_array, $module->admin_menu());
			}
		}

		$menu = [];
		foreach (['Dashboard', 'Content', 'Structure', 'Settings', 'Users', 'DevOps', 'Testing'] as $key)
		{
			if (isset($menu_array[$key]))
			{
				$menu[$key] = $menu_array[$key];
			}
			unset($menu_array[$key]);
		}

		View::share('module_menu_array', $menu);

		// If we have a current module, and quick links we fetch them here
		if ($oModule = Modules::instance($this->current_module))
		{
			$menu = $oModule->admin_quick_menu();

			if (isset($menu[$this->section]))
			{
				View::share('quick_links', $menu[$this->section]);
			}
			else {
				View::share('quick_links', array());
			}

			//
			//@todo Create menu with sections
			//

		}

    }
	/**
	 * Login
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$this->getLogin();
	}

	public function getLogin()
	{
		if ( ! Sentry::check())
		{
			//dd(URL::previous());

			$this->setLayout('layouts.login');
			$this->setContent('admin.login');
		}
		else {
			$this->layout->content = View::make('admin.dashboard');
		}
		// Show the register form
	}

	public function postLogin() 
	{

		try
		{

			$credentials = array(
				'email'      => Input::get('email'),
				'password'   => Input::get('password')
			);
		    $user = Sentry::authenticate($credentials, Input::get('rememberMe'));
		}

		catch (Cartalyst\Sentry\Users\LoginRequiredException $e)
		{
			Session::flash('error', 'Login field is required.');
			return Redirect::to('admin/login')->withInput();
		}
		catch (Cartalyst\Sentry\Users\PasswordRequiredException $e)
		{
			Session::flash('error', 'Password field is required.');
			return Redirect::to('admin/login')->withInput();
		}
		catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
			Session::flash('error', 'Invalid username or password.' );
			return Redirect::to('admin/login')->withInput();
		}
		catch (Cartalyst\Sentry\Users\WrongPasswordException $e)
		{
			Session::flash('error', 'Invalid username or password.' );
			return Redirect::to('admin/login')->withInput();
		}
		catch (Cartalyst\Sentry\Users\UserNotActivatedException $e)
		{
			Session::flash('error', 'You have not yet activated this account.');
			return Redirect::to('admin/login')->withInput();
		}

		catch (Cartalyst\Sentry\Throttling\UserSuspendedException $e)
		{
			$throttle = Sentry::getThrottleProvider()->findByUserId(1);
    		$attempts = $throttle->getLoginAttempts();

			$time = $throttle->getSuspensionTime();
			Session::flash('error', "Your account has been suspended for $time minutes.");
			return Redirect::to('admin/login')->withInput();
		}
		catch (Cartalyst\Sentry\Throttling\UserBannedException $e)
		{
			Session::flash('error', 'You have been banned.');
			return Redirect::to('admin/login')->withInput();
		}
		
		Event::fire('admin.logged.in', $user);

		if (Session::has('redirect'))
		{
			$redirect = Session::get('redirect');
			Session::forget('redirect');
			return Redirect::to($redirect);
		} else {
			//default index page
			return Redirect::to('admin');
		}

	}

	/**
	 * Logout
	 */
	
	public function getLogout() 
	{
		Sentry::logout();
		return Redirect::to('/');
	}


    // public function getIndex() {
    // 	
    // }
}