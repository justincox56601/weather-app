<?php
/**
 * Object for making and handling the making of CSV files
 */

class CSV{
	public function __construct(){}

	public function makeCSV($data){
		$path = 'media/weather data.csv';
		
		if(file_exists($path)){
			unlink($path);
		}
		
		$result = ['url' => URL_ROOT . '/' . $path];
		
		$file = fopen($path, 'w');
		fputcsv($file, $data['headers']);
		foreach($data['values'] as $d){
			//check if $d is an array, convert it if needed
			if(!is_array($d)){
				$temp = [];
				foreach($d as $key=>$val){
					$temp[] = $val;
				}
				$d = $temp;
			}
			if(!fputcsv($file, $d)){
				$result = ['url' => ''];
				break;
			}
		}
		fclose($file);

		return $result;
	}
}