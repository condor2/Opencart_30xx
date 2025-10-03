<?php
/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:3.65|configurator
 * you can change this configuration by importing this file.
 */
$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setIndent("\t")
    ->setRules([
        '@PHP81Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'array_indentation' => true,
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => ['=' => 'align_single_space_minimal', '=>' => 'align_single_space_minimal'],
        ],
        'blank_line_before_statement' => ['statements' => ['declare', 'return', 'throw', 'try']],
        'braces_position' => ['classes_opening_brace' => 'same_line', 'functions_opening_brace' => 'same_line'],
        'concat_space' => ['spacing' => 'one'],
        'declare_parentheses' => true,
        'linebreak_after_opening_tag' => true,
        'indentation_type' => true,
        'multiline_whitespace_before_semicolons' => true,
        'single_blank_line_at_eof' => true,
        'empty_loop_body' => true,
        'empty_loop_condition' => true,
        'error_suppression' => true,
        'explicit_indirect_variable' => true,
        'explicit_string_variable' => true,
        'function_to_constant' => true,
        'heredoc_to_nowdoc' => true,
        'increment_style' => ['style' => 'post'],
        'is_null' => true,
        'list_syntax' => true,
        'long_to_shorthand_operator' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'modernize_types_casting' => true,
        'native_function_casing' => true,
        'native_type_declaration_casing' => true,
        'no_unused_imports' => true,
        'no_useless_return' => true,
        'nullable_type_declaration' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'object_operator_without_whitespace' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'pow_to_exponentiation' => true,
        'random_api_migration' => true,
        'return_assignment' => true,
        'simplified_if_return' => true,
        'standardize_not_equals' => true,
        'switch_continue_to_break' => true,
        'trailing_comma_in_multiline' => true,
        'types_spaces' => true,
        'use_arrow_functions' => true,
        'yield_from_array_to_yields' => true,
        'array_push' => false,
        'assign_null_coalescing_to_coalesce_equal' => true,
        'attribute_empty_parentheses' => true,
        'backtick_to_shell_exec' => true,
        'cast_spaces' => ['space' => 'none'],
        'class_attributes_separation' => ['elements' => ['method' => 'one']],
        'comment_to_phpdoc' => true,
        'date_time_create_from_format_call' => true,
        'logical_operators' => true,
        'method_chaining_indentation' => true,
        'no_alias_functions' => true,
        'no_alias_language_construct_call' => true,
        'no_binary_string' => true,
        'no_empty_statement' => true,
        'no_homoglyph_names' => true,
        'no_leading_namespace_whitespace' => true,
        'no_mixed_echo_print' => true,
        'no_trailing_whitespace' => true,
        'no_unneeded_braces' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unset_cast' => true,
        'no_unset_on_property' => true,
        'non_printable_character' => true,
        'operator_linebreak' => true,
        'regular_callable_call' => true,
        'self_accessor' => true,
        'self_static_accessor' => true,
        'set_type_to_cast' => true,
        'single_line_comment_style' => true,
        'single_line_throw' => true,
        'single_space_around_construct' => true,
        'space_after_semicolon' => true,
        'standardize_increment' => true,
        'string_length_to_empty' => true,
        'string_line_ending' => true,
        'type_declaration_spaces' => true,
        'unary_operator_spaces' => true,
        'whitespace_after_comma_in_array' => true,
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->in(__DIR__ . '/upload/')
         ->exclude([
             __DIR__ . '/upload/system/storage/vendor/',
         ])
        // ->append([
        //     'file-to-include',
        // ])
    )
;
