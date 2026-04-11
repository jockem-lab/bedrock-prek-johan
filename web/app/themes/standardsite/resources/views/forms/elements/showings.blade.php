@if($showings && !empty($showings['showings']))
  <div class="showings grid gap-x-5 gap-y-3 grid-cols-[max-content_max-content] mb-3">
    @foreach($showings['showings'] as $showing)
      <div class="showingcheckbox">
        <x-forms.checkbox name="showing" data-exclusive="showing" label="{{ $showing->date }}" value="{{ $showing->showingid }}" required data-cy="showing-radio" />
      </div>
      <div class="optionselect">
        <select name="slot" data-belongsto="showing-{{ $showing->showingid }}" disabled>
          @foreach($showing->slots as $slot)
            <option value="{{ $slot->id }}" {{ $slot->disabled ? 'disabled' : '' }}>{{ $slot->label }}</option>
          @endforeach
        </select>
      </div>
    @endforeach
  </div>
@endif