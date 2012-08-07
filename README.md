#MY FORM PROCESS#


First of all, you need to change the following informations into the DbProcess class by your own informations:
- HOST
- USERNAME
- PASSWORD
- DATABASE
Exemple of use:

	$form =  new Form($_POST);

	$form->fields("email lastname username"); //the fields to be parse
	$form->columns("mail lastname login"); //the columns in the database
	$form->table("form"); //the table name
	// all these methods can be chained
	// $form->fields("email lastname username")->columns("mail lastname login")->table("form");
	$form->check("mail"); // This will check whether row already with the same mail already exist, You need to use the column name
	$form->save(); // to save the database into the database

There is also some methods to get informations:
- DbProcess::showTables() return all the tables into the database
- DbProcess::getStructure("tableName") return the strucutre of the table
- Form::received() return the $_REQUEST

##V1##

- able to check on column
- save into the database
- get some informations

