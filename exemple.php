
<?php
include("forms_process.class.php");

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