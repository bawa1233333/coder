<?php 
// Initialize variables
$first_letter = '';
$full_name = '';

if(isset($_GET['letters'])) {
	$full_name = sanitize_text_field($_GET['letters']);
	$first_letter = strtoupper(substr($full_name, 0, 1));
}
?>

<form method="GET">
	<input type="hidden" name="first_letter" value="<?php echo $first_letter; ?>">
	<input type="hidden" name="full_name" value="<?php echo $full_name; ?>">
	<a href="../game_one_letter?letter=<?php echo $first_letter; ?>">One Letter</a>
	<a href="../game_all_letters?letters=<?php echo $full_name; ?>">All Letters</a>
</form>

