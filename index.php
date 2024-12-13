<?php
require_once 'core/models.php';

if (isHR()) {
    redirect('pages/hr/dashboard.php');
} elseif (isApplicant()) {
    redirect('pages/applicants/dashboard.php');
} else {
    redirect('pages/forms/login.php');
}
?>