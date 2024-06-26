<?php
require_once 'GlitchDatabase.class.php';
require_once 'utilities.inc.php';

try {
    $action = getParameter('action');
    $db = new com\elmakers\glitch\GlitchDatabase();

    switch ($action) {
        case 'login':
            $email = getParameter('email');
            $password = getParameter('password');
            $user = $db->login($email, $password);
            die(json_encode(array('success' => true, 'user' => $user)));
        case 'logout':
            $userId = getParameter('user');
            $token = getParameter('token');
            $db->logout($userId, $token);
            die(json_encode(array('success' => true)));
        case 'register':
            $email = getParameter('email');
            $password = getParameter('password');
            $firstName = getParameter('first', '');
            $lastName = getParameter('last', '');
            $user = $db->createUser($email, $password, $firstName, $lastName);
            die(json_encode(array('success' => true, 'user' => $user)));
        case 'save';
            $userId = getParameter('user');
            $token = getParameter('token');
            $user = $db->validateLogin($userId, $token);
            $property = getParameter('property');
            $value = getParameter('value');
            $db->saveUserProperty($userId, $property, $value);
            $user['properties'][$property] = array(
                'property_id' => $property,
                'value' => $value
            );
            die(json_encode(array('success' => true, 'user' => $user)));
        case 'return':
            $userId = getParameter('user');
            $token = getParameter('token');
            $user = $db->validateLogin($userId, $token);
            die(json_encode(array('success' => true, 'user' => $user)));
        default:
            throw new Exception("Invalid action: $action");
    }
} catch (Exception $ex) {
    echo json_encode(array(
       'success' => false,
       'message' => $ex->getMessage()
    ));
    // error_log($ex->getTraceAsString());
}
