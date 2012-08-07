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

	public function __construct($post = array()){
		$this->post = $post;
	}
	
	// Set the fields form the form
	public function fields($fields){
		$this->aFields = explode(" ", $fields);
		return $this;
	}

	// Set the columns name from the database
	public function columns($columns){
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
		if(empty($columns))
			$columns = self::getStructure($table, false);
	}

	public function check($args){
		$bdd = self::dbConnect();
		print_r(array_keys($args));
		$response = $bdd->prepare("SELECT count(*) AS total FROM :table WHERE");
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
$_POST["ln"] = "montalembert";
$_POST["mphone"] = "+8618600014793";
$_POST["mail"] = "feunetre@hotmail.com";
$_POST["country"] = "france";

$form = new Form($_POST);
$form->fields("fn ln mphone mail country")->columns("firstname lastname phone email country")->table("form");
$form->check("email, firstname");

$form->save("form");
?>

