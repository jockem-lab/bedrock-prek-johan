<div id="intresseanmalan" class="col-span-12 row mb-4" data-cy="interest-form">
  <div class="interest-form-wrapper fasad-inquiry-form-holder form-holder col-span-12 md:col-span-6 md:col-start-4">
    <h3 class="text-center">Intresseanmälan</h3>
    <x-html-forms :form="$form">
      {!! \PrekWebHelper\PrekWebHelper::getInstance()->form->honeypotField() !!}
      @if($listing)
        <input type="hidden" name="fkobject" value="{{ $listing->id }}">
      @endif
      @if($inputs['interestform'])
        @foreach($inputs['interestform'] as $input)
          @include('forms.elements.' . $input['fieldType'])
        @endforeach
      @endif
      <div class="col-span-12 row">
        <div class="col-span-12 md:col-span-8">
          @include('forms.elements.gdpr')
        </div>
        <div class="col-span-12 md:col-span-4">
          @include('forms.elements.submit')
        </div>
      </div>
    </x-html-forms>
  </div>
</div>
