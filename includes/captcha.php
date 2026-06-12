<?php
// includes/captcha.php - Reusable, session-based Math CAPTCHA helper

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get or generate the current math CAPTCHA question for a form.
 *
 * @param string $form_id Unique identifier for the form (e.g. 'contact', 'join', 'newsletter')
 * @return string The mathematical expression (e.g. "4 + 7")
 */
function get_captcha_question($form_id) {
    if (!isset($_SESSION['captcha_q_' . $form_id]) || !isset($_SESSION['captcha_a_' . $form_id])) {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $_SESSION['captcha_q_' . $form_id] = "$num1 + $num2";
        $_SESSION['captcha_a_' . $form_id] = $num1 + $num2;
    }
    return $_SESSION['captcha_q_' . $form_id];
}

/**
 * Validate the user's answer and immediately unset the session question to prevent replay attacks.
 *
 * @param string $form_id Unique identifier for the form
 * @param string|int $user_answer The answer entered by the user
 * @return bool True if correct, false otherwise
 */
function check_captcha_answer($form_id, $user_answer) {
    if (!isset($_SESSION['captcha_a_' . $form_id])) {
        return false;
    }
    $correct = (int)$_SESSION['captcha_a_' . $form_id];
    
    // Always clear the challenge once validated (success or fail)
    unset($_SESSION['captcha_q_' . $form_id]);
    unset($_SESSION['captcha_a_' . $form_id]);
    
    return (int)$user_answer === $correct;
}

/**
 * Manually reset/invalidate the current CAPTCHA challenge.
 *
 * @param string $form_id Unique identifier for the form
 */
function reset_captcha($form_id) {
    unset($_SESSION['captcha_q_' . $form_id]);
    unset($_SESSION['captcha_a_' . $form_id]);
}
?>
