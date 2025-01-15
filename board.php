<!DOCTYPE html>

<?php
require("db.php");

if(isset($_POST["Comment"]) && file_exists($_FILES["File"]["tmp_name"])) {
	$name == "Anonymous";

	if(isset($_POST["Name"]) && trim($_POST["Name"]) != "") {
		$name = $_POST["Name"];
	}

	$r = $db->prepare("INSERT INTO threads
		(board, timestamp, name, comment)
		VALUES (:b, :t, :n, :c)");
	$r->execute([
		":b" => $_POST["Board"],
		":t" => time(),
		":n" => $name,
		":c" => $_POST["Comment"]
	]);

	$p = $db->prepare("SELECT * FROM threads WHERE board = :b AND name = :n AND comment = :c");
	$p->execute([
		":b" => $_POST["Board"],
		":n" => $name,
		":c" => $_POST["Comment"]
	]);

	$type = mime_content_type($_FILES["File"]["tmp_name"]);
	$convert = [
		"image/bmp" => function($filename) { return imagecreatefrombmp($filename); },
		"image/gif" => function($filename) { return imagecreatefromgif($filename); },
		"image/png" => function($filename) { return imagecreatefrompng($filename); },
		"image/tga" => function($filename) { return imagecreatefromtga($filename); },
		"image/avif" => function($filename) { return imagecreatefromavif($filename); },
		"image/jpeg" => function($filename) { return imagecreatefromjpeg($filename); },
		"image/webp" => function($filename) { return imagecreatefromwebp($filename); }
	];

	imagewebp($convert[$type]($_FILES["File"]["tmp_name"]), "uploads/threads/" . $p->fetch()["id"] . ".webp");
}
?>

<html>
<head>
<?php
$id = $_GET["id"];

$boardr= $db->prepare("SELECT * FROM boards WHERE id = ?");
$boardr->execute([$id]);
$board = $boardr->fetch();

echo "<title>[". $id . "] - " . $board["name"] . " - 3san</title>";
?>
	<link rel="stylesheet" href="style.css">
	<link rel="icon" href="media/icon.ico">
	<script src="script.js"></script>
</head>

<body>
	<a id="home" class="link" href="index.php">[home]</a>
	<img src="media/logo.svg">
<?php echo "<h1 class=\"title\">/" . $id . "/ - " . $board["name"] . "</h1>"; ?>
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

$page = 1;

if(isset($_GET["page"])) {
	$page = $_GET["page"];
}

$max = 15;
$min = ($page - 1) * $max;

$posts = $db->prepare("SELECT * FROM threads WHERE board = :b ORDER BY timestamp DESC LIMIT :min, :max");
$posts->bindParam(":b", $id, PDO::PARAM_STR);
$posts->bindParam(":min", $min, PDO::PARAM_INT);
$posts->bindParam(":max", $max, PDO::PARAM_INT);
$posts->execute();

foreach($posts as $post) {
	$image = "uploads/threads/" . $post["id"] . ".webp";
	$dimensions = getimagesize($image)[0] . "x" . getimagesize($image)[1];

	$timestamp = new DateTime("@" . $post["timestamp"], new DateTimeZone("UTC"));

	$replies = $db->prepare("SELECT COUNT(*) FROM replies WHERE thread = ?");
	$replies->execute([$post["id"]]);
	
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
	echo "<p>" . $timestamp->format("d/m/y H:i:s") . " (UTC)</p>";
	echo "<p>#" . $post["id"] . "</p>";
	echo "<p>(" . $replies->fetch()[0] . ")</p>";
	echo "</h1>";
	echo "<p class=\"comment\">" . nl2br($post["comment"]) . "</p>";
	echo "</div>";
}

echo "<p>";
if($page > 1) {
	$url = $_SERVER["REQUEST_URI"];
	$np = str_replace("page=" . $_GET["page"], "page=" . $_GET["page"] - 1, $url);

	echo "<a href=\"" . $np . "\">[prev]</a>";
}

$pagesr = $db->prepare("SELECT COUNT(*) FROM threads WHERE board = :b");
$pagesr->execute([$id]);
$pages = ceil($pagesr->fetch()[0] / 15);

echo " page " . $page . "/" . $pages . " ";

if($page < $pages) {
	$url = $_SERVER["REQUEST_URI"];
	$np = str_replace("page=" . $_GET["page"], "page=" . $_GET["page"] + 1, $url);

	echo "<a href=\"" . $np . "\">[next]</a>";
}
echo "</p>";
?>
</body>
</html>
