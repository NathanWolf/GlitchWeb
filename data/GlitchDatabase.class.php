<?php

namespace com\elmakers\glitch;

use Exception;

require_once 'Database.class.php';

class GlitchDatabase extends Database {
    public function createUser($email, $password, $firstName, $lastName) {
        $existing = $this->lookupUser($email);
        if ($existing) {
            throw new Exception("User $email already exists");
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = $this->generateToken();
        $user = array(
            'email' => $email,
            'password_hash' => $hash,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'token' => $token
        );
        $this->insert('user', $user);
        $user = $this->lookupUser($email);
        if (!$user) {
            throw new Exception("Failed to create new user account");
        }
        $this->sanitize($user);
        $user['properties'] = array();
        return $user;
    }

    private function generateToken() {
        return bin2hex(random_bytes(16));;
    }

    public function login($email, $password) {
        $user = $this->lookupUser($email);
        if (!$user) {
            throw new Exception("Unknown user $email");
        }
        if (!password_verify($password, $user['password_hash'])) {
            throw new Exception("Incorrect password for user $email");
        }
        if (!$user['token']) {
            $user['token'] = $this->generateToken();
            $this->saveUser($user);
        }
        $this->sanitize($user);
        return $user;
    }

    public function sanitize(&$user) {
        unset($user['password_hash']);
    }

    public function changePassword($userId, $token, $password) {
        $user = $this->validateLogin($userId, $token);
        $user['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        $this->saveUser($user);
    }

    public function forceChangePassword($email, $password) {
        $user = $this->lookupUser($email);
        if (!$user) {
            throw new Exception("Invalid user $email");
        }
        $user['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        $this->saveUser($user);
    }

    public function validateLogin($userId, $token) {
        $user = $this->getUser($userId);
        if (!$user || $user['token'] !== $token) {
            throw new Exception("Invalid login for $userId");
        }
        $this->sanitize($user);
        return $user;
    }

    public function logout($userId, $token) {
        $this->validateLogin($userId, $token);
        $this->execute('UPDATE user SET token=null WHERE id=:id', array('id' => $userId));
    }

    private function processUser(&$user) {
        if ($user) {
            $user['properties'] = $this->getUserProperties($user['id']);
        }
    }

    public function lookupUser($email) {
        $user = $this->get('user', $email, 'email');
        $this->processUser($user);
        return $user;
    }

    public function getUser($userId) {
        $user = $this->get('user', $userId);
        $this->processUser($user);
        return $user;
    }

    public function saveUser($user) {
        unset($user['properties']);
        $this->save('user', $user);
    }

    public function getUserProperties($userId) {
        $properties = $this->query('SELECT property_id, value FROM user_property WHERE user_id = :id',
            array('id' => $userId)
        );
        return $this->index($properties, 'property_id');
    }

    public function getTierLists() {
        $tierLists = $this->getAll('tier_list');
        $tiers = $this->getAll('tier', 'priority desc');

        $results = array();
        foreach ($tierLists as $tierList) {
            $tierList['tiers'] = array();
            $results[$tierList['id']] = $tierList;
        }
        foreach ($tiers as $tier) {
            $results[$tier['tier_list_id']]['tiers'][$tier['id']] = $tier;
        }

        return $results;
    }

    public function getCharacters() {
        // Note that the db name is "persona" to avoid issues with "character" being a reserved word.
        $characters = $this->getAll('persona');
        $relationships = $this->getAll('persona_relationship');
        $tiers = $this->getAll('persona_tier');
        $properties = $this->getAll('persona_property');

        $results = array();
        foreach ($characters as $character) {
            $character['tiers'] = array();
            $character['relationships'] = array();
            $character['properties'] = array();
            $character['name'] = $character['first_name'];
            $character['full_name'] = $character['first_name'];
            if ($character['last_name']) {
                $character['full_name'] .= ' ' . $character['last_name'];
            }
            if ($character['chat']) {
                $character['chat'] = json_decode($character['chat'], true);
            }
            if ($character['portrait']) {
                $character['portrait'] = json_decode($character['portrait'], true);
            }
            $results[$character['id']] = $character;
        }
        foreach ($relationships as $relationship) {
            if (isset($results[$relationship['persona_id']]['relationships'][$relationship['relationship_id']])) {
                $results[$relationship['persona_id']]['relationships'][$relationship['relationship_id']][] = $relationship['related_persona_id'];
            } else {
                $results[$relationship['persona_id']]['relationships'][$relationship['relationship_id']] = array($relationship['related_persona_id']);
            }
        }
        foreach ($tiers as $tier) {
            $results[$tier['persona_id']]['tiers'][$tier['tier_list_id']] = $tier;
        }
        foreach ($properties as $property) {
            $results[$property['persona_id']]['properties'][$property['property_id']] = $property['value'];
        }

        return $results;
    }

    public function getQuizzes() {
        $quizzes = $this->getAll('quiz');
        $quizzes = $this->index($quizzes);
        foreach ($quizzes as &$quizRef) {
            $quizRef['questions'] = array();
        }
        $questions = $this->getAll('quiz_question');
        foreach ($questions as &$questionRef) {
            $questionRef['answers'] = array();
        }
        $questions = $this->index($questions);
        $answers = $this->getAll('quiz_answer');
        foreach ($answers as $answer) {
            if (isset($questions[$answer['quiz_question_id']])) {
                $questions[$answer['quiz_question_id']]['answers'][] = $answer;
            }
        }
        foreach ($questions as $question) {
            if (isset($quizzes[$question['quiz_id']])) {
                $quizzes[$question['quiz_id']]['questions'][] = $question;
            }
        }
        return $quizzes;
    }

    public function getRelationships() {
        $relationships = $this->getAll('relationship');
        $relationships = $this->index($relationships);
        return $relationships;
    }

    public function getProperties() {
        $properties = $this->getAll('property', 'question, priority desc');
        $properties = $this->index($properties);
        return $properties;
    }

    public function saveUserProperty($userId, $propertyId, $value) {
        $property = $this->queryOne(
            'user_property',
            'user_id = :user AND property_id = :property',
            array('user' => $userId, 'property' => $propertyId)
        );
        if (!$property) {
            $property = array('user_id' => $userId, 'property_id' => $propertyId, 'value' => $value);
            $this->insert('user_property', $property);
        } else {
            $property['value'] = $value;
            $this->save('user_property', $property, 'user_id', 'property_id');
        }
    }
}
