<?php
$hash = '$2y$10$i/y0W0wJCmL7n7TQYwFMtOvhcE9nmtY8ewo1GG4scax1lmJVGvdSK';

if (password_verify("Strategic123#", $hash)) {
    echo "? MATCH";
} else {
    echo "? FAIL";
}