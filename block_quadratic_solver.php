<?php
declare(strict_types=1);

class block_quadratic_solver extends block_base {
    public function init() {
        $this->title = get_string('quadratic_solver', 'block_quadratic_solver');
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = $this->display_form();

        return $this->content;
    }

    private function display_form()
    {
        $result = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['a'], $_POST['b'], $_POST['c'])) {
            try {
                $a = $this->validate_input($_POST['a']);
                $b = $this->validate_input($_POST['b']);
                $c = $this->validate_input($_POST['c']);

                if ($a == 0) {
                    throw new Exception('Ошибка: значение "a" не может быть равно нулю.');
                }

                $solution = $this->solve_quadratic($a, $b, $c);
                $this->save_result($a, $b, $c, $solution['x1'], $solution['x2']);
                $result = $solution['message'];

            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        // основная форма с инпут боксами
        return '
    <form method="post" class="block_quadratic_solver">
        <div class="input-group">
            <label>a = </label>
            <input type="text" name="a" required placeholder="введите значение...">
        </div>
        <div class="input-group">
            <label>b = </label>
            <input type="text" name="b" required placeholder="введите значение...">
        </div>
        <div class="input-group">
            <label>c = </label>
            <input type="text" name="c" required placeholder="введите значение...">
        </div>
        
        <input type="submit" value="Найти решение">
        
        ' . ($error ? $this->display_error($error) : '') .
            ($result ? '<div class="results">' . $result . '</div>' : '') .

            '<div style="text-align: right; margin-top: 10px;">
            <a href="' . new moodle_url('/blocks/quadratic_solver/view.php') . '" 
               style="color: blue;">История</a>
        </div>
    </form>';
    }

    private function display_error($message) {
        global $OUTPUT;
        return $OUTPUT->notification($message, 'notifyproblem');
    }

    private function validate_input($value): float {
        // для поддержки разных форматов ввода
        $value = str_replace(',', '.', $value);

        // проверка на числовое значение
        if (!is_numeric($value)) {
            throw new Exception('Ошибка: Введите числовое значение.');
        }

        // преобразование в float
        $float_value = (float)$value;

        // проверка конечности значения
        if (!is_finite($float_value)) {
            throw new Exception('Ошибка: Введено слишком большое или слишком маленькое значение.');
        }

        return $float_value;
    }

    // основная логика решения КУ
    private function solve_quadratic(float $a, float $b, float $c): array {
        $discriminant = $b * $b - 4 * $a * $c;

        if ($discriminant < 0) {
            return ['message' => 'Корней нет, дискриминант меньше нуля.', 'x1' => null, 'x2' => null];
        }

        if ($discriminant == 0) {
            $x1 = -$b / (2 * $a);
            $x1 = ($x1 === -0.0) ? 0 : $x1;
            return ['message' => "Единственный корень: x = {$x1}.", 'x1' => $x1, 'x2' => null];
        } else {
            $x1 = (-$b + sqrt($discriminant)) / (2 * $a);
            $x2 = (-$b - sqrt($discriminant)) / (2 * $a);
            return ['message' => "Корни уравнения: x1 = {$x1}, x2 = {$x2}.", 'x1' => $x1, 'x2' => $x2];
        }
    }

    private function save_result(float $a, float $b, float $c, ?float $x1, ?float $x2) {
        global $DB;
        $record = new stdClass();
        $record->a = $a;
        $record->b = $b;
        $record->c = $c;
        $record->x1 = $x1;
        $record->x2 = $x2;
        $record->timecreated = time();
        $DB->insert_record('block_quadratic_solver_results', $record);
    }
}
