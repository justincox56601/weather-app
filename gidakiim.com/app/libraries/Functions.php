<?php

function get_header(){
	return file_get_contents(APP_ROOT . '/views/header-footer/header.php');
}

function get_footer(){
	return file_get_contents(APP_ROOT . '/views/header-footer/footer.php');
}