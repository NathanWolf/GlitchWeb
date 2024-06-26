<?php
header("Content-Type: application/json");
require_once 'GlitchDatabase.class.php';

try {
    $db = new \com\elmakers\glitch\GlitchDatabase();
    $characters = $db->getCharacters();
    $tiers = $db->getTierLists();
    $relationships = $db->getRelationships();
    $properties = $db->getProperties();
    $quizzes = $db->getQuizzes();
    $response = array(
        'success' => true,
        'characters' => $characters,
        'quizzes' => $quizzes,
        'tiers' => $tiers,
        'relationships' => $relationships,
        'properties' => $properties
    );
} catch (Exception $ex) {
    $response = array(
        'success' => false,
        'message' => $ex->getMessage()
    );
}

echo json_encode($response);
