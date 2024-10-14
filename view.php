<?php
require_once('../../config.php');
require_login();

$PAGE->set_url('/blocks/quadratic_solver/view.php');
$PAGE->set_heading('История решений квадратных уравнений');
$PAGE->set_title('История решений');

echo $OUTPUT->header();

global $DB;
$results = $DB->get_records('block_quadratic_solver_results');
if ($results) {
    echo '<table class="quadratic_solver_results">';
    echo '<tr><th>a</th><th>b</th><th>c</th><th>x1</th><th>x2</th><th>Дата</th></tr>';
    foreach ($results as $result) {
        echo '<tr>';
        echo '<td>' . $result->a . '</td>';
        echo '<td>' . $result->b . '</td>';
        echo '<td>' . $result->c . '</td>';

        if (is_null($result->x1) && is_null($result->x2)) {
            echo '<td colspan="2">Корней нет</td>';
        } elseif (is_null($result->x2)) {
            echo '<td>' . $result->x1 . '</td><td>Корня нет</td>';
        } else {
            echo '<td>' . $result->x1 . '</td><td>' . $result->x2 . '</td>';
        }

        echo '<td>' . date('Y-m-d H:i:s', $result->timecreated) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo 'История решений пуста.';
}

echo $OUTPUT->footer();
