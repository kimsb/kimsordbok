<?php
	$word = $_POST['word'];
						
	$mysql_host = "localhost";
	$mysql_database = "ordbok";
	$mysql_user = "kim";
	$mysql_password = "kimmysqlordbok";
	
	$mysql_connection = mysql_connect($mysql_host, $mysql_user, $mysql_password) or die(mysql_error());
	mysql_query("SET NAMES 'utf8'");
	mysql_select_db($mysql_database, $mysql_connection);
	
	function mbStringToArray ($string) { 
		$strlen = mb_strlen($string); 
		while ($strlen) { 
			$array[] = mb_substr($string,0,1,"UTF-8"); 
			$string = mb_substr($string,1,$strlen,"UTF-8"); 
			$strlen = mb_strlen($string); 
		} 
		return $array; 
	} 
	$word = mb_strtoupper($word, 'UTF-8');
	$split = mbStringToArray($word);
	natcasesort($split);
	$alpha = implode($split);
	
	//lagrer originalstatus av ordet
	$sql = "SELECT * FROM dictionary WHERE word = '$word'";
	$status = "notPresent";
	$result = mysql_query($sql, $mysql_connection);
	if (mysql_num_rows($result) !== 0) {
		$row = mysql_fetch_array($result);
		if ($row[isValid]) {
			$status = "valid";
		} else {
			$status = "uncertain";
		}
	}
	$sql = "INSERT IGNORE INTO beforeChanges (word, status) VALUES ('$word', '$status')";
	mysql_query($sql, $mysql_connection);
	
	//lagrer endringen
	date_default_timezone_set("Europe/Oslo");
	$timestamp = date("Y m j H:i:s");
	$username = ucfirst($_SERVER['REMOTE_USER']);
	$action = "added";
	$sql = "INSERT INTO changes (timestamp, username, action, word) VALUES ('$timestamp', '$username', '$action', '$word')";
	mysql_query($sql, $mysql_connection);
	
	//legger til i databasen
	$sql = "INSERT INTO dictionary (alpha, word, isValid) VALUES ('$alpha', '$word', 1)";
	mysql_query($sql, $mysql_connection);
?>
