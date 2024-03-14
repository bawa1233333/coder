<?php 

global $wpdb;

// Table name
$table_name = $wpdb->prefix . "custom_input_data";

// SQL query to retrieve the latest record ordered by ID in descending order
$query = "SELECT * FROM $table_name ORDER BY id DESC LIMIT 1";

// Retrieve the result
$result = $wpdb->get_row($query);

    $full_name = $result->name;
    $email = $result->email;
    $birthdate = $result->birthdate;
	$first_letter = strtoupper(substr($full_name, 0, 1));

?>

<form method="GET">
	<input type="hidden" name="first_letter" value="<?php echo $first_letter; ?>">
	<input type="hidden" name="full_name" value="<?php echo $full_name; ?>">
	<a href="../game_one_letter?letter=<?php echo $first_letter; ?>">One Letter</a>
	<a href="../game_all_letters?letters=<?php echo $full_name; ?>">All Letters</a>
</form>
