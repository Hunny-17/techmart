<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Validator đơn giản.
 *
 * Rules hỗ trợ: required, email, min:N, max:N, numeric, integer, in:a,b,c.
 * min/max sẽ kiểm tra giá trị số nếu field có rule numeric/integer,
 * ngược lại sẽ kiểm tra độ dài chuỗi.
 */
final class Validator
{
    private array $errors = [];

    /**
     * @param array<string,mixed> $data
     * @param array<string,array<int,string>> $rules
     */
    public function __construct(
        private array $data,
        private array $rules
    ) {
        $this->validate();
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;
            $numericContext = in_array('numeric', $rules, true) || in_array('integer', $rules, true);

            foreach ($rules as $rule) {
                if (!$this->applyRule($field, $value, $rule, $numericContext)) {
                    break;
                }
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule, bool $numericContext): bool
    {
        [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

        switch ($name) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && $value === [])) {
                    return $this->fail($field, 'là bắt buộc');
                }
                break;

            case 'email':
                if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return $this->fail($field, 'không đúng định dạng email');
                }
                break;

            case 'min':
                if ($numericContext && is_numeric($value) && (float)$value < (float)$param) {
                    return $this->fail($field, "phải >= $param");
                }
                if (!$numericContext && is_string($value) && mb_strlen($value) < (int)$param) {
                    return $this->fail($field, "phải có ít nhất $param ký tự");
                }
                break;

            case 'max':
                if ($numericContext && is_numeric($value) && (float)$value > (float)$param) {
                    return $this->fail($field, "phải <= $param");
                }
                if (!$numericContext && is_string($value) && mb_strlen($value) > (int)$param) {
                    return $this->fail($field, "không được quá $param ký tự");
                }
                break;

            case 'numeric':
                if ($value !== '' && !is_numeric($value)) {
                    return $this->fail($field, 'phải là số');
                }
                break;

            case 'integer':
                if ($value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    return $this->fail($field, 'phải là số nguyên');
                }
                break;

            case 'in':
                $allowed = explode(',', (string)$param);
                if (!in_array((string)$value, $allowed, true)) {
                    return $this->fail($field, 'không nằm trong giá trị cho phép');
                }
                break;
        }

        return true;
    }

    private function fail(string $field, string $message): bool
    {
        $this->errors[$field] = $field . ' ' . $message;
        return false;
    }
}
