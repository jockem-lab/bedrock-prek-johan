<div id="corporationform" class="col-span-12 row mb-4">
  <div class="fasad-inquiry-form-holder contact-form-wrapper form-holder col-span-12">
    <x-html-forms :form="$form">
      {!! \PrekWebHelper\PrekWebHelper::getInstance()->form->honeypotField() !!}
      @if($inputs['corporationform'])
        @foreach($inputs['corporationform'] as $input)
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
