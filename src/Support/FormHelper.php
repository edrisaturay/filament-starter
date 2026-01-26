<?php

namespace EdrisaTuray\FilamentStarter\Support;

use Cheesegrits\FilamentPhoneNumbers\Forms\Components\PhoneNumber;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\HtmlString;

class FormHelper
{
    /**
     * Create a standard text input field.
     */
    public static function textField(
        string $name,
        ?string $label = null,
        bool $required = false,
        string $type = 'text',
        ?string $placeholder = null,
        bool $disabled = false,
        int|string|null $columnSpan = null,
        ?string $helperText = null,
        ?string $hint = null,
    ): TextInput {
        $field = TextInput::make($name)
            ->label($label)
            ->type($type)
            ->placeholder($placeholder)
            ->disabled($disabled)
            ->columnSpan($columnSpan)
            ->helperText($helperText)
            ->hint($hint);

        if ($required) {
            $field->required();
        }

        return $field;
    }

    /**
     * Create a numeric input field.
     */
    public static function numericField(
        string $name,
        ?string $label = null,
        bool $required = false,
        ?int $min = null,
        ?int $max = null,
        string|float|null $step = null,
    ): TextInput {
        return self::textField($name, $label, $required)
            ->numeric()
            ->minValue($min)
            ->maxValue($max)
            ->step($step);
    }

    /**
     * Create a textarea field.
     */
    public static function textareaField(
        string $name,
        ?string $label = null,
        int $rows = 3,
        bool $required = false,
        int|string|null $columnSpan = 'full',
    ): Textarea {
        $field = Textarea::make($name)
            ->label($label)
            ->rows($rows)
            ->columnSpan($columnSpan);

        if ($required) {
            $field->required();
        }

        return $field;
    }

    /**
     * Create a select field.
     */
    public static function selectField(
        string $name,
        array|\Closure $options,
        ?string $label = null,
        bool $required = false,
        bool $searchable = true,
        bool $multiple = false,
        bool $preload = true,
    ): Select {
        $field = Select::make($name)
            ->label($label)
            ->options($options)
            ->searchable($searchable)
            ->multiple($multiple)
            ->preload($preload);

        if ($required) {
            $field->required();
        }

        return $field;
    }

    /**
     * Create a toggle field.
     */
    public static function toggleField(
        string $name,
        ?string $label = null,
        string|HtmlString|null $helperText = null,
        bool $default = false,
    ): Toggle {
        return Toggle::make($name)
            ->label($label)
            ->helperText($helperText)
            ->default($default);
    }

    /**
     * Create a date time picker field.
     */
    public static function dateTimeField(
        string $name,
        ?string $label = null,
        bool $required = false,
        bool $native = false,
    ): DateTimePicker {
        $field = DateTimePicker::make($name)
            ->label($label)
            ->native($native);

        if ($required) {
            $field->required();
        }

        return $field;
    }

    /**
     * Create a phone number field.
     */
    public static function phoneField(
        string $name,
        ?string $label = null,
        bool $required = false,
        bool $displayNumberFormat = true,
        bool $validate = true,
    ): PhoneNumber {
        $field = PhoneNumber::make($name)
            ->label($label)
            ->displayNumberFormat($displayNumberFormat)
            ->validate($validate);

        if ($required) {
            $field->required();
        }

        return $field;
    }

    /**
     * Create a repeater field.
     */
    public static function repeaterField(
        string $name,
        array $schema,
        ?string $label = null,
        string $addActionLabel = 'Add Item',
        bool $collapsible = true,
    ): Repeater {
        return Repeater::make($name)
            ->label($label)
            ->schema($schema)
            ->addActionLabel($addActionLabel)
            ->collapsible($collapsible);
    }
}
