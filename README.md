#MY FORM PROCESS#


First of all, you need to change the following informations into the DbProcess class by your own informations:
- HOST
- USERNAME
- PASSWORD
- DATABASE
Exemple of use:

	$form =  new Form($_POST);

	//the fields to be parse
	$form->fields("email lastname username");

	//the columns in the database
	$form->columns("mail lastname login");

	//the table name
	$form->table("form");

	// all these methods can be chained
	$form->fields("email lastname username")
		->columns("mail lastname login")
		->table("form");

	// This will check whether row already exist with the same mail already exist, You need to use the column name
	$form->check("mail"); 

	// to save the database into the database
	$form->save(); 

There is also some methods to get informations:
- DbProcess::showTables() return all the tables into the database
- DbProcess::getStructure("tableName") return the strucutre of the table
- Form::received() return the $_REQUEST

##V2 Coming##

- check differents types(phone, email...)
- able to add custom(ip, date...)


##V1##

- able to check whether already exist
- save into the database
- get some informations for developer

