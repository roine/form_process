
<?php
include("forms_process.class.php");

$_POST["fn"] = "jonathan";
$_POST["ln"] = "de montalembert";
$_POST["mphone"] = "+8618600014793";
$_POST["mail"] = "leunetre@hotmail.com";
$_POST["country"] = "france";


$form = new Form($_POST);

// $form->received();
// $form->getStructure("form");

$form->setFields("fn ln mphone mail country")->setColumns("firstname lastname phone email country")->setTable("form");
date_default_timezone_set('Asia/Shanghai');
$form->add(array("fromURL"=> "google", "website" => "cn"));
$form->addIP("user_ip");
$form->received();
// echo $form->showTables();
// $form->getStructure("form");
$form->check("mail")->exist()->isEmail();
$form->check("mphone")->isPhone()->save();
?>
<script>
var d = document;
calc1 = d.createElement("input");
cal1.setAttribute("name", "calc1");
calc1.value = Math.floor(Math.random()*101)
d.getElementsByTagName("input").appendChild(calc1);

calc2 = d.createElement("input");
calc2.setAttribute("name", "calc2");
calc1.value = Math.floor(Math.random()*101)
d.getElementsByTagName("input").appendChild(calc2);