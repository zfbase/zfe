<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

$header = <<<'EOF'
ZFE – платформа для построения редакторских интерфейсов.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->exclude('src')
    ->exclude('node_modules')
    ->exclude('vendor')
    ->name('*.phtml')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@PHP71Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => true,
        'class_attributes_separation' => ['elements' => ['method']],
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'concat_space' => ['spacing' => 'one'],
        'dir_constant' => true,
        'explicit_indirect_variable' => true,
        'explicit_string_variable' => true,
        'full_opening_tag' => false,  // В шаблонах длинные php-теги (*.phtml) излишни.
        'function_to_constant' => ['functions' => ['get_class', 'get_called_class', 'php_sapi_name', 'phpversion', 'pi']],
        'header_comment' => ['header' => $header],
        'heredoc_to_nowdoc' => true,
        'logical_operators' => true,
        'lowercase_static_reference' => true,
        'mb_str_functions' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
        'no_binary_string' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_homoglyph_names' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],
        'no_null_property_initialization' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_around_offset' => true,
        'no_superfluous_elseif' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unneeded_curly_braces' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'non_printable_character' => true,
        'not_operator_with_space' => true,
        'object_operator_without_whitespace' => true,
        'ordered_imports' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_align' => ['tags' => ['param', 'property', 'return', 'throws', 'type', 'var', 'method']],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order' => true,
        'phpdoc_return_self_reference' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_to_return_type' => false,  // хорошо бы включить, но заебешься обновлять существующие проекты
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_types' => true,
        'phpdoc_var_without_name' => true,
        'psr4' => true,
        'random_api_migration' => true,
        'return_assignment' => true,
        'return_type_declaration' => true,
        'self_accessor' => true,
        'semicolon_after_instruction' => false,  // В шаблонах теги <?= содержат одно выражение и нет смысла в точке с запятой
        'set_type_to_cast' => true,
        'short_scalar_cast' => true,
        'single_blank_line_before_namespace' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],
        'single_quote' => true,
        'space_after_semicolon' => ['remove_in_empty_for_expressions' => true],
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'string_line_ending' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_null_coalescing' => true,
        'trailing_comma_in_multiline_array' => true,
        'trim_array_spaces' => true,
        'unary_operator_spaces' => false,
        'visibility_required' => ['property', 'method'],
        'void_return' => false,  // хорошо бы включить, но заебешься обновлять существующие проекты
        'whitespace_after_comma_in_array' => true,
        'yoda_style' => true,
    ])
    ->setFinder($finder)
    ->setUsingCache(false)
;
