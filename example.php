
<?php
include("forms_process.class.php");

$_POST["fn"] = "jonathan";
$_POST["ln"] = "de montalembert";
$_POST["mphone"] = "+8618600014793";
$_POST["mail"] = "leunetre@hotmail.com";
$_POST["country"] = "france";
$_POST["city"] = "paris";
$_POST['lang'] = 'en';

$_POST['fullname'] = $_POST["fn"].' '.$_POST["ln"];
$form = new Form($_POST);
// if($form->setConnection('localhost', 'root', '', 'fuel_dev'))
// echo 'connected';
// else
// echo 'not connected';

// $form->received();
// $form->getStructure("callback");
// $form->setConnection('localhost', 'root', '', 'fuel_dev');
$form->setValues("city fn mphone mail country lang")->setColumns("city name phone email country language")->setTable("form");
date_default_timezone_set('Asia/Shanghai');
$form->add(array("fromURL"=> "google", "website" => "cn"));
$form->addIP("user_ip");
$form->check("mail")->exist()->isEmail();
$form->check("mphone")->isPhone();
if($form->save())
echo 'success';
else
echo 'not saved';

