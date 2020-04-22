<?php

include __DIR__ . '/../setup.php';

use lib\components\Alertbox;
use lib\db\Connection;
use lib\db\Messages;
use lib\service\SessionManagerPhp;
use lib\service\SymmetricEncryption;

$sessMgr = new SessionManagerPhp();
$user = $sessMgr->getAuthenticatedUser();
if (!$user) {
    header('Location: /login.php?message=login-required', true, 303);
    exit();
}

try {
    $encryption = SymmetricEncryption::fromEnvironment();
    $message = new Messages(Connection::get_db_pdo(), $encryption);
    $boxMessages = $message->loadMessageViewsByRecipient($user->getId());
} catch (Exception $e) {
    echo 'Internal Server Error';
    http_response_code(500);
}

$pageTitle = 'Inbox';

ob_start();

if (isset($_GET['message'])) {
    $msg = '';
    switch ($_GET['message']) {
        case 'already-authenticated':
            $msg = "You are already authenticated.";
            break;
        case 'login-successful':
            $msg = 'Logged in successfully';
    }
    if (!empty($msg)) {
        echo '<section class="section">' . Alertbox::renderInfo(htmlspecialchars($msg)) . '</section>';
    }
}

include TEMPLATE_DIR . '/pages/message-box.php';
$htmlContent = ob_get_clean();

include TEMPLATE_DIR . '/page.php';
