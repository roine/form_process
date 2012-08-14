#MY FORM PROCESS#


First of all, you need to change the following informations into the DbProcess class by your own informations:
- __HOST__
- __USERNAME__
- __PASSWORD__
- __DATABASE__

Exemple of use:

	$form =  new Form($_POST);

the fields to be parse

	$form->fields("email lastname username");

the columns in the database

	$form->columns("mail lastname login");

the table name

	$form->table("form");

all these methods can be chained

	$form->fields("email lastname username")
		->columns("mail lastname login")
		->table("form");

This will add this field to the stack all the method after will use this data

	$form->check("mail");

to save the database into the database

	$form->save(); 

Here is an exemple how all the fields can be checked

	$form->check("mail")->isEmail()->exist()->check("phone")->isPhone()->save();

Here is the list of all the validation methods
- __isEmail__, to check whether it's an email
- __isPhone__, to check whether it's a phone number
- __exist__, to check whether the data is already in the database
- __maxLength__ & __minLength__, to limit the length

Next validations methods:
- it's a alphanumeric
- it's a digit
- it's alphabetic
- it's url

Here are the methods to get informations:
- DbProcess::showTables() return all the tables into the database
- DbProcess::getStructure("tableName") return the strucutre of the table
- Form::received() return the $_REQUEST

##V2 Coming##

- check differents types(phone, email...) √
- able to add custom(ip, date...) √
- send email
- fix the __call method with/without arguments


##V1##

- able to check whether already exist
- save into the database
- get some informations for developer

