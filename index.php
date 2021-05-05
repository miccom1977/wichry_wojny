<?php
// Defines
include('defines.php');

// Klasa debugowa
require_once(BASE_DIR . 'includes/core/Debug_class.php');

// Error handling
debug::startErrorCollect();

// GLOWNA KLASA SILNIKA //
require_once(BASE_DIR . 'includes/core/Core_class.php');

require_once BASE_DIR . 'library/Twig/Autoloader.php';
Twig_Autoloader::register();

$app = NEW init();
$app -> getDependencies(); // DI

$mainframe = init::getFactory();

// Uruchamiamy strone
$mainframe -> component -> checkAccess() -> getPageContent();

debug::stopErrorCollect();

?>
