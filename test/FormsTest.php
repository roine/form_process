<?php
require_once "PHPUnit/Autoload.php";
require_once "forms_process.class.php";

class formsTest  extends PHPUnit_Framework_TestCase
{
  protected $form;

  function setUp(){
    $_POST["fn"] = "jonathan";
    $_POST["ln"] = "de montalembert";
    $_POST["mphone"] = "+8618600014793";
    $_POST["mail"] = "leunetre@hotmail.com";
    $_POST["country"] = "france";
    $_POST["city"] = "paris";
    $_POST['lang'] = 'en';
    $this->form = new Form($_POST);
  }

  function  testConstructor(){
    try{
      $b = new Form();
    }
    catch(Exception $e){
      return;
    }
    $this->fail('An expected exception has not been raised.');
  }

  function testSetFieldsException(){
    try{
      return $result = $this->form->setFields('');
    }
    catch(Exception $e){
      return;
    }
    $this->fail('An expected exception has not been raised.');
  }

  function testSetValuesAliasOfSetField(){
    $this->assertTrue(gettype($this->form->setValues('')) == 'object');
  }

  function testSetFields(){
    $this->form->aFields = array('fn');
    $this->form->setFields('ln email phone')->setFields('yoursistername')->add('ip', '192.168.0.1');
    $expected = array('fn', 'ln', 'email', 'phone', 'yoursistername', 'ip');
    $this->assertEquals($this->form->aFields, $expected);
  }


}