<?php
/**
 * This is the model for the Pages controller
 * 
 */

include_once APP_ROOT . '/libraries/simple_html_dom.php';

class Page{
	private $db;
	public function __construct(){
		$this->db = Database::getInstance();
	}

	
	public function get_cities(){
		$this->db->query('SELECT * FROM weathercities');
		return $this->db->getResults();
	}

	public function get_fields(){
		$results = [];
		$this->db->query("SELECT * FROM weatherdata limit 1");
		
		$query =  $this->db->getResults();
		foreach($query[0] as $key => $val){
			if($key == 'id' || $key == 'cityId'){continue;}
			if(is_numeric($val)){
				$results[] = $key;
			}
		}

		return $results;
	}

	public function get_dates(){
		$this->db->query('SELECT date FROM weatherdata ORDER BY date ASC LIMIT 1');
		$start =  $this->db->getResults();

		$this->db->query('SELECT date FROM weatherdata ORDER BY date DESC LIMIT 1');
		$end = $this->db->getResults();

		return [$start[0]->date, $end[0]->date];
	}

	public function get_data($params){
		$results = [];
		foreach($params['cities'] as $c){
			$this->db->query('SELECT weathercities.name, weatherdata.date, weatherdata.' . strtolower($params['column']) . ' 
							FROM weathercities
							INNER JOIN weatherdata ON weathercities.id = weatherdata.cityId
							WHERE cityID=? AND weatherdata.date BETWEEN ? AND ?
							ORDER BY weatherdata.date ASC');
			$this->db->bind($c, 'i');
			$this->db->bind(date('Y-m-d', strtotime($params['start'])), 's');
			$this->db->bind(date('Y-m-d', strtotime($params['end'])), 's');

			$temp = $this->db->getResults();
			foreach($temp as $t){
				$results[] = $t;
			}
		}
		
		return $results;
	}

	public function fetch_data($params){
		$data = $this->get_data($params);
		$results = [];
		$results['headers'] = array_keys(get_object_vars($data[0]));
		$results['values'] = $data;

		return $results;
	}

	public function fetch_all_data(){
		$this->db->query('SELECT * FROM weatherdata');
		$data = $this->db->getResults();
		$results = [];
		$results['headers'] = array_keys(get_object_vars($data[0]));
		$results['values'] = $data;

		return $results;
	}

	public function scrape_data($city){
		//get raw html
		$data = $this->curl($city->url);

		//filter data
		$data = $this->get_raw_data($data);
		$data = $this->filter_data($data);

		//reverse array because of how the table is captured
		$data = array_reverse($data, false);

		//remove duplicate data
		$data = $this->remove_duplicate_data($data, $city->id);
		
		//retun array
		return $data;
	}

	private function curl($url){
		//not going to make a whole curl library for the little bit I need for this
		$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, $agent);

		$page = curl_exec($curl);
		if(curl_errno($curl)){
			echo "scraper error: " . curl_error($curl);
			exit;
		}
		curl_close($curl);

		return $page;

	}

	private function get_raw_data($data){
		//filters page data and returns an array of raw data
		$html = str_get_html($data);
		$tables = $html->find('table');	


		$rows = $tables[3]->find('tr');
		$results = [];	

		for($r=3; $r<count($rows); $r++){
			$cells = $rows[$r]->find('td');
			
			if(isset($cells[0])){
				$date = $this->validate_date(date("Y", strtotime('now')), date("m", strtotime('now')), $cells[0]->plaintext, $cells[1]->plaintext);
				if($date != false){
					$temp = [
						'Date' 							=> $date,
						'Wind (MPH)' 					=> $cells[2]->plaintext,
						'Visibility (mi)' 				=> $cells[3]->plaintext,
						"Weather" 						=> $cells[4]->plaintext, 
						'Sky Condition' 				=> $cells[5]->plaintext,
						'Temperature - Air (F)' 		=> $cells[6]->plaintext,
						'Temperature - Dewpoint (F)' 	=> $cells[7]->plaintext,
						'Relative Humidity' 			=> $cells[10]->plaintext,
						'Windchill (F)' 				=> $cells[11]->plaintext,
						'Heat Index (F)' 				=> $cells[12]->plaintext,
						'Pressure - Altimeter (in)' 	=> $cells[13]->plaintext,
						'Precipiation - 1 hour (in)' 	=> ($cells[15]->plaintext != null)? $cells[15]->plaintext : 0,
					];
			
					$results[] = $temp;
				}
			}
		}

		return $results;
	}

	private function filter_data($data){
		//filters the raw data to change rain accumilation to actual rain fall
		//comparing the timestamp and precipitation amounts for $i and $i+1
		for($i=0; $i<count($data)-1; $i++){
			if(date('H', strtotime($data[$i]['Date'])) != date('H', strtotime($data[$i+1]['Date']))){
				continue; //dont need to make a comparison at the start of a new hour
			}

			$data[$i]['Precipiation - 1 hour (in)'] = $data[$i]['Precipiation - 1 hour (in)'] -  $data[$i+1]['Precipiation - 1 hour (in)'];
		}

		return $data;
	}

	private function validate_date($year, $month, $day, $time){
		//returns date as a string YYYY-MM-DD HH:MM
		$date =  date('Y-m-d', strtotime($month .'/' . $day .'/' . $year . ' ' . $time));
		
		if($date == date('Y-m-d', strtotime('today')) ||
		   $date == date('Y-m-d', strtotime('yesterday')) ||
		   $date == date('Y-m-d', strtotime('-2 days'))){
				return date('Y-m-d H:i', strtotime($month .'/' . $day .'/' . $year . ' ' . $time));
		}

		//when the month changes, the month can be weird so need to change check that
		$month = date('m', strtotime('last month'));
		$date =  date('Y-m-d', strtotime($month .'/' . $day .'/' . $year . ' ' . $time));
		
		if($date == date('Y-m-d', strtotime('today')) ||
		   $date == date('Y-m-d', strtotime('yesterday')) ||
		   $date == date('Y-m-d', strtotime('-2 days'))){
				return date('Y-m-d H:i', strtotime($month .'/' . $day .'/' . $year . ' ' . $time));
		}

		return false; //if we get to this point, this is not a valid date for entry
	}

	private function remove_duplicate_data($data, $id){
		//unsets any duplicate entries using the data as the decider

		//retrieve the date of the most recent entry
		$this->db->query('SELECT date FROM weatherdata WHERE cityId =? ORDER BY date DESC'); //retrieves the most current Cityid and Date in the table
		$this->db->bind($id, 'i');
		$date = $this->db->getResults();

		//check if $date[0] is empty or not before trying to use it
		if(!empty($date[0])){
			$date = $date[0]; 
			$date = ($date)? strtotime($date->date) : 0; //converts date=>date to an integer for easier comparison or 0 if empty
			
			foreach($data as $key => $val){
				$time = strtotime($val['Date']);
				if($time <= $date){
					unset($data[$key]);
				}
			}
		}
		
		return $data;
	}

	public function insert_data($data){
		//recieves an array of data[cities], cities[id], cities[data], data[]

		$results = [];
		$this->db->query('INSERT INTO weatherdata VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

		foreach($data as $city){
			foreach($city['data'] as $data){
				$this->db->bind($city['id'], 'i');
				foreach($data as $d){
					$this->db->bind($d, 's');
				}
				$results[] = $this->db->insert();
			}
		}

		return $results;
	}

	public function backup_database(){
		$path = '../public/media/bdbackup-' . date("Y-m-d"). '.sql';
		$file = fopen($path, 'w');
		$command = 'mysqldump  -u ' . DB_USER . ' -p ' . DB_PASS . ' ' . DB_NAME . ' > ' . $path;
		exec($command, $worked, $output);
		fclose($file);
		echo $path . "<pre> ";
		print_r($worked);
		if($output){
			echo "Output: " . $output;
		}else{
			echo 'THere was no output';
		}
		
	}

 }