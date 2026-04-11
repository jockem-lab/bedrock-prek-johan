<input
    name="{{ $input['name'] }}"
    type="{{ $input['type'] }}"
    {{ isset($input['placeholder']) ? 'placeholder=' . $input['placeholder'] : '' }}
    {{ isset($input['required']) && $input['required'] ? 'required' : '' }}
    {{ isset($input['value']) ? 'value=' . $input['value'] : ''}}
>