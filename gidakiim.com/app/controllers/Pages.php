<?php
include_once APP_ROOT . '/libraries/CSV.php';
class Pages extends Controller{
	private $data = [];

	public function __construct(){
		$this->userModel = $this->model('Page');
	}

	//default method
	public function index($params = []){
		$this->data['pageName'] = 'Home';
		$this->data['weatherData']=[];
		$this->data['weatherData']['cities'] = $this->userModel->get_cities();
		$this->data['weatherData']['fields'] = $this->userModel->get_fields();
		$this->data['weatherData']['dates'] = $this->userModel->get_dates();
		$this->view('weather/index', $this->data);
	}

	public function get_data(){
		if(isset($_GET['ajax']) && $_GET['ajax'] == 'get_raw_data'){
			$params = [
				'cities' => explode(',', $_GET['city']),
				'column' => $_GET['column'],
				'start' => $_GET['start'],
				'end' => $_GET['end'],
			];
			echo json_encode($this->userModel->get_data($params));
		}
		
	}

	public function csv(){
		if(isset($_GET['ajax']) && $_GET['ajax'] == 'get_csv'){
			$params = [
				'cities' => explode(',', $_GET['city']),
				'column' => $_GET['column'],
				'start' => $_GET['start'],
				'end' => $_GET['end'],
			];

			$data = $this->userModel->fetch_data($params);

			$csv = new CSV();
			echo json_encode($csv->makeCSV($data));
		}

		
	}

	public function csvAll(){
		if(isset($_GET['ajax']) && $_GET['ajax'] == 'get_csv_all'){
			$data = $this->userModel->fetch_all_data();

			$csv = new CSV();
			echo json_encode($csv->makeCSV($data));
		}
	}

	public function scrape_data(){
		if(isset($_GET['ajax']) && $_GET['ajax'] == 'get_scraped_data'){
			$cities = $this->userModel->get_cities();
			$data = [];
			foreach($cities as $city){
				$data[$city->name]['id'] = $city->id;
				$data[$city->name]['data'] = $this->userModel->scrape_data($city);
			}
			$result = $this->userModel->insert_data($data);
			echo json_encode(['message' => count($result) . ' entries were added']);
			
		}
	}

	public function cron(){
		//updated database
		//$this->scrape_data();

		//backupdatabse
		//$this->userModel->backup_database();
	}
}