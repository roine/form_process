<?php
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

class ErrorMessages {
    const PHONE_FORMAT = 'Error: Format invalid! Phone number should be between %d and %d characteres. Also the format should be as following +33123456789, your is as following %d';
    const EMAIL_FORMAT = 'Error: Email format is invalid';
    const METHOD_DOESNT_EXIST = 'Error:  There is no method %s!';
    const ALREADY_EXIST = 'Error: %s already exist';
    const COLUMN_DOESNT_EXIST = 'Error: %s column do not exist in the database!';
    const IS_NOT_STRING = 'Error: Please enter a string separated by space.<br>i.e: email phone lastname';
    const COMBINE_IMPOSSIBLE = 'Error: There is %d values and %d columns. There should have the same number';
    const MAX_MIN_LENGTH_ERROR = '%s contains %d characteres, while it should contains %d characteres!';
}

class Form {

    /**
     * $post
     *
     * @var mixed
     *
     * @access private
     */
    private $aFields, $aColumns, $sTable, $currentValue, $currentColumn, $aCombined;
    public static $post;

    /**
     * $flag
     *
     * @var mixed
     *
     * @access private
     */
    private $flag = true;

    // if the method doesnt exist the method is called in the DbProcess Class

    /**
     * __call
     *
     * @param mixed   $method    Description.
     * @param mixed   $arguments Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function __call( $method, $arguments ) {
        // $argc = count($arguments);
        // $a = $this->aCombined;
        // prepare the array for call_user_func_array
        $dbProcess = new DbProcess();
        $handler = array( $dbProcess, $method );

        // $key = '';
        $argv = $arguments;
        if ( !is_callable( $handler ) )
            exit( printf( ErrorMessages::METHOD_DOESNT_EXIST, $method ) );
        else {
            // if($argc > 0){
            //  foreach($arguments as $k){
            //   if(!isset($a[$k]))
            //    exit(printf(ErrorMessages::COLUMN_DOESNT_EXIST, $k));
            //   $key = $a[$k];
            //   $argv[$k] = $this->post[$key];
            //  }
            // }
            call_user_func_array( $handler, $argv );
        }
    }

    // Constructor

    /**
     * __construct
     *
     * @param array   $post Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function __construct( $post = array() ) {
        self::$post = $post;
        if ( count( $post ) === 0 ) {
            exit( 'POST is empty!' );
        }
    }


    // Set the fields form the form

    /**
     * setFields
     *
     * @param mixed   $fields Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function setFields( $fields ) {
        if ( gettype( $fields ) != 'string' || func_num_args() > 1 )
            exit( ErrorMessages::IS_NOT_STRING );
        if ( count( $this->aFields ) == 0 )
            $this->aFields = explode( ' ', $fields );
        else
            $this->aFields[] = $fields;
        $this->flag = !$this->flag;
        if ( $this->flag )
            $this->combine();
        return $this;
    }

    /**
     * alias for setFields which isn't clean
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function setValues( $values ) {
        return $this->setFields( $values );
    }

    // Set the columns name from the database

    /**
     * setColumns
     *
     * @param string  $columns Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function setColumns( $columns = '' ) {
        if ( gettype( $columns ) != 'string' || func_num_args() > 1 || empty( $columns ) )
            exit( ErrorMessages::IS_NOT_STRING );

        // set the columns name
        //
        if ( count( $this->aColumns ) === 0 )
            $this->aColumns = explode( ' ', $columns );
        else
            $this->aColumns = array_merge( $this->aColumns, explode( ' ', $columns ) );

        $this->flag = !$this->flag;
        if ( $this->flag )
            $this->combine();
        return $this;
    }

    /**
     * setTable
     *
     * @param mixed   $table Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function setTable( $table ) {
        $this->sTable = DbProcess::$sTable = $table;
        return $this;
    }


    // Save

    /**
     * save
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function save() {
        $validData = array_map( "self::getValidFields", $this->aFields );
        DbProcess::insert( $validData, $this->aColumns );
    }

    // check whether the value exist

    /**
     * exist
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function exist() {
        DbProcess::exist( $this->currentValue, array_search( $this->currentColumn, $this->aCombined ) );
        return $this;
    }

    /**
     * fixe a typo
     *
     * @access public
     *
     * @return bool return whether .
     */
    public function exists() {
        return $this->exist();
    }

    // Call to check the data received

    /**
     * Output the data received from the form
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function received() {
        echo '<pre>';
        echo var_dump( self::$post );
        echo '</pre>';
    }

    /**
     * get the ip of the user and set it to a defined column
     *
     * @param string  $str name of the column.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function addIP( $str = 'user_ip' ) {
        $this->add( $str, Extras::getIp() );
        return $this;
    }

    /**
     * addDate
     *
     * @param string  $str    Description.
     * @param string  $format Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function addDate( $str = 'created_at', $format = "Y-m-d H:i:s" ) {
        $this->add( $str, date( $format ) );
        return $this;
    }

    /**
     * add
     *
     * @param string  $str   Description.
     * @param string  $value Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function add( $str = '', $value = '' ) {
        if ( gettype( $str ) == 'array' ) {
            $key = array_keys( $str );
            $size = count( $key );
            for ( $i = 0; $i < $size;$i++ ) {
                $this->setColumns( $key[$i] );
                $this->setFields( $key[$i] );
                self::$post[$key[$i]] = $str[$key[$i]];
            }
        }else {
            $this->setColumns( $str );
            $this->setFields( $str );
            self::$post[$str] = $value;
        }
        return $this;
    }

    // set the value and the column name

    /**
     * check
     *
     * @param mixed   $str Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function check( $str ) {
        $this->currentValue = self::$post[$str];
        $this->currentColumn = $str;
        return $this;
    }

    // Check whether it's an email formatted

    /**
     * isEmail
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function isEmail() {
        $email = $this->currentValue;
        $reg = "/^[^@]*@[^@]*\.[^@]*$/";
        if ( !preg_match( $reg, $email, $m ) ) {
            exit( ErrorMessages::EMAIL_FORMAT );
        }
        return $this;
    }

    // Check whether it's a phone type

    /**
     * isPhone
     *
     * @param int     $minlength Description.
     * @param int     $maxlength Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function isPhone( $minlength = 5, $maxlength = 50 ) {
        $phone = $this->currentValue;
        $error = sprintf( ErrorMessages::PHONE_FORMAT, $minlength, $maxlength, $this->currentValue );
        $reg = "/^(([0\+]\d{2,5}-?)?\d{5,20}|\d{5,15})$/";
        if ( !preg_match( $reg, $phone, $match ) || ( strlen( $phone ) < $minlength || strlen( $phone ) > $maxlength ) ) {
            exit( Extras::wrap( $error, 'span', 'phoneError' ) );
        }
        return $this;
    }

    // check the max size

    /**
     * maxLength
     *
     * @param int     $length Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function maxLength( $length = 10 ) {
        $str = $this->currentValue;
        if ( strlen( $str ) > $length ) {
            exit( printf( ErrorMessages::MAX_MIN_LENGTH_ERROR, $str, strlen( $str ), $length ) );
        }
        return $this;
    }

    // check the min size

    /**
     * minLength
     *
     * @param int     $length Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function minLength( $length = 10 ) {
        $str = $this->currentValue;
        if ( strlen( $str ) < $length ) {
            exit( printf( ErrorMessages::MAX_MIN_LENGTH_ERROR, $str, strlen( $str ), $length ) );
        }
        return $this;
    }


    // combine the fields and column into one array with key

    /**
     * combine
     *
     * @access private
     *
     * @return mixed Value.
     */
    private function combine() {
        if ( count( $this->aColumns ) != count( $this->aFields ) )
            exit( printf( ErrorMessages::COMBINE_IMPOSSIBLE, count( $this->aFields ), count( $this->aColumns ) ) );
        $this->aCombined = array_combine( $this->aColumns, $this->aFields );
    }

    //

    /**
     * getValidFields
     *
     * @param mixed   $str Description.
     *
     * @access private
     *
     * @return mixed Value.
     */
    private function getValidFields( $str ) {
        if ( !isset( self::$post[$str] ) || gettype( self::$post[$str] ) != 'string' ) {
            exit( "Error: <b class='missing'> {strtoupper($str)} </b> does not exist, here is the list of received <b>"
                . "{count($this->post)}</b> variables:<br />"
                . "{implode('<br />', array_keys($this->post))}"
                . "<br />While it should receive those <b>{count($this->aFields)}</b> variables:<br />"
                . "{implode('<br />', $this->aFields)}"
                . Extras::wrap( $error, "div", "error" ) );
        }
        return self::$post[$str];
    }


}

class DbProcess {

    /**
     * $host
     *
     * @var string
     *
     * @access public
     * @static
     */
    public static $host = null;

    /**
     * $username
     *
     * @var string
     *
     * @access public
     * @static
     */

    /**
     * $username
     *
     * @var string
     *
     * @access public
     * @static
     */
    public static $username = null;

    /**
     * $password
     *
     * @var string
     *
     * @access public
     * @static
     */
    public static $password = null;

    /**
     * $database
     *
     * @var string
     *
     * @access public
     * @static
     */
    public static $database = null;

    /**
     * $conf_file
     *
     * @var string
     *
     * @access public
     * @static
     */
    public static $conf_file = 'conf.php';

    /**
     * $sTable
     *
     * @var mixed
     *
     * @access public
     * @static
     */
    public static $sTable;


    /**
     * __construct
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function __construct() {
        if ( !isset( $this->connection ) ) {
            self::setCredential();
            $this->connection = $this->dbConnect();
        }
    }


    /**
     * dbConnect
     *
     * @access private
     *
     * @return mixed Value.
     */
    private static function dbConnect() {

        try{
            $bdd = new PDO( "mysql:host=".self::$host.";dbname=".self::$database, self::$username, self::$password, array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' ) );
        }
        catch( Exception $e ) {
            die( "error: ".$e->getMessage() );
        }
        return $bdd;
    }

    public function start($table = null){

        echo 'Ok, let\'s see what we have here!<br>';
        echo 'I have received <b>'.count(Form::$post). '</b> values from the form.<br>';
        if($table == null){
            exit('to get more informations add the table name you want to use, like so $form->start("table_name")');
        }
        echo 'Now let\'s see the level of similarity with the columns name we have in the table <b>'.$table.'</b>...<br>';

        $structure = self::getStructure($table, 0);
        foreach(Form::$post as $key => $value){
            $match = 0;
            foreach($structure as $column){
                if(self::check_similarity($key, $column) > $match){
                     $match = self::check_similarity($key, $column);
                     $columnMatch = $column;
                }
            }
            echo "the best match for <b contenteditable title='please edit me if Im wrong'>$key</b> is <b title='please edit me if Im wrong' contenteditable>$columnMatch</b> (<span data-perc=$match>".round($match, 2)."</span>)<br>";
        }
    }

    private function check_similarity($str1, $str2){
        similar_text($str1, $str2, $c);

        // start with same letter let's add 10 points, it might be initial
        if($str1[0] == $str2[0]){
            $c+=10;
        }
        // if it was already a perfect match reajust
        if($c > 100){
            $c = 100;
        }
        return $c;
    }

    public function setConnection( $host = '', $user = '', $password = '', $database = '' ) {
        self::$host = $host;
        self::$username = $user;
        self::$password = $password;
        self::$database = $database;
    }
    /**
     * setCredential
     *
     * @access private
     *
     * @return mixed Value.
     */
    private function setCredential() {
        $conf_file = self::$conf_file;
        if ( file_exists( $conf_file ) ) {
            $config = require_once $conf_file;
        }
        if ( is_null( self::$host ) ) {
            self::$host = $config['db']['host'];
        }
        if ( is_null( self::$username ) ) {
            self::$username = $config['db']['username'];
        }
        if ( is_null( self::$password ) ) {
            self::$password = $config['db']['password'];
        }
        if ( is_null( self::$database ) ) {
            self::$database = $config['db']['database'];
        }
    }

    /**
     * insert data in DB
     *
     * @param mixed   $fields  Description.
     * @param mixed   $columns Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public static function insert( $fields, $columns ) {
        $table = self::$sTable;
        $bdd = self::dbConnect();
        $fields = get_magic_quotes_gpc() ? array_map( 'stripslashes', $fields ) : $fields;
        $columns = get_magic_quotes_gpc() ? array_map( 'stripslashes', $columns ) : $columns;
        $sql = "INSERT INTO $table (".implode( ', ', $columns ).") VALUES (".implode( ',', array_fill( 0, count( $fields ), '?' ) ).")";
        $response = $bdd->prepare( $sql );
        if ( $response->execute( $fields ) )
            echo 'Successfully registered';
        // $arr = $response->errorInfo();
        // print_r($arr);
    }

    /**
     * exist
     *
     * @param mixed   $val Description.
     * @param mixed   $col Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public static function exist( $val, $col ) {
        $table = self::$sTable;
        $bdd = self::dbConnect();
        $sql = "SELECT count(*) AS total FROM $table WHERE $col=:val";
        $response = $bdd->prepare( $sql );
        $response->bindParam( ':val', $val, PDO::PARAM_STR );
        $response->execute();
        $row = $response->fetch();

        if ( $row['total'] > 0 ) {
            exit( printf( ErrorMessages::ALREADY_EXIST, $val ) );
        }
        // debug
        // $arr = $response->errorInfo();
        // print_r($arr);
    }

    public function tableExists( $tableName = '' ) {
        if ( $tableName === '' ) {
            $tableName = self::$sTable;
        }
        $bdd = self::dbConnect();
        return gettype( $bdd->exec( "SELECT count(*) FROM $tableName" ) ) == "integer";
    }

    /**
     * getStructure
     *
     * @param mixed   $table Description.
     * @param mixed   $io    Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function getStructure( $table = null, $io = true ) {
        if ( gettype( $table ) === 'NULL' ) {
            exit( 'No table defined' );
        }
        $bdd = self::dbConnect();
        if ( self::tableExists( $table ) ) {
            $response = $bdd->prepare( "DESCRIBE {$table}" );
            $response->execute();
            while ( $row = $response->fetch( PDO::FETCH_ASSOC ) ) {
                $total = Extras::pluralize( 'column', count( $row ) );
                $rows[] = $row;
                $fields[] = $row['Field'];
            }

            if ( $io ) {
                print "There is <b>{$total}</b> in <b>{$table}</b> table.<br />";
                // print_r($fields);
                print implode("<br>", array_map(function($str, $row){
                    return Extras::wrapper($str, 'input', array(), true).$row['Type'];
                }, $fields, $rows));
            }
            return $fields;
        }
        else {
            exit( "Table `{$table}` doesn't exist" );
        }
    }

/**
     * showTables
     *
     * @param mixed   $io Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
public function showTables( $io = true ) {
    $bdd = self::dbConnect();
    $sql = 'SHOW TABLES';
    $response = $bdd->prepare( $sql );
    $response->execute();

    while ( $row = $response->fetch() ) {
        $tables[] = $row[0];
    }
    if ( $io ) {
        echo 'There is '
            // total number of tables
        . Extras::pluralize( "table", count( $tables ) )
            // database name
        . ' into the '.self::DATABASE.' database:<br />'
            // liste of the tables
        . implode( '<br />', $tables ).'<br />';
    }
    return implode( ', ', $tables );
}

}

class Extras {

    public function __construct() {
        exit( 'Fobidden Access!' );
    }

    /**
     * pluralize
     *
     * @param mixed   $str   Description.
     * @param mixed   $count Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public static function pluralize( $str, $count ) {
        return $count > 1 ? "$count {$str}s" : "$count $str";
    }

    /**
     * wrap
     *
     * @param mixed   $str   Description.
     * @param string  $tag   Description.
     * @param string  $id    Description.
     * @param string  $class Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public static function wrap( $str, $tag = 'span', $id = '', $class = '' ) {
        return "<$tag id='$id' class='$class'>$str</$tag>";
    }
    public static function wrapper($str, $tag, $attributes = array(), $selfclose = false){
        if(!$selfclose)
            return "<$tag ".implode(' ', $attributes).">$str</$tag>";

        return "<$tag ".implode(' ', $attributes)." value='$str'/>";
    }

    /**
     * getIp
     *
     * @access public
     *
     * @return mixed Value.
     */
    public static function getIp() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) )
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

        return $ip;
    }

}

?>
