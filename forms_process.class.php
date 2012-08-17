<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

class ErrorMessages{
	const PHONE_FORMAT = "Error: Format invalid! Phone number should be between %d and %d characteres. Also the format should be as following +33123456789";
	const EMAIL_FORMAT = "Error: Email format is invalid";
	const METHOD_DOESNT_EXIST = "Error:  There is no method %s!";
	const ALREADY_EXIST = "Error: %s already exist";
	const COLUMN_DOESNT_EXIST = "Error: %s column do not exist in the database!";
	const IS_NOT_STRING = "Error: Please enter a string separated by space.<br>i.e: email phone lastname";
	const COMBINE_IMPOSSIBLE = "Error: There is %d values and %d columns. There should have the same number";
	const MAX_MIN_LENGTH_ERROR = "%s contains %d characteres, while it should contains %d characteres!";
}

class Form{

	private $post, $aFields, $aColumns, $sTable, $currentValue, $currentColumn, $aCombined;
	private $flag = true;

	// if the method doesnt exist the method is called in the DbProcess Class
	public function __call($method, $arguments){
		$argc = count($arguments);
		$a = $this->aCombined;
		print_r($this->post);
		// prepare the array for call_user_func_array
		$dbProcess = new DbProcess();
		$handler = array($dbProcess, $method);

		$key = ""; 
		$argv = $arguments;
		if(!is_callable($handler))
			exit(printf(ErrorMessages::METHOD_DOESNT_EXIST, $method));
		else{
			// if($argc > 0){
			// 	foreach($arguments as $k){
			// 		if(!isset($a[$k]))
			// 			exit(printf(ErrorMessages::COLUMN_DOESNT_EXIST, $k));
			// 		$key = $a[$k];
			// 		$argv[$k] = $this->post[$key];
			// 	}
			// }
			call_user_func_array($handler, $argv);
		}
	}

	// Constructor
	public function __construct($post = array()){
		$this->post = $post;
		if(count($post) == 0) exit("POST is empty!");
	}
	
	// Set the fields form the form
	public function setFields($fields){
		if(gettype($fields) != "string" || func_num_args() > 1)
			exit(ErrorMessages::IS_NOT_STRING);
		if(count($this->aFields) == 0)
			$this->aFields = explode(" ", $fields);
		else
			$this->aFields[] = $fields;
		$this->flag = !$this->flag;
		if($this->flag)
			$this->combine();
		return $this;
	}

	// Set the columns name from the database
	public function setColumns($columns){
		if(gettype($columns) != "string" || func_num_args() > 1)
			exit(ErrorMessages::IS_NOT_STRING);
		if(count($this->aColumns) == 0)
			$this->aColumns = explode(" ", $columns);
		else
			$this->aColumns[] = $columns;
		$this->flag = !$this->flag;
		if($this->flag)
			$this->combine();
		return $this;
	}

	public function setTable($table){
		$this->sTable = DbProcess::$sTable = $table;
		return $this;
	}


	// Save
	public function save(){
		$validData = array_map("self::getValidFields", $this->aFields);
		DbProcess::insert($validData, $this->aColumns);
	}

	// check whether the value exist
	public function exist(){
		DbProcess::exist($this->currentValue, array_search($this->currentColumn, $this->aCombined));
		return $this;
	}

	// Call to check the data received
	public function received(){
		echo "<pre>";
		echo var_dump($this->post);
		echo "</pre>";
	}

	public function addIP($str = "user_ip"){
		$this->setColumns($str);
		$this->setFields($str);
		$this->post[$str] = Extras::getIp();
		return $this;
	}

	public function addDate($str = "created_at", $format = "Y-m-d H:i:s"){
		$this->setColumns($str);
		$this->setFields($str);
		$this->post[$str] = date($format);
		return $this;
	}

	public function add($str = "", $value = ""){
		if(gettype($str) == "array"){
			foreach($str as $k => $v){
				$this->setColumns($k);
				$this->setFields($k);
				$this->post[$k] = $v;
			}
		}else{
			$this->setColumns($str);
			$this->setFields($str);
			$this->post[$str] = $value;
		}
		return $this;
	}

	// set the value and the column name
	public function check($str){
		$this->currentValue = $this->post[$str];
		$this->currentColumn = $str;
		return $this;
	}

	// Check whether it's an email formatted
	public function isEmail(){
		$email = $this->currentValue;
		$reg = "/^[^@]*@[^@]*\.[^@]*$/";
		if(!preg_match($reg, $email, $m)){
			exit(ErrorMessages::EMAIL_FORMAT);
		}
		return $this;
	}

	// Check whether it's a phone type 
	public function isPhone($minlength = 5, $maxlength = 50){
		$phone = $this->currentValue;
		$error = sprintf(ErrorMessages::PHONE_FORMAT, $minlength, $maxlength);
		$reg = "/^(([0\+]\d{2,5}-?)?\d{5,20}|\d{5,15})$/";
		if(!preg_match($reg, $phone, $match) || (strlen($phone) < $minlength && strlen($phone) > $maxlength)){
			exit(Extras::wrap($error, "span", "phoneError"));
		}
		return $this;
	}

	// check the max size
	public function maxLength($length = 10){
		$str = $this->currentValue;
		if(strlen($str) > $length){
			exit(printf(ErrorMessages::MAX_MIN_LENGTH_ERROR, $str, strlen($str), $length));
		}
		return $this;
	}

	// check the min size
	public function minLength($length = 10){
		$str = $this->currentValue;
		if(strlen($str) < $length){
			exit(printf(ErrorMessages::MAX_MIN_LENGTH_ERROR, $str, strlen($str), $length));
		}
		return $this;
	}

	// combine the fields and column into one array with key 
	private function combine(){
		if(count($this->aColumns) != count($this->aFields))
			exit(printf(ErrorMessages::COMBINE_IMPOSSIBLE, count($this->aFields), count($this->aColumns)));
		$this->aCombined = array_combine($this->aColumns, $this->aFields);
	}

	// 
	private function getValidFields($str){
		if(!isset($this->post[$str]) || gettype($this->post[$str]) != 'string'){
			
			$error = "Error: <b class='missing'>".strtoupper($str)."</b> does not exist, here is the list of received <b>";
			$error .= count($this->post)."</b> variables:<br />";
			$error .= implode("<br />", array_keys($this->post));
			$error .= "<br />While it should receive those <b>".count($this->aFields)."</b> variables:<br />";
			$error .= implode("<br />", $this->aFields);
			$error = Extras::wrap($error, "div", "error");
			exit($error);
		}
		else
			return $this->post[$str];
	}

}


class DbProcess{

	const HOST = "localhost";
	const USERNAME = "test";
	const PASSWORD = "123456";
	const DATABASE = "test";
	public static $sTable;


	public function __construct(){

		$this->connection = $this->dbConnect();
	}

	// connection to
	private function dbConnect(){
		try{
			$bdd = new PDO("mysql:host=".self::HOST.";dbname=".self::DATABASE, self::USERNAME, self::PASSWORD);
		}
		catch(Exception $e){
			die("error: ".$e->getMessage());
		}
		return $bdd;
	}

	public function insert($fields, $columns){
		$table = self::$sTable;
		$bdd = self::dbConnect();
		$fields = get_magic_quotes_gpc() ? array_map("stripslashes", $fields) : $fields;
		$columns = get_magic_quotes_gpc() ? array_map("stripslashes", $columns) : $columns;
		
		$sColumns = implode(", ", $columns);
		$sFields = implode(',', array_fill(0, count($fields), '?'));
		$sql = "INSERT INTO $table ($sColumns) VALUES ($sFields)";

		$response = $bdd->prepare($sql);
		if($response->execute($fields))
			echo "Successfully registered";
		// $arr = $response->errorInfo();
		// print_r($arr);
	}

	public function exist($val, $col){
		$table = self::$sTable;
		$bdd = self::dbConnect();
		$sql = "SELECT count(*) AS total FROM $table WHERE $col=:val";
		$response = $bdd->prepare($sql);
		$response->bindParam(':val', $val, PDO::PARAM_STR);
		$response->execute();
		$row = $response->fetch();

		if($row["total"] > 0){
			exit(printf(ErrorMessages::ALREADY_EXIST, $val));
		}
		// debug
		// $arr = $response->errorInfo();
		// print_r($arr);
	}

	public function getStructure($table, $io = true){
		$bdd = self::dbConnect();
		$sql = "DESCRIBE ".$table;
		$response = $bdd->prepare($sql);
		$response->execute();

		while($row = $response->fetch(PDO::FETCH_ASSOC)){
			$total = Extras::pluralize("column", count($row));
			$rows[] = $row;
			$fields[] = $row["Field"];
		}

		if($io){
			print "There is <b>".$total."</b> in <b>".$table."</b> table.<br />";
			print implode(", ", $fields)."\n";
			print "<pre>";
			print_r($rows);
			print "</pre>";
		}
		return $fields;
	}

	public function showTables($io = true){
		$bdd = self::dbConnect();
		$sql = "SHOW TABLES";
		$response = $bdd->prepare($sql);
		$response->execute();

		while($row = $response->fetch()){
			$tables[] = $row[0];
		}
		$str = "There is ";
		// total number of tables
		$str .= Extras::pluralize("table", count($tables));
		// database name
		$str .= " in ".self::DATABASE.": <br />";
		// liste of the tables
		$str .= implode("<br />", $tables)."<br />";
		if($io)
			echo $str;
		return implode(", ", $tables);
	}

}

class Extras{

	public function __construct(){
		exit("Fobidden Access!");
	}

	public function pluralize($str, $count){
		if($count > 1){
			$str .= "s";
		}
		return $count." ".$str;
	}

	public function  wrap($str, $tag = "span", $id = '', $class = ''){

		return "<".$tag." id=".$id." class=".$class.">".$str."</".$tag.">";
	}

	public function getIp() {
	$ip = $_SERVER['REMOTE_ADDR'];
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) 
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

	return $ip;
}
	
}


$_POST["fn"] = "jonathan";
$_POST["ln"] = "de montalembert";
$_POST["mphone"] = "+8618600014793";
$_POST["mail"] = "reunetre@hotmail.com";
$_POST["country"] = "france";


$form = new Form($_POST);

// $form->received();
// $form->getStructure("form");

$form->setFields("fn ln mphone mail country")->setColumns("firstname lastname phone email country")->setTable("form");
date_default_timezone_set('Asia/Shanghai');
$form->add(array("fromURL"=> "google", "website" => "cn"));
// echo $form->showTables();
$form->check("mail")->exist()->isEmail();
$form->check("mphone")->isPhone()->save();


?>

