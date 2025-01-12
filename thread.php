<!DOCTYPE html>

<?php
require("db.php");

if($_POST["Name"] && $_POST["Comment"] && $_FILES["File"]) {
	$r = $db->prepare("INSERT INTO threads
		(thread, timestamp, name, comment)
		VALUES (:b, :t, :n, :c)");
	$p = $r->execute([
		":t" => $_POST["Thread"],
		":t" => time(),
		":n" => $_POST["Name"],
		":c" => $_POST["Comment"]
	]);

	if(getimagesize($_FILES["File"]["tmp_name"])) {
		$image = imagecreatefromjpeg($_FILES["File"]["tmp_name"]);
		imagewebp($image, "uploads/" . $p["id"] . ".webp");
	}
}
?>

<html>
<head>
<?php
$id = $_GET["id"];

$q = $db->prepare("SELECT * FROM boards WHERE id = ?");
$q->execute([$id]);
$name = $q->fetchAll()[0]["name"];

echo "<title>[". $id . "] - " . $name . " - 3san</title>";
?>
	<link rel="stylesheet" href="style.css">
	<link rel="icon" href="media/icon.ico">
	<script src="script.js"></script>
</head>

<body>
	<img src="media/logo.svg">
<?php echo "<h1 class=\"title\">/" . $id . "/ - " . $name . "</h1>"; ?>
<h1 class="title link" onclick="showPostForm(this)">[Start a New Thread]</h1>
<form id="post" class="hidden" method="POST" enctype="multipart/form-data">
	<table>
		<tr>
			<th>Name</th>
			<td><input name="Name" placeholder="Anon"></td>
		</tr>
		<tr>
			<th>Comment</th>
			<td><textarea name="Comment"></textarea></td>
		</tr>
		<tr>
			<th>File</th>
			<td><input type="file" name="File" placeholder="Anon"></td>
		</tr>
	</table>
	<input type="hidden" name="Board" value="<?php echo $id; ?>">
	<input type="submit" value="Post">
</form>

<?php

$min = 0;
$max = 15;

if($_GET["page"]) {
	$min = ($_GET["page"] - 1) * $max;
}

$posts = $db->prepare("SELECT * FROM threads WHERE board = ? LIMIT " . $min . ", " . $max);
$posts->execute([$_GET["id"]]);

foreach($posts as $post) {
	$image = "uploads/" . $post["id"] . ".webp";
	$dimensions = getimagesize($image)[0] . "x" . getimagesize($image)[1];

	$timestamp = date("d/m/y H:i:s (e)");
	
	$units = array("B", "KB", "MB");
	$bytes = max(filesize($image), 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);
	$bytes /= (1 << (10 * $pow));
	$size = round($bytes, 2) . $units[$pow];
	
	echo "<div class=\"post\">";
	echo "<div class=\"image\">";
	echo "<p><a href=\"" . $image . "\">File</a> (" . $size . ", " . $dimensions . ")</p>";
	echo "<img src=\"" . $image . "\">";
	echo "</div>";
	echo "<h1><a class=\"link\" href=\"thread.php?id=" . $post["id"] . "\">" . $post["name"] . "</a>";
	echo "<p>" . $timestamp . "</p>";
	echo "<p>#" . $post["id"] . "</p>";
	echo "</h1>";
	echo "<p>" . nl2br($post["comment"]) . "</p>";
	echo "</div>";
}

?>
</body>
</html>
