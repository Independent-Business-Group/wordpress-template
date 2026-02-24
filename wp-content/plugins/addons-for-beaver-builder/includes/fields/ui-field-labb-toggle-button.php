<#
    var field = data.field,
        name = data.name,
        value = data.value,
        toggle = '',
        atts = '';

    // Toggle data
    if (field.toggle) {
        toggle = " data-toggle='" + JSON.stringify(field.toggle) + "'";
    }

    if (('undefined' === typeof value || '' === value) && field.default) {
        value = field.default;
    }
#>

<div class="labb-toggle-button fl-field" data-type="select" data-preview="{{data.preview}}">
    <select class="labb_toggle_button_select labb_button_toggle" name="{{name}}" {{{toggle}}}>
        <option value="yes"<# if ( value === 'yes' ) { #> selected="selected" <# } #>><?php echo __('Yes', 'livemesh-bb-addons') ?></option>
        <option value="no"<# if ( value === 'no' ) { #> selected="selected" <# } #>><?php echo __('No', 'livemesh-bb-addons'); ?></option>
    </select>
</div>