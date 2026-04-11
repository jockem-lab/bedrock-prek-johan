<textarea
    name="{{ $input['name'] }}"
    {!! $input['attributes'] !!}
    {{ isset($input['placeholder']) ? 'placeholder=' . $input['placeholder'] . '' : '' }}
    {{ isset($input['required']) && $input['required'] ? 'required' : '' }}
></textarea>