<!DOCTYPE html>

<?php
require("db.php");
?>

<html>
<head>
	<title>san〜san</title>
	<link rel="stylesheet" href="style.css">
	<link rel="icon" href="media/icon.ico">
	<script src="script.js"></script>
</head>

<body style="width:60em">
	<img src="media/logo.svg">

	<div class="window">
		<div class="title">
			<img src="media/cross.png" onclick="win_close(this)">
			<h1>What is 3san?</h1>
		</div>
		<p>3san (san-san) is a (very) simple image board where anyone can post comments and share Images.</p>
	</div>

	<div class="window">
		<div class="title">
			<h1>Boards</h1>
		</div>
		<table id="boards"><tr>
<?php

$boards = $db->query("SELECT * FROM boards ORDER BY category");
$category = "";

foreach($boards as $board) {
	if($category != $board["category"]) {
		if($category != "") {
			echo "</td>";
		}

		$category = $board["category"];

		echo "<td><p class=\"category\">" . $category . "</p>";
	}

	$id = $board["id"];
	$name = $board["name"];
	echo "<a class=\"link\" href=\"board.php?id=" . $id . "\">" .$name . "</a><br>";
}

?>
		</tr>
		</table>
	</div>
</body>
</html>

