<?php
/* register.php now redirects to login.php with register panel open
   The register form lives inside login.php to keep one unified auth page */
header('Location: index.php?action=login&panel=register');
exit;