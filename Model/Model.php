<?php
try {		
	class Model {
		public $db;

		function __construct()
	    {
			// Create connection
			$conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DATABASE);

			// Check connection
			if ($conn->connect_error) {
			    die("Connection failed: " . $conn->connect_error);
			} 
			// Set character set and collation
			$conn->set_charset("utf8");

	        $this->db = $conn;
	    }
	}
}
catch(PDOException $e)
{
	echo "Connection failed: " . $e->getMessage();
	exit;
}

?>