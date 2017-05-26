<?php

namespace App\Http\Controllers;
use App\User;
use App\user_details;
use App\locations;
use App\Http\Requests;
use Illuminate\Http\Request;

class IndexController extends Controller {

	private $headLinks, $footerLinks, $_mail, $UID, $logged;
	public function __construct(Request $request)
	{
		$this->headLinks = [
			['href' => 'story', 'title' => 'Our Story', 'active' => false],
			['href' => 'scoop', 'title' => 'The Scoop', 'active' => false],
			['href' => 'prducts', 'title' => 'Products', 'active' => false],
			['href' => 'locations', 'title' => 'Find Epigamia', 'active' => false]
		];

		$this->footerLinks = [
			['href' => 'story', 'title' => 'Our Story', 'active' => false],
			['href' => 'scoop', 'title' => 'The Scoop', 'active' => false],
			['href' => 'prducts', 'title' => 'Products', 'active' => false],
			['href' => 'locations', 'title' => 'Find Epigamia', 'active' => false],
			['href' => 'contact', 'title' => 'Contact', 'active' => false],
			['href' => '#', 'title' => 'Press', 'active' => false],
			['href' => '#', 'title' => 'Privacy', 'active' => false],
			['href' => '#', 'title' => 'Terms', 'active' => false],
			['href' => '#', 'title' => 'FAQ', 'active' => false]
		];

		$this->_mail = $request->session()->get('_user');
		$this->logged = $request->session()->get('_user') != '';
		$this->UID = ($this->_mail != "") ? User::where('email', $this->_mail)->first()->id : '';
	}

	public function index()
	{
		$data = [
			'page' => 'main',
			'title' => 'Epigamia - Be Healthier with Greek Yogurt',
			'f_title' => 'Be Healthier With Greek Yogurt',
			'darkHeader' => false,
			'loggedIn' => $this->logged,
			'headLinks' => $this->headLinks,
			'footLinks' => $this->footerLinks
		];

		return view('front', $data);
	}

	public function story() {
		$this->headLinks[0]['active'] = true;
		$this->footerLinks[0]['active'] = true;
		$data = [
			'page' => 'story',
			'title' => 'Epigamia - Our Story',
			'f_title' => 'Our Story',
			'darkHeader' => false,
			'loggedIn' => $this->logged,
			'headLinks' => $this->headLinks,
			'footLinks' => $this->footerLinks
		];

		return view('front', $data);
	}

	public function scoop() {
		$this->headLinks[1]['active'] = true;
		$this->footerLinks[1]['active'] = true;
		$data = [
			'page' => 'scoop',
			'title' => 'Epigamia - The Scoop',
			'darkHeader' => false,
			'loggedIn' => $this->logged,
			'headLinks' => $this->headLinks,
			'footLinks' => $this->footerLinks
		];

		return view('front', $data);
	}

	public function products() {
		$this->headLinks[2]['active'] = true;
		$this->footerLinks[2]['active'] = true;
		$data = [
			'page' => 'products',
			'title' => 'Epigamia - Products',
			'f_title' => 'Our Products',
			'darkHeader' => false,
			'loggedIn' => $this->logged,
			'headLinks' => $this->headLinks,
			'footLinks' => $this->footerLinks
		];

		return view('front', $data);
	}
	
	public function locations() {
		$this->headLinks[3]['active'] = true;
		$this->footerLinks[3]['active'] = true;

		$locations = locations::get();
		$mapLocations = [];
		foreach ($locations as $location) {
			$lct = [$location->name,$location->latitude,$location->longitude];
			array_push($mapLocations, $lct);
		}

		$data = [
			'page' => 'locations',
			'title' => 'Epigamia - Locations',
			'darkHeader' => false,
			'mapLocations' => json_encode($mapLocations),
			'loggedIn' => $this->logged,
			'headLinks' => $this->headLinks,
			'footLinks' => $this->footerLinks
		];
		
		return view('front', $data);
	}

	public function contact() {
		$this->footerLinks[4]['active'] = true;

		$data = [
			'page' => 'contact',
			'title' => 'Epigamia - Contact Us',
			'darkHeader' => false,
			'f_title' => 'Contact Us',
			'loggedIn' => $this->logged,
			'headLinks' => $this->headLinks,
			'footLinks' => $this->footerLinks
		];
		
		return view('front', $data);
	}
}
