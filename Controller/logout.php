<?php
session_unset();
session_destroy();

header("Location: http://localhost:8000/index.php");
