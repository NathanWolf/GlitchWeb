<?php

namespace com\elmakers\glitch;

require_once 'GlitchDatabase.class.php';

class GlitchAdminDatabase extends GlitchDatabase {
    public function __construct() {
        parent::__construct(true);
    }

    public function authorize($email, $token) {
        $user = $this->validateLogin($email, $token);
        if (!$user || !$user['admin']) {
            throw new Exception("Unauthorized user");
        }
    }
}
