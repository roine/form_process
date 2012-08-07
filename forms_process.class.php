<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

class Form{

	private $post, $aFields, $aColumns, $sTable;

	// if the method doesnt exist the method is called in the DbProcess Class
	public function __call($method, $arguments){
		$dbProcess = new DbProcess();
		$handler = array($dbProcess, $method);
		$a = array_combine($this->aColumns, $this->aFields);
		$key = $argv = "";
		
		if(count($arguments) <= 1){
		$arguments = explode(" ", implode(" ", $arguments));
		}

		if(!is_callable($handler))
			echo "There is no method ".$method." into DbProcess and Form Class!";
		else{	
			foreach($arguments as $k){
				if(!isset($a[$k]))
					exit("Error: ".$k." column do not exist in the database!");
				$key = $a[$k];
				$argv[$k] = $this->post[$key];
			}
			call_user_func_array($handler, array($argv));
		}
	}
	// Constructor
	public function __construct($post = array()){
		$this->post = $post;
		if(count($post) == 0)
			exit("POST is empty!");
	}
	
	// Set the fields form the form
	public function fields($fields){
		if(gettype($fields) != "string" || func_num_args() > 1)
			die("Error: Please enter a string separated by space.<br>i.e: email phone lastname");
		$this->aFields = explode(" ", $fields);
		return $this;
	}

	// Set the columns name from the database
	public function columns($columns){
		if(gettype($columns) != "string" || func_num_args() > 1)
			die("Error: Please enter a string separated by space.<br>i.e: email phone lastname");
		$this->aColumns = explode(" ", $columns);
		return $this;
	}

	public function table($table){
		$this->sTable = DbProcess::$sTable = $table;
		return $this;
	}

	// Save
	public function save($table){
		$validData = array_map("self::getValidFields", $this->aFields);
		DbProcess::insert($table, $validData, $this->aColumns);
	}

	// Call to check the data received
	public function received(){
		print_r($_REQUEST);
	}

	private function getValidFields($str){
		if(!isset($this->post[$str]) || gettype($this->post[$str]) != 'string'){
			$error = "<div class='error'>";
			$error .= "Error: <b class='missing'>".strtoupper($str)."</b> does not exist, here is the list of received <b>";
			$error .= count($this->post)."</b> variables:<br />";
			$error .= implode("<br />", array_keys($this->post));
			$error .= "<br />While it should receive those <b>".count($this->aFields)."</b> variables:<br />";
			$error .= implode("<br />", $this->aFields);
			$error .= "</div>";
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

	public function insert($table, $fields, $columns){
		$columns = empty($columns) ? self::getStructure($table, false) : $columns;

	}

	public function check($args){
		$bdd = self::dbConnect();
		$col = implode(", ", array_keys($args));
		$val = implode(", ", $args);
		$table = self::$sTable;
		$sql = "SELECT count(*) AS total FROM $table WHERE $col=:val";

		$response = $bdd->prepare($sql);
		$response->bindParam(':val', $val, PDO::PARAM_STR);
		$response->execute();
		$row = $response->fetch();
		if($row["total"] > 0){
			exit("Error: ".$val." already exist!");
		}
		// debug
		// $arr = $response->errorInfo();


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
		$str .= implode("<br />", $tables);

		if($io)
			echo $str;
		return implode(", ", $tables);
	}


	public function __destruct(){
		
	}

}

class Extras{
	public function pluralize($str, $count){
		if($count > 1){
			$str .= "s";
		}
		return $count." ".$str;
	}
	
}


$_POST["fn"] = "jonathan";
$_POST["ln"] = "de montalembert";
$_POST["mphone"] = "+8618600014793";
$_POST["mail"] = "demonj@gmail.com";
$_POST["country"] = "france";


$form = new Form($_POST);
$form->fields("fn ln mphone mail country")->columns("firstname lastname phone email country")->table("form");
$form->check("email");
$form->save("form");
// $c = new DbProcess();
// $c->getStructure("form");
?>

