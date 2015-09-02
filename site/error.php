<?php
if (count(get_included_files()) == 1) { exit("Direct access not permitted."); }

header($_SERVER['SERVER_PROTOCOL'] . " 404 Not Found");
include(DIR_INCLUDE . "/header.php")
?>

<header>
    <h1>Error</h1>
    <h2>The resource was not found</h2>
</header>

<?php include(DIR_INCLUDE . "/footer.php")?>

