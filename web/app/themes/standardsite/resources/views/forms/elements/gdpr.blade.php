@if($gdpr)
  <x-forms.checkbox name="gdpr" value="Jag godkänner integritetspolicy" required label="{!! $gdpr['label'] !!}"/>
@endif