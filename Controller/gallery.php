<?php require(__DIR__ . "/../View/header/in-app-header.php"); ?>

<h3>Pictures Gallery</h3>
<?php
    require_once(__DIR__ . "/../Model/manageDatabase.php");
    $db = new ManageDatabase;
	if (!isset($_SERVER['QUERY_STRING'])) {
		$i = 0;
	} else {
		parse_str($_SERVER['QUERY_STRING'], $query);
		if (isset($query['imageIndex'])) {
			$i = intval($query['imageIndex']); 
		} else { $i = 0; } 
	}

    $pics = $db->getPictures();
	if (count($pics) === 0) { echo "<p>No pictures exist yet.</p>"; }
	while ($i < count($pics)) {
		$pic = $pics[$i];
		echo "<img src='$pic->imageData' width='320' height='240'>";
		$i++;
		if ($i % 9 === 0) { break; }
		if ($i % 3 === 0) { echo "<br><br>"; } else { echo "&emsp;"; }
	}
	$back_page_index = (($i % 9 === 0) ? ($i - 18) : ($i - ($i % 9) - 9)); 
	$next_page_index = $i;
?>
<br><br>
<?php if ($i > 9) { ?>
	<button onClick="window.location.href='/gallery.php?imageIndex=<?php echo $back_page_index; ?>'">
		Back</button>
<?php } ?>
<?php if ($i < count($pics)) { ?>
	<button onClick="window.location.href='/gallery.php?imageIndex=<?php echo $next_page_index; ?>'">
		Next</button>
<?php } ?>
<br><br>

<?php require(__DIR__ . "/../View/footer/footer.html"); ?>

<script>
function changePage(direction, newPage) {
    fetch(`http://localhost:8000/gallery.php?imageIndex=${newPage}`, {
        method: "GET", 
    });
}	
</script>
